<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LiveTrainingMessage;
use App\Models\LiveTrainingParticipant;
use App\Models\LiveTrainingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LiveTrainingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $canManageLive = $this->canManageLive($user);

        $coursesForStart = collect();
        $accessiblePrograms = collect();
        $activeSessions = [];

        if ($canManageLive) {
            $coursesForStart = Course::query()
                ->where('is_published', true)
                ->orderBy('title')
                ->get(['id', 'title', 'slug']);
        } else {
            $accessiblePrograms = Course::query()
                ->where('is_published', true)
                ->whereHas('enrollments', function ($query) use ($user) {
                    $query->where('user_id', $user->id)->where('status', '!=', 'cancelled');
                })
                ->orderBy('title')
                ->get(['id', 'title', 'slug']);
        }

        foreach (($canManageLive ? $coursesForStart : $accessiblePrograms) as $course) {
            $session = $this->getSessionData($course->id);
            if ($session !== null) {
                $activeSessions[$course->id] = $session;
            }
        }

        return view('live-training.index', [
            'jitsiDomain' => config('services.jitsi.domain', 'meet.jit.si'),
            'user' => $user,
            'isAdmin' => $canManageLive,
            'coursesForStart' => $coursesForStart,
            'accessiblePrograms' => $accessiblePrograms,
            'activeSessions' => $activeSessions,
            'selectedCourse' => null,
            'roomName' => null,
            'sessionStartedByAdmin' => false,
            'liveSessionId' => null,
        ]);
    }

    public function start(Request $request)
    {
        $user = $request->user();
        if (! $this->canManageLive($user)) {
            abort(403, 'Seuls les roles autorises peuvent demarrer un live.');
        }

        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:contents,id'],
            'room' => ['nullable', 'string', 'max:80'],
        ]);

        $course = Course::query()->findOrFail($validated['course_id']);
        $room = $this->sanitizeRoomName((string) ($validated['room'] ?? ''), $course);

        $session = LiveTrainingSession::query()->create([
            'course_id' => $course->id,
            'started_by' => $user->id,
            'room_name' => $room,
            'started_at' => now(),
            'status' => 'active',
        ]);

        Cache::put($this->sessionKey($course->id), [
            'session_id' => $session->id,
            'room' => $room,
            'started_by' => $user->id,
            'started_at' => now()->toDateTimeString(),
        ], now()->addHours(8));

        return redirect()->route('live-training.show', $course->slug);
    }

    public function show(Request $request, Course $course)
    {
        $user = $request->user();
        $canManageLive = $this->canManageLive($user);

        if (! $canManageLive && ! $this->userCanJoinCourse($user?->id, $course)) {
            abort(403, 'Vous n’avez pas accès à ce programme.');
        }

        $sessionData = $this->getSessionData($course->id);
        if ($sessionData === null) {
            return redirect()->route('live-training.index')
                ->with('error', 'Aucun live actif pour ce programme. Attendez le démarrage par un administrateur.');
        }

        $session = LiveTrainingSession::query()->find($sessionData['session_id'] ?? 0);
        if (! $session) {
            return redirect()->route('live-training.index')
                ->with('error', 'La session live est introuvable.');
        }

        $this->registerJoin($session, $user->id, $user->name, null);

        return view('live-training.index', [
            'jitsiDomain' => config('services.jitsi.domain', 'meet.jit.si'),
            'user' => $user,
            'isAdmin' => $canManageLive,
            'coursesForStart' => collect(),
            'accessiblePrograms' => collect(),
            'activeSessions' => [$course->id => $sessionData],
            'selectedCourse' => $course,
            'roomName' => $sessionData['room'],
            'sessionStartedByAdmin' => true,
            'liveSessionId' => $session->id,
        ]);
    }

    public function stop(Request $request, Course $course)
    {
        $user = $request->user();
        if (! $this->canManageLive($user)) {
            abort(403, 'Seuls les roles autorises peuvent arreter un live.');
        }

        $sessionData = $this->getSessionData($course->id);
        if ($sessionData && ! empty($sessionData['session_id'])) {
            $session = LiveTrainingSession::query()->find((int) $sessionData['session_id']);
            if ($session) {
                $session->update([
                    'status' => 'ended',
                    'ended_at' => now(),
                ]);

                LiveTrainingParticipant::query()
                    ->where('session_id', $session->id)
                    ->whereNull('left_at')
                    ->get()
                    ->each(function (LiveTrainingParticipant $participant) {
                        $leftAt = now();
                        $duration = max(0, $participant->joined_at?->diffInSeconds($leftAt) ?? 0);
                        $participant->update([
                            'left_at' => $leftAt,
                            'duration_seconds' => max((int) $participant->duration_seconds, $duration),
                        ]);
                    });
            }
        }

        Cache::forget($this->sessionKey($course->id));

        return redirect()->route('live-training.index')->with('success', 'Le live a été arrêté.');
    }

    public function participantPing(Request $request, Course $course)
    {
        $user = $request->user();
        $canManageLive = $this->canManageLive($user);

        if (! $canManageLive && ! $this->userCanJoinCourse($user?->id, $course)) {
            abort(403);
        }

        $validated = $request->validate([
            'jitsi_participant_id' => ['nullable', 'string', 'max:120'],
            'display_name' => ['nullable', 'string', 'max:255'],
        ]);

        $sessionData = $this->getSessionData($course->id);
        if (! $sessionData || empty($sessionData['session_id'])) {
            return response()->json(['ok' => false, 'message' => 'Aucune session active'], 422);
        }

        $session = LiveTrainingSession::query()->find((int) $sessionData['session_id']);
        if (! $session || $session->status !== 'active') {
            return response()->json(['ok' => false, 'message' => 'Session inactive'], 422);
        }

        $participant = $this->registerJoin(
            $session,
            $user->id,
            (string) ($validated['display_name'] ?? $user->name),
            $validated['jitsi_participant_id'] ?? null
        );

        return response()->json(['ok' => true, 'participant_id' => $participant->id]);
    }

    public function participantLeave(Request $request, Course $course)
    {
        $user = $request->user();
        $validated = $request->validate([
            'jitsi_participant_id' => ['nullable', 'string', 'max:120'],
        ]);

        $sessionData = $this->getSessionData($course->id);
        if (! $sessionData || empty($sessionData['session_id'])) {
            return response()->json(['ok' => true]);
        }

        $sessionId = (int) $sessionData['session_id'];
        $query = LiveTrainingParticipant::query()
            ->where('session_id', $sessionId)
            ->where('user_id', $user->id)
            ->whereNull('left_at');

        if (! empty($validated['jitsi_participant_id'])) {
            $query->where('jitsi_participant_id', $validated['jitsi_participant_id']);
        }

        $participant = $query->latest('joined_at')->first();
        if ($participant) {
            $leftAt = now();
            $duration = max(0, $participant->joined_at?->diffInSeconds($leftAt) ?? 0);
            $participant->update([
                'left_at' => $leftAt,
                'duration_seconds' => max((int) $participant->duration_seconds, $duration),
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function storeMessage(Request $request, Course $course)
    {
        $user = $request->user();
        $canManageLive = $this->canManageLive($user);

        if (! $canManageLive && ! $this->userCanJoinCourse($user?->id, $course)) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'sender_name' => ['nullable', 'string', 'max:255'],
            'message_type' => ['nullable', 'string', 'max:30'],
        ]);

        $sessionData = $this->getSessionData($course->id);
        if (! $sessionData || empty($sessionData['session_id'])) {
            return response()->json(['ok' => false], 422);
        }

        LiveTrainingMessage::query()->create([
            'session_id' => (int) $sessionData['session_id'],
            'user_id' => $user->id,
            'sender_name' => (string) ($validated['sender_name'] ?? $user->name),
            'message' => trim((string) $validated['message']),
            'message_type' => (string) ($validated['message_type'] ?? 'chat'),
            'sent_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    private function sessionKey(int $courseId): string
    {
        return 'live_training:course:'.$courseId;
    }

    private function getSessionData(int $courseId): ?array
    {
        $value = Cache::get($this->sessionKey($courseId));

        if (is_array($value) && ! empty($value['room'])) {
            return $value;
        }

        $dbSession = LiveTrainingSession::query()
            ->where('course_id', $courseId)
            ->where('status', 'active')
            ->latest('started_at')
            ->first();

        if (! $dbSession) {
            return null;
        }

        $sessionData = [
            'session_id' => $dbSession->id,
            'room' => $dbSession->room_name,
            'started_by' => $dbSession->started_by,
            'started_at' => optional($dbSession->started_at)->toDateTimeString(),
        ];

        Cache::put($this->sessionKey($courseId), $sessionData, now()->addHours(8));

        return $sessionData;
    }

    private function sanitizeRoomName(string $roomInput, Course $course): string
    {
        $room = Str::of($roomInput)
            ->trim()
            ->replaceMatches('/\s+/', '-')
            ->replaceMatches('/[^a-zA-Z0-9\-_]/', '')
            ->trim('-_')
            ->lower()
            ->value();

        if ($room === '') {
            $room = Str::slug($course->slug ?: $course->title).'-live';
        }

        return Str::limit($room, 80, '');
    }

    private function userCanJoinCourse(?int $userId, Course $course): bool
    {
        if (! $userId) {
            return false;
        }

        return $course->enrollments()
            ->where('user_id', $userId)
            ->where('status', '!=', 'cancelled')
            ->exists();
    }

    private function canManageLive($user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasRole(['super_user', 'admin', 'provider']);
    }

    private function registerJoin(LiveTrainingSession $session, int $userId, ?string $displayName, ?string $jitsiParticipantId): LiveTrainingParticipant
    {
        $participant = LiveTrainingParticipant::query()
            ->where('session_id', $session->id)
            ->where('user_id', $userId)
            ->whereNull('left_at')
            ->latest('joined_at')
            ->first();

        if (! $participant) {
            $participant = LiveTrainingParticipant::query()->create([
                'session_id' => $session->id,
                'user_id' => $userId,
                'display_name' => $displayName,
                'jitsi_participant_id' => $jitsiParticipantId,
                'joined_at' => now(),
            ]);
        } else {
            $participant->update([
                'display_name' => $displayName ?: $participant->display_name,
                'jitsi_participant_id' => $jitsiParticipantId ?: $participant->jitsi_participant_id,
            ]);
        }

        return $participant;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseDownload;
use App\Models\Enrollment;
use App\Models\Order;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function dashboard()
    {
        $student = auth()->user();

        $recentEnrollments = $student->enrollments()
            ->with(['course.instructor', 'course.category', 'order', 'course.downloads'])
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        $allEnrollments = $student->enrollments()
            ->with(['course'])
            ->get();

        $totalCertificates = $student->certificates()->count();

        // Charger les compteurs de téléchargements pour chaque cours récent
        foreach ($recentEnrollments as $enrollment) {
            $course = $enrollment->course;
            if ($course && $course->is_downloadable) {
                $course->user_downloads_count = CourseDownload::where('course_id', $course->id)
                    ->where('user_id', $student->id)
                    ->count() ?? 0;
                $course->total_downloads_count = CourseDownload::where('course_id', $course->id)->count() ?? 0;
            }
        }

        $certificates = $student->certificates()
            ->with(['course'])
            ->latest()
            ->limit(4)
            ->get();

        $recentOrders = $student->orders()
            ->with(['enrollments.course'])
            ->latest()
            ->limit(4)
            ->get();

        $recommendedCourses = Course::published()
            ->with(['instructor', 'category'])
            ->whereNotIn('id', $allEnrollments->pluck('course_id')->filter()->all())
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'total_courses' => $allEnrollments->count(),
            'active_courses' => $allEnrollments->where('status', 'active')->count(),
            'completed_courses' => $allEnrollments->where('status', 'completed')->count(),
            'certificates_earned' => $totalCertificates,
            'average_progress' => $allEnrollments->count() > 0 ? round($allEnrollments->avg('progress'), 1) : 0,
            'learning_minutes' => $allEnrollments->sum(function ($enrollment) {
                return $enrollment->course ? $enrollment->course->duration : 0;
            }),
        ];

        return view('students.dashboard', [
            'user' => $student,
            'enrollments' => $recentEnrollments,
            'certificates' => $certificates,
            'stats' => $stats,
            'orders' => $recentOrders,
            'recommendedCourses' => $recommendedCourses,
            'lastUpdatedEnrollment' => $recentEnrollments->first(),
        ]);
    }

    public function courses(Request $request)
    {
        $student = auth()->user();

        $statusFilter = $request->get('status', 'all');
        $search = $request->get('q');

        $baseQuery = $student->enrollments()
            ->with(['course.instructor', 'course.category', 'order', 'course.downloads'])
            ->orderByDesc('updated_at');

        if ($statusFilter !== 'all') {
            $baseQuery->where('status', $statusFilter);
        }

        if (!empty($search)) {
            $baseQuery->whereHas('course', function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            });
        }

        $enrollments = $baseQuery->paginate(12)->withQueryString();

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            if ($course && $course->is_downloadable) {
                $course->user_downloads_count = CourseDownload::where('course_id', $course->id)
                    ->where('user_id', $student->id)
                    ->count();
                $course->total_downloads_count = CourseDownload::where('course_id', $course->id)->count();
            }
        }

        $enrollmentCounts = $student->enrollments()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $courseSummary = [
            'total' => $enrollmentCounts->sum(),
            'active' => $enrollmentCounts->get('active', 0),
            'completed' => $enrollmentCounts->get('completed', 0),
            'suspended' => $enrollmentCounts->get('suspended', 0),
            'cancelled' => $enrollmentCounts->get('cancelled', 0),
        ];

        return view('students.courses', [
            'user' => $student,
            'enrollments' => $enrollments,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'courseSummary' => $courseSummary,
        ]);
    }

    public function learn(Course $course)
    {
        $student = auth()->user();
        
        // Vérifier si l'étudiant est inscrit au cours
        $enrollment = $course->getEnrollmentFor($student->id);
        
        if (!$enrollment) {
            return redirect()->route('courses.show', $course->slug)
                ->with('error', 'Vous devez être inscrit à ce cours pour y accéder.');
        }

        $course->load(['sections.lessons']);
        
        return view('students.learn', compact('course', 'enrollment'));
    }

    public function enroll(Request $request, Course $course)
    {
        $student = auth()->user();
        $redirectTo = $request->input('redirect_to', 'learn');
        
        // Vérifier si l'étudiant n'est pas déjà inscrit
        if ($course->isEnrolledBy($student->id)) {
            return $this->redirectAfterEnrollment(
                $course,
                $redirectTo,
                'Vous êtes déjà inscrit à ce cours.',
                'info'
            );
        }

        // Pour les cours payants, vérifier que l'utilisateur a acheté le cours
        if (!$course->is_free) {
            $hasPurchased = Order::where('user_id', $student->id)
                ->where('status', 'paid')
                ->whereHas('orderItems', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })
                ->exists();

            if (!$hasPurchased) {
                return redirect()->route('courses.show', $course->slug)
                    ->with('error', 'Veuillez acheter ce cours pour y accéder.');
            }
        }

        // Créer l'inscription si le cours est gratuit ou si l'achat est confirmé
        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        // Envoyer l'email de confirmation d'inscription
        try {
            $student->notify(new \App\Notifications\CourseEnrolled($course));
        } catch (\Exception $e) {
            \Log::error("Erreur lors de l'envoi de l'email d'inscription: " . $e->getMessage());
        }

        $successMessage = $redirectTo === 'download'
            ? 'Inscription réussie ! Téléchargement en cours...'
            : 'Inscription réussie ! Vous pouvez commencer à apprendre.';

        return $this->redirectAfterEnrollment($course, $redirectTo, $successMessage);
    }

    protected function redirectAfterEnrollment(Course $course, string $redirectTo, string $message, string $flashType = 'success')
    {
        $route = match ($redirectTo) {
            'download' => ['name' => 'courses.download', 'params' => ['course' => $course->slug]],
            'dashboard' => ['name' => 'student.courses', 'params' => []],
            default => ['name' => 'learning.course', 'params' => ['course' => $course->slug]],
        };

        return redirect()->route($route['name'], $route['params'])->with($flashType, $message);
    }
    public function certificates()
    {
        $student = auth()->user();

        $certificateBase = $student->certificates();
        $certificatesQuery = (clone $certificateBase)
            ->with(['course.instructor'])
            ->latest('issued_at');

        $certificates = $certificatesQuery->paginate(12);

        return view('students.certificates', [
            'user' => $student,
            'certificates' => $certificates,
            'certificateSummary' => [
                'total' => (clone $certificateBase)->count(),
                'recent' => (clone $certificateBase)->with('course')->latest('issued_at')->first(),
                'issued_this_year' => (clone $certificateBase)->whereYear('issued_at', now()->year)->count(),
            ],
        ]);
    }
}

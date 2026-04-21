<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseDownload;
use App\Models\Order;
use App\Models\User;

class ReviewEligibilityService
{
    /**
     * @return array{can_review: bool, message: string}
     */
    public function evaluate(User $user, Course $course): array
    {
        if ($course->is_in_person_program ?? false) {
            return ['can_review' => true, 'message' => ''];
        }

        if ($course->is_downloadable) {
            if ($course->is_free) {
                $hasDownloaded = CourseDownload::where('content_id', $course->id)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($hasDownloaded) {
                    return ['can_review' => true, 'message' => ''];
                }

                return [
                    'can_review' => false,
                    'message' => 'Vous devez avoir téléchargé ce contenu au moins une fois pour pouvoir le noter.',
                ];
            }

            $hasPurchased = Order::where('user_id', $user->id)
                ->whereIn('status', ['paid', 'completed'])
                ->whereHas('orderItems', function ($query) use ($course) {
                    $query->where('content_id', $course->id);
                })
                ->exists();

            if ($hasPurchased || $course->isEnrolledBy($user->id)) {
                return ['can_review' => true, 'message' => ''];
            }

            return [
                'can_review' => false,
                'message' => 'Vous devez avoir acheté ce contenu (ou bénéficier d\'un accès accordé) pour pouvoir le noter.',
            ];
        }

        if ($course->is_free) {
            if ($course->isEnrolledBy($user->id)) {
                return ['can_review' => true, 'message' => ''];
            }

            return [
                'can_review' => false,
                'message' => 'Vous devez être inscrit à ce contenu pour pouvoir le noter.',
            ];
        }

        $hasPurchased = Order::where('user_id', $user->id)
            ->whereIn('status', ['paid', 'completed'])
            ->whereHas('orderItems', function ($query) use ($course) {
                $query->where('content_id', $course->id);
            })
            ->exists();

        if ($hasPurchased || $course->isEnrolledBy($user->id)) {
            return ['can_review' => true, 'message' => ''];
        }

        return [
            'can_review' => false,
            'message' => 'Vous devez avoir acheté ce contenu (ou bénéficier d\'un accès accordé) pour pouvoir le noter.',
        ];
    }
}

<?php

namespace App\Traits;

trait CourseStatistics
{
    /**
     * Ajouter les statistiques calculées à une collection de cours
     */
    public function addCourseStatistics($courses)
    {
        return $courses->map(function($course) {
            $course->stats = $course->getCourseStats();
            return $course;
        });
    }

    /**
     * Obtenir les statistiques d'un cours spécifique
     */
    public function getCourseStatistics($course)
    {
        return $course->getCourseStats();
    }
}

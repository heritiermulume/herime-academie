<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Certificate;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function dashboard()
    {
        $student = auth()->user();
        
        $enrollments = $student->enrollments()
            ->with(['course.instructor', 'course.category', 'order', 'course.downloads'])
            ->withCount(['course' => function($query) {
                $query->withCount('downloads');
            }])
            ->latest()
            ->limit(6)
            ->get();
        
        // Charger les compteurs de téléchargements pour chaque cours
        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            if ($course && $course->is_downloadable) {
                // Compter les téléchargements de ce cours par cet utilisateur
                $course->user_downloads_count = \App\Models\CourseDownload::where('course_id', $course->id)
                    ->where('user_id', $student->id)
                    ->count();
                // Compter tous les téléchargements de ce cours
                $course->total_downloads_count = \App\Models\CourseDownload::where('course_id', $course->id)
                    ->count();
            }
        }

        $recent_courses = Course::published()
            ->whereIn('id', $enrollments->pluck('course_id'))
            ->with(['instructor', 'category'])
            ->withCount('enrollments')
            ->latest()
            ->limit(4)
            ->get();

        $certificates = $student->certificates()
            ->with(['course'])
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'total_courses' => $enrollments->count(),
            'completed_courses' => $enrollments->where('status', 'completed')->count(),
            'certificates_earned' => $certificates->count(),
            'learning_hours' => $enrollments->sum(function($enrollment) {
                return $enrollment->course ? $enrollment->course->duration : 0;
            }),
        ];

        return view('students.dashboard', compact('enrollments', 'recent_courses', 'certificates', 'stats'));
    }

    public function courses()
    {
        $student = auth()->user();
        
        $enrollments = $student->enrollments()
            ->with(['course.instructor', 'course.category', 'order', 'course.downloads'])
            ->withCount(['course' => function($query) {
                $query->withCount('downloads');
            }])
            ->latest()
            ->paginate(25);
        
        // Charger les compteurs de téléchargements pour chaque cours
        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            if ($course && $course->is_downloadable) {
                // Compter les téléchargements de ce cours par cet utilisateur
                $course->user_downloads_count = \App\Models\CourseDownload::where('course_id', $course->id)
                    ->where('user_id', $student->id)
                    ->count();
                // Compter tous les téléchargements de ce cours
                $course->total_downloads_count = \App\Models\CourseDownload::where('course_id', $course->id)
                    ->count();
            }
        }

        return view('students.courses', compact('enrollments'));
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

    public function enroll(Course $course)
    {
        $student = auth()->user();
        
        // Vérifier si l'étudiant n'est pas déjà inscrit
        if ($course->isEnrolledBy($student->id)) {
            return redirect()->route('student.courses.learn', $course->slug)
                ->with('info', 'Vous êtes déjà inscrit à ce cours.');
        }

        // Créer l'inscription
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        // Note: Le compteur d'étudiants est maintenant calculé dynamiquement via les enrollments

        return redirect()->route('student.courses.learn', $course->slug)
            ->with('success', 'Inscription réussie ! Vous pouvez maintenant commencer à apprendre.');
    }

    public function certificates()
    {
        $student = auth()->user();
        
        $certificates = $student->certificates()
            ->with(['course.instructor'])
            ->latest()
            ->paginate(12);

        return view('students.certificates', compact('certificates'));
    }
}

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
            ->with(['course.instructor', 'course.category'])
            ->latest()
            ->limit(6)
            ->get();

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
                return $enrollment->course->duration;
            }),
        ];

        return view('students.dashboard', compact('enrollments', 'recent_courses', 'certificates', 'stats'));
    }

    public function courses()
    {
        $student = auth()->user();
        
        $enrollments = $student->enrollments()
            ->with(['course.instructor', 'course.category'])
            ->latest()
            ->paginate(12);

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

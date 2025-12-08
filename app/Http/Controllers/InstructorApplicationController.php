<?php

namespace App\Http\Controllers;

use App\Models\InstructorApplication;
use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class InstructorApplicationController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Afficher la page d'explication du rôle formateur
     */
    public function index()
    {
        if (Auth::check()) {
            // Si l'utilisateur est déjà formateur, rediriger vers le dashboard
            if (Auth::user()->role === 'instructor') {
                return redirect()->route('instructor.dashboard')
                    ->with('info', 'Vous êtes déjà formateur.');
            }

            // Récupérer la candidature si elle existe
            $application = InstructorApplication::where('user_id', Auth::id())->first();
        } else {
            $application = null;
        }

        // Toujours afficher la page, même si l'utilisateur a déjà postulé
        // La vue gère l'affichage des messages et boutons appropriés
        return view('instructor-application.index', compact('application'));
    }

    /**
     * Afficher le formulaire de candidature (étape 1)
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour postuler.');
        }

        // Si l'utilisateur est déjà formateur, rediriger
        if (Auth::user()->role === 'instructor') {
            return redirect()->route('instructor.dashboard');
        }

        // Vérifier si une candidature existe déjà
        $application = InstructorApplication::where('user_id', Auth::id())->first();
        if ($application && !$application->canBeEdited()) {
            return redirect()->route('instructor-application.status', $application);
        }

        return view('instructor-application.create', [
            'application' => $application
        ]);
    }

    /**
     * Sauvegarder les informations de base (étape 1)
     */
    public function storeStep1(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Vérifier que l'utilisateur a un numéro de téléphone
        if (!$user->phone) {
            return redirect()->back()
                ->with('error', 'Veuillez renseigner votre numéro de téléphone dans votre profil avant de continuer.')
                ->withInput();
        }

        // Vérifier si une candidature existe déjà
        $existingApplication = InstructorApplication::where('user_id', Auth::id())->first();
        
        // Si une candidature existe et qu'elle ne peut plus être modifiée, rediriger
        if ($existingApplication && !$existingApplication->canBeEdited()) {
            return redirect()->route('instructor-application.status', $existingApplication)
                ->with('error', 'Cette candidature ne peut plus être modifiée car elle a déjà été soumise.');
        }

        $request->validate([
            'professional_experience' => 'required|string|min:50|max:2000',
            'teaching_experience' => 'required|string|min:50|max:2000',
        ]);

        $application = InstructorApplication::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'phone' => $user->phone, // Récupérer depuis le profil utilisateur
                'professional_experience' => $request->professional_experience,
                'teaching_experience' => $request->teaching_experience,
                'status' => 'pending',
            ]
        );

        return redirect()->route('instructor-application.step2', $application);
    }

    /**
     * Afficher l'étape 2 (spécialisations et formation)
     */
    public function step2(InstructorApplication $application)
    {
        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$application->canBeEdited()) {
            return redirect()->route('instructor-application.status', $application);
        }

        return view('instructor-application.step2', compact('application'));
    }

    /**
     * Sauvegarder l'étape 2
     */
    public function storeStep2(Request $request, InstructorApplication $application)
    {
        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$application->canBeEdited()) {
            return redirect()->route('instructor-application.status', $application)
                ->with('error', 'Cette candidature ne peut plus être modifiée car elle a déjà été soumise.');
        }

        $request->validate([
            'specializations' => 'required|string|min:20|max:1000',
            'education_background' => 'required|string|min:20|max:1000',
        ]);

        $application->update([
            'specializations' => $request->specializations,
            'education_background' => $request->education_background,
        ]);

        return redirect()->route('instructor-application.step3', $application);
    }

    /**
     * Afficher l'étape 3 (documents)
     */
    public function step3(InstructorApplication $application)
    {
        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$application->canBeEdited()) {
            return redirect()->route('instructor-application.status', $application);
        }

        return view('instructor-application.step3', compact('application'));
    }

    /**
     * Sauvegarder l'étape 3 (upload des documents)
     */
    public function storeStep3(Request $request, InstructorApplication $application)
    {
        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$application->canBeEdited()) {
            return redirect()->route('instructor-application.status', $application)
                ->with('error', 'Cette candidature ne peut plus être modifiée car elle a déjà été soumise.');
        }

        // Validation : soit les fichiers sont uploadés directement, soit les chemins sont fournis (upload par chunks)
        $request->validate([
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'cv_path' => 'nullable|string',
            'motivation_letter' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'motivation_letter_path' => 'nullable|string',
        ]);

        // Vérifier qu'au moins un des deux (fichier ou chemin) est fourni pour chaque document
        if (!$request->hasFile('cv') && !$request->filled('cv_path')) {
            return back()->with('error', 'Le CV est requis.')->withInput();
        }

        if (!$request->hasFile('motivation_letter') && !$request->filled('motivation_letter_path')) {
            return back()->with('error', 'La lettre de motivation est requise.')->withInput();
        }

        try {
            // Upload du CV
            if ($request->hasFile('cv')) {
                $cvPath = $this->fileUploadService->upload(
                    $request->file('cv'),
                    'instructor-applications/cv',
                    $application->cv_path
                );
                $application->cv_path = $cvPath['path'];
            } elseif ($request->filled('cv_path')) {
                $cvPath = $request->input('cv_path');
                
                // Vérifier que le chemin est valide et dans le bon répertoire
                if (str_starts_with($cvPath, \App\Services\FileUploadService::TEMPORARY_BASE_PATH . '/')) {
                    // Le fichier est dans le dossier temporaire, le promouvoir vers le dossier final
                    try {
                        $finalPath = $this->fileUploadService->promoteTemporaryFile(
                            $cvPath,
                            'instructor-applications/cv'
                        );
                        $application->cv_path = $finalPath;
                    } catch (\Exception $e) {
                        Log::error('Error promoting temporary CV file', [
                            'application_id' => $application->id,
                            'temporary_path' => $cvPath,
                            'error' => $e->getMessage(),
                        ]);
                        // Si la promotion échoue, utiliser le chemin temporaire comme fallback
                        $application->cv_path = $cvPath;
                    }
                } else {
                    // Le chemin n'est pas temporaire, l'utiliser directement
                    $application->cv_path = $cvPath;
                }
            }

            // Upload de la lettre de motivation
            if ($request->hasFile('motivation_letter')) {
                $letterPath = $this->fileUploadService->upload(
                    $request->file('motivation_letter'),
                    'instructor-applications/motivation-letters',
                    $application->motivation_letter_path
                );
                $application->motivation_letter_path = $letterPath['path'];
            } elseif ($request->filled('motivation_letter_path')) {
                $letterPath = $request->input('motivation_letter_path');
                
                // Vérifier que le chemin est valide et dans le bon répertoire
                if (str_starts_with($letterPath, \App\Services\FileUploadService::TEMPORARY_BASE_PATH . '/')) {
                    // Le fichier est dans le dossier temporaire, le promouvoir vers le dossier final
                    try {
                        $finalPath = $this->fileUploadService->promoteTemporaryFile(
                            $letterPath,
                            'instructor-applications/motivation-letters'
                        );
                        $application->motivation_letter_path = $finalPath;
                    } catch (\Exception $e) {
                        Log::error('Error promoting temporary motivation letter file', [
                            'application_id' => $application->id,
                            'temporary_path' => $letterPath,
                            'error' => $e->getMessage(),
                        ]);
                        // Si la promotion échoue, utiliser le chemin temporaire comme fallback
                        $application->motivation_letter_path = $letterPath;
                    }
                } else {
                    // Le chemin n'est pas temporaire, l'utiliser directement
                    $application->motivation_letter_path = $letterPath;
                }
            }

            // Passer la candidature en 'under_review' lors de la soumission finale
            // Cela empêchera toute modification ou abandon ultérieur
            // Important : une candidature soumise ne peut plus être modifiée ou abandonnée
            $application->status = 'under_review';
            $application->save();

            return redirect()->route('instructor-application.status', $application)
                ->with('success', 'Votre candidature a été soumise avec succès !');

        } catch (\Exception $e) {
            Log::error('Error uploading application documents', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Erreur lors de l\'upload des documents. Veuillez réessayer.');
        }
    }

    /**
     * Afficher le statut de la candidature
     */
    public function status(InstructorApplication $application)
    {
        if ($application->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        return view('instructor-application.status', compact('application'));
    }

    public function abandon(Request $request, InstructorApplication $application)
    {
        if (!Auth::check() || $application->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$application->canBeEdited()) {
            return redirect()->route('instructor-application.status', $application)
                ->with('error', 'Cette candidature ne peut plus être abandonnée.');
        }

        if ($application->cv_path) {
            Storage::delete($application->cv_path);
        }

        if ($application->motivation_letter_path) {
            Storage::delete($application->motivation_letter_path);
        }

        $application->delete();

        return redirect()->route('instructor-application.create')
            ->with('success', 'Votre candidature a été réinitialisée. Vous pouvez recommencer depuis le début.');
    }

    /**
     * Télécharger le CV
     */
    public function downloadCv(InstructorApplication $application)
    {
        if ($application->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        if (!$application->cv_path) {
            abort(404);
        }

        return Storage::download($application->cv_path);
    }

    /**
     * Télécharger la lettre de motivation
     */
    public function downloadMotivationLetter(InstructorApplication $application)
    {
        if ($application->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        if (!$application->motivation_letter_path) {
            abort(404);
        }

        return Storage::download($application->motivation_letter_path);
    }
}

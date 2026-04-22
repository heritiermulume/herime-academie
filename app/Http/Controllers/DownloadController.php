<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseDownload;
use App\Models\CourseLesson;
use App\Models\Enrollment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\EnrollmentReceiptPdfService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function __construct(
        private EnrollmentReceiptPdfService $receiptPdfService
    ) {}

    /**
     * Download course materials (ou reçu d'inscription pour présentiel / contenu sans fichier)
     */
    public function course(Course $course)
    {
        // Vérifier que le cours est publié
        if (! $course->is_published) {
            abort(404, 'Ce cours n\'est pas disponible.');
        }

        // Vérifier si l'utilisateur est connecté
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour télécharger ce cours.');
        }

        $userId = Auth::id();

        // Cours en présentiel : uniquement le reçu (priorité sur tout le reste)
        if ($course->is_in_person_program ?? false) {
            $enrollment = $this->getEnrollmentForReceipt($course, $userId);
            if (! $enrollment) {
                return back()->with('error', 'Vous devez être inscrit à ce programme pour télécharger le reçu.');
            }

            return $this->downloadEnrollmentReceipt($enrollment);
        }

        // Cours téléchargeable : priorité au contenu (fichier ou ZIP des sections/leçons)
        if ($course->is_downloadable) {
            if (! $this->hasAccessToCourse($course, $userId)) {
                if (! $course->is_sale_enabled) {
                    return back()->with('error', 'Ce cours n\'est pas actuellement disponible.');
                }
                if ($course->is_free) {
                    return back()->with('error', 'Vous devez être inscrit à ce cours pour le télécharger.');
                }

                return back()->with('error', 'Vous devez acheter ce cours pour le télécharger.');
            }

            // Si un fichier de téléchargement spécifique est défini et existe, le télécharger
            if ($course->download_file_path) {
                $specificFileResponse = $this->tryDownloadSpecificFile($course);
                if ($specificFileResponse) {
                    $this->recordDownload($course, 'file');

                    return $specificFileResponse;
                }
                // Fichier manquant : télécharger le reçu à la place
                $enrollment = $this->getEnrollmentForReceipt($course, $userId);
                if ($enrollment) {
                    return $this->downloadEnrollmentReceipt($enrollment);
                }

                return back()->with('error', 'Le fichier de téléchargement n\'existe plus sur le serveur.');
            }

            // Sinon, créer un ZIP avec tout le contenu du cours (sections et leçons)
            $zipResponse = $this->tryDownloadCourseAsZip($course);
            if ($zipResponse) {
                $this->recordDownload($course, 'zip');

                return $zipResponse;
            }

            // Fallback : reçu d'inscription (téléchargeable sans fichier ni sections/leçons)
            $enrollment = $this->getEnrollmentForReceipt($course, $userId);
            if ($enrollment) {
                return $this->downloadEnrollmentReceipt($enrollment);
            }

            return back()->with('error', 'Aucun contenu téléchargeable disponible pour ce cours.');
        }

        // Contenu en ligne non téléchargeable avec reçu PDF : uniquement le reçu d'inscription (même logique que le présentiel côté fichier)
        if ($course->isEnrollmentReceiptOnly()) {
            $enrollment = $this->getEnrollmentForReceipt($course, $userId);
            if (! $enrollment) {
                return back()->with('error', 'Vous devez être inscrit pour télécharger le reçu d\'inscription.');
            }

            return $this->downloadEnrollmentReceipt($enrollment);
        }

        return back()->with('error', 'Ce cours n\'est pas disponible en téléchargement.');
    }

    /**
     * Obtenir l'inscription de l'utilisateur pour générer le reçu
     */
    private function getEnrollmentForReceipt(Course $course, int $userId): ?Enrollment
    {
        return Enrollment::where('content_id', $course->id)
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'completed'])
            ->with(['user', 'course.provider', 'course.category'])
            ->first();
    }

    /**
     * Télécharger le reçu d'inscription en PDF
     */
    private function downloadEnrollmentReceipt(Enrollment $enrollment): Response
    {
        $pdfContent = $this->receiptPdfService->generatePdfContent($enrollment);
        $filename = 'recu-inscription-'.\Illuminate\Support\Str::slug($enrollment->course->title).'.pdf';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => strlen($pdfContent),
        ]);
    }

    /**
     * Tenter de télécharger le fichier spécifique (retourne null si fichier absent)
     */
    private function tryDownloadSpecificFile(Course $course)
    {
        return $this->downloadSpecificFile($course, true);
    }

    /**
     * Tenter de télécharger le cours en ZIP (retourne null si aucun contenu)
     */
    private function tryDownloadCourseAsZip(Course $course)
    {
        $result = $this->downloadCourseAsZip($course);

        return $result ?: null;
    }

    /**
     * Enregistrer un téléchargement
     */
    private function recordDownload(Course $course, $downloadType = 'zip')
    {
        try {
            $ipAddress = request()->ip();
            $userAgent = request()->userAgent();

            // Important perf: ne pas faire d'appel réseau externe dans le chemin critique du download.
            // La géolocalisation peut être traitée plus tard via job asynchrone si nécessaire.
            $geoInfo = [];

            CourseDownload::create([
                'content_id' => $course->id,
                'user_id' => Auth::id(),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'country' => $geoInfo['country'] ?? null,
                'country_name' => $geoInfo['country_name'] ?? null,
                'city' => $geoInfo['city'] ?? null,
                'region' => $geoInfo['region'] ?? null,
                'download_type' => $downloadType,
            ]);
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas bloquer le téléchargement
            Log::warning('Erreur lors de l\'enregistrement du téléchargement: '.$e->getMessage());
        }
    }

    /**
     * Obtenir les informations géographiques depuis une adresse IP
     */
    private function getGeoInfoFromIp($ipAddress)
    {
        // Pour les IPs locales, retourner null
        if ($this->isPrivateIpAddress($ipAddress)) {
            return [];
        }

        try {
            // Essayer d'utiliser un service gratuit comme ipapi.co ou ip-api.com
            // Utilisons ip-api.com (gratuit, limite 45 requêtes/min)
            $response = @file_get_contents("http://ip-api.com/json/{$ipAddress}?fields=status,country,countryCode,city,regionName", false, stream_context_create([
                'http' => [
                    'timeout' => 2, // Timeout de 2 secondes
                ],
            ]));

            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['status']) && $data['status'] === 'success') {
                    return [
                        'country' => $data['countryCode'] ?? null,
                        'country_name' => $data['country'] ?? null,
                        'city' => $data['city'] ?? null,
                        'region' => $data['regionName'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Erreur lors de la récupération des informations géographiques: '.$e->getMessage());
        }

        return [];
    }

    /**
     * Télécharger un fichier spécifique défini pour le cours
     */
    private function downloadSpecificFile(Course $course, bool $returnNullWhenMissing = false)
    {
        $filePath = null;
        $fileName = null;

        if (! filter_var($course->download_file_path, FILTER_VALIDATE_URL)) {
            ['filePath' => $filePath, 'fileName' => $fileName] = $this->resolveDownloadFileFromStoragePath($course->download_file_path);
        } else {
            // C'est une URL externe - rediriger vers cette URL
            return redirect($course->download_file_path);
        }

        if (! $filePath || ! file_exists($filePath)) {
            if ($returnNullWhenMissing) {
                return null;
            }

            return back()->with('error', 'Le fichier de téléchargement n\'existe plus sur le serveur.');
        }

        // Si pas de nom de fichier spécifique, utiliser un nom par défaut
        if (! $fileName) {
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $fileName = $this->sanitizeFileName($course->title).'.'.($extension ?: 'zip');
        }

        return $this->buildDownloadResponseWithOptionalAcceleration($filePath, $fileName);
    }

    /**
     * Résoudre un chemin de fichier uploadé (formats actuels + anciens formats possibles).
     *
     * @return array{filePath:?string,fileName:?string}
     */
    private function resolveDownloadFileFromStoragePath(?string $storedPath): array
    {
        if (! $storedPath) {
            return ['filePath' => null, 'fileName' => null];
        }

        $disk = Storage::disk('local');
        $cleanPath = ltrim(trim($storedPath), '/');

        if ($cleanPath === '') {
            return ['filePath' => null, 'fileName' => null];
        }

        $candidates = array_values(array_unique(array_filter([
            $cleanPath,
            preg_replace('#^storage/#', '', $cleanPath),
            preg_replace('#^app/private/#', '', $cleanPath),
            preg_replace('#^private/#', '', $cleanPath),
        ], fn ($value) => is_string($value) && $value !== '')));

        foreach ($candidates as $candidate) {
            if ($disk->exists($candidate)) {
                return [
                    'filePath' => $disk->path($candidate),
                    'fileName' => basename($candidate),
                ];
            }
        }

        return ['filePath' => null, 'fileName' => null];
    }

    /**
     * Retourne une réponse download Laravel avec accélération serveur optionnelle.
     * Fallback automatique: si l'en-tête n'est pas supporté, Laravel envoie le fichier normalement.
     */
    private function buildDownloadResponseWithOptionalAcceleration(string $filePath, string $fileName)
    {
        $response = response()->download($filePath, $fileName);
        $lastModified = gmdate('D, d M Y H:i:s', filemtime($filePath)).' GMT';
        $etag = '"'.md5($filePath.'|'.filesize($filePath).'|'.filemtime($filePath)).'"';

        // Headers utiles pour les gros fichiers: reprise, validation conditionnelle, cache privé.
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('ETag', $etag);
        $response->headers->set('Last-Modified', $lastModified);
        $response->headers->set('Cache-Control', 'private, max-age=0, must-revalidate');

        // Désactivé par défaut : certains hébergements mutualisés servent un mauvais payload
        // (ex: page HTML) quand X-Accel-Redirect est envoyé sans configuration serveur adaptée.
        if ((bool) env('DOWNLOAD_ACCEL_ENABLED', false)) {
            $privateRoot = rtrim(storage_path('app/private'), '/');
            if (str_starts_with($filePath, $privateRoot.'/')) {
                $relativePath = substr($filePath, strlen($privateRoot));
                $internalPrefix = rtrim((string) env('DOWNLOAD_ACCEL_INTERNAL_PREFIX', '/protected-downloads'), '/');
                $internalPath = $internalPrefix.$relativePath;

                $response->headers->set('X-Accel-Redirect', $internalPath);
            }
        }

        // Désactivé par défaut pour éviter les réponses erronées si mod_xsendfile n'est pas actif.
        if ((bool) env('DOWNLOAD_X_SENDFILE_ENABLED', false)) {
            $response->headers->set('X-Sendfile', $filePath);
        }

        return $response;
    }

    /**
     * Télécharger le cours complet sous forme de ZIP
     */
    private function downloadCourseAsZip(Course $course)
    {
        // Récupérer toutes les sections et leçons du cours (téléchargement = accès complet)
        $course->load([
            'provider',
            'category',
            'sections' => function ($query) {
                $query->orderBy('sort_order');
            },
            'sections.lessons' => function ($query) {
                $query->orderBy('sort_order');
            },
        ]);

        // Aucune leçon : le ZIP serait vide (README/index puis suppression). Passer directement au reçu côté appelant.
        if (! $course->sections->some(fn ($section) => $section->lessons->isNotEmpty())) {
            return null;
        }

        $zipResult = $this->getCachedOrCreateCourseZip($course);
        if ($zipResult === null) {
            return null;
        }

        if ($zipResult === false) {
            return back()->with('error', 'Impossible de créer le fichier ZIP.');
        }

        ['zipPath' => $zipPath, 'zipFileName' => $zipFileName] = $zipResult;

        return response()->download($zipPath, $zipFileName);
    }

    /**
     * Retourne un ZIP de cours prêt à servir (cache disque + rebuild si nécessaire).
     *
     * @return array{zipPath:string,zipFileName:string}|false|null
     */
    private function getCachedOrCreateCourseZip(Course $course)
    {
        $cacheDir = storage_path('app/temp/course-zips');
        if (! File::exists($cacheDir)) {
            File::makeDirectory($cacheDir, 0755, true);
        }

        $contentSignature = $this->buildCourseZipContentSignature($course);
        $zipFileName = 'cours-'.$course->slug.'-'.substr($contentSignature, 0, 12).'.zip';
        $zipPath = $cacheDir.'/'.$zipFileName;

        // Réutiliser directement le ZIP si déjà prêt.
        if (File::exists($zipPath) && File::size($zipPath) > 0) {
            return [
                'zipPath' => $zipPath,
                'zipFileName' => $zipFileName,
            ];
        }

        // Nettoyer les anciennes versions de ZIP du même cours.
        foreach (glob($cacheDir.'/cours-'.$course->slug.'-*.zip') ?: [] as $oldZipPath) {
            if ($oldZipPath !== $zipPath && File::exists($oldZipPath)) {
                @unlink($oldZipPath);
            }
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            return false;
        }

        // Ajouter un fichier README avec les informations du cours
        $readmeContent = $this->generateCourseReadme($course);
        $zip->addFromString('README.txt', $readmeContent);

        // Ajouter les leçons organisées par sections
        $hasContent = false;
        foreach ($course->sections as $section) {
            if ($section->lessons->isNotEmpty()) {
                $sectionPath = 'Section '.$section->sort_order.' - '.$this->sanitizeFileName($section->title).'/';

                foreach ($section->lessons as $lesson) {
                    if ($this->addLessonToZip($zip, $lesson, $sectionPath)) {
                        $hasContent = true;
                    }
                }
            }
        }

        // Ajouter les ressources supplémentaires si elles existent
        $this->addCourseResources($zip, $course);

        $zip->close();

        if (! $hasContent) {
            @unlink($zipPath);

            return null;
        }

        return [
            'zipPath' => $zipPath,
            'zipFileName' => $zipFileName,
        ];
    }

    /**
     * Génère une signature qui change quand le contenu ZIP source change.
     */
    private function buildCourseZipContentSignature(Course $course): string
    {
        $latestLessonUpdate = $course->sections
            ->flatMap(fn ($section) => $section->lessons)
            ->max('updated_at');

        $latestSectionUpdate = $course->sections->max('updated_at');
        $latestResourceUpdate = $course->updated_at;

        return sha1(implode('|', [
            $course->id,
            optional($latestLessonUpdate)->timestamp ?? 0,
            optional($latestSectionUpdate)->timestamp ?? 0,
            optional($latestResourceUpdate)->timestamp ?? 0,
            $course->sections->count(),
            $course->sections->sum(fn ($section) => $section->lessons->count()),
        ]));
    }

    /**
     * Vérifie si l'adresse IP est privée / locale.
     */
    private function isPrivateIpAddress(?string $ipAddress): bool
    {
        if (! $ipAddress) {
            return true;
        }

        if (in_array($ipAddress, ['127.0.0.1', '::1'], true)) {
            return true;
        }

        if (str_starts_with($ipAddress, '192.168.') || str_starts_with($ipAddress, '10.')) {
            return true;
        }

        return str_starts_with($ipAddress, '172.');
    }

    /**
     * Download a specific lesson file
     */
    public function lesson(Course $course, CourseLesson $lesson)
    {
        // Vérifier que le cours est publié
        if (! $course->is_published) {
            abort(404, 'Ce cours n\'est pas disponible.');
        }

        // Vérifier si l'utilisateur est connecté
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour télécharger ce fichier.');
        }

        // Vérifier que le cours est téléchargeable
        if (! $course->is_downloadable) {
            return back()->with('error', 'Ce cours n\'est pas disponible en téléchargement.');
        }

        // Vérifier l'accès au cours selon le type (gratuit/payant)
        if (! $this->hasAccessToCourse($course, Auth::id())) {
            if ($course->is_free) {
                return back()->with('error', 'Vous devez être inscrit à ce cours pour télécharger ce fichier.');
            } else {
                return back()->with('error', 'Vous devez acheter ce cours pour télécharger ce fichier.');
            }
        }

        // Vérifier si la leçon appartient au cours
        if ($lesson->content_id !== $course->id) {
            return back()->with('error', 'Cette leçon n\'appartient pas à ce cours.');
        }

        // Fichier principal de la leçon (file_path ou content_url sur disque privé)
        $filePath = null;
        $relative = $lesson->getStoredLessonFileRelativePath();
        if ($relative) {
            $disk = Storage::disk('local');
            $filePath = $disk->path($relative);
        }

        if (! $filePath || ! file_exists($filePath)) {
            return back()->with('error', 'Fichier non disponible sur le site.');
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $fileName = 'Leçon '.$lesson->sort_order.' - '.$this->sanitizeFileName($lesson->title);
        if ($extension !== '') {
            $fileName .= '.'.$extension;
        }

        return response()->download($filePath, $fileName);
    }

    /**
     * Vérifier si l'utilisateur a accès au cours
     *
     * Règles spécifiques :
     * - Pour les produits téléchargeables (cours téléchargeables) :
     *   - Si le produit est payant : il suffit d'avoir une commande payée (pas besoin d'inscription)
     *   - Si le produit est gratuit : aucun achat ni inscription n'est requis, seul le fait d'être connecté suffit
     * - Pour les cours NON téléchargeables : on conserve la logique existante basée sur l'inscription
     */
    private function hasAccessToCourse(Course $course, $userId)
    {
        if ($course->is_downloadable) {
            // Pour les produits téléchargeables payants : commande payée, ou inscription (ex. abonnement Membre)
            // avec période suffisante si le contenu est réservé aux abonnés.
            if (! $course->is_free) {
                $hasPaidOrder = \App\Models\Order::where('user_id', $userId)
                    ->whereIn('status', ['paid', 'completed'])
                    ->whereHas('orderItems', function ($query) use ($course) {
                        $query->where('content_id', $course->id);
                    })
                    ->exists();

                if ($hasPaidOrder) {
                    return true;
                }

                $user = User::find($userId);
                if ($user && $course->isEnrolledBy($userId)) {
                    return SubscriptionPlan::userMeetsMemberPeriodForSubscriptionGatedContent($user, $course);
                }

                return false;
            }

            // Pour les produits téléchargeables gratuits : aucun achat ni inscription n'est requis,
            // le simple fait d'être connecté et que le cours soit publié suffit.
            return true;
        }

        // Pour les cours NON téléchargeables, vérifier l'inscription normalement
        // Pour les cours gratuits, vérifier seulement l'inscription
        if ($course->is_free) {
            return $course->isEnrolledBy($userId);
        }

        // Pour les cours payants, vérifier d'abord l'inscription
        $enrollment = $course->enrollments()
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if ($enrollment) {
            // Si l'utilisateur est inscrit, il a accès au téléchargement
            // Vérifier si la commande associée est payée (si elle existe)
            if ($enrollment->order_id) {
                $order = $enrollment->order;
                // Si la commande existe et est payée, accès autorisé
                if ($order && in_array($order->status, ['paid', 'completed'])) {
                    return true;
                }
            } else {
                // Si pas d'order_id mais enrollment existe, c'est probablement un cours gratuit ou inscription manuelle
                // Autoriser l'accès
                return true;
            }
        }

        // Si pas d'inscription, vérifier si l'utilisateur a acheté le cours via une commande payée
        $hasPurchased = \App\Models\Order::where('user_id', $userId)
            ->whereIn('status', ['paid', 'completed'])
            ->whereHas('orderItems', function ($query) use ($course) {
                $query->where('content_id', $course->id);
            })
            ->exists();

        return $hasPurchased;
    }

    /**
     * Générer le contenu README pour le cours
     */
    private function generateCourseReadme(Course $course)
    {
        $content = 'COURS: '.$course->title."\n";
        $content .= '='.str_repeat('=', strlen($course->title))."\n\n";

        $content .= "Description:\n";
        $content .= ($course->description ?? '')."\n\n";

        $content .= 'Prestataire: '.($course->provider?->name ?? '—')."\n";
        $content .= 'Durée: '.($course->duration ?? 0)." minutes\n";
        $content .= 'Niveau: '.ucfirst((string) ($course->level ?? ''))."\n";
        $content .= 'Catégorie: '.($course->category?->name ?? '—')."\n\n";

        $content .= "STRUCTURE DU COURS:\n";
        $content .= str_repeat('-', 20)."\n\n";

        foreach ($course->sections as $section) {
            $content .= 'Section '.$section->sort_order.': '.$section->title."\n";
            if ($section->description) {
                $content .= '  '.$section->description."\n";
            }
            $content .= "\n";

            foreach ($section->lessons as $lesson) {
                $content .= '  Leçon '.$lesson->sort_order.': '.$lesson->title."\n";
                if ($lesson->description) {
                    $content .= '    '.$lesson->description."\n";
                }
                $content .= '    Type: '.ucfirst((string) ($lesson->type ?? ''))."\n";
                $content .= '    Durée: '.$lesson->duration." minutes\n\n";
            }
        }

        $content .= 'Téléchargé le: '.now()->format('d/m/Y à H:i')."\n";
        $content .= "Source: Herime Academie\n";

        return $content;
    }

    /**
     * Ajouter une leçon au ZIP
     */
    private function addLessonToZip($zip, $lesson, $sectionPath)
    {
        $added = false;
        $disk = Storage::disk('local'); // Utiliser le stockage privé

        // Ajouter le fichier de la leçon s'il existe (file_path)
        $sourceForExtension = $lesson->file_path ?: $lesson->content_url;
        $extension = $sourceForExtension ? pathinfo($sourceForExtension, PATHINFO_EXTENSION) : 'bin';
        $lessonFileName = 'Leçon '.$lesson->sort_order.' - '.$this->sanitizeFileName($lesson->title);
        if ($extension) {
            $lessonFileName .= '.'.$extension;
        }

        if ($lesson->file_path && ! filter_var($lesson->file_path, FILTER_VALIDATE_URL)) {
            $cleanPath = ltrim($lesson->file_path, '/');
            if ($disk->exists($cleanPath)) {
                $zip->addFile($disk->path($cleanPath), $sectionPath.$lessonFileName);

                return true;
            }
        }

        if ($lesson->content_url && ! filter_var($lesson->content_url, FILTER_VALIDATE_URL)) {
            $cleanPath = ltrim($lesson->content_url, '/');
            if ($disk->exists($cleanPath)) {
                $zip->addFile($disk->path($cleanPath), $sectionPath.$lessonFileName);

                return true;
            }
        }

        // Ajouter le contenu texte s'il existe
        if ($lesson->content_text) {
            $textFileName = 'Leçon '.$lesson->sort_order.' - '.$this->sanitizeFileName($lesson->title).' - Contenu.txt';
            $zip->addFromString($sectionPath.$textFileName, $lesson->content_text);
            $added = true;
        }

        // Ajouter l'URL de la vidéo s'il s'agit d'une leçon vidéo (YouTube ou autre URL externe)
        if ($lesson->type === 'video') {
            $videoInfo = 'Titre: '.$lesson->title."\n";
            $videoInfo .= 'Description: '.($lesson->description ?? 'Aucune description')."\n";
            $videoInfo .= 'Durée: '.$lesson->duration." minutes\n";

            // Ajouter l'URL YouTube si disponible
            if ($lesson->youtube_video_id) {
                $videoInfo .= 'URL YouTube: https://www.youtube.com/watch?v='.$lesson->youtube_video_id."\n";
            }

            // Ajouter l'URL de contenu si c'est une URL externe
            if ($lesson->content_url && filter_var($lesson->content_url, FILTER_VALIDATE_URL)) {
                $videoInfo .= 'URL de la vidéo: '.$lesson->content_url."\n";
            }

            $videoFileName = 'Leçon '.$lesson->sort_order.' - '.$this->sanitizeFileName($lesson->title).' - Info Vidéo.txt';
            $zip->addFromString($sectionPath.$videoFileName, $videoInfo);
            $added = true;
        }

        // Pour les leçons de type text, quiz, assignment, etc., ajouter les informations
        if (in_array($lesson->type, ['text', 'quiz', 'assignment'])) {
            $infoFileName = 'Leçon '.$lesson->sort_order.' - '.$this->sanitizeFileName($lesson->title).' - Info.txt';
            $infoContent = 'Titre: '.$lesson->title."\n";
            $infoContent .= 'Type: '.ucfirst($lesson->type)."\n";
            $infoContent .= 'Description: '.($lesson->description ?? 'Aucune description')."\n";
            if ($lesson->duration) {
                $infoContent .= 'Durée: '.$lesson->duration." minutes\n";
            }
            $zip->addFromString($sectionPath.$infoFileName, $infoContent);
            $added = true;
        }

        // Si aucune ressource ajoutée mais que la leçon existe, ajouter au minimum les métadonnées
        if (! $added) {
            $infoFileName = 'Leçon '.$lesson->sort_order.' - '.$this->sanitizeFileName($lesson->title).' - Info.txt';
            $infoContent = 'Titre: '.$lesson->title."\n";
            $infoContent .= 'Type: '.ucfirst($lesson->type ?? 'texte')."\n";
            $infoContent .= 'Description: '.($lesson->description ?? 'Aucune description')."\n";
            $zip->addFromString($sectionPath.$infoFileName, $infoContent);
            $added = true;
        }

        return $added;
    }

    /**
     * Ajouter les ressources supplémentaires du cours
     */
    private function addCourseResources($zip, $course)
    {
        // Ajouter l'image/thumbnail du cours si elle existe
        if ($course->thumbnail && ! filter_var($course->thumbnail, FILTER_VALIDATE_URL)) {
            $thumbnailPath = ltrim($course->thumbnail, '/');
            $disk = Storage::disk('local');

            if ($disk->exists($thumbnailPath)) {
                $fullPath = $disk->path($thumbnailPath);
                $zip->addFile($fullPath, 'image-cours.'.pathinfo($fullPath, PATHINFO_EXTENSION));
            }
        }

        if ($course->image_path && $course->image_path !== $course->thumbnail && ! filter_var($course->image_path, FILTER_VALIDATE_URL)) {
            $imagePath = ltrim($course->image_path, '/');
            $disk = Storage::disk('local');

            if ($disk->exists($imagePath)) {
                $fullPath = $disk->path($imagePath);
                $zip->addFile($fullPath, 'image-cours-2.'.pathinfo($fullPath, PATHINFO_EXTENSION));
            }
        }

        // Ajouter un fichier d'index HTML pour une meilleure navigation
        $indexContent = $this->generateIndexHtml($course);
        $zip->addFromString('index.html', $indexContent);
    }

    /**
     * Générer un fichier index HTML pour le cours
     */
    private function generateIndexHtml(Course $course)
    {
        $html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>'.htmlspecialchars($course->title).'</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .header { border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 30px; }
        .section { margin-bottom: 30px; }
        .lesson { margin-left: 20px; margin-bottom: 15px; }
        .lesson-type { background: #f8f9fa; padding: 2px 8px; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>'.htmlspecialchars($course->title).'</h1>
        <p><strong>Prestataire:</strong> '.htmlspecialchars($course->provider?->name ?? '—').'</p>
        <p><strong>Durée:</strong> '.($course->duration ?? 0).' minutes</p>
        <p><strong>Niveau:</strong> '.ucfirst((string) ($course->level ?? '')).'</p>
    </div>
    
    <div class="description">
        <h2>Description</h2>
        <p>'.nl2br(htmlspecialchars((string) ($course->description ?? ''))).'</p>
    </div>
    
    <div class="content">
        <h2>Structure du cours</h2>';

        foreach ($course->sections as $section) {
            $html .= '<div class="section">
                <h3>Section '.$section->sort_order.': '.htmlspecialchars($section->title).'</h3>';

            if ($section->description) {
                $html .= '<p>'.htmlspecialchars($section->description).'</p>';
            }

            foreach ($section->lessons as $lesson) {
                $html .= '<div class="lesson">
                    <h4>Leçon '.$lesson->sort_order.': '.htmlspecialchars($lesson->title).'</h4>
                    <span class="lesson-type">'.ucfirst((string) ($lesson->type ?? '')).'</span>
                    <span> - '.$lesson->duration.' minutes</span>';

                if ($lesson->description) {
                    $html .= '<p>'.htmlspecialchars($lesson->description).'</p>';
                }

                $html .= '</div>';
            }

            $html .= '</div>';
        }

        $html .= '</div>
    
    <div class="footer">
        <p><em>Téléchargé le '.now()->format('d/m/Y à H:i').' depuis Herime Academie</em></p>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Nettoyer un nom de fichier pour éviter les caractères problématiques
     */
    private function sanitizeFileName($fileName)
    {
        // Remplacer les caractères problématiques
        $fileName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $fileName);

        // Limiter la longueur
        return substr($fileName, 0, 100);
    }
}

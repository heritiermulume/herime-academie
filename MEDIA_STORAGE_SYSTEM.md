# 🎬 Système de Stockage Multimédia Professionnel
## Inspiré de YouTube, Facebook, Instagram

Ce système implémente une architecture de stockage de fichiers multimédia similaire aux grandes plateformes, avec :
- ✅ Stockage binaire optimisé (BLOB)
- ✅ Métadonnées séparées (JSON/SQL)
- ✅ Object Storage avec clés uniques
- ✅ Multiples résolutions pour vidéos (360p, 480p, 720p, 1080p)
- ✅ Streaming HLS avec manifestes M3U8
- ✅ Thumbnails automatiques
- ✅ Checksums pour intégrité
- ✅ CDN-ready

---

## 📊 Architecture du Système

### 1. Structure de Base de Données

#### Table `media_files` (Métadonnées)
```sql
{
  "file_id": "mf_a7f9c2e4b1...",      -- Clé unique (comme YouTube)
  "filename": "conference_tech.mp4",   -- Nom original
  "mime_type": "video/mp4",
  "media_type": "video",               -- image, video, audio
  "size": 245812000,                   -- En bytes
  "storage_bucket": "media/videos/user_123",
  "storage_path": "storage/media/videos/user_123/mf_xxx/original.mp4",
  "checksum_md5": "a7f9c2e4b1...",
  "checksum_sha256": "...",
  "metadata": {                         -- JSON flexible
    "video": {
      "duration": 360.5,
      "width": 1920,
      "height": 1080,
      "codec": "h264",
      "bitrate": 5000000
    }
  },
  "status": "ready"                     -- uploading, processing, ready, failed
}
```

#### Table `media_variants` (Résolutions/Formats)
```sql
{
  "media_file_id": 1,
  "variant_type": "720p",               -- 360p, 480p, 720p, 1080p, thumbnail
  "format": "m3u8",                     -- m3u8, mp4, jpg, webp
  "storage_path": "storage/media/videos/user_123/mf_xxx/720p/playlist.m3u8",
  "size": 45000000,
  "width": 1280,
  "height": 720,
  "bitrate": 2500000,
  "codec": "h264",
  "status": "ready"
}
```

### 2. Structure de Stockage Fichiers

```
storage/app/public/
└── media/
    ├── images/
    │   └── user_123/
    │       └── mf_abc123xyz/
    │           ├── original.jpg
    │           ├── thumbnail.jpg
    │           ├── small.jpg
    │           ├── medium.jpg
    │           └── large.jpg
    │
    └── videos/
        └── user_456/
            └── mf_def456xyz/
                ├── original.mp4
                ├── thumbnail.jpg
                ├── 360p/
                │   ├── playlist.m3u8
                │   ├── segment_000.ts
                │   ├── segment_001.ts
                │   └── ...
                ├── 720p/
                │   ├── playlist.m3u8
                │   └── segment_*.ts
                ├── 1080p/
                │   ├── playlist.m3u8
                │   └── segment_*.ts
                └── master.m3u8          -- Manifeste HLS master
```

---

## 🚀 Installation et Configuration

### 1. Installer les dépendances

```bash
# FFmpeg (pour traitement vidéo)
# Mac
brew install ffmpeg

# Ubuntu/Debian
sudo apt install ffmpeg

# Intervention Image (pour traitement d'images)
composer require intervention/image
```

### 2. Exécuter les migrations

```bash
php artisan migrate
```

### 3. Créer le lien symbolique storage

```bash
php artisan storage:link
```

### 4. Vérifier FFmpeg

```php
$videoService = new \App\Services\VideoProcessingService();
if ($videoService->isFFmpegAvailable()) {
    echo "✅ FFmpeg est disponible";
} else {
    echo "❌ FFmpeg n'est pas installé";
}
```

---

## 💻 Utilisation

### Upload d'une Image

```php
use App\Services\MediaStorageService;

$mediaService = new MediaStorageService();

$mediaFile = $mediaService->upload(
    file: $request->file('image'),
    mediaType: 'image',
    userId: auth()->id(),
    entityType: 'Course',  // Optionnel
    entityId: $course->id  // Optionnel
);

// Récupérer l'URL
$url = $mediaFile->url;  // URL de l'original

// Récupérer une résolution spécifique
$thumbnailUrl = $mediaFile->getThumbnailUrl();
$mediumUrl = $mediaFile->getUrl('medium');
```

### Upload d'une Vidéo

```php
$mediaFile = $mediaService->upload(
    file: $request->file('video'),
    mediaType: 'video',
    userId: auth()->id()
);

// La vidéo sera traitée automatiquement :
// - Extraction métadonnées
// - Génération thumbnail
// - Encodage multiples résolutions (360p, 720p, 1080p)
// - Création segments HLS
// - Génération manifeste M3U8

// Récupérer le manifeste HLS pour streaming
$hlsUrl = $mediaFile->getHlsManifestUrl();

// Récupérer les résolutions disponibles
$resolutions = $mediaFile->getAvailableResolutions();
// Retourne: [360, 720, 1080]

// Récupérer la durée
$duration = $mediaFile->getDuration(); // En secondes
$formatted = $mediaFile->getDurationFormatted(); // "05:30"
```

### Afficher une Vidéo avec HLS.js

```html
<video id="video" controls style="width: 100%;"></video>

<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
  var video = document.getElementById('video');
  var videoSrc = '{{ $mediaFile->getHlsManifestUrl() }}';
  
  if (Hls.isSupported()) {
    var hls = new Hls();
    hls.loadSource(videoSrc);
    hls.attachMedia(video);
  } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
    // Safari natif
    video.src = videoSrc;
  }
</script>
```

### Récupérer les Médias d'une Entité

```php
// Récupérer toutes les images d'un cours
$images = $mediaService->getEntityMedia('Course', $courseId, 'image');

// Récupérer toutes les vidéos
$videos = $mediaService->getEntityMedia('Course', $courseId, 'video');
```

### Supprimer un Média

```php
$mediaService->delete($mediaFile);
// Supprime le fichier original + toutes les variantes + enregistrements DB
```

---

## 🎨 Exemples d'Intégration

### 1. Bannières (Remplacement du système actuel)

```php
// Dans BannerController
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required',
        'image' => 'required|image|max:20480',
        'mobile_image' => 'nullable|image|max:20480',
    ]);
    
    $mediaService = new MediaStorageService();
    
    // Upload image desktop
    $desktopMedia = $mediaService->upload(
        $request->file('image'),
        'image',
        auth()->id()
    );
    
    // Upload image mobile
    $mobileMedia = null;
    if ($request->hasFile('mobile_image')) {
        $mobileMedia = $mediaService->upload(
            $request->file('mobile_image'),
            'image',
            auth()->id()
        );
    }
    
    $banner = Banner::create([
        'title' => $validated['title'],
        'image_id' => $desktopMedia->id,
        'mobile_image_id' => $mobileMedia?->id,
    ]);
    
    return redirect()->back();
}

// Dans Banner Model
public function desktopImage()
{
    return $this->belongsTo(MediaFile::class, 'image_id');
}

public function mobileImage()
{
    return $this->belongsTo(MediaFile::class, 'mobile_image_id');
}

// Dans la vue
<img src="{{ $banner->desktopImage->getUrl('large') }}" 
     srcset="{{ $banner->desktopImage->getUrl('small') }} 640w,
             {{ $banner->desktopImage->getUrl('medium') }} 1280w,
             {{ $banner->desktopImage->getUrl('large') }} 1920w"
     sizes="(max-width: 640px) 100vw, (max-width: 1280px) 50vw, 33vw">
```

### 2. Vidéos de Cours

```php
// Dans CourseLessonController
public function storeVideo(Request $request, CourseLesson $lesson)
{
    $mediaService = new MediaStorageService();
    
    $videoMedia = $mediaService->upload(
        $request->file('video'),
        'video',
        auth()->id(),
        'CourseLesson',
        $lesson->id
    );
    
    $lesson->update([
        'video_id' => $videoMedia->id,
    ]);
    
    // Le traitement se fait automatiquement
    // L'admin peut suivre le statut: uploading → processing → ready
    
    return response()->json([
        'success' => true,
        'media_id' => $videoMedia->id,
        'status' => $videoMedia->status,
    ]);
}

// Dans CourseLesson Model
public function video()
{
    return $this->belongsTo(MediaFile::class, 'video_id');
}

// Dans la vue du lecteur
@if($lesson->video && $lesson->video->isReady())
    <div class="video-player">
        <video id="lesson-video" controls></video>
        
        <div class="video-info">
            <p>Durée: {{ $lesson->video->getDurationFormatted() }}</p>
            <p>Résolutions: {{ implode(', ', $lesson->video->getAvailableResolutions()) }}p</p>
        </div>
    </div>
    
    <script>
        var hls = new Hls();
        hls.loadSource('{{ $lesson->video->getHlsManifestUrl() }}');
        hls.attachMedia(document.getElementById('lesson-video'));
    </script>
@elseif($lesson->video && $lesson->video->status === 'processing')
    <p>⏳ Vidéo en cours de traitement...</p>
@endif
```

---

## ⚡ Avantages du Système

### 1. Performance
- ✅ Multiples résolutions = adaptation automatique à la bande passante
- ✅ Streaming HLS = lecture instantanée sans téléchargement complet
- ✅ Thumbnails = prévisualisation rapide
- ✅ CDN-ready = distribution mondiale facile

### 2. Économie de Stockage
- ✅ Encodage optimisé (H.264 CRF 23)
- ✅ Bitrates adaptés par résolution
- ✅ Compression intelligente des images (quality 85%)

### 3. Sécurité
- ✅ Checksums MD5/SHA256 = vérification intégrité
- ✅ Validation MIME type
- ✅ Isolation par utilisateur (user_123/)
- ✅ Soft delete = récupération possible

### 4. Scalabilité
- ✅ Structure extensible (facile de passer à S3/GCS)
- ✅ Métadonnées JSON flexibles
- ✅ Traitement asynchrone (jobs queues)
- ✅ Système de variantes illimité

---

## 🔄 Migration du Système Actuel

### Étape 1 : Migrer les bannières existantes

```php
php artisan make:command MigrateBannersToMediaSystem
```

```php
// Dans le command
public function handle()
{
    $mediaService = new MediaStorageService();
    
    Banner::whereNotNull('image')->chunk(100, function ($banners) use ($mediaService) {
        foreach ($banners as $banner) {
            // Copier l'image existante vers le nouveau système
            $oldPath = public_path(str_replace('storage/', 'storage/app/public/', $banner->image));
            
            if (file_exists($oldPath)) {
                $uploadedFile = new \Illuminate\Http\UploadedFile(
                    $oldPath,
                    basename($oldPath),
                    mime_content_type($oldPath),
                    null,
                    true
                );
                
                $mediaFile = $mediaService->upload(
                    $uploadedFile,
                    'image',
                    null,
                    'Banner',
                    $banner->id
                );
                
                $banner->update(['image_id' => $mediaFile->id]);
                
                $this->info("✅ Banner #{$banner->id} migrated");
            }
        }
    });
}
```

---

## 📈 Monitoring et Statistiques

```php
// Statistiques globales
$totalMedia = MediaFile::count();
$totalSize = MediaFile::sum('size');
$videoCount = MediaFile::videos()->count();
$imageCount = MediaFile::images()->count();

// Médias en traitement
$processing = MediaFile::where('status', 'processing')->count();

// Médias échoués
$failed = MediaFile::where('status', 'failed')->get();

// Espace disque utilisé par utilisateur
$userStorage = MediaFile::selectRaw('user_id, SUM(size) as total_size')
    ->groupBy('user_id')
    ->get();
```

---

## 🛠️ Configuration Avancée

### Personnaliser les Résolutions Vidéo

Modifier `VideoProcessingService::determineResolutions()`:

```php
protected function determineResolutions(int $originalHeight): array
{
    return [
        '240p' => ['height' => 240, 'bitrate' => '500k', 'audio_bitrate' => '64k'],
        '360p' => ['height' => 360, 'bitrate' => '800k', 'audio_bitrate' => '96k'],
        '480p' => ['height' => 480, 'bitrate' => '1200k', 'audio_bitrate' => '128k'],
        '720p' => ['height' => 720, 'bitrate' => '2500k', 'audio_bitrate' => '128k'],
        '1080p' => ['height' => 1080, 'bitrate' => '5000k', 'audio_bitrate' => '192k'],
        '1440p' => ['height' => 1440, 'bitrate' => '9000k', 'audio_bitrate' => '192k'],
        '2160p' => ['height' => 2160, 'bitrate' => '18000k', 'audio_bitrate' => '256k'],
    ];
}
```

### Utiliser Amazon S3

```php
// Dans config/filesystems.php
'disks' => [
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
    ],
],

// Dans MediaStorageService
protected function upload(...) {
    // Changer 'public' par 's3'
    $disk = Storage::disk('s3');
    // ...
}
```

---

## 🎯 Prochaines Étapes

1. ✅ Implémenter le système de base (FAIT)
2. ⏳ Créer les jobs asynchrones (ProcessVideoJob, OptimizeImageJob)
3. ⏳ Ajouter l'interface d'administration
4. ⏳ Migrer les bannières existantes
5. ⏳ Intégrer aux vidéos de cours
6. ⏳ Ajouter le player vidéo avec qualité adaptative
7. ⏳ Implémenter les analytics (vues, durée regardée)
8. ⏳ Ajouter le support CDN (CloudFlare, AWS CloudFront)

---

## 📚 Ressources

- [HLS Specification](https://developer.apple.com/streaming/)
- [FFmpeg Documentation](https://ffmpeg.org/documentation.html)
- [Intervention Image](http://image.intervention.io/)
- [HLS.js Player](https://github.com/video-dev/hls.js/)

---

**🎉 Votre plateforme est maintenant équipée d'un système de gestion multimédia de niveau entreprise !**


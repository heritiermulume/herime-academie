@php
    $p = $package ?? null;
    $highlights = old('marketing_highlights');
    if ($highlights === null) {
        $highlights = $p ? ($p->marketing_highlights ?? []) : [];
    }
    $highlights = array_values(array_filter($highlights, fn ($x) => true));
    while (count($highlights) < 5) {
        $highlights[] = '';
    }
    $benefits = old('marketing_benefits');
    if ($benefits === null) {
        $benefits = $p ? ($p->marketing_benefits ?? []) : [];
    }
    $benefits = array_values(array_filter($benefits, fn ($x) => true));
    while (count($benefits) < 5) {
        $benefits[] = '';
    }
    $selectedContentIds = old('content_ids', $p ? $p->contents->pluck('id')->all() : []);
@endphp

<div class="admin-form-grid">
    <div class="admin-form-card">
        <h5><i class="fas fa-box me-2"></i>Informations principales</h5>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control form-control-lg @error('title') is-invalid @enderror"
                       value="{{ old('title', $p->title ?? '') }}" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @if($p)
                <div class="col-md-6">
                    <label class="form-label fw-bold">Slug URL</label>
                    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                           value="{{ old('slug', $p->slug) }}">
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endif
            <div class="col-12">
                <label class="form-label fw-bold">Sous-titre</label>
                <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $p->subtitle ?? '') }}">
            </div>
            <div class="col-12">
                <label class="form-label fw-bold">Accroche marketing</label>
                <input type="text" name="marketing_headline" class="form-control"
                       value="{{ old('marketing_headline', $p->marketing_headline ?? '') }}"
                       placeholder="Ex : Maîtrisez la bureautique en un seul pack">
            </div>
            <div class="col-12">
                <label class="form-label fw-bold">Résumé court</label>
                <textarea name="short_description" class="form-control" rows="2">{{ old('short_description', $p->short_description ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label fw-bold">Description détaillée</label>
                <textarea name="description" class="form-control" rows="6">{{ old('description', $p->description ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="admin-form-card">
        <h5><i class="fas fa-layer-group me-2"></i>Contenus inclus <span class="text-danger">*</span></h5>
        <p class="text-muted small">Maintenez Ctrl (Cmd sur Mac) pour sélectionner plusieurs contenus. L’ordre suit la sélection.</p>
        <select name="content_ids[]" class="form-select @error('content_ids') is-invalid @enderror" multiple size="14">
            @foreach($courses as $course)
                <option value="{{ $course->id }}" @selected(in_array($course->id, $selectedContentIds, true))>
                    {{ $course->title }}
                </option>
            @endforeach
        </select>
        @error('content_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="admin-form-card">
        <h5><i class="fas fa-images me-2"></i>Couverture</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold" for="packageThumbnail">Image de couverture</label>
                @if($p && $p->thumbnail_url)
                    <div class="current-package-thumbnail mb-2 text-center">
                        <p class="small text-success mb-1"><i class="fas fa-check-circle me-1"></i>Image actuelle</p>
                        <img src="{{ $p->thumbnail_url }}" alt="" class="img-thumbnail rounded" style="max-height:120px;">
                    </div>
                @endif
                <div class="upload-zone package-upload-zone" id="packageThumbnailUploadZone">
                    <input type="file"
                           class="form-control d-none"
                           id="packageThumbnail"
                           name="thumbnail"
                           accept="image/jpeg,image/png,image/jpg,image/webp,image/gif"
                           onchange="handlePackageThumbnailUpload(this)">
                    <input type="hidden" id="thumbnail_chunk_path" name="thumbnail_chunk_path" value="{{ old('thumbnail_chunk_path') }}">
                    <input type="hidden" id="thumbnail_chunk_name" name="thumbnail_chunk_name" value="{{ old('thumbnail_chunk_name') }}">
                    <input type="hidden" id="thumbnail_chunk_size" name="thumbnail_chunk_size" value="{{ old('thumbnail_chunk_size') }}">
                    <div class="upload-placeholder text-center p-3" onclick="document.getElementById('packageThumbnail').click()">
                        <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-2"></i>
                        <p class="mb-1 small"><strong>Glissez-déposez une image ou cliquez pour parcourir</strong></p>
                        <p class="text-muted small mb-0">JPG, PNG, WEBP, GIF — max 5&nbsp;Mo · envoi par morceaux (comme les contenus)</p>
                    </div>
                    <div class="upload-preview d-none">
                        <p class="small text-info text-center mb-2"><i class="fas fa-eye me-1"></i>Nouvelle image</p>
                        <img src="" alt="" class="img-fluid rounded mx-auto d-block" style="max-width:100%;max-height:200px;">
                        <div class="upload-info mt-2 text-center">
                            <span class="badge bg-primary file-name"></span>
                            <span class="badge bg-info file-size"></span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2 d-block mx-auto" onclick="clearPackageThumbnail()">
                            <i class="fas fa-times me-1"></i>Annuler le remplacement
                        </button>
                    </div>
                </div>
                <div class="invalid-feedback d-block" id="packageThumbnailError" style="display:none;"></div>
                @error('thumbnail')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('thumbnail_chunk_path')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @if($p)
                    <small class="text-muted d-block mt-1">Laissez vide pour conserver l’image actuelle.</small>
                @endif
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold" for="packageCoverVideo">Vidéo de couverture (fichier)</label>
                @if($p && $p->cover_video && ! $p->isYoutubeCoverVideo() && ! filter_var($p->cover_video, FILTER_VALIDATE_URL))
                    <div class="current-package-cover-video mb-2">
                        <p class="small text-success mb-1"><i class="fas fa-check-circle me-1"></i>Vidéo fichier actuelle</p>
                        <x-hls-native-video
                            class="w-100 rounded"
                            style="max-height:180px;background:#000;"
                            :fallback-src="$p->cover_video_url"
                            :hls-url="$p->hasCoverVideoHlsStreamReady() ? $p->cover_video_hls_manifest_url : ''"
                        />
                    </div>
                @endif
                <div class="upload-zone package-upload-zone" id="packageCoverVideoUploadZone">
                    <input type="file"
                           class="form-control d-none"
                           id="packageCoverVideo"
                           name="cover_video_file"
                           accept="video/mp4,video/webm,video/ogg"
                           onchange="handlePackageCoverVideoUpload(this)">
                    <input type="hidden" id="cover_video_path" name="cover_video_path" value="{{ old('cover_video_path', ($p && $p->cover_video && ! $p->isYoutubeCoverVideo() && ! filter_var($p->cover_video, FILTER_VALIDATE_URL)) ? $p->cover_video : '') }}">
                    <input type="hidden" id="cover_video_name" name="cover_video_name" value="{{ old('cover_video_name') }}">
                    <input type="hidden" id="cover_video_size" name="cover_video_size" value="{{ old('cover_video_size') }}">
                    <div class="upload-placeholder text-center p-3" onclick="document.getElementById('packageCoverVideo').click()">
                        <i class="fas fa-cloud-upload-alt fa-2x text-success mb-2"></i>
                        <p class="mb-1 small"><strong>Glissez-déposez une vidéo ou cliquez pour parcourir</strong></p>
                        <p class="text-muted small mb-0">MP4, WEBM, OGG — max 10&nbsp;Go · envoi par morceaux (comme les contenus)</p>
                    </div>
                    <div class="upload-preview d-none">
                        <p class="small text-info text-center mb-2"><i class="fas fa-eye me-1"></i>Nouvelle vidéo</p>
                        <video controls playsinline preload="metadata" class="w-100 rounded herime-stream-video" style="max-height:200px;background:#000;"></video>
                        <div class="upload-info mt-2 text-center">
                            <span class="badge bg-primary file-name"></span>
                            <span class="badge bg-info file-size"></span>
                        </div>
                        <div class="progress mt-2" style="height:6px;display:none;" id="packageCoverVideoProgress">
                            <div class="progress-bar bg-success" role="progressbar" style="width:0%"></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2 d-block mx-auto" onclick="clearPackageCoverVideo()">
                            <i class="fas fa-times me-1"></i>Annuler le remplacement
                        </button>
                    </div>
                </div>
                <div class="invalid-feedback d-block" id="packageCoverVideoError" style="display:none;"></div>
                @error('cover_video_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('cover_video_path')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @if($p)
                    <small class="text-muted d-block mt-1">Laissez vide pour conserver la vidéo fichier actuelle (si aucune URL YouTube n’est renseignée).</small>
                @endif
                <label class="form-label fw-bold mt-3">Ou ID / URL YouTube</label>
                <input type="text" name="cover_video_youtube_id" class="form-control"
                       value="{{ old('cover_video_youtube_id', $p->cover_video_youtube_id ?? '') }}"
                       placeholder="https://youtu.be/... ou ID">
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="cover_video_is_unlisted" value="1" id="cv_unlisted"
                        @checked(old('cover_video_is_unlisted', $p->cover_video_is_unlisted ?? false))>
                    <label class="form-check-label" for="cv_unlisted">Vidéo YouTube non répertoriée</label>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-form-card">
        <h5><i class="fas fa-tags me-2"></i>Prix &amp; promotion</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Prix <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" name="price" class="form-control @error('price') is-invalid @enderror"
                       value="{{ old('price', $p->price ?? '0') }}" required>
                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Prix promo</label>
                <input type="number" step="0.01" min="0" name="sale_price" class="form-control @error('sale_price') is-invalid @enderror"
                       value="{{ old('sale_price', $p->sale_price ?? '') }}">
                @error('sale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Tri (liste)</label>
                <input type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $p->sort_order ?? 0) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Début promo</label>
                <input type="datetime-local" name="sale_start_at" class="form-control"
                       value="{{ old('sale_start_at', $p && $p->sale_start_at ? $p->sale_start_at->format('Y-m-d\TH:i') : '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Fin promo</label>
                <input type="datetime-local" name="sale_end_at" class="form-control"
                       value="{{ old('sale_end_at', $p && $p->sale_end_at ? $p->sale_end_at->format('Y-m-d\TH:i') : '') }}">
            </div>
            <div class="col-12 d-flex flex-wrap gap-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_sale_enabled" value="1" id="is_sale_enabled"
                        @checked(old('is_sale_enabled', $p->is_sale_enabled ?? true))>
                    <label class="form-check-label" for="is_sale_enabled">Promotions activées</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published"
                        @checked(old('is_published', $p->is_published ?? false))>
                    <label class="form-check-label" for="is_published">Publié sur le site</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured"
                        @checked(old('is_featured', $p->is_featured ?? false))>
                    <label class="form-check-label" for="is_featured">À la une</label>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-form-card">
        <h5><i class="fas fa-bullhorn me-2"></i>Marketing &amp; SEO</h5>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-bold">Texte du bouton d’achat (optionnel)</label>
                <input type="text" name="cta_label" class="form-control" value="{{ old('cta_label', $p->cta_label ?? '') }}" placeholder="Ex : Obtenir le pack">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Meta titre</label>
                <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $p->meta_title ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Meta mots-clés</label>
                <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords', $p->meta_keywords ?? '') }}">
            </div>
            <div class="col-12">
                <label class="form-label fw-bold">Meta description</label>
                <textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description', $p->meta_description ?? '') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Points forts (liste)</label>
                @foreach($highlights as $i => $line)
                    <input type="text" name="marketing_highlights[]" class="form-control mb-2" value="{{ $line }}" placeholder="Point {{ $i + 1 }}">
                @endforeach
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Bénéfices (liste)</label>
                @foreach($benefits as $i => $line)
                    <input type="text" name="marketing_benefits[]" class="form-control mb-2" value="{{ $line }}" placeholder="Bénéfice {{ $i + 1 }}">
                @endforeach
            </div>
        </div>
    </div>
</div>

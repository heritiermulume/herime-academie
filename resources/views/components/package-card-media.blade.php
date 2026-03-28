@props([
    'package',
    'variant' => 'default',
])

@php
    $preload = in_array($p = config('video.player_preload', 'metadata'), ['none', 'metadata', 'auto'], true) ? $p : 'metadata';
    $fallbackImg = 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop';
@endphp

@if($variant === 'nested')
    @if($package->isYoutubeCoverVideo())
        <iframe src="{{ $package->cover_video_url }}" title="{{ $package->title }}" allowfullscreen class="border-0 w-100 h-100"></iframe>
    @elseif($package->cover_video_url)
        <video src="{{ $package->cover_video_url }}" controls playsinline preload="{{ $preload }}" class="w-100 h-100 object-fit-cover"></video>
    @elseif($package->thumbnail_url)
        <img src="{{ $package->thumbnail_url }}" alt="{{ $package->title }}" class="object-fit-cover w-100 h-100">
    @else
        <div class="d-flex align-items-center justify-content-center text-muted w-100 h-100">
            <i class="fas fa-box-open fa-3x opacity-50"></i>
        </div>
    @endif
@elseif($variant === 'thumb')
    @if($package->thumbnail_url)
        <img src="{{ $package->thumbnail_url }}" alt="{{ $package->title }}" class="w-100 h-100 object-fit-cover">
    @elseif($package->cover_video_url && ! $package->isYoutubeCoverVideo())
        <video src="{{ $package->cover_video_url }}" muted playsinline preload="metadata" class="w-100 h-100 object-fit-cover"></video>
    @elseif($package->isYoutubeCoverVideo())
        <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-dark text-white">
            <i class="fab fa-youtube fa-2x text-danger"></i>
        </div>
    @else
        <img src="{{ $fallbackImg }}" alt="" class="w-100 h-100 object-fit-cover">
    @endif
@else
    @if($package->isYoutubeCoverVideo())
        <div class="ratio ratio-16x9">
            <iframe src="{{ $package->cover_video_url }}" title="{{ $package->title }}" allowfullscreen class="border-0"></iframe>
        </div>
    @elseif($package->cover_video_url)
        <div class="ratio ratio-16x9 bg-dark">
            <video src="{{ $package->cover_video_url }}" controls playsinline preload="{{ $preload }}" class="w-100 h-100 object-fit-cover"></video>
        </div>
    @elseif($package->thumbnail_url)
        <img src="{{ $package->thumbnail_url }}" class="card-img-top" alt="{{ $package->title }}">
    @else
        <img src="{{ $fallbackImg }}" class="card-img-top" alt="{{ $package->title }}">
    @endif
@endif

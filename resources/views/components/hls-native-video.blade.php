@props([
    'fallbackSrc' => '',
    'hlsUrl' => '',
    'poster' => null,
    'muted' => false,
    'controls' => true,
])

@php
    $preload = in_array($p = config('video.player_preload', 'metadata'), ['none', 'metadata', 'auto'], true) ? $p : 'metadata';
    $hls = is_string($hlsUrl) ? trim($hlsUrl) : '';
    $fb = is_string($fallbackSrc) ? trim($fallbackSrc) : '';
@endphp
<div class="herime-hls-native-root w-100 h-100" data-herime-video-interact-root="1">
<video {{ $attributes->class(['herime-stream-video']) }}
    @if($controls) controls @endif
    playsinline
    preload="{{ $muted ? 'metadata' : $preload }}"
    @if($muted) muted @endif
    @if($poster) poster="{{ $poster }}" @endif
    @if($hls !== '' && $fb !== '')
        data-hls-url="{{ $hls }}"
        data-fallback-src="{{ $fb }}"
    @elseif($fb !== '')
        src="{{ $fb }}"
    @endif
></video>
</div>

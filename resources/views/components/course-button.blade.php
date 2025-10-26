@props(['course', 'size' => 'normal', 'showCart' => true])

@php
    $userId = auth()->id();
    $buttonConfig = $course->getButtonConfigForUser($userId);
    $sizeClass = $size === 'small' ? 'btn-sm' : ($size === 'large' ? 'btn-lg' : '');
    
    // Ne pas afficher le bouton panier pour les états qui n'en ont pas besoin
    $shouldShowCart = $showCart && $buttonConfig['type'] === 'buttons';
@endphp

@if($buttonConfig['type'] === 'link')
    <a href="{{ $buttonConfig['url'] }}" 
       class="{{ $buttonConfig['class'] }} {{ $sizeClass }} w-100 mb-2"
       @if(isset($buttonConfig['target'])) target="{{ $buttonConfig['target'] }}" @endif>
        <i class="{{ $buttonConfig['icon'] }} me-2"></i>{{ $buttonConfig['text'] }}
    </a>
@elseif($buttonConfig['type'] === 'form')
    <form action="{{ $buttonConfig['action'] }}" method="POST" class="w-100 mb-2">
        @csrf
        <button type="submit" class="{{ $buttonConfig['class'] }} {{ $sizeClass }} w-100">
            <i class="{{ $buttonConfig['icon'] }} me-2"></i>{{ $buttonConfig['text'] }}
        </button>
    </form>
@elseif($buttonConfig['type'] === 'buttons')
    <div class="d-grid gap-2 mb-2">
        @foreach($buttonConfig['buttons'] as $button)
            @if($button['type'] === 'button')
                <button type="button" class="{{ $button['class'] }} {{ $sizeClass }}" onclick="{{ $button['onclick'] }}">
                    <i class="{{ $button['icon'] }} me-2"></i>{{ $button['text'] }}
                </button>
            @elseif($button['type'] === 'link')
                <a href="{{ $button['url'] }}" class="{{ $button['class'] }} {{ $sizeClass }}">
                    <i class="{{ $button['icon'] }} me-2"></i>{{ $button['text'] }}
                </a>
            @endif
        @endforeach
        
        @if($shouldShowCart)
            <div class="d-grid gap-1">
                <a href="{{ route('cart.index') }}" class="btn btn-outline-primary {{ $sizeClass }}">
                    <i class="fas fa-shopping-bag me-2"></i>Voir le panier
                </a>
            </div>
        @endif
    </div>
@endif

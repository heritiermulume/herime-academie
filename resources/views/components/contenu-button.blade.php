@props(['course', 'size' => 'normal', 'showCart' => true])

@php
    $userId = auth()->id();
    $buttonConfig = $course->getButtonConfigForUser($userId);
    $buttonState = $course->getButtonStateForUser($userId);
    $sizeClass = $size === 'small' ? 'btn-sm' : ($size === 'large' ? 'btn-lg' : '');
    
    // Ne pas afficher le bouton panier pour les états qui n'en ont pas besoin
    // Le panier s'affiche uniquement pour le cas 'purchase' (pas encore acheté)
    $shouldShowCart = $showCart && $buttonConfig['type'] === 'buttons' && $buttonState === 'purchase';
@endphp

@if($buttonConfig['type'] === 'disabled')
    <button type="button" 
            class="{{ $buttonConfig['class'] }} {{ $sizeClass }} w-100" 
            disabled
            @if(isset($buttonConfig['tooltip'])) title="{{ $buttonConfig['tooltip'] }}" data-bs-toggle="tooltip" @endif
            onclick="event.stopPropagation();">
        <i class="{{ $buttonConfig['icon'] }} me-2"></i>{{ $buttonConfig['text'] }}
    </button>
@elseif($buttonConfig['type'] === 'link')
    <a href="{{ $buttonConfig['url'] }}" 
       class="{{ $buttonConfig['class'] }} {{ $sizeClass }} w-100"
       @if(isset($buttonConfig['target'])) target="{{ $buttonConfig['target'] }}" @endif
       onclick="event.stopPropagation();">
        <i class="{{ $buttonConfig['icon'] }} me-2"></i>{{ $buttonConfig['text'] }}
    </a>
@elseif($buttonConfig['type'] === 'form')
    <form action="{{ $buttonConfig['action'] }}" method="POST" class="w-100" onclick="event.stopPropagation();">
        @csrf
        <button type="submit" class="{{ $buttonConfig['class'] }} {{ $sizeClass }} w-100">
            <i class="{{ $buttonConfig['icon'] }} me-2"></i>{{ $buttonConfig['text'] }}
        </button>
    </form>
@elseif($buttonConfig['type'] === 'buttons')
    <div class="d-grid gap-2" onclick="event.stopPropagation();">
        @foreach($buttonConfig['buttons'] as $button)
            @if($button['type'] === 'button')
                <button type="button" class="{{ $button['class'] }} {{ $sizeClass }}" onclick="event.stopPropagation(); {{ $button['onclick'] }}">
                    <i class="{{ $button['icon'] }} me-2"></i>{{ $button['text'] }}
                </button>
            @elseif($button['type'] === 'link')
                <a href="{{ $button['url'] }}" class="{{ $button['class'] }} {{ $sizeClass }}" onclick="event.stopPropagation();">
                    <i class="{{ $button['icon'] }} me-2"></i>{{ $button['text'] }}
                </a>
            @endif
        @endforeach
        
        @if($shouldShowCart)
            <div class="d-grid gap-1">
                <a href="{{ route('cart.index') }}" class="btn btn-outline-primary {{ $sizeClass }}" onclick="event.stopPropagation();">
                    <i class="fas fa-shopping-bag me-2"></i>Voir le panier
                </a>
            </div>
        @endif
    </div>
@endif

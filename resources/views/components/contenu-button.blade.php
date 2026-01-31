@props(['course', 'size' => 'normal', 'showCart' => true])

@php
    $userId = auth()->id();
    $buttonConfig = $course->getButtonConfigForUser($userId);
    $buttonState = $course->getButtonStateForUser($userId);
    $sizeClass = $size === 'small' ? 'btn-sm' : ($size === 'large' ? 'btn-lg' : '');
    $whatsappUrl = $course->whatsapp_chat_url ?? null;
    $isInPerson = (bool) ($course->is_in_person_program ?? false);
    
    // Ne pas afficher le bouton panier pour les états qui n'en ont pas besoin
    // Le panier s'affiche uniquement pour le cas 'purchase' (pas encore acheté)
    $shouldShowCart = $showCart && $buttonConfig['type'] === 'buttons' && $buttonState === 'purchase';

    // Pour les programmes en présentiel: afficher WhatsApp en permanence.
    // Et enlever le bouton "Ajouter au panier" pour éviter un doublon / comportement non souhaité.
    if ($isInPerson && $whatsappUrl && ($buttonConfig['type'] ?? null) === 'buttons' && isset($buttonConfig['buttons']) && is_array($buttonConfig['buttons'])) {
        $buttonConfig['buttons'] = array_values(array_filter($buttonConfig['buttons'], function ($btn) {
            $text = $btn['text'] ?? '';
            return $text !== 'Ajouter au panier';
        }));
    }
@endphp

@if($isInPerson && $whatsappUrl)
    <a href="{{ $whatsappUrl }}"
       target="_blank"
       rel="noopener noreferrer"
       data-meta-trigger="whatsapp"
       class="btn btn-whatsapp-herime {{ $sizeClass }} w-100 mb-2"
       onclick="event.stopPropagation();">
        <i class="fab fa-whatsapp me-2"></i>Contacter sur WhatsApp
    </a>
@endif

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
       @if(isset($buttonConfig['meta_trigger'])) data-meta-trigger="{{ $buttonConfig['meta_trigger'] }}" @endif
       @if(isset($buttonConfig['target'])) target="{{ $buttonConfig['target'] }}" @endif
       onclick="event.stopPropagation();">
        <i class="{{ $buttonConfig['icon'] }} me-2"></i>{{ $buttonConfig['text'] }}
    </a>
@elseif($buttonConfig['type'] === 'form')
    <form action="{{ $buttonConfig['action'] }}" method="POST" class="w-100" onclick="event.stopPropagation();">
        @csrf
        <button type="submit"
                class="{{ $buttonConfig['class'] }} {{ $sizeClass }} w-100"
                @if(isset($buttonConfig['meta_trigger'])) data-meta-trigger="{{ $buttonConfig['meta_trigger'] }}" @endif>
            <i class="{{ $buttonConfig['icon'] }} me-2"></i>{{ $buttonConfig['text'] }}
        </button>
    </form>
@elseif($buttonConfig['type'] === 'buttons')
    <div class="d-grid gap-2" onclick="event.stopPropagation();">
        @foreach($buttonConfig['buttons'] as $button)
            @if($button['type'] === 'button')
                <button type="button"
                        class="{{ $button['class'] }} {{ $sizeClass }}"
                        @if(isset($button['meta_trigger'])) data-meta-trigger="{{ $button['meta_trigger'] }}" @endif
                        onclick="event.stopPropagation(); {{ $button['onclick'] }}">
                    <i class="{{ $button['icon'] }} me-2"></i>{{ $button['text'] }}
                </button>
            @elseif($button['type'] === 'link')
                <a href="{{ $button['url'] }}"
                   class="{{ $button['class'] }} {{ $sizeClass }}"
                   @if(isset($button['meta_trigger'])) data-meta-trigger="{{ $button['meta_trigger'] }}" @endif
                   onclick="event.stopPropagation();"
                   @if(isset($button['target'])) target="{{ $button['target'] }}" @endif>
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

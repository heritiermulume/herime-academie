@props([
    'id' => '',
    'loop' => null,
    'actions' => []
])

@php
    // Actions disponibles : 'view', 'edit', 'delete', 'toggle' (avec data-toggle-action, data-toggle-confirm)
    $hasActions = !empty($actions);
    $isFirst = $loop && $loop->first;
@endphp

@if($hasActions)
    <td class="text-center align-top">
        @if($isFirst)
            {{-- Desktop: dropdown vers le bas pour première ligne --}}
            <div class="dropdown d-none d-md-block">
                <button class="btn btn-sm btn-light course-actions-btn" type="button" id="actionsDropdown{{ $id }}" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdown{{ $id }}">
                    @foreach($actions as $action)
                        @if($action['type'] === 'view')
                            <li>
                                <a class="dropdown-item" href="{{ $action['url'] }}">
                                    <i class="fas fa-eye me-2"></i>Voir
                                </a>
                            </li>
                        @elseif($action['type'] === 'edit')
                            <li>
                                <a class="dropdown-item" href="{{ $action['url'] }}">
                                    <i class="fas fa-edit me-2"></i>Modifier
                                </a>
                            </li>
                        @elseif($action['type'] === 'delete')
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" 
                                   {!! isset($action['data-id']) ? 'data-item-id="' . $action['data-id'] . '"' : '' !!}
                                   {!! isset($action['data-title']) ? 'data-item-title="' . $action['data-title'] . '"' : '' !!}
                                   {!! isset($action['onclick']) ? 'onclick="' . $action['onclick'] . '; return false;"' : '' !!}>
                                    <i class="fas fa-trash me-2"></i>Supprimer
                                </a>
                            </li>
                        @elseif($action['type'] === 'toggle')
                            <li>
                                <a class="dropdown-item" href="#" 
                                   data-action="{{ $action['url'] }}"
                                   {!! isset($action['data-confirm']) ? 'data-confirm="' . $action['data-confirm'] . '"' : '' !!}
                                   {!! isset($action['onclick']) ? 'onclick="' . $action['onclick'] . '; return false;"' : '' !!}>
                                    <i class="fas fa-toggle-{{ isset($action['active']) && $action['active'] ? 'on' : 'off' }} me-2"></i>
                                    {{ isset($action['active']) && $action['active'] ? 'Désactiver' : 'Activer' }}
                                </a>
                            </li>
                        @elseif($action['type'] === 'custom')
                            <li>
                                <a class="dropdown-item {{ isset($action['class']) ? $action['class'] : '' }}" href="{{ $action['url'] ?? '#' }}"
                                   {!! isset($action['onclick']) ? 'onclick="' . $action['onclick'] . '; return false;"' : '' !!}>
                                    {!! isset($action['icon']) ? '<i class="' . $action['icon'] . ' me-2"></i>' : '' !!}
                                    {{ $action['label'] }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
            {{-- Mobile: dropdown vers le bas pour première ligne --}}
            <div class="dropdown d-md-none">
                <button class="btn btn-sm btn-light course-actions-btn course-actions-btn--mobile" type="button" id="actionsDropdownMobile{{ $id }}" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdownMobile{{ $id }}">
                    @foreach($actions as $action)
                        @if($action['type'] === 'view')
                            <li>
                                <a class="dropdown-item" href="{{ $action['url'] }}">
                                    <i class="fas fa-eye me-2"></i>Voir
                                </a>
                            </li>
                        @elseif($action['type'] === 'edit')
                            <li>
                                <a class="dropdown-item" href="{{ $action['url'] }}">
                                    <i class="fas fa-edit me-2"></i>Modifier
                                </a>
                            </li>
                        @elseif($action['type'] === 'delete')
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" 
                                   {!! isset($action['data-id']) ? 'data-item-id="' . $action['data-id'] . '"' : '' !!}
                                   {!! isset($action['data-title']) ? 'data-item-title="' . $action['data-title'] . '"' : '' !!}
                                   {!! isset($action['onclick']) ? 'onclick="' . $action['onclick'] . '; return false;"' : '' !!}>
                                    <i class="fas fa-trash me-2"></i>Supprimer
                                </a>
                            </li>
                        @elseif($action['type'] === 'toggle')
                            <li>
                                <a class="dropdown-item" href="#" 
                                   data-action="{{ $action['url'] }}"
                                   {!! isset($action['data-confirm']) ? 'data-confirm="' . $action['data-confirm'] . '"' : '' !!}
                                   {!! isset($action['onclick']) ? 'onclick="' . $action['onclick'] . '; return false;"' : '' !!}>
                                    <i class="fas fa-toggle-{{ isset($action['active']) && $action['active'] ? 'on' : 'off' }} me-2"></i>
                                    {{ isset($action['active']) && $action['active'] ? 'Désactiver' : 'Activer' }}
                                </a>
                            </li>
                        @elseif($action['type'] === 'custom')
                            <li>
                                <a class="dropdown-item {{ isset($action['class']) ? $action['class'] : '' }}" href="{{ $action['url'] ?? '#' }}"
                                   {!! isset($action['onclick']) ? 'onclick="' . $action['onclick'] . '; return false;"' : '' !!}>
                                    {!! isset($action['icon']) ? '<i class="' . $action['icon'] . ' me-2"></i>' : '' !!}
                                    {{ $action['label'] }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @else
            {{-- Desktop: dropup vers le haut pour autres lignes --}}
            <div class="dropup d-none d-md-block">
                <button class="btn btn-sm btn-light course-actions-btn" type="button" id="actionsDropdown{{ $id }}" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdown{{ $id }}">
                    @foreach($actions as $action)
                        @if($action['type'] === 'view')
                            <li>
                                <a class="dropdown-item" href="{{ $action['url'] }}">
                                    <i class="fas fa-eye me-2"></i>Voir
                                </a>
                            </li>
                        @elseif($action['type'] === 'edit')
                            <li>
                                <a class="dropdown-item" href="{{ $action['url'] }}">
                                    <i class="fas fa-edit me-2"></i>Modifier
                                </a>
                            </li>
                        @elseif($action['type'] === 'delete')
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" 
                                   {!! isset($action['data-id']) ? 'data-item-id="' . $action['data-id'] . '"' : '' !!}
                                   {!! isset($action['data-title']) ? 'data-item-title="' . $action['data-title'] . '"' : '' !!}
                                   {!! isset($action['onclick']) ? 'onclick="' . $action['onclick'] . '; return false;"' : '' !!}>
                                    <i class="fas fa-trash me-2"></i>Supprimer
                                </a>
                            </li>
                        @elseif($action['type'] === 'toggle')
                            <li>
                                <a class="dropdown-item" href="#" 
                                   data-action="{{ $action['url'] }}"
                                   {!! isset($action['data-confirm']) ? 'data-confirm="' . $action['data-confirm'] . '"' : '' !!}
                                   {!! isset($action['onclick']) ? 'onclick="' . $action['onclick'] . '; return false;"' : '' !!}>
                                    <i class="fas fa-toggle-{{ isset($action['active']) && $action['active'] ? 'on' : 'off' }} me-2"></i>
                                    {{ isset($action['active']) && $action['active'] ? 'Désactiver' : 'Activer' }}
                                </a>
                            </li>
                        @elseif($action['type'] === 'custom')
                            <li>
                                <a class="dropdown-item {{ isset($action['class']) ? $action['class'] : '' }}" href="{{ $action['url'] ?? '#' }}"
                                   {!! isset($action['onclick']) ? 'onclick="' . $action['onclick'] . '; return false;"' : '' !!}>
                                    {!! isset($action['icon']) ? '<i class="' . $action['icon'] . ' me-2"></i>' : '' !!}
                                    {{ $action['label'] }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
            {{-- Mobile: dropup vers le haut pour autres lignes --}}
            <div class="dropup d-md-none">
                <button class="btn btn-sm btn-light course-actions-btn course-actions-btn--mobile" type="button" id="actionsDropdownMobile{{ $id }}" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdownMobile{{ $id }}">
                    @foreach($actions as $action)
                        @if($action['type'] === 'view')
                            <li>
                                <a class="dropdown-item" href="{{ $action['url'] }}">
                                    <i class="fas fa-eye me-2"></i>Voir
                                </a>
                            </li>
                        @elseif($action['type'] === 'edit')
                            <li>
                                <a class="dropdown-item" href="{{ $action['url'] }}">
                                    <i class="fas fa-edit me-2"></i>Modifier
                                </a>
                            </li>
                        @elseif($action['type'] === 'delete')
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" 
                                   {!! isset($action['data-id']) ? 'data-item-id="' . $action['data-id'] . '"' : '' !!}
                                   {!! isset($action['data-title']) ? 'data-item-title="' . $action['data-title'] . '"' : '' !!}
                                   {!! isset($action['onclick']) ? 'onclick="' . $action['onclick'] . '; return false;"' : '' !!}>
                                    <i class="fas fa-trash me-2"></i>Supprimer
                                </a>
                            </li>
                        @elseif($action['type'] === 'toggle')
                            <li>
                                <a class="dropdown-item" href="#" 
                                   data-action="{{ $action['url'] }}"
                                   {!! isset($action['data-confirm']) ? 'data-confirm="' . $action['data-confirm'] . '"' : '' !!}
                                   {!! isset($action['onclick']) ? 'onclick="' . $action['onclick'] . '; return false;"' : '' !!}>
                                    <i class="fas fa-toggle-{{ isset($action['active']) && $action['active'] ? 'on' : 'off' }} me-2"></i>
                                    {{ isset($action['active']) && $action['active'] ? 'Désactiver' : 'Activer' }}
                                </a>
                            </li>
                        @elseif($action['type'] === 'custom')
                            <li>
                                <a class="dropdown-item {{ isset($action['class']) ? $action['class'] : '' }}" href="{{ $action['url'] ?? '#' }}"
                                   {!! isset($action['onclick']) ? 'onclick="' . $action['onclick'] . '; return false;"' : '' !!}>
                                    {!! isset($action['icon']) ? '<i class="' . $action['icon'] . ' me-2"></i>' : '' !!}
                                    {{ $action['label'] }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif
    </td>
@endif







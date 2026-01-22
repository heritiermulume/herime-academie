@props([
    'tableId' => 'dataTable',
    'exportRoute' => null,
    'exportFormats' => ['csv', 'excel'],
    'actions' => []
])

@php
    // Actions par défaut si aucune n'est fournie
    $defaultActions = [
        'delete' => [
            'label' => 'Supprimer',
            'icon' => 'fa-trash',
            'class' => 'btn-danger',
            'confirm' => true,
            'confirmMessage' => 'Êtes-vous sûr de vouloir supprimer les éléments sélectionnés ?'
        ],
    ];
    
    $finalActions = !empty($actions) ? $actions : $defaultActions;
@endphp

<div id="bulkActionsBar-{{ $tableId }}" class="bulk-actions-bar" style="display: none;">
    <div class="bulk-actions-bar__content">
        <div class="bulk-actions-bar__info">
            <span class="bulk-actions-bar__count" id="selectedCount-{{ $tableId }}">0</span>
            <span class="bulk-actions-bar__text">élément(s) sélectionné(s)</span>
        </div>
        
        <div class="bulk-actions-bar__actions">
            @foreach($finalActions as $actionKey => $action)
                <button 
                    type="button" 
                    class="btn btn-sm {{ $action['class'] ?? 'btn-primary' }} bulk-action-btn"
                    data-action="{{ $actionKey }}"
                    data-table-id="{{ $tableId }}"
                    @if(isset($action['confirm']) && $action['confirm'])
                        data-confirm="true"
                        data-confirm-message="{{ $action['confirmMessage'] ?? 'Confirmer cette action ?' }}"
                    @endif
                    @if(isset($action['route']))
                        data-route="{{ $action['route'] }}"
                    @endif
                    @if(isset($action['method']))
                        data-method="{{ $action['method'] }}"
                    @endif
                >
                    <i class="fas {{ $action['icon'] ?? 'fa-check' }} me-1"></i>
                    {{ $action['label'] ?? ucfirst($actionKey) }}
                </button>
            @endforeach
            
            @if($exportRoute)
                <div class="dropdown">
                    <button class="btn btn-sm btn-success dropdown-toggle" type="button" id="exportDropdown-{{ $tableId }}" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download me-1"></i>Exporter
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown-{{ $tableId }}">
                        @if(in_array('csv', $exportFormats))
                            <li>
                                <a class="dropdown-item export-link" href="#" data-format="csv" data-table-id="{{ $tableId }}">
                                    <i class="fas fa-file-csv me-2"></i>CSV
                                </a>
                            </li>
                        @endif
                        @if(in_array('excel', $exportFormats))
                            <li>
                                <a class="dropdown-item export-link" href="#" data-format="excel" data-table-id="{{ $tableId }}">
                                    <i class="fas fa-file-excel me-2"></i>Excel
                                </a>
                            </li>
                        @endif
                        @if(in_array('pdf', $exportFormats))
                            <li>
                                <a class="dropdown-item export-link" href="#" data-format="pdf" data-table-id="{{ $tableId }}">
                                    <i class="fas fa-file-pdf me-2"></i>PDF
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif
            
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="bulkActions.clearSelection('{{ $tableId }}')">
                <i class="fas fa-times me-1"></i>Annuler
            </button>
        </div>
    </div>
</div>

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('css/bulk-actions.css') }}">
@endpush
@endonce

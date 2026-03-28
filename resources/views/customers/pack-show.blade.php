@extends('customers.admin.layout')

@section('admin-title', $package->title)
@section('admin-subtitle', 'Pack acheté — accédez à chaque contenu ci-dessous.')

@section('admin-actions')
    <a href="{{ route('customer.contents') }}" class="admin-btn soft">
        <i class="fas fa-arrow-left me-2"></i>Mes contenus
    </a>
@endsection

@section('admin-content')
<section class="admin-panel admin-panel--main">
    <div class="admin-panel__body">
        <div class="customer-pack-hero d-flex flex-column flex-md-row gap-4 align-items-start mb-4">
            <div class="customer-pack-hero__thumb flex-shrink-0 rounded-3 overflow-hidden bg-light" style="width: 100%; max-width: 280px; aspect-ratio: 16/10;">
                <x-package-card-media :package="$package" variant="nested" />
            </div>
            <div class="flex-grow-1">
                <span class="badge bg-primary mb-2">Pack</span>
                <h2 class="h4 fw-bold mb-2">{{ $package->title }}</h2>
                @if($package->subtitle)
                    <p class="text-muted mb-2">{{ $package->subtitle }}</p>
                @endif
                @if($order)
                    <p class="small text-muted mb-0">
                        <i class="fas fa-receipt me-1"></i>Commande du {{ ($order->paid_at ?? $order->created_at)->format('d/m/Y') }}
                    </p>
                @endif
            </div>
        </div>

        <h3 class="h5 fw-bold mb-3">
            <i class="fas fa-layer-group me-2"></i>Contenus du pack ({{ $package->contents->count() }})
        </h3>
        <p class="text-muted small mb-4">
            Ouvrez chaque contenu pour apprendre en ligne, télécharger ou consulter la fiche détaillée.
        </p>

        <div class="customer-pack-course-list">
            @foreach($package->contents as $course)
                @if(!$course->is_published)
                    @continue
                @endif
                @php
                    $enrollment = $enrollments[$course->id] ?? null;
                @endphp
                <div class="customer-pack-course card border-0 shadow-sm mb-3">
                    <div class="card-body d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between">
                        <div class="d-flex gap-3 align-items-center flex-grow-1 min-w-0">
                            <div class="customer-pack-course__mini-thumb flex-shrink-0 rounded-2 overflow-hidden bg-light" style="width: 72px; height: 48px;">
                                @if($course->thumbnail_url)
                                    <img src="{{ $course->thumbnail_url }}" alt="" class="w-100 h-100 object-fit-cover">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100 small text-muted">{{ $course->initials ?? '?' }}</div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h4 class="h6 fw-semibold mb-1 text-truncate">{{ $course->title }}</h4>
                                <p class="small text-muted mb-0 text-truncate">
                                    {{ $course->provider->name ?? 'Prestataire' }}
                                    @if($course->category?->name)
                                        · {{ $course->category->name }}
                                    @endif
                                </p>
                                @if($enrollment)
                                    <span class="badge bg-info bg-opacity-10 text-info mt-2">{{ ucfirst($enrollment->status) }}</span>
                                    @if(!($course->is_downloadable ?? false) && !($course->is_in_person_program ?? false))
                                        <span class="small text-muted ms-2">{{ (int) $enrollment->progress }}% complété</span>
                                    @endif
                                @else
                                    <span class="badge bg-warning text-dark mt-2">Accès à activer</span>
                                @endif
                            </div>
                        </div>
                        @include('customers.partials.pack-course-actions', ['course' => $course, 'enrollment' => $enrollment, 'package' => $package])
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .customer-pack-course__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }
    @media (max-width: 991.98px) {
        .customer-pack-course__actions {
            width: 100%;
        }
        .customer-pack-course__actions .admin-btn {
            flex: 1 1 auto;
            justify-content: center;
        }
    }
</style>
@endpush

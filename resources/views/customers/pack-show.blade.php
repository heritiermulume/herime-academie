@extends('customers.admin.layout')

@section('admin-title', $package->title)
@section('admin-subtitle', 'Pack acheté — accédez à chaque contenu ci-dessous.')

@section('admin-actions')
    <a href="{{ route('customer.contents') }}" class="admin-btn ghost customer-pack-back-btn">
        <i class="fas fa-arrow-left me-2"></i>Retour à mes contenus
    </a>
@endsection

@section('admin-content')
<section class="admin-panel admin-panel--main">
    <div class="admin-panel__body">
        <div class="customer-pack-hero d-flex flex-column flex-md-row gap-4 align-items-center align-items-md-start mb-4">
            <div class="customer-pack-hero__thumb flex-shrink-0 rounded-3 overflow-hidden bg-light">
                <x-package-card-media :package="$package" variant="nested" />
            </div>
            <div class="flex-grow-1 customer-pack-hero__meta w-100 text-center text-md-start">
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
            Ouvrez chaque contenu pour apprendre en ligne ou télécharger les ressources disponibles.
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
    .customer-pack-hero__thumb {
        width: 100%;
        max-width: 280px;
        aspect-ratio: 16 / 10;
    }

    .customer-pack-hero__thumb > * {
        width: 100%;
        height: 100%;
        min-height: 0;
    }

    .customer-pack-hero__thumb img,
    .customer-pack-hero__thumb video {
        object-fit: cover;
        display: block;
    }

    .customer-pack-hero__thumb iframe {
        display: block;
        border: 0;
    }

    @media (max-width: 767.98px) {
        .customer-pack-hero__thumb {
            max-width: 100%;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
        }
    }

    .admin-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border-radius: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        padding: 0.65rem 1.2rem;
        border: 1px solid transparent;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.2s ease, border-color 0.2s ease;
        color: inherit;
        cursor: pointer;
        font-family: inherit;
        line-height: 1.25;
    }

    .admin-btn.primary {
        background: linear-gradient(90deg, var(--student-primary), #0b4f99);
        color: #ffffff;
        box-shadow: 0 22px 38px -28px rgba(30, 58, 138, 0.55);
    }

    .admin-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 26px 44px -28px rgba(30, 58, 138, 0.45);
        color: #ffffff;
    }

    .admin-btn.success {
        background: linear-gradient(90deg, #22c55e, #16a34a);
        color: #ffffff;
        box-shadow: 0 22px 38px -28px rgba(34, 197, 94, 0.55);
    }

    .admin-btn.success:hover {
        transform: translateY(-2px);
        box-shadow: 0 26px 44px -28px rgba(34, 197, 94, 0.45);
        background: linear-gradient(90deg, #16a34a, #15803d);
        color: #ffffff;
    }

    /* Même logique que la page détail commande : retour lisible sur fond clair */
    .admin-header .customer-pack-back-btn.admin-btn.ghost,
    .admin-btn.ghost.customer-pack-back-btn {
        border-color: rgba(0, 51, 102, 0.28);
        color: var(--student-primary);
        background: rgba(0, 51, 102, 0.06);
    }

    .admin-header .customer-pack-back-btn.admin-btn.ghost:hover,
    .admin-btn.ghost.customer-pack-back-btn:hover {
        background: rgba(0, 51, 102, 0.12);
        border-color: var(--student-primary);
        color: var(--student-primary-dark);
    }

    .admin-btn.sm {
        padding: 0.5rem 0.9rem;
        border-radius: 0.75rem;
        font-size: 0.85rem;
    }

    .customer-pack-course__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .customer-pack-course__actions .admin-btn {
        box-shadow: 0 14px 28px -22px rgba(15, 23, 42, 0.35);
    }

    @media (max-width: 991.98px) {
        .customer-pack-course__actions {
            width: 100%;
        }

        .customer-pack-course__actions .admin-btn {
            flex: 1 1 auto;
            justify-content: center;
            min-height: 2.65rem;
        }
    }

    @media (max-width: 767.98px) {
        .admin-header .customer-pack-back-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@extends('layouts.app')

@section('title', 'Espace d’apprentissage - ' . $course->title)
@section('description', 'Progressez dans le cours ' . $course->title . ' grâce à notre espace d’apprentissage immersif et moderne.')

@push('styles')
<style>
:root {
    --learning-bg: #003366;
    --learning-sidebar: rgba(0, 51, 102, 0.92);
    --learning-card: rgba(0, 51, 102, 0.9);
    --learning-highlight: #ffcc33;
    --learning-muted: #94a3b8;
    --bs-info: #ffcc33;
    --bs-primary: #003366;
}

/* Override Bootstrap colors for this page */
.learning-shell .text-info,
.learning-shell .btn-info {
    color: #ffcc33 !important;
}

.learning-shell .btn-info {
    background-color: #ffcc33 !important;
    border-color: #ffcc33 !important;
    color: #003366 !important;
}

.learning-shell .btn-info:hover {
    background-color: #ff9933 !important;
    border-color: #ff9933 !important;
}

.learning-shell .bg-info {
    background-color: rgba(255, 204, 51, 0.1) !important;
}

.learning-shell .border-info {
    border-color: rgba(255, 204, 51, 0.25) !important;
}

.learning-shell .bg-primary {
    background-color: rgba(0, 51, 102, 0.1) !important;
}

.learning-shell .text-primary {
    color: #ffcc33 !important;
}

.learning-shell .border-primary {
    border-color: rgba(255, 204, 51, 0.25) !important;
}

.learning-shell {
    background: linear-gradient(135deg, #003366 0%, #004080 100%);
    min-height: 100vh;
    color: #e2e8f0;
}

@media (max-width: 991.98px) {
    .learning-shell {
        margin-top: -0.3rem !important;
    }
}

@media (max-width: 767.98px) {
    .learning-shell {
        margin-top: -0.4rem !important;
    }
}

@media (max-width: 575.98px) {
    .learning-shell {
        margin-top: -0.5rem !important;
    }
}

/* S'assurer que tous les titres sont en couleur claire */
.learning-shell h1, 
.learning-shell h2, 
.learning-shell h3, 
.learning-shell h4, 
.learning-shell h5, 
.learning-shell h6 {
    color: #f8fafc !important;
}

.learning-shell h1 {
    font-size: 1.75rem;
}

.learning-shell h2 {
    font-size: 1.5rem;
}

.learning-shell h3 {
    font-size: 1.25rem;
}

.learning-shell h4 {
    font-size: 1.1rem;
}

.learning-shell h5 {
    font-size: 1rem;
}

.learning-shell h6 {
    font-size: 0.95rem;
}

.learning-shell p {
    color: #cbd5e1;
}

/* S'assurer que tous les textes sont lisibles */
.learning-shell span {
    color: #cbd5e1;
}

.learning-shell .text-muted {
    color: #94a3b8 !important;
}

.learning-shell .small {
    color: #94a3b8;
}

.learning-shell strong {
    color: #f8fafc;
}

/* S'assurer que les badges sont lisibles */
.learning-shell .badge {
    font-weight: 600;
}

/* Corriger les couleurs des métadonnées */
.learning-shell .lesson-header__meta span {
    color: #94a3b8;
}

.learning-shell .outline-lesson__meta span {
    color: #94a3b8;
}

.learning-shell .insight-list__item span {
    color: #cbd5e1;
}

.learning-shell .recommended-meta span {
    color: #94a3b8;
}

/* S'assurer que les icônes dans la section toggle sont visibles */
.learning-shell .section-toggle-icon {
    color: #94a3b8 !important;
}

/* Corriger les couleurs par défaut des éléments */
.learning-shell div,
.learning-shell label {
    color: inherit;
}

/* Classes de taille de police Bootstrap */
.learning-shell .fs-1,
.learning-shell .fs-2,
.learning-shell .fs-3,
.learning-shell .fs-4,
.learning-shell .fs-5,
.learning-shell .fs-6 {
    color: #f8fafc;
}

/* S'assurer que tous les liens sont visibles */
.learning-shell a {
    color: #cbd5e1;
}

.learning-shell a:hover {
    color: #f8fafc;
}

/* Classes Bootstrap de poids de police */
.learning-shell .fw-bold:not(.text-white):not(.text-info):not(.text-warning):not(.text-success):not(.text-danger),
.learning-shell .fw-semibold:not(.text-white):not(.text-info):not(.text-warning):not(.text-success):not(.text-danger) {
    color: #f8fafc;
}

/* S'assurer que les text-uppercase sont visibles */
.learning-shell .text-uppercase {
    color: #94a3b8;
}

.learning-shell .container-fluid {
    padding: clamp(0.75rem, 0.5rem + 1vw, 1.75rem);
}

.learning-grid {
    display: grid;
    grid-template-columns: minmax(280px, 320px) minmax(0, 1fr) minmax(260px, 320px);
    gap: clamp(1rem, 0.5rem + 1vw, 1.75rem);
}

.learning-column {
    display: flex;
    flex-direction: column;
    gap: clamp(1rem, 0.8rem + 0.5vw, 1.5rem);
}

.learning-card {
    background: var(--learning-card);
    border-radius: 18px;
    border: 1px solid rgba(255, 204, 51, 0.15);
    box-shadow: 0 25px 50px -12px rgba(0, 51, 102, 0.45);
    overflow: hidden;
}

.learning-card .card-body {
    padding: clamp(1.1rem, 1rem + 0.6vw, 1.6rem);
}

.learning-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.85rem;
    padding: clamp(1rem, 0.8rem + 0.6vw, 1.5rem);
    background: radial-gradient(circle at top left, rgba(255, 204, 51, 0.12), transparent);
}

.learning-meta__item {
    background: rgba(0, 51, 102, 0.75);
    border: 1px solid rgba(255, 204, 51, 0.15);
    border-radius: 16px;
    padding: 0.9rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.learning-meta__item span:first-child {
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.65rem;
    color: var(--learning-muted);
}

.learning-progress-bar {
    position: relative;
    height: 12px;
    background: rgba(148, 163, 184, 0.15);
    border-radius: 999px;
    overflow: hidden;
}

.learning-progress-bar__fill {
    position: absolute;
    inset: 0;
    width: 0%;
    background: linear-gradient(90deg, #ffcc33 0%, #ff9933 100%);
}

.lesson-outline {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.outline-section {
    border: 1px solid rgba(255, 204, 51, 0.15);
    border-radius: 16px;
    overflow: hidden;
    background: rgba(0, 51, 102, 0.75);
}

.outline-section__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.65rem;
    padding: 0.9rem 1.1rem;
    cursor: pointer;
    transition: background 0.25s ease;
}

.outline-section__header:hover {
    background: rgba(255, 204, 51, 0.12);
}

.outline-section__header.active {
    background: rgba(255, 204, 51, 0.18);
}

.outline-section__body {
    padding: 0.75rem 1rem 1rem;
    border-top: 1px solid rgba(255, 204, 51, 0.12);
}

.outline-lesson {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.65rem 0.8rem;
    border-radius: 14px;
    border: 1px solid transparent;
    transition: all 0.2s ease;
    position: relative;
}

.outline-lesson:hover {
    border-color: rgba(255, 204, 51, 0.4);
    background: rgba(255, 204, 51, 0.08);
}

.outline-lesson.active {
    background: rgba(255, 204, 51, 0.18);
    border-color: rgba(255, 204, 51, 0.55);
    box-shadow: inset 0 0 0 1px rgba(255, 204, 51, 0.25);
}

.outline-lesson__icon {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    background: rgba(255, 204, 51, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 204, 51, 0.9);
}

.outline-lesson.completed .outline-lesson__icon {
    background: rgba(34, 197, 94, 0.15);
    color: rgba(34, 197, 94, 0.95);
}

.outline-lesson__title {
    font-weight: 600;
    color: #f8fafc;
    font-size: 0.875rem;
}

.outline-lesson__meta {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    font-size: 0.75rem;
    color: #94a3b8;
}

.learning-player-card {
    padding: clamp(1rem, 0.75rem + 0.8vw, 1.6rem);
    background: linear-gradient(160deg, rgba(0, 51, 102, 0.92), rgba(0, 64, 128, 0.82));
    border-radius: 24px;
    border: 1px solid rgba(255, 204, 51, 0.15);
    box-shadow: 0 32px 55px -25px rgba(0, 51, 102, 0.55);
    overflow: hidden;
}

.learning-player-card .lesson-header {
    padding-bottom: clamp(0.75rem, 0.6rem + 0.6vw, 1.25rem);
    margin-bottom: clamp(0.75rem, 0.6rem + 0.6vw, 1.25rem);
    border-bottom: 1px solid rgba(148, 163, 184, 0.2);
}

.lesson-header__title {
    font-size: clamp(1.1rem, 1rem + 0.5vw, 1.4rem);
    font-weight: 700;
    color: #f8fafc;
}

.lesson-header__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    font-size: 0.8rem;
    color: #94a3b8;
}

.player-shell {
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    border: 1px solid rgba(148, 163, 184, 0.18);
    background: #040913;
    margin-bottom: clamp(0.75rem, 0.6rem + 0.6vw, 1.2rem);
}

.player-shell .ratio {
    width: 100%;
}

.lesson-cta-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.65rem;
}

.lesson-cta-row .btn {
    border-radius: 12px;
    font-weight: 600;
    min-height: 44px;
}

.lesson-cta-row .btn i {
    font-size: 0.9rem;
}

.lesson-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    margin-bottom: 1rem;
}

.lesson-tab {
    border: 1px solid rgba(255, 204, 51, 0.2);
    border-radius: 999px;
    padding: 0.4rem 0.9rem;
    font-size: 0.82rem;
    color: #cbd5f5;
    background: transparent;
    transition: all 0.2s ease;
}

@media (max-width: 991.98px) {
    .lesson-tabs {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
    }

    .lesson-tab {
        width: 100%;
        padding: 0.35rem 0.6rem;
        font-size: 0.75rem;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .lesson-tab i {
        font-size: 0.7rem;
        margin-right: 0.3rem !important;
    }
}

.lesson-tab.active {
    background: rgba(255, 204, 51, 0.15);
    border-color: rgba(255, 204, 51, 0.5);
    color: #ffcc33;
}

.lesson-tab-content {
    display: none;
}

.lesson-tab-content.active {
    display: block;
}

.learning-insights {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.insight-card {
    background: rgba(0, 51, 102, 0.78);
    border-radius: 18px;
    border: 1px solid rgba(255, 204, 51, 0.15);
    padding: clamp(1rem, 0.8rem + 0.6vw, 1.4rem);
}

.insight-card h6 {
    font-weight: 700;
    font-size: 0.95rem;
    color: #e0f2fe;
}

.insight-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    margin-top: 0.75rem;
}

.insight-list__item {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: #cbd5f5;
}

.recommended-courses {
    display: grid;
    gap: 0.75rem;
}

.recommended-courses .recommended-item {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 16px;
    border: 1px solid rgba(255, 204, 51, 0.15);
    background: rgba(0, 51, 102, 0.65);
    transition: border 0.2s ease, transform 0.2s ease;
}

.recommended-courses .recommended-item:hover {
    border-color: rgba(255, 204, 51, 0.4);
    transform: translateY(-2px);
}

.recommended-thumb {
    width: 72px;
    height: 72px;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
}

.recommended-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.recommended-content h6 {
    margin-bottom: 0.15rem;
    font-weight: 600;
    font-size: 0.875rem;
    color: #f8fafc;
}

.recommended-meta {
    font-size: 0.75rem;
    color: #94a3b8;
    display: flex;
    gap: 0.65rem;
}

.recommended-actions {
    margin-top: 0.65rem;
    display: flex;
    gap: 0.4rem;
}

.recommended-actions .btn {
    font-size: 0.75rem;
    padding: 0.35rem 0.65rem;
    border-radius: 10px;
}

.mobile-outline-drawer {
    position: fixed;
    inset: 0;
    background: rgba(3, 7, 18, 0.7);
    backdrop-filter: blur(6px);
    display: none;
    align-items: flex-end;
    justify-content: center;
    z-index: 1060;
    padding: 0 0.75rem 0.75rem;
}

.mobile-outline-drawer.is-open {
    display: flex;
}

.mobile-outline-drawer__panel {
    width: min(100%, 540px);
    max-height: 88vh;
    background: rgba(0, 51, 102, 0.95);
    border-radius: 24px;
    border: 1px solid rgba(255, 204, 51, 0.25);
    box-shadow: 0 -22px 45px -30px rgba(0, 51, 102, 0.65);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.mobile-outline-drawer__header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(255, 204, 51, 0.2);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.mobile-outline-close-btn {
    padding: 0.5rem !important;
    min-width: 36px !important;
    width: 36px !important;
    height: 36px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.mobile-outline-close-btn i {
    margin: 0 !important;
    font-size: 0.9rem;
}

.mobile-outline-drawer__body {
    padding: 1rem 1.25rem 1.5rem;
    overflow-y: auto;
}

.mobile-outline-progress {
    background: rgba(0, 51, 102, 0.55);
    border-radius: 16px;
    border: 1px solid rgba(255, 204, 51, 0.15);
    padding: 0.85rem 1rem;
}

/* Responsive */
@media (max-width: 1199px) {
    .learning-grid {
        grid-template-columns: minmax(0, 1fr) minmax(260px, 320px);
    }

    .learning-grid .learning-column:nth-child(1) {
        display: none;
    }
}

@media (max-width: 991.98px) {
    .learning-shell {
        padding-top: 0 !important;
    }

    .learning-shell .container-fluid {
        padding: 0 !important;
        padding-top: 0 !important;
        margin-top: 0 !important;
    }

    .learning-topbar {
        margin-bottom: 0.4rem !important;
        margin-top: 0 !important;
        padding-top: 0 !important;
    }

    /* Ajouter des marges horizontales à la barre de progression sur tablette */
    .learning-topbar .learning-progress-bar {
        margin-left: 0.75rem;
        margin-right: 0.75rem;
    }

    .learning-grid {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding-bottom: 1.5rem;
        margin-top: 0 !important;
        padding-top: 0 !important;
    }

    .learning-column {
        gap: 1rem;
    }

    .learning-player-card {
        border-radius: 0;
        box-shadow: none;
        border-left: 0;
        border-right: 0;
        padding: 0.85rem;
        margin-top: 0 !important;
    }

    .player-shell {
        border-radius: 12px;
    }

    /* Réduire les tailles de texte pour tablette */
    .learning-shell h1 {
        font-size: 1.4rem;
    }

    .learning-shell h2 {
        font-size: 1.2rem;
    }

    .learning-shell h3 {
        font-size: 1.1rem;
    }

    .learning-shell h4 {
        font-size: 1rem;
    }

    .learning-shell h5 {
        font-size: 0.95rem;
    }

    .learning-shell h6 {
        font-size: 0.9rem;
    }

    .lesson-header__title {
        font-size: 1.1rem;
    }

    .lesson-header__meta {
        font-size: 0.75rem;
    }

    .lesson-cta-row {
        gap: 0.5rem;
    }

    .lesson-cta-row .btn {
        flex: 1;
        min-width: 0;
        padding: 0.55rem;
        font-size: 0.85rem;
    }

    .learning-meta {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        padding: 0.75rem;
    }

    .learning-meta__item {
        padding: 0.75rem 0.85rem;
    }

    .learning-meta__item span:first-child {
        font-size: 0.6rem;
    }

    .learning-meta__item strong {
        font-size: 0.95rem;
    }

    .learning-column.secondary,
    .learning-column.sidebar {
        order: 3;
    }

    /* Ajouter des marges horizontales sur mobile pour les cartes de la colonne secondaire */
    .learning-column.secondary .learning-card {
        margin-left: 0.75rem;
        margin-right: 0.75rem;
    }

    .outline-section__header {
        padding: 0.75rem 0.8rem;
    }

    .outline-section__header h6 {
        font-size: 0.9rem;
    }

    .outline-lesson {
        padding: 0.55rem 0.65rem;
    }

    .outline-lesson__title {
        font-size: 0.8rem;
    }

    .outline-lesson__meta {
        font-size: 0.7rem;
    }

    .lesson-tabs {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.4rem;
    }

    .lesson-tab {
        font-size: 0.7rem;
        padding: 0.3rem 0.5rem;
        width: 100%;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .lesson-tab i {
        font-size: 0.65rem;
        margin-right: 0.25rem !important;
    }

    .learning-insights {
        gap: 0.75rem;
    }

    .insight-card h6 {
        font-size: 0.9rem;
    }

    .insight-list__item {
        font-size: 0.8rem;
    }

    .recommended-content h6 {
        font-size: 0.8rem;
    }

    .recommended-meta {
        font-size: 0.7rem;
    }

    .recommended-actions .btn {
        font-size: 0.7rem;
        padding: 0.3rem 0.55rem;
    }

    .learning-topbar h6 {
        font-size: 0.9rem;
    }

    .learning-topbar p {
        font-size: 0.6rem;
    }

    /* Boutons retour et sommaire - icônes uniquement sur tablette et mobile */
    .learning-topbar .btn i {
        margin: 0 !important;
    }

    .learning-topbar .btn {
        padding: 0.5rem !important;
        min-width: 42px !important;
        width: 42px !important;
        height: 42px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .learning-topbar .btn span {
        display: none !important;
    }

    /* Boutons Partager, Suivant, Précédent, Terminé, Ressources - icônes uniquement sur tablette et mobile */
    .lesson-header .btn i,
    .lesson-cta-row .btn i {
        margin: 0 !important;
    }

    .lesson-header .btn,
    .lesson-cta-row .btn {
        padding: 0.5rem !important;
        min-width: 42px !important;
        width: auto !important;
        height: 42px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .lesson-header .btn span,
    .lesson-cta-row .btn span {
        display: none !important;
    }

    .card-body {
        padding: 0.85rem !important;
    }

    .learning-card .card-body {
        padding: 0.85rem !important;
    }
}

@media (max-width: 767.98px) {
    /* Réduire le padding-top pour mobile */
    .learning-shell {
        padding-top: 0 !important;
    }

    .learning-shell .container-fluid {
        padding-top: 0 !important;
        margin-top: 0 !important;
        padding: 0 !important;
    }

    .learning-topbar {
        margin-bottom: 0.3rem !important;
        margin-top: 0 !important;
        padding-top: 0 !important;
    }

    /* Ajouter des marges horizontales à la barre de progression sur mobile */
    .learning-topbar .learning-progress-bar {
        margin-left: 0.5rem;
        margin-right: 0.5rem;
    }

    .learning-grid {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }

    /* Ajouter des marges horizontales sur mobile pour les cartes de la colonne secondaire */
    .learning-column.secondary .learning-card {
        margin-left: 0.5rem;
        margin-right: 0.5rem;
    }

    /* Réduire encore plus pour mobile */
    .learning-shell h1 {
        font-size: 1.2rem;
    }

    .learning-shell h2 {
        font-size: 1.1rem;
    }

    .learning-shell h3 {
        font-size: 1rem;
    }

    .learning-shell h4 {
        font-size: 0.95rem;
    }

    .learning-shell h5 {
        font-size: 0.9rem;
    }

    .learning-shell h6 {
        font-size: 0.85rem;
    }

    .lesson-header__title {
        font-size: 1rem;
    }

    .lesson-header__meta {
        font-size: 0.7rem;
        gap: 0.5rem;
    }

    .learning-player-card {
        padding: 0.75rem;
    }

    .lesson-cta-row .btn {
        font-size: 0.8rem;
        padding: 0.5rem;
    }

    .learning-meta {
        padding: 0.65rem;
        gap: 0.65rem;
    }

    .learning-meta__item {
        padding: 0.65rem 0.75rem;
    }

    .learning-meta__item span:first-child {
        font-size: 0.55rem;
    }

    .learning-meta__item strong {
        font-size: 0.85rem;
    }

    .outline-section__header {
        padding: 0.65rem 0.7rem;
    }

    .outline-section__header h6 {
        font-size: 0.85rem;
    }

    .outline-section__header p {
        font-size: 0.65rem;
    }

    .outline-lesson {
        padding: 0.5rem 0.6rem;
    }

    .outline-lesson__title {
        font-size: 0.75rem;
    }

    .outline-lesson__meta {
        font-size: 0.65rem;
    }

    .lesson-tabs {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.35rem;
    }

    .lesson-tab {
        font-size: 0.65rem;
        padding: 0.25rem 0.45rem;
        width: 100%;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .lesson-tab i {
        font-size: 0.6rem;
        margin-right: 0.2rem !important;
    }

    .insight-card {
        padding: 0.75rem;
    }

    .insight-card h6 {
        font-size: 0.85rem;
    }

    .insight-card span {
        font-size: 0.65rem;
    }

    .insight-list__item {
        font-size: 0.75rem;
    }

    .recommended-content h6 {
        font-size: 0.75rem;
    }

    .recommended-meta {
        font-size: 0.65rem;
    }

    .recommended-actions .btn {
        font-size: 0.65rem;
        padding: 0.25rem 0.5rem;
    }

    .learning-topbar h6 {
        font-size: 0.85rem;
    }

    .learning-topbar p {
        font-size: 0.55rem;
    }

    /* Boutons retour et sommaire - icônes uniquement sur mobile */
    .learning-topbar .btn {
        font-size: 0.75rem;
        padding: 0.5rem !important;
        min-width: 40px !important;
        width: 40px !important;
        height: 40px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .learning-topbar .btn i {
        margin: 0 !important;
    }

    /* Boutons Partager, Suivant, Précédent, Terminé, Ressources - icônes uniquement sur mobile */
    .lesson-header .btn i,
    .lesson-cta-row .btn i {
        margin: 0 !important;
    }

    .lesson-header .btn,
    .lesson-cta-row .btn {
        padding: 0.5rem !important;
        min-width: 40px !important;
        width: auto !important;
        height: 40px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .lesson-header .btn span,
    .lesson-cta-row .btn span {
        display: none !important;
    }

    .card-body {
        padding: 0.75rem !important;
    }

    .learning-card .card-body {
        padding: 0.75rem !important;
    }

    .mobile-outline-drawer__header h5 {
        font-size: 0.95rem;
    }

    .mobile-outline-drawer__body {
        padding: 0.75rem;
    }

    .mobile-outline-progress {
        padding: 0.7rem 0.85rem;
    }

    .mobile-outline-close-btn {
        padding: 0.5rem !important;
        min-width: 36px !important;
        width: 36px !important;
        height: 36px !important;
    }

    .mobile-outline-close-btn i {
        margin: 0 !important;
        font-size: 0.85rem;
    }

    .text-muted {
        font-size: 0.8rem;
    }

    .small {
        font-size: 0.75rem;
    }

    p {
        font-size: 0.85rem;
    }
}

@media (max-width: 575.98px) {
    /* Réduire encore plus le padding-top pour très petits écrans */
    .learning-shell {
        padding-top: 0 !important;
    }

    .learning-shell .container-fluid {
        padding-top: 0 !important;
        margin-top: 0 !important;
        padding: 0 !important;
    }

    .learning-topbar {
        margin-bottom: 0.25rem !important;
        margin-top: 0 !important;
        padding-top: 0 !important;
    }

    /* Ajouter des marges horizontales à la barre de progression sur très petit mobile */
    .learning-topbar .learning-progress-bar {
        margin-left: 0.4rem;
        margin-right: 0.4rem;
    }

    .learning-grid {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }

    /* Ajouter des marges horizontales sur très petit mobile pour les cartes de la colonne secondaire */
    .learning-column.secondary .learning-card {
        margin-left: 0.4rem;
        margin-right: 0.4rem;
    }

    /* Très petits écrans */
    .learning-shell h1 {
        font-size: 1.1rem;
    }

    .learning-shell h2 {
        font-size: 1rem;
    }

    .learning-shell h3 {
        font-size: 0.95rem;
    }

    .learning-shell h4 {
        font-size: 0.9rem;
    }

    .learning-shell h5 {
        font-size: 0.85rem;
    }

    .learning-shell h6 {
        font-size: 0.8rem;
    }

    .lesson-header__title {
        font-size: 0.95rem;
    }

    .lesson-header__meta {
        font-size: 0.65rem;
    }

    .learning-player-card {
        padding: 0.65rem;
    }

    .lesson-cta-row .btn {
        font-size: 0.75rem;
        padding: 0.45rem;
    }

    .learning-meta {
        padding: 0.55rem;
        gap: 0.55rem;
    }

    .learning-meta__item {
        padding: 0.55rem 0.65rem;
    }

    .learning-meta__item span:first-child {
        font-size: 0.5rem;
    }

    .learning-meta__item strong {
        font-size: 0.8rem;
    }

    .outline-section__header {
        padding: 0.55rem 0.6rem;
    }

    .outline-section__header h6 {
        font-size: 0.8rem;
    }

    .outline-section__header p {
        font-size: 0.6rem;
    }

    .outline-lesson {
        padding: 0.45rem 0.55rem;
    }

    .outline-lesson__title {
        font-size: 0.7rem;
    }

    .outline-lesson__meta {
        font-size: 0.6rem;
    }

    .lesson-tabs {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.3rem;
    }

    .lesson-tab {
        font-size: 0.6rem;
        padding: 0.2rem 0.4rem;
        width: 100%;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .lesson-tab i {
        font-size: 0.55rem;
        margin-right: 0.15rem !important;
    }

    .insight-card {
        padding: 0.65rem;
    }

    .insight-card h6 {
        font-size: 0.8rem;
    }

    .insight-card span {
        font-size: 0.6rem;
    }

    .insight-list__item {
        font-size: 0.7rem;
    }

    .recommended-content h6 {
        font-size: 0.7rem;
    }

    .recommended-meta {
        font-size: 0.6rem;
    }

    .recommended-actions .btn {
        font-size: 0.6rem;
        padding: 0.2rem 0.45rem;
    }

    .learning-topbar h6 {
        font-size: 0.8rem;
    }

    .learning-topbar p {
        font-size: 0.5rem;
    }

    /* Boutons retour et sommaire - icônes uniquement sur très petit mobile */
    .learning-topbar .btn {
        font-size: 0.7rem;
        padding: 0.5rem !important;
        min-width: 36px !important;
        width: 36px !important;
        height: 36px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .learning-topbar .btn i {
        margin: 0 !important;
        font-size: 0.9rem;
    }

    /* Boutons Partager, Suivant, Précédent, Terminé, Ressources - icônes uniquement sur très petit mobile */
    .lesson-header .btn i,
    .lesson-cta-row .btn i {
        margin: 0 !important;
        font-size: 0.9rem;
    }

    .lesson-header .btn,
    .lesson-cta-row .btn {
        padding: 0.5rem !important;
        min-width: 36px !important;
        width: auto !important;
        height: 36px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .lesson-header .btn span,
    .lesson-cta-row .btn span {
        display: none !important;
    }

    .card-body {
        padding: 0.65rem !important;
    }

    .learning-card .card-body {
        padding: 0.65rem !important;
    }

    .mobile-outline-drawer__header h5 {
        font-size: 0.9rem;
    }

    .mobile-outline-drawer__body {
        padding: 0.65rem;
    }

    .mobile-outline-progress {
        padding: 0.6rem 0.75rem;
    }

    .mobile-outline-close-btn {
        padding: 0.45rem !important;
        min-width: 32px !important;
        width: 32px !important;
        height: 32px !important;
    }

    .mobile-outline-close-btn i {
        margin: 0 !important;
        font-size: 0.8rem;
    }

    .text-muted {
        font-size: 0.75rem;
    }

    .small {
        font-size: 0.7rem;
    }

    p {
        font-size: 0.8rem;
    }

    .btn {
        font-size: 0.75rem;
    }

    .form-control {
        font-size: 0.85rem;
    }
}
</style>
@endpush

@section('content')
<div class="learning-shell">
    <div class="container-fluid">
        <div class="learning-topbar d-lg-none mb-3">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                    <div>
                        <p class="text-uppercase text-muted fw-semibold mb-0" style="font-size: 0.65rem;">Mon apprentissage</p>
                        <h6 class="mb-0 fw-bold text-white">{{ Str::limit($course->title, 40) }}</h6>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-light" id="mobile-outline-toggle">
                    <i class="fas fa-list-ul"></i>
                    <span class="d-none d-md-inline ms-2">Sommaire</span>
                </button>
            </div>
            <div class="learning-progress-bar mt-3">
                <div class="learning-progress-bar__fill" style="width: {{ $progress['overall_progress'] ?? 0 }}%;"></div>
                        </div>
                    </div>

        <div class="learning-grid">
            {{-- Outline Column --}}
            <div class="learning-column sidebar d-none d-xl-flex">
                <div class="learning-card">
                    <div class="card-body pb-2">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                            <div>
                                <p class="text-uppercase text-muted fw-semibold mb-1" style="letter-spacing: 0.08em;">Progression</p>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span class="fs-4 fw-bold text-white">{{ $progress['overall_progress'] ?? 0 }}%</span>
                                    <span class="text-muted small">{{ $progress['completed_lessons'] ?? 0 }}/{{ $progress['total_lessons'] ?? 0 }} leçons</span>
                        </div>
                        </div>
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill">
                                Niveau {{ ucfirst($course->level) }}
                            </span>
                        </div>
                        <div class="learning-progress-bar mb-2">
                            <div class="learning-progress-bar__fill" style="width: {{ $progress['overall_progress'] ?? 0 }}%;"></div>
                        </div>
                        <p class="text-muted small mb-0">Dernière mise à jour : {{ optional($course->updated_at)->diffForHumans() ?? 'non disponible' }}</p>
                    </div>
                    </div>

                <div class="learning-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0 fw-bold text-white">Plan du cours</h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2">
                                {{ $course->sections?->count() ?? 0 }} sections
                            </span>
                        </div>

                        <div class="lesson-outline">
                            @foreach($course->sections as $section)
                                @php
                                    $sectionLessons = $section->lessons ?? collect();
                                    $isSectionOpen = $sectionLessons->contains(fn($lesson) => isset($activeLessonId) && $lesson->id === $activeLessonId);
                                @endphp
                                <div class="outline-section" data-section-id="{{ $section->id }}">
                                    <button class="outline-section__header {{ $isSectionOpen ? 'active' : '' }}" type="button">
                                        <div>
                                            <p class="text-uppercase small mb-1 fw-semibold" style="color: #94a3b8;">Section {{ $loop->iteration }}</p>
                                            <h6 class="mb-0 fw-semibold" style="color: #f8fafc;">{{ $section->title }}</h6>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-1">
                                                {{ $sectionLessons->count() }} leçons
                                            </span>
                                            <i class="fas fa-chevron-down ms-2 text-muted section-toggle-icon"></i>
                                    </div>
                                    </button>
                                
                                    <div class="outline-section__body {{ $isSectionOpen ? '' : 'd-none' }}">
                                        @foreach($sectionLessons as $sectionLesson)
                                        @php
                                                $isActive = isset($activeLessonId) && $sectionLesson->id === $activeLessonId;
                                                $isCompleted = $progress['completed_lessons_ids']->contains($sectionLesson->id ?? 0);
                                                $progressEntry = $progress['lesson_progress'][$sectionLesson->id] ?? null;
                                        @endphp
                                            <a href="{{ route('learning.lesson', ['course' => $course->slug, 'lesson' => $sectionLesson->id]) }}"
                                               class="outline-lesson {{ $isActive ? 'active' : '' }} {{ $isCompleted ? 'completed' : '' }}">
                                                <div class="outline-lesson__icon">
                                                    @switch($sectionLesson->type)
                                                        @case('video')
                                                            <i class="fas fa-play"></i>
                                                            @break
                                                        @case('pdf')
                                                        <i class="fas fa-file-pdf"></i>
                                                            @break
                                                        @case('quiz')
                                                            <i class="fas fa-star"></i>
                                                            @break
                                                        @case('text')
                                                            <i class="fas fa-align-left"></i>
                                                            @break
                                                        @default
                                                        <i class="fas fa-file"></i>
                                                    @endswitch
                                                </div>
                                                <div class="flex-grow-1">
                                                    <p class="outline-lesson__title mb-1">{{ $sectionLesson->title }}</p>
                                                    <div class="outline-lesson__meta">
                                                        @if($sectionLesson->duration)
                                                            <span><i class="far fa-clock me-1"></i>{{ $sectionLesson->duration }} min</span>
                                                        @endif
                                                        @if($progressEntry)
                                                            <span><i class="fas fa-chart-line me-1 text-success"></i>{{ round($progressEntry->progress_percentage) }}%</span>
                                                        @endif
                                                @if($isCompleted)
                                                            <span class="text-success"><i class="fas fa-check-circle me-1"></i>Terminé</span>
                                                @endif
                                            </div>
                                        </div>
                                                <i class="fas fa-chevron-right text-muted small"></i>
                                            </a>
                                        @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Column --}}
            <div class="learning-column main">
                {{-- Course Title Header --}}
                <div class="learning-card mb-3 d-none d-lg-block">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div class="flex-grow-1">
                                <p class="text-uppercase small mb-1" style="color: #94a3b8; letter-spacing: 0.08em;">Cours en formation</p>
                                <h2 class="mb-0" style="color: #f8fafc; font-size: 1.25rem; font-weight: 700;">{{ $course->title }}</h2>
                            </div>
                            <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-info-circle me-2"></i>Détails
                            </a>
                        </div>
                    </div>
                </div>

                <div class="learning-player-card">
                    <div class="lesson-header">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                            <div>
                                <p class="text-uppercase small text-muted fw-semibold mb-1" style="letter-spacing: 0.08em;">
                                    @if(isset($activeLesson))
                                        Leçon {{ $progress['lesson_progress'][$activeLesson->id]->watched_seconds ?? 0 > 0 ? 'en cours' : 'nouvelle' }}
                                    @else
                                        Aperçu du cours
                                    @endif
                                </p>
                                <h1 class="lesson-header__title mb-2">
                                    {{ $activeLesson->title ?? 'Commencez votre apprentissage' }}
                                </h1>
                                <div class="lesson-header__meta">
                                    <span><i class="fas fa-layer-group me-1 text-info"></i>{{ $course->sections?->count() ?? 0 }} sections</span>
                                    <span><i class="fas fa-play-circle me-1 text-info"></i>{{ $progress['total_lessons'] ?? 0 }} leçons</span>
                                    @if(isset($activeLesson) && $activeLesson->duration)
                                        <span><i class="far fa-clock me-1 text-info"></i>{{ $activeLesson->duration }} min</span>
                                    @endif
                                    <span><i class="fas fa-signal me-1 text-info"></i>{{ ucfirst($course->level) }}</span>
                        </div>
                    </div>
                            @if(isset($activeLesson) && ($progress['lesson_progress'][$activeLesson->id]->watched_seconds ?? 0) > 0)
                            <button class="btn btn-outline-light d-flex align-items-center gap-2">
                                <i class="fas fa-share-nodes"></i>
                                <span class="d-none d-md-inline">Partager</span>
                        </button>
                            @endif
                    </div>
                </div>

                    <div class="player-shell mb-4">
                                <div class="ratio ratio-16x9">
                            @if(isset($activeLesson))
                                    @switch($activeLesson->type)
                                        @case('video')
                                        <x-plyr-player :lesson="$activeLesson" :course="$course" :is-mobile="false" />
                                            @break
                                        @case('pdf')
                                            <x-pdf-viewer :lesson="$activeLesson" />
                                            @break
                                        @case('text')
                                            <x-text-viewer :lesson="$activeLesson" />
                                            @break
                                        @case('quiz')
                                            <x-quiz-viewer :lesson="$activeLesson" :course="$course" />
                                            @break
                                        @default
                                        <div class="d-flex align-items-center justify-content-center bg-dark">
                                            <p class="text-white">Type de contenu non supporté</p>
                                            </div>
                                    @endswitch
                            @else
                                <div class="d-flex flex-column align-items-center justify-content-center bg-dark text-white p-5">
                                    <i class="fas fa-graduation-cap fa-3x mb-3 text-info"></i>
                                    <h4 class="mb-2">Sélectionnez une leçon pour commencer</h4>
                                    <p class="text-muted mb-4 text-center">
                                        Explorez le contenu du cours et lancez-vous dans une expérience immersive.
                                    </p>
                                    @if($course->sections->first()?->lessons->first())
                                        <a href="{{ route('learning.lesson', ['course' => $course->slug, 'lesson' => $course->sections->first()->lessons->first()->id]) }}"
                                           class="btn btn-info btn-lg px-4">
                                            <i class="fas fa-play me-2"></i>Commencer maintenant
                                        </a>
                                    @endif
                                </div>
                            @endif
                                </div>
                            </div>

                    <div class="lesson-cta-row mb-4">
                        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap w-100">
                            {{-- Navigation Buttons (Left) --}}
                            <div class="d-flex gap-2">
                                    @if(isset($previousLesson))
                                <a href="{{ route('learning.lesson', ['course' => $course->slug, 'lesson' => $previousLesson->id]) }}"
                                   class="btn btn-outline-light d-flex align-items-center gap-2">
                                    <i class="fas fa-arrow-left"></i>
                                        <span class="d-none d-md-inline">Précédent</span>
                                </a>
                            @endif

                            @if(isset($nextLesson))
                                <a href="{{ route('learning.lesson', ['course' => $course->slug, 'lesson' => $nextLesson->id]) }}"
                                   class="btn btn-info d-flex align-items-center gap-2">
                                        <span class="d-none d-md-inline">Suivant</span>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            @endif
                            </div>

                            {{-- Action Buttons (Center/Right) --}}
                            <div class="d-flex gap-2 flex-wrap">
                            @if(isset($activeLesson))
                                <button class="btn btn-success d-flex align-items-center gap-2"
                                        @if($progress['completed_lessons_ids']->contains($activeLesson->id))
                                            disabled
                                    @else
                                            onclick="markAsComplete({{ $activeLesson->id }})"
                                    @endif
                                >
                                    <i class="fas fa-check"></i>
                                        <span class="d-none d-md-inline">{{ $progress['completed_lessons_ids']->contains($activeLesson->id) ? 'Leçon terminée' : 'Marquer comme terminé' }}</span>
                                    </button>
                            @endif
                        </div>
                        </div>
                    </div>

                    <div class="lesson-tabs">
                        <button class="lesson-tab active" data-tab="overview">
                            <i class="fas fa-book-open me-2"></i>Aperçu
                                        </button>
                        @isset($activeLesson)
                            <button class="lesson-tab" data-tab="notes">
                                <i class="fas fa-pen-to-square me-2"></i>Notes
                            </button>
                            <button class="lesson-tab" data-tab="resources">
                                <i class="fas fa-folder-open me-2"></i>Ressources
                            </button>
                            <button class="lesson-tab" data-tab="discussion">
                                <i class="fas fa-comments me-2"></i>Discussion
                            </button>
                        @endisset
                    </div>

                    <div class="lesson-tab-panels">
                        <div class="lesson-tab-content active" id="tab-overview">
                            @isset($activeLesson)
                                <div class="mb-4">
                                    <h5 class="text-white fw-semibold mb-3">À propos de cette leçon</h5>
                                    <div class="text-muted">
                                        {!! nl2br(e($activeLesson->description ?? 'Aucune description fournie pour cette leçon.')) !!}
                                    </div>
                                </div>

                                {{-- Liste des sections du cours - Mobile/Tablette uniquement --}}
                                <div class="d-lg-none mb-4">
                                    <h5 class="text-white fw-semibold mb-3">Plan du cours</h5>
                                    <div class="lesson-outline">
                                        @foreach($course->sections as $section)
                                            @php
                                                $sectionLessons = $section->lessons ?? collect();
                                                $isSectionOpen = $sectionLessons->contains(fn($lesson) => isset($activeLessonId) && $lesson->id === $activeLessonId);
                                            @endphp
                                            <div class="outline-section" data-section-id="mobile-overview-{{ $section->id }}">
                                                <button class="outline-section__header {{ $isSectionOpen ? 'active' : '' }}" type="button">
                                                    <div>
                                                        <p class="text-uppercase small mb-1 fw-semibold" style="color: #94a3b8;">Section {{ $loop->iteration }}</p>
                                                        <h6 class="mb-0 fw-semibold" style="color: #f8fafc;">{{ $section->title }}</h6>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-1">
                                                            {{ $sectionLessons->count() }} leçons
                                                        </span>
                                                        <i class="fas fa-chevron-down ms-2 text-muted section-toggle-icon"></i>
                                                    </div>
                                                </button>
                                                
                                                <div class="outline-section__body {{ $isSectionOpen ? '' : 'd-none' }}">
                                                    @foreach($sectionLessons as $sectionLesson)
                                                        @php
                                                            $isActive = isset($activeLessonId) && $sectionLesson->id === $activeLessonId;
                                                            $isCompleted = $progress['completed_lessons_ids']->contains($sectionLesson->id ?? 0);
                                                            $progressEntry = $progress['lesson_progress'][$sectionLesson->id] ?? null;
                                                        @endphp
                                                        <a href="{{ route('learning.lesson', ['course' => $course->slug, 'lesson' => $sectionLesson->id]) }}"
                                                           class="outline-lesson {{ $isActive ? 'active' : '' }} {{ $isCompleted ? 'completed' : '' }}">
                                                            <div class="outline-lesson__icon">
                                                                @switch($sectionLesson->type)
                                                                    @case('video')
                                                                        <i class="fas fa-play"></i>
                                                                        @break
                                                                    @case('pdf')
                                                                        <i class="fas fa-file-pdf"></i>
                                                                        @break
                                                                    @case('quiz')
                                                                        <i class="fas fa-star"></i>
                                                                        @break
                                                                    @case('text')
                                                                        <i class="fas fa-align-left"></i>
                                                                        @break
                                                                    @default
                                                                        <i class="fas fa-file"></i>
                                                                @endswitch
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <p class="outline-lesson__title mb-1">{{ $sectionLesson->title }}</p>
                                                                <div class="outline-lesson__meta">
                                                                    @if($sectionLesson->duration)
                                                                        <span><i class="far fa-clock me-1"></i>{{ $sectionLesson->duration }} min</span>
                                                                    @endif
                                                                    @if($progressEntry)
                                                                        <span><i class="fas fa-chart-line me-1 text-success"></i>{{ round($progressEntry->progress_percentage) }}%</span>
                                                                    @endif
                                                                    @if($isCompleted)
                                                                        <span class="text-success"><i class="fas fa-check-circle me-1"></i>Terminé</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <i class="fas fa-chevron-right text-muted small"></i>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-sm-4">
                                        <div class="insight-card h-100">
                                            <span class="text-uppercase text-muted small fw-semibold">Type de contenu</span>
                                            <h6 class="mt-2 mb-0 text-info">
                                                <i class="fas fa-layer-group me-2"></i>{{ ucfirst($activeLesson->type) }}
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="insight-card h-100">
                                            <span class="text-uppercase text-muted small fw-semibold">Durée prévue</span>
                                            <h6 class="mt-2 mb-0 text-warning">
                                                <i class="far fa-clock me-2"></i>{{ $activeLesson->duration ?? '–' }} min
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="insight-card h-100">
                                            <span class="text-uppercase text-muted small fw-semibold">Progression</span>
                                            <h6 class="mt-2 mb-0 text-success">
                                                <i class="fas fa-chart-line me-2"></i>
                                                {{ isset($progress['lesson_progress'][$activeLesson->id]) ? round($progress['lesson_progress'][$activeLesson->id]->progress_percentage) : 0 }}%
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                                    @else
                                <p class="text-muted mb-0">Choisissez une leçon pour afficher son contenu et ses ressources.</p>
                            @endisset
                        </div>

                        @isset($activeLesson)
                            <div class="lesson-tab-content" id="tab-notes">
                                <div class="mb-3">
                                    <h6 class="text-white fw-semibold mb-3">Mes notes</h6>
                                    <form id="note-form" class="mb-4">
                                        <textarea id="note-content" class="form-control mb-2" rows="4" placeholder="Ajoutez une note pour cette leçon..." style="background: rgba(255,255,255,0.1); border-color: rgba(255,204,51,0.25); color: #fff;"></textarea>
                                        <button type="submit" class="btn btn-info btn-sm">
                                            <i class="fas fa-save me-2"></i>Enregistrer la note
                                        </button>
                                    </form>
                                </div>
                                <div id="notes-list"></div>
                            </div>
                            <div class="lesson-tab-content" id="tab-resources">
                                <h6 class="text-white fw-semibold mb-3">Ressources de la leçon</h6>
                                <div id="resources-list"></div>
                            </div>
                            <div class="lesson-tab-content" id="tab-discussion">
                                <div class="mb-4">
                                    <h6 class="text-white fw-semibold mb-3">Discussions</h6>
                                    <form id="discussion-form" class="mb-4">
                                        <textarea id="discussion-content" class="form-control mb-2" rows="3" placeholder="Posez une question ou partagez votre avis..." style="background: rgba(255,255,255,0.1); border-color: rgba(255,204,51,0.25); color: #fff;"></textarea>
                                        <button type="submit" class="btn btn-info btn-sm">
                                            <i class="fas fa-paper-plane me-2"></i>Publier
                                        </button>
                                    </form>
                                </div>
                                <div id="discussions-list"></div>
                            </div>
                        @endisset
                    </div>
                </div>
            </div>

            {{-- Insights Column --}}
            <div class="learning-column secondary">
                <div class="learning-card">
                    <div class="card-body">
                        <h6 class="text-uppercase text-muted fw-semibold mb-3" style="letter-spacing: 0.12em;">
                            Statistiques du cours
                        </h6>
                        <div class="learning-meta">
                            <div class="learning-meta__item">
                                <span>Étudiants</span>
                                <strong>{{ $courseStats['total_students'] ?? 0 }}</strong>
                            </div>
                            <div class="learning-meta__item">
                                <span>Durée totale</span>
                                <strong>{{ $courseStats['total_duration'] ?? 0 }} min</strong>
                            </div>
                            <div class="learning-meta__item">
                                <span>Leçons vidéos</span>
                                <strong>{{ $courseStats['video_lessons'] ?? 0 }}</strong>
                            </div>
                            <div class="learning-meta__item">
                                <span>Quiz</span>
                                <strong>{{ $courseStats['quiz_lessons'] ?? 0 }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="learning-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0 text-white fw-bold">Prochaines étapes</h6>
                            <a href="{{ route('courses.show', $course->slug) }}#curriculum" class="text-info small">Voir le plan complet</a>
                        </div>
                        <ul class="insight-list mb-0">
                                    @if(isset($nextLesson))
                                <li class="insight-list__item">
                                    <span>Leçon suivante</span>
                                    <strong>{{ Str::limit($nextLesson->title, 45) }}</strong>
                                </li>
                                    @endif
                            <li class="insight-list__item">
                                <span>Cours complétés</span>
                                <strong>{{ $progress['completed_lessons'] ?? 0 }}/{{ $progress['total_lessons'] ?? 0 }}</strong>
                            </li>
                            <li class="insight-list__item">
                                <span>Progression moyenne des étudiants</span>
                                <strong>{{ $courseStats['average_progress'] ?? 0 }}%</strong>
                            </li>
                            <li class="insight-list__item">
                                <span>Note moyenne</span>
                                <strong>{{ $courseStats['average_rating'] ?? 0 }}/5</strong>
                            </li>
                        </ul>
                                </div>
                            </div>

                @if(!empty($recommendedCourses) && $recommendedCourses->count())
                    <div class="learning-card">
                        <div class="card-body">
                            <h6 class="text-white fw-bold mb-3">Cours à explorer ensuite</h6>
                            <div class="recommended-courses">
                                @foreach($recommendedCourses as $recommended)
                                    <a href="{{ route('courses.show', $recommended->slug) }}" class="recommended-item">
                                        <div class="recommended-thumb">
                                            <img src="{{ $recommended->thumbnail_url ?? 'https://source.unsplash.com/300x200/?learning' }}" alt="{{ $recommended->title }}">
                                        </div>
                                        <div class="recommended-content flex-grow-1">
                                            <h6>{{ $recommended->title }}</h6>
                                            <div class="recommended-meta">
                                                <span><i class="fas fa-user me-1"></i>{{ $recommended->instructor?->name }}</span>
                                                <span><i class="fas fa-signal me-1"></i>{{ ucfirst($recommended->level) }}</span>
                                            </div>
                                            <div class="recommended-actions">
                                                <span class="badge bg-primary bg-opacity-10 text-info border-0">
                                                    {{ $recommended->stats['total_lessons'] ?? 0 }} leçons
                                                </span>
                                                <span class="badge bg-primary bg-opacity-10 text-warning border-0">
                                                    {{ $recommended->stats['average_rating'] ?? 0 }}/5
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                            </div>
                        @endif
            </div>
                    </div>
                </div>

    {{-- Mobile Outline Drawer --}}
    <div class="mobile-outline-drawer d-lg-none" id="mobile-outline-drawer" aria-hidden="true">
        <div class="mobile-outline-drawer__panel">
            <div class="mobile-outline-drawer__header">
                <h5 class="mb-0 fw-semibold text-white">Contenu du cours</h5>
                <button type="button" class="btn btn-sm btn-outline-light mobile-outline-close-btn" id="mobile-outline-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mobile-outline-drawer__body">
                <div class="mobile-outline-progress mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-uppercase text-muted fw-semibold">Progression</span>
                        <span class="fw-bold text-white">{{ $progress['overall_progress'] ?? 0 }}%</span>
                            </div>
                    <div class="learning-progress-bar">
                        <div class="learning-progress-bar__fill" style="width: {{ $progress['overall_progress'] ?? 0 }}%;"></div>
                            </div>
                    <span class="small text-muted d-block mt-1">{{ $progress['completed_lessons'] ?? 0 }} / {{ $progress['total_lessons'] ?? 0 }} leçons terminées</span>
                        </div>

                <div class="lesson-outline">
                                @foreach($course->sections as $section)
                        @php
                            $sectionLessons = $section->lessons ?? collect();
                            $isSectionOpen = $sectionLessons->contains(fn($lesson) => isset($activeLessonId) && $lesson->id === $activeLessonId);
                        @endphp
                        <div class="outline-section {{ $isSectionOpen ? 'open' : '' }}" data-section-id="mobile-{{ $section->id }}">
                            <button class="outline-section__header {{ $isSectionOpen ? 'active' : '' }}" type="button">
                                            <div>
                                    <p class="text-uppercase small mb-1 fw-semibold" style="color: #94a3b8;">Section {{ $loop->iteration }}</p>
                                    <h6 class="mb-0 fw-semibold" style="color: #f8fafc;">{{ $section->title }}</h6>
                                            </div>
                                <div class="text-end">
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-1">
                                        {{ $sectionLessons->count() }} leçons
                                    </span>
                                    <i class="fas fa-chevron-down ms-2 text-muted section-toggle-icon"></i>
                                        </div>
                            </button>
                                    
                            <div class="outline-section__body {{ $isSectionOpen ? '' : 'd-none' }}">
                                @foreach($sectionLessons as $sectionLesson)
                                            @php
                                        $isActive = isset($activeLessonId) && $sectionLesson->id === $activeLessonId;
                                        $isCompleted = $progress['completed_lessons_ids']->contains($sectionLesson->id ?? 0);
                                        $progressEntry = $progress['lesson_progress'][$sectionLesson->id] ?? null;
                                            @endphp
                                    <a href="{{ route('learning.lesson', ['course' => $course->slug, 'lesson' => $sectionLesson->id]) }}"
                                       class="outline-lesson {{ $isActive ? 'active' : '' }} {{ $isCompleted ? 'completed' : '' }}">
                                        <div class="outline-lesson__icon">
                                            @switch($sectionLesson->type)
                                                @case('video')
                                                    <i class="fas fa-play"></i>
                                                    @break
                                                @case('pdf')
                                                            <i class="fas fa-file-pdf"></i>
                                                    @break
                                                @case('quiz')
                                                    <i class="fas fa-star"></i>
                                                    @break
                                                @case('text')
                                                    <i class="fas fa-align-left"></i>
                                                    @break
                                                @default
                                                            <i class="fas fa-file"></i>
                                            @endswitch
                                                    </div>
                                                    <div class="flex-grow-1">
                                            <p class="outline-lesson__title mb-1">{{ $sectionLesson->title }}</p>
                                            <div class="outline-lesson__meta">
                                                @if($sectionLesson->duration)
                                                    <span><i class="far fa-clock me-1"></i>{{ $sectionLesson->duration }} min</span>
                                                @endif
                                                @if($progressEntry)
                                                    <span><i class="fas fa-chart-line me-1 text-success"></i>{{ round($progressEntry->progress_percentage) }}%</span>
                                                    @endif
                                                </div>
                                            </div>
                                        <i class="fas fa-chevron-right text-muted small"></i>
                                    </a>
                                            @endforeach
                                    </div>
                                </div>
                                @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const sectionHeaders = document.querySelectorAll('.outline-section__header');
    sectionHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const section = header.closest('.outline-section');
            const body = section.querySelector('.outline-section__body');
            const icon = header.querySelector('.section-toggle-icon');
            const isOpen = !body.classList.contains('d-none');

            header.classList.toggle('active', !isOpen);
            body.classList.toggle('d-none', isOpen);
            icon?.classList.toggle('rotate-180', !isOpen);
        });
    });

    document.querySelectorAll('.lesson-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            document.querySelectorAll('.lesson-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.lesson-tab-content').forEach(panel => panel.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById(`tab-${target}`).classList.add('active');
        });
    });

    const mobileDrawer = document.getElementById('mobile-outline-drawer');
    const mobileToggle = document.getElementById('mobile-outline-toggle');
    const mobileClose = document.getElementById('mobile-outline-close');

    const setDrawerState = (isOpen) => {
        mobileDrawer?.classList.toggle('is-open', isOpen);
        document.body.classList.toggle('overflow-hidden', isOpen);
    };

    mobileToggle?.addEventListener('click', () => setDrawerState(true));
    mobileClose?.addEventListener('click', () => setDrawerState(false));

    mobileDrawer?.addEventListener('click', (event) => {
        if (event.target === mobileDrawer) {
            setDrawerState(false);
        }
    });

    const progressFill = document.querySelector('.learning-progress-bar__fill');
    if (progressFill) {
        requestAnimationFrame(() => {
            const width = progressFill.style.width;
            progressFill.style.width = '0%';
            requestAnimationFrame(() => progressFill.style.width = width);
        });
    }
});

function markAsComplete(lessonId) {
    fetch(`/learning/courses/{{ $course->slug }}/lessons/${lessonId}/complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Erreur: ' + (data.message || 'Impossible de marquer la leçon comme terminée'));
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la mise à jour de la leçon.');
    });
}

function showLessonResources() {
    // Switch to resources tab
    document.querySelectorAll('.lesson-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.lesson-tab-content').forEach(panel => panel.classList.remove('active'));
    const resourceTab = document.querySelector('[data-tab="resources"]');
    if (resourceTab) {
        resourceTab.classList.add('active');
        document.getElementById('tab-resources').classList.add('active');
    }
}

@isset($activeLesson)
// API URLs for active lesson
const notesUrl = '{{ route('learning.notes.index', ['course' => $course->slug, 'lesson' => $activeLesson->id]) }}';
const notesStoreUrl = '{{ route('learning.notes.store', ['course' => $course->slug, 'lesson' => $activeLesson->id]) }}';
const notesAllUrl = '{{ route('learning.notes.all', ['course' => $course->slug, 'lesson' => $activeLesson->id]) }}';
const resourcesUrl = '{{ route('learning.resources.index', ['course' => $course->slug, 'lesson' => $activeLesson->id]) }}';
const discussionsUrl = '{{ route('learning.discussions.index', ['course' => $course->slug, 'lesson' => $activeLesson->id]) }}';
const discussionsStoreUrl = '{{ route('learning.discussions.store', ['course' => $course->slug, 'lesson' => $activeLesson->id]) }}';
const discussionsAllUrl = '{{ route('learning.discussions.all', ['course' => $course->slug, 'lesson' => $activeLesson->id]) }}';
const courseSlug = '{{ $course->slug }}';
const activeLessonId = '{{ $activeLesson->id }}';
const currentUserId = {{ auth()->id() ?? 0 }};

// Helper function to generate note delete URL
function getNoteDeleteUrl(noteId) {
    return `{{ route('learning.notes.index', ['course' => $course->slug, 'lesson' => $activeLesson->id]) }}/${noteId}`;
}

// Helper function to generate note update URL
function getNoteUpdateUrl(noteId) {
    return `{{ route('learning.notes.index', ['course' => $course->slug, 'lesson' => $activeLesson->id]) }}/${noteId}`;
}

// Helper function to generate discussion like URL
function getDiscussionLikeUrl(discussionId) {
    return `{{ route('learning.discussions.index', ['course' => $course->slug, 'lesson' => $activeLesson->id]) }}/${discussionId}/like`;
}

// Helper function to generate discussion update URL
function getDiscussionUpdateUrl(discussionId) {
    return `{{ route('learning.discussions.index', ['course' => $course->slug, 'lesson' => $activeLesson->id]) }}/${discussionId}`;
}

// Load Notes
function loadNotes() {
    fetch(notesUrl)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notesList = document.getElementById('notes-list');
                if (data.notes.length === 0) {
                    notesList.innerHTML = '<p class="text-muted">Aucune note pour cette leçon.</p>';
                } else {
                    const notesHtml = data.notes.map(note => `
                        <div class="card mb-2" style="background: rgba(0,51,102,0.75); border-color: rgba(255,204,51,0.15);" id="note-card-${note.id}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <small class="text-muted">${new Date(note.created_at).toLocaleDateString('fr-FR')}</small>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-info" onclick="toggleEditNoteInline(${note.id})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteNote(${note.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="note-content-display-inline" id="note-content-inline-${note.id}">
                                    <p class="mb-0" style="color: #cbd5e1;">${note.content}</p>
                                </div>
                                <form class="note-edit-form-inline" id="note-edit-form-inline-${note.id}" style="display: none;" onsubmit="updateNoteInline(event, ${note.id})">
                                    <textarea name="content" class="form-control mb-2" rows="4" required style="background: rgba(255,255,255,0.1); border-color: rgba(255,204,51,0.25); color: #fff;">${note.content}</textarea>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-save me-1"></i>Enregistrer
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleEditNoteInline(${note.id})">
                                            Annuler
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    `).join('');
                    
                    // Ajouter le bouton "Voir tout" si on a 5 notes (limite atteinte)
                    const viewAllButton = data.notes.length >= 5 ? `
                        <div class="text-center mt-3">
                            <a href="${notesAllUrl}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-list me-2"></i>Voir toutes les notes
                            </a>
                        </div>
                    ` : '';
                    
                    notesList.innerHTML = notesHtml + viewAllButton;
                }
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des notes:', error);
            alert('Erreur lors du chargement des notes');
        });
}

// Load Resources
function loadResources() {
    fetch(resourcesUrl)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const resourcesList = document.getElementById('resources-list');
                if (data.resources.length === 0) {
                    resourcesList.innerHTML = '<p class="text-muted">Aucune ressource disponible pour cette leçon.</p>';
                } else {
                    resourcesList.innerHTML = data.resources.map(resource => `
                        <div class="card mb-3" style="background: rgba(0,51,102,0.75); border-color: rgba(255,204,51,0.15);">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div class="flex-grow-1">
                                        <h6 class="text-white mb-1"><i class="fas fa-file-download me-2"></i>${resource.title}</h6>
                                        ${resource.description ? `<p class="text-muted small mb-2">${resource.description}</p>` : ''}
                                        <div class="d-flex gap-3 small text-muted">
                                            <span><i class="fas fa-file me-1"></i>${resource.file_type || 'Fichier'}</span>
                                            <span><i class="fas fa-weight me-1"></i>${resource.file_size}</span>
                                            <span><i class="fas fa-download me-1"></i>${resource.download_count} téléchargements</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('learning.resources.index', ['course' => $course->slug, 'lesson' => $activeLesson->id]) }}/${resource.id}/download" 
                                       class="btn btn-info btn-sm" target="_blank">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    `).join('');
                }
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des ressources:', error);
        });
}

// Load Discussions
function loadDiscussions() {
    fetch(discussionsUrl)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const discussionsList = document.getElementById('discussions-list');
                if (data.discussions.length === 0) {
                    discussionsList.innerHTML = '<p class="text-muted">Aucune discussion pour cette leçon. Soyez le premier à poser une question !</p>';
                } else {
                    const discussionsHtml = data.discussions.map(discussion => {
                        const canDeleteDiscussion = discussion.user_id === currentUserId;
                        const canEditDiscussion = discussion.user_id === currentUserId;
                        return `
                        <div class="card mb-3" style="background: rgba(0,51,102,0.75); border-color: rgba(255,204,51,0.15);" id="discussion-card-${discussion.id}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong class="text-white">${discussion.user.name}</strong>
                                        <small class="text-muted ms-2">${discussion.created_at}</small>
                                        ${discussion.is_pinned ? '<span class="badge bg-warning ms-2">Épinglé</span>' : ''}
                                        ${discussion.is_answered ? '<span class="badge bg-success ms-2">Répondu</span>' : ''}
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-info" onclick="toggleLike(${discussion.id})">
                                            <i class="fas fa-thumbs-up me-1"></i>${discussion.likes_count}
                                        </button>
                                        ${canEditDiscussion ? `
                                            <button class="btn btn-sm btn-outline-info" onclick="toggleEditDiscussionInline(${discussion.id})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        ` : ''}
                                        ${canDeleteDiscussion ? `
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteDiscussion(${discussion.id})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        ` : ''}
                                    </div>
                                </div>
                                <div class="discussion-content-display-inline" id="discussion-content-inline-${discussion.id}">
                                    <p class="mb-2" style="color: #cbd5e1;">${discussion.content}</p>
                                </div>
                                <form class="discussion-edit-form-inline" id="discussion-edit-form-inline-${discussion.id}" style="display: none;" onsubmit="updateDiscussionInline(event, ${discussion.id})">
                                    <textarea name="content" class="form-control mb-2" rows="4" required style="background: rgba(255,255,255,0.1); border-color: rgba(255,204,51,0.25); color: #fff;">${discussion.content}</textarea>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-save me-1"></i>Enregistrer
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleEditDiscussionInline(${discussion.id})">
                                            Annuler
                                        </button>
                                    </div>
                                </form>
                                ${discussion.replies_count > 0 ? `
                                    <div class="mt-3 ms-4">
                                        <p class="small text-muted mb-2"><i class="fas fa-comments me-1"></i>${discussion.replies_count} réponse(s)</p>
                                        ${discussion.replies.map(reply => {
                                            const canDeleteReply = reply.user_id === currentUserId;
                                            const canEditReply = reply.user_id === currentUserId;
                                            return `
                                            <div class="card mb-2" style="background: rgba(0,51,102,0.5); border-color: rgba(255,204,51,0.1);" id="reply-card-${reply.id}">
                                                <div class="card-body p-2">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <div>
                                                            <strong class="text-white small">${reply.user.name}</strong>
                                                            <small class="text-muted ms-2">${reply.created_at}</small>
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            ${canEditReply ? `
                                                                <button class="btn btn-sm btn-outline-info" onclick="toggleEditReplyInline(${reply.id})">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                            ` : ''}
                                                            ${canDeleteReply ? `
                                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteDiscussion(${reply.id})">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            ` : ''}
                                                        </div>
                                                    </div>
                                                    <div class="reply-content-display-inline" id="reply-content-inline-${reply.id}">
                                                        <p class="mb-0 small" style="color: #cbd5e1;">${reply.content}</p>
                                                    </div>
                                                    <form class="reply-edit-form-inline" id="reply-edit-form-inline-${reply.id}" style="display: none;" onsubmit="updateReplyInline(event, ${reply.id})">
                                                        <textarea name="content" class="form-control mb-2" rows="3" required style="background: rgba(255,255,255,0.1); border-color: rgba(255,204,51,0.25); color: #fff;">${reply.content}</textarea>
                                                        <div class="d-flex gap-2">
                                                            <button type="submit" class="btn btn-sm btn-success">
                                                                <i class="fas fa-save me-1"></i>Enregistrer
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleEditReplyInline(${reply.id})">
                                                                Annuler
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        `;
                                        }).join('')}
                                    </div>
                                ` : ''}
                                <button class="btn btn-sm btn-outline-light mt-2" onclick="replyToDiscussion(${discussion.id})">
                                    <i class="fas fa-reply me-1"></i>Répondre
                                </button>
                            </div>
                        </div>
                    `;
                    }).join('');
                    
                    // Ajouter le bouton "Voir tout" si on a 5 discussions (limite atteinte)
                    const viewAllButton = data.discussions.length >= 5 ? `
                        <div class="text-center mt-3">
                            <a href="${discussionsAllUrl}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-list me-2"></i>Voir toutes les discussions
                            </a>
                        </div>
                    ` : '';
                    
                    discussionsList.innerHTML = discussionsHtml + viewAllButton;
                }
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des discussions:', error);
        });
}

// Initialize form handlers when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Note form handler
    const noteForm = document.getElementById('note-form');
    if (noteForm) {
        noteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const content = document.getElementById('note-content').value;
            if (!content.trim()) {
                alert('Veuillez entrer une note');
                return;
            }
            
            fetch(notesStoreUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ content })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('note-content').value = '';
                    loadNotes();
                    alert('Note enregistrée avec succès !');
                } else {
                    alert('Erreur: ' + (data.message || 'Impossible d\'enregistrer la note'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'enregistrement de la note');
            });
        });
    }

    // Discussion form handler
    const discussionForm = document.getElementById('discussion-form');
    if (discussionForm) {
        discussionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const content = document.getElementById('discussion-content').value;
            if (!content.trim()) {
                alert('Veuillez entrer un message');
                return;
            }
            
            fetch(discussionsStoreUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ content })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('discussion-content').value = '';
                    loadDiscussions();
                    alert('Discussion publiée avec succès !');
                } else {
                    alert('Erreur: ' + (data.message || 'Impossible de publier la discussion'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la publication de la discussion');
            });
        });
    }

    // Attach tab click handlers
    const notesTab = document.querySelector('[data-tab="notes"]');
    if (notesTab) {
        notesTab.addEventListener('click', loadNotes);
    }
    
    const resourcesTab = document.querySelector('[data-tab="resources"]');
    if (resourcesTab) {
        resourcesTab.addEventListener('click', loadResources);
    }
    
    const discussionTab = document.querySelector('[data-tab="discussion"]');
    if (discussionTab) {
        discussionTab.addEventListener('click', loadDiscussions);
    }
});

function deleteNote(noteId) {
    if (!confirm('Supprimer cette note ?')) return;
    
    fetch(getNoteDeleteUrl(noteId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotes();
            alert('Note supprimée avec succès');
        } else {
            alert('Erreur: ' + (data.message || 'Impossible de supprimer la note'));
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la suppression de la note');
    });
}

function deleteDiscussion(discussionId) {
    if (!confirm('Supprimer cette discussion ?')) return;
    
    fetch(getDiscussionUpdateUrl(discussionId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadDiscussions();
            alert('Discussion supprimée avec succès');
        } else {
            alert('Erreur: ' + (data.message || 'Impossible de supprimer la discussion'));
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la suppression de la discussion');
    });
}

function toggleLike(discussionId) {
    fetch(getDiscussionLikeUrl(discussionId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadDiscussions();
        } else {
            alert('Erreur: ' + (data.message || 'Impossible d\'aimer cette discussion'));
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors du like');
    });
}

function replyToDiscussion(parentId) {
    const content = prompt('Votre réponse:');
    if (!content) return;
    
    fetch(discussionsStoreUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ content, parent_id: parentId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadDiscussions();
            alert('Réponse publiée avec succès !');
        } else {
            alert('Erreur: ' + (data.message || 'Impossible de publier la réponse'));
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la publication de la réponse');
    });
}

// Toggle edit mode for notes (inline)
function toggleEditNoteInline(noteId) {
    const contentDisplay = document.getElementById('note-content-inline-' + noteId);
    const editForm = document.getElementById('note-edit-form-inline-' + noteId);
    
    if (contentDisplay && editForm) {
        const isHidden = contentDisplay.style.display === 'none';
        contentDisplay.style.display = isHidden ? 'block' : 'none';
        editForm.style.display = isHidden ? 'none' : 'block';
    }
}

// Update note inline
function updateNoteInline(event, noteId) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const content = formData.get('content');
    
    fetch(getNoteUpdateUrl(noteId), {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ content })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotes();
            alert('Note mise à jour avec succès !');
        } else {
            alert('Erreur: ' + (data.message || 'Impossible de mettre à jour la note'));
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la mise à jour de la note');
    });
}

// Toggle edit mode for discussions (inline)
function toggleEditDiscussionInline(discussionId) {
    const contentDisplay = document.getElementById('discussion-content-inline-' + discussionId);
    const editForm = document.getElementById('discussion-edit-form-inline-' + discussionId);
    
    if (contentDisplay && editForm) {
        const isHidden = contentDisplay.style.display === 'none';
        contentDisplay.style.display = isHidden ? 'block' : 'none';
        editForm.style.display = isHidden ? 'none' : 'block';
    }
}

// Update discussion inline
function updateDiscussionInline(event, discussionId) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const content = formData.get('content');
    
    fetch(getDiscussionUpdateUrl(discussionId), {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ content })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadDiscussions();
            alert('Discussion mise à jour avec succès !');
        } else {
            alert('Erreur: ' + (data.message || 'Impossible de mettre à jour la discussion'));
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la mise à jour de la discussion');
    });
}

// Toggle edit mode for replies (inline)
function toggleEditReplyInline(replyId) {
    const contentDisplay = document.getElementById('reply-content-inline-' + replyId);
    const editForm = document.getElementById('reply-edit-form-inline-' + replyId);
    
    if (contentDisplay && editForm) {
        const isHidden = contentDisplay.style.display === 'none';
        contentDisplay.style.display = isHidden ? 'block' : 'none';
        editForm.style.display = isHidden ? 'none' : 'block';
    }
}

// Update reply inline
function updateReplyInline(event, replyId) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const content = formData.get('content');
    
    fetch(`/learning/courses/${courseSlug}/lessons/${activeLessonId}/discussions/${replyId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ content })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadDiscussions();
            alert('Réponse mise à jour avec succès !');
        } else {
            alert('Erreur: ' + (data.message || 'Impossible de mettre à jour la réponse'));
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la mise à jour de la réponse');
    });
}

// Load notes by default if on notes tab
if (document.getElementById('tab-notes')?.classList.contains('active')) {
    loadNotes();
}
@endisset
</script>
@endpush

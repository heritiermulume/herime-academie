@extends('layouts.app')

@section('title', $course->title . ' - Herime Academie')
@section('description', $course->meta_description ?: Str::limit($course->description, 160))

@push('styles')
<style>
:root {
    --primary-color: #003366;
    --accent-color: #ffcc33;
    --secondary-color: #f8f9fa;
    --text-color: #2c3e50;
    --text-muted: #6c757d;
    --border-color: #e9ecef;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --info-color: #17a2b8;
    --light-color: #f8f9fa;
    --dark-color: #343a40;
}

.course-details-page {
    background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
    min-height: 100vh;
    margin-top: 0;
    padding-top: 0;
}

@media (max-width: 991.98px) {
    .course-details-page {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
}

/* Hero Section */
.course-hero {
    background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%);
    color: white;
    padding: 2rem 0 3rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    margin-top: 0;
}

@media (max-width: 991.98px) {
    .course-hero {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
}

.course-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
    opacity: 0.1;
}

.course-hero .container {
    position: relative;
    z-index: 1;
}

.btn-back {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.3);
    display: inline-flex;
    align-items: center;
}

.btn-back:hover {
    background: rgba(255, 255, 255, 0.3);
    color: white;
    transform: translateX(-3px);
    border-color: rgba(255, 255, 255, 0.5);
}

.breadcrumb-modern {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 0.625rem 1rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
}

.breadcrumb-modern .breadcrumb {
    margin: 0;
    padding: 0;
}

.breadcrumb-modern .breadcrumb-item {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.875rem;
}

.breadcrumb-modern .breadcrumb-item a {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    transition: all 0.3s ease;
}

.breadcrumb-modern .breadcrumb-item a:hover {
    color: var(--accent-color);
}

.breadcrumb-modern .breadcrumb-item.active {
    color: var(--accent-color);
    font-weight: 600;
}

.breadcrumb-modern .breadcrumb-item + .breadcrumb-item::before {
    color: rgba(255, 255, 255, 0.6);
    content: "›";
    padding: 0 0.5rem;
}

.course-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.course-badge {
    padding: 0.4rem 0.875rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    white-space: nowrap;
}

.course-badge.featured {
    background: linear-gradient(135deg, var(--accent-color) 0%, #e6b800 100%);
    color: var(--primary-color);
}

.course-badge.free {
    background: linear-gradient(135deg, var(--success-color) 0%, #20c997 100%);
    color: white;
}

.course-badge.category {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.course-badge.level {
    background: rgba(255, 255, 255, 0.15);
    color: white;
}

.course-title-hero {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.3;
    margin-bottom: 1rem;
    color: white;
    word-wrap: break-word;
}

.course-stats-hero {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 0;
}

.course-stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.875rem;
    white-space: nowrap;
}

.course-stat-item i {
    color: var(--accent-color);
    font-size: 1rem;
    flex-shrink: 0;
}

.course-stat-item span {
    font-weight: 500;
}

/* Main Content */
.main-content {
    padding-bottom: 3rem;
}

.content-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

.content-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.section-title-modern {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid var(--accent-color);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-title-modern i {
    color: var(--accent-color);
}

/* Video Preview */
.video-preview-wrapper {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 2rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    cursor: pointer;
    transition: all 0.3s ease;
}

.video-preview-wrapper:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2);
}

.video-preview-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 51, 102, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
    transition: all 0.3s ease;
}

.video-preview-wrapper:hover .video-preview-overlay {
    background: rgba(0, 51, 102, 0.85);
}

.play-button-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.95);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    font-size: 2rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.video-preview-wrapper:hover .play-button-large {
    transform: scale(1.1);
    background: white;
}

/* Course Description */
.course-description {
    line-height: 1.8;
    color: var(--text-color);
    font-size: 1.05rem;
}

.course-description p {
    margin-bottom: 1rem;
}

/* What You'll Learn */
.learning-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
    margin-bottom: 0.75rem;
    background: var(--light-color);
    border-radius: 12px;
    border-left: 4px solid var(--success-color);
    transition: all 0.3s ease;
}

.learning-item:hover {
    background: #e9ecef;
    transform: translateX(4px);
}

.learning-item i {
    color: var(--success-color);
    font-size: 1.25rem;
    margin-top: 0.125rem;
    flex-shrink: 0;
}

/* Requirements */
.requirement-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
    margin-bottom: 0.75rem;
    background: var(--light-color);
    border-radius: 12px;
    border-left: 4px solid var(--info-color);
}

.requirement-item i {
    color: var(--info-color);
    font-size: 1.25rem;
    margin-top: 0.125rem;
    flex-shrink: 0;
}

/* Curriculum */
.curriculum-section {
    margin-bottom: 1rem;
}

.curriculum-section-header {
    background: var(--primary-color);
    color: white;
    padding: 1.25rem;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.curriculum-section-header:hover {
    background: #004080;
}

.curriculum-section-header h5 {
    margin: 0;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.curriculum-section-header .fa-chevron-right {
    display: inline-block;
}

.curriculum-section-header .fa-chevron-down {
    display: none;
}

.curriculum-section-header[aria-expanded="true"] .fa-chevron-right {
    display: none !important;
}

.curriculum-section-header[aria-expanded="true"] .fa-chevron-down {
    display: inline-block !important;
}

.curriculum-section-header[aria-expanded="false"] .fa-chevron-down {
    display: none !important;
}

.curriculum-section-header[aria-expanded="false"] .fa-chevron-right {
    display: inline-block !important;
}

.curriculum-section-stats {
    display: flex;
    gap: 1rem;
    font-size: 0.875rem;
    opacity: 0.9;
}

.curriculum-lessons {
    background: white;
    border: 1px solid var(--border-color);
    border-top: none;
    border-radius: 0 0 12px 12px;
    padding: 0;
}

.curriculum-section-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
    opacity: 0;
    pointer-events: none;
}

.curriculum-section-content.is-open {
    max-height: 5000px;
    opacity: 1;
    transition: max-height 0.4s ease-in, opacity 0.3s ease-in;
    pointer-events: auto;
}

.lesson-item {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

.lesson-item:last-child {
    border-bottom: none;
}

.lesson-item:hover {
    background: var(--light-color);
}

.lesson-item.locked {
    opacity: 0.6;
}

.lesson-item.preview-clickable {
    cursor: pointer !important;
    user-select: none;
}

.lesson-item.preview-clickable:hover {
    background: rgba(0, 51, 102, 0.05) !important;
    transform: translateX(2px);
}

.lesson-item.preview-clickable:active {
    background: rgba(0, 51, 102, 0.1) !important;
}

.lesson-item.preview-clickable * {
    pointer-events: none;
}

.preview-item {
    user-select: none;
}

.preview-item.active {
    background: rgba(0, 51, 102, 0.1) !important;
    border-color: #003366 !important;
}

.preview-player-wrapper {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.preview-player-wrapper:not(.active) {
    display: none !important;
}

.modal-fixed-height .modal-content {
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

.modal-fixed-height .modal-body {
    overflow-y: auto;
    overflow-x: hidden;
    flex: 1;
    min-height: 0;
    -webkit-overflow-scrolling: touch;
}

.modal-fixed-height .modal-body {
    scrollbar-width: thin;
    scrollbar-color: rgba(0, 51, 102, 0.3) transparent;
}

.modal-fixed-height .modal-body::-webkit-scrollbar {
    width: 8px;
}

.modal-fixed-height .modal-body::-webkit-scrollbar-track {
    background: transparent;
}

.modal-fixed-height .modal-body::-webkit-scrollbar-thumb {
    background-color: rgba(0, 51, 102, 0.3);
    border-radius: 4px;
}

.modal-fixed-height .modal-body::-webkit-scrollbar-thumb:hover {
    background-color: rgba(0, 51, 102, 0.5);
}

.preview-list-scrollable {
    max-height: none;
}

.lesson-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.lesson-icon.video {
    background: rgba(220, 53, 69, 0.1);
    color: var(--danger-color);
}

.lesson-icon.text {
    background: rgba(23, 162, 184, 0.1);
    color: var(--info-color);
}

.lesson-icon.pdf {
    background: rgba(220, 53, 69, 0.1);
    color: var(--danger-color);
}

.lesson-icon.quiz {
    background: rgba(255, 193, 7, 0.1);
    color: var(--warning-color);
}

.lesson-content {
    flex: 1;
}

.lesson-title {
    font-weight: 500;
    color: var(--text-color);
    margin-bottom: 0.25rem;
}

.lesson-meta {
    font-size: 0.875rem;
    color: var(--text-muted);
}

.lesson-preview-badge {
    background: var(--accent-color);
    color: var(--primary-color);
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Instructor Card */
.instructor-card {
    display: flex;
    gap: 1.5rem;
    align-items: center;
}

.instructor-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--accent-color);
    flex-shrink: 0;
}

.instructor-info h5 {
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.instructor-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-top: 0.75rem;
    font-size: 0.875rem;
    color: var(--text-muted);
}

/* Reviews */
.rating-summary {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.rating-score {
    font-size: 3rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1;
}

.rating-stars {
    color: var(--warning-color);
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.rating-count {
    color: var(--text-muted);
    font-size: 0.875rem;
}

.rating-distribution {
    margin-bottom: 2rem;
}

.rating-bar-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.rating-bar-label {
    min-width: 80px;
    font-size: 0.875rem;
    color: var(--text-color);
}

.rating-bar {
    flex: 1;
    height: 8px;
    background: var(--border-color);
    border-radius: 4px;
    overflow: hidden;
}

.rating-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--warning-color) 0%, #ffb300 100%);
    border-radius: 4px;
    transition: width 0.5s ease;
}

.rating-bar-count {
    min-width: 30px;
    text-align: right;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.review-card {
    background: var(--light-color);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    border: 1px solid var(--border-color);
}

.review-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.review-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.review-author {
    flex: 1;
}

.review-author-name {
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 0.25rem;
}

.review-date {
    font-size: 0.875rem;
    color: var(--text-muted);
}

.review-comment {
    color: var(--text-color);
    line-height: 1.6;
}

/* Sidebar */
.course-sidebar {
    position: sticky;
    top: 2rem;
}

.sidebar-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    margin-bottom: 1.5rem;
    border: 1px solid var(--border-color);
}

.price-display {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid var(--border-color);
}

.price-free {
    font-size: 2rem;
    font-weight: 700;
    color: var(--success-color);
}

.price-current {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1;
    margin-bottom: 0.5rem;
}

.price-original {
    font-size: 1.25rem;
    color: var(--text-muted);
    text-decoration: line-through;
    margin-bottom: 0.75rem;
}

.price-discount {
    background: var(--danger-color);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    display: inline-block;
}

/* Promotion Countdown Styles */
.promotion-countdown {
    background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);
    border: 1px solid #fecaca;
    border-radius: 12px;
    padding: 0.75rem 1rem;
    text-align: center;
    width: 100%;
}

.countdown-label {
    font-size: 0.75rem;
    margin-bottom: 0.5rem;
    display: block;
}

.countdown-text {
    font-size: 0.95rem;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.25rem;
}

.countdown-text span {
    display: inline-block;
    min-width: fit-content;
    white-space: nowrap;
}

.mobile-countdown {
    font-size: 0.7rem;
    background: transparent;
    border: none;
    padding: 0.25rem 0;
    margin-top: 0.25rem;
}

.mobile-countdown .countdown-text {
    font-size: 0.7rem;
    gap: 0.125rem;
}

.mobile-countdown .countdown-text span {
    min-width: auto;
}

.course-features-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.course-features-list li {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-color);
}

.course-features-list li:last-child {
    border-bottom: none;
}

.course-features-list i {
    color: var(--primary-color);
    font-size: 1.125rem;
    width: 24px;
    text-align: center;
}

.share-buttons {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.share-btn {
    flex: 1;
    min-width: 60px;
    padding: 0.75rem;
    border-radius: 12px;
    border: 1px solid var(--border-color);
    background: white;
    color: var(--primary-color);
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.share-btn:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    transform: translateY(-2px);
}

.share-btn i {
    font-size: 1.25rem;
}

/* Related Courses */
.related-course-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid var(--border-color);
    height: 100%;
}

.related-course-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.related-course-card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

.related-course-card-body {
    padding: 1.25rem;
}

.related-course-title {
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 0.75rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 3rem;
}

.related-course-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary-color);
}

/* Mobile Payment Button */
.mobile-payment-btn {
    position: fixed;
    bottom: 60px; /* Au-dessus de la navigation mobile en bas */
    left: 0;
    right: 0;
    background: white;
    border-top: 2px solid var(--border-color);
    padding: 0.625rem 1rem;
    z-index: 999; /* Sous la navigation mobile mais au-dessus du contenu */
    box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.15);
    display: none;
    box-sizing: border-box;
}

@media (max-width: 991.98px) {
    .mobile-payment-btn {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
}

@media (max-width: 575.98px) {
    .mobile-payment-btn {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }
}

.mobile-payment-btn .container-fluid {
    padding-left: 0 !important;
    padding-right: 0 !important;
    width: 100%;
    box-sizing: border-box;
}

.mobile-payment-btn .row {
    margin-left: 0;
    margin-right: 0;
    width: 100%;
}

.mobile-price-col {
    flex: 1;
    min-width: 0;
    padding-left: 0;
    padding-right: 0.5rem;
}

.mobile-btn-col {
    flex-shrink: 0;
    padding-left: 0;
    padding-right: 0;
}

.mobile-price {
    width: 100%;
}

.mobile-price-label {
    font-size: 0.65rem;
    color: var(--text-muted);
    margin-bottom: 0.125rem;
    display: block;
    line-height: 1.2;
}

.mobile-price-wrapper {
    display: flex;
    flex-direction: column;
    gap: 0;
    line-height: 1.3;
}

.mobile-price-value {
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.mobile-price-original {
    font-size: 0.85rem;
    color: var(--text-muted);
    text-decoration: line-through;
    line-height: 1.2;
}

.mobile-price-discount {
    font-size: 0.7rem;
    color: var(--danger-color);
    font-weight: 600;
    margin-top: 0.125rem;
    line-height: 1.2;
}

.mobile-payment-btn .btn {
    flex-shrink: 0;
    white-space: nowrap;
    padding: 0.5rem 0.75rem;
    font-size: 0.7rem;
    min-width: 120px;
    max-width: 140px;
    width: auto;
    text-align: center;
    line-height: 1.2;
    overflow: visible;
    text-overflow: clip;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
}

.mobile-payment-btn .btn i {
    font-size: 0.7rem;
    flex-shrink: 0;
    margin-right: 0.3rem;
}

.mobile-payment-btn .btn,
.mobile-payment-btn .btn-group,
.mobile-payment-btn .d-grid {
    max-width: 140px;
    min-width: 120px;
    box-sizing: border-box;
}

.mobile-payment-btn .btn-group .btn,
.mobile-payment-btn .d-grid .btn {
    font-size: 0.65rem;
    padding: 0.4rem 0.6rem;
    min-width: auto;
    max-width: 100%;
    white-space: nowrap;
    line-height: 1.2;
    height: 36px;
    overflow: visible;
    text-overflow: clip;
    box-sizing: border-box;
}

.mobile-payment-btn .d-grid {
    gap: 0.3rem !important;
}

/* S'assurer que les boutons ne dépassent pas */
.mobile-payment-btn > .container > div:last-child {
    max-width: 140px;
    flex-shrink: 0;
    box-sizing: border-box;
    margin-right: 0;
}

/* Forcer le respect des marges */
.mobile-payment-btn * {
    box-sizing: border-box;
}

/* Responsive Design */
@media (max-width: 991.98px) {
    .main-content {
        padding-bottom: 140px; /* Espace pour le bouton de paiement (80px) + navigation mobile (60px) */
    }
    
    .course-hero {
        padding: 0.75rem 0 1.5rem;
        margin-bottom: 1.5rem;
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    
    .course-title-hero {
        font-size: 1.75rem;
        line-height: 1.3;
        margin-bottom: 0.875rem;
    }
    
    .course-stats-hero {
        gap: 0.75rem;
    }
    
    .course-stat-item {
        font-size: 0.8125rem;
    }
    
    .course-stat-item i {
        font-size: 0.875rem;
    }
    
    .breadcrumb-modern {
        padding: 0.5rem 0.75rem;
        margin-bottom: 0.75rem;
        margin-top: 0.5rem;
        font-size: 0.75rem;
    }
    
    .course-badges {
        gap: 0.4rem;
        margin-bottom: 0.875rem;
    }
    
    .course-badge {
        padding: 0.35rem 0.75rem;
        font-size: 0.7rem;
    }
    
    .mobile-payment-btn {
        display: flex;
    }
    
    .course-sidebar {
        position: relative;
        top: 0;
    }
    
    .sidebar-card {
        margin-bottom: 1rem;
        padding: 1rem;
    }
    
    .sidebar-card .btn,
    .sidebar-card .btn-lg,
    .sidebar-card .btn-sm {
        padding: 0.45rem 0.75rem;
        font-size: 0.8rem;
        width: 100%;
    }
    
    .sidebar-card .d-grid {
        gap: 0.5rem !important;
    }
    
    .sidebar-card .d-grid .btn {
        padding: 0.45rem 0.65rem;
        font-size: 0.8rem;
    }
    
    .price-display {
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }
    
    .price-current {
        font-size: 1.75rem;
    }
    
    .price-original {
        font-size: 0.9rem;
    }
    
    .price-discount {
        padding: 0.35rem 0.65rem;
        font-size: 0.7rem;
    }
    
    .instructor-card {
        flex-direction: column;
        text-align: center;
    }
    
    .rating-summary {
        flex-direction: column;
        text-align: center;
    }
}

@media (max-width: 767.98px) {
    .course-hero {
        padding: 1.25rem 0 1.75rem;
    }
    
    .course-title-hero {
        font-size: 1.5rem;
        margin-bottom: 0.75rem;
    }
    
    .content-card {
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }
    
    .section-title-modern {
        font-size: 1.25rem;
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
    }
    
    .course-stats-hero {
        gap: 0.625rem;
        flex-direction: row;
        justify-content: flex-start;
    }
    
    .course-stat-item {
        font-size: 0.75rem;
    }
    
    .breadcrumb-modern {
        padding: 0.5rem 0.625rem;
        font-size: 0.75rem;
    }
    
    .breadcrumb-modern .breadcrumb-item {
        font-size: 0.75rem;
    }
    
    .course-badge {
        padding: 0.3rem 0.625rem;
        font-size: 0.65rem;
    }
    
    .share-buttons {
        justify-content: center;
    }
    
    .share-btn {
        flex: 0 0 auto;
    }
    
    .mobile-payment-btn {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
        padding-top: 0.625rem;
        padding-bottom: 0.625rem;
    }
    
    .mobile-payment-btn .container-fluid {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    
    .mobile-price-col {
        padding-right: 0.5rem;
    }
    
    .mobile-price {
        width: 100%;
    }
    
    .mobile-price-value {
        font-size: 1.25rem;
    }
    
    .mobile-price-original {
        font-size: 0.8rem;
    }
    
    .mobile-price-discount {
        font-size: 0.65rem;
    }
    
    .mobile-payment-btn .btn {
        padding: 0.45rem 0.65rem;
        font-size: 0.68rem;
        min-width: 115px;
        max-width: 135px;
        white-space: nowrap;
        line-height: 1.2;
        height: 36px;
        overflow: visible;
        text-overflow: clip;
    }
    
    .mobile-payment-btn .btn i {
        font-size: 0.68rem;
        margin-right: 0.25rem;
    }
    
    .mobile-payment-btn .btn-group,
    .mobile-payment-btn .d-grid {
        max-width: 135px;
        min-width: 115px;
    }
    
    .mobile-payment-btn .btn-group .btn,
    .mobile-payment-btn .d-grid .btn {
        font-size: 0.62rem;
        padding: 0.35rem 0.55rem;
        white-space: nowrap;
        line-height: 1.2;
        height: 34px;
        overflow: visible;
        text-overflow: clip;
    }
    
    .promotion-countdown {
        padding: 0.625rem 0.75rem;
    }
    
    .countdown-label {
        font-size: 0.7rem;
    }
    
    .countdown-text {
        font-size: 0.85rem;
        flex-wrap: wrap;
    }
    
    .sidebar-card {
        padding: 1rem;
    }
    
    .sidebar-card .btn,
    .sidebar-card .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        width: 100%;
    }
    
    .sidebar-card .btn-sm {
        padding: 0.4rem 0.75rem;
        font-size: 0.8rem;
    }
    
    .sidebar-card .d-grid {
        gap: 0.75rem !important;
    }
    
    .sidebar-card .d-grid .btn {
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
    }
    
    .price-display {
        padding-bottom: 1.25rem;
        margin-bottom: 1.25rem;
    }
    
    .price-current {
        font-size: 2rem;
    }
    
    .price-original {
        font-size: 1rem;
    }
    
    .price-discount {
        padding: 0.4rem 0.75rem;
        font-size: 0.75rem;
    }
}

@media (max-width: 575.98px) {
    .course-hero {
        padding: 0.5rem 0 1.25rem;
        margin-bottom: 1rem;
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    
    .course-title-hero {
        font-size: 1.375rem;
        line-height: 1.35;
        margin-bottom: 0.625rem;
    }
    
    .content-card {
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1.25rem;
    }
    
    .section-title-modern {
        font-size: 1.125rem;
        margin-bottom: 1rem;
        padding-bottom: 0.625rem;
    }
    
    .section-title-modern i {
        font-size: 1rem;
    }
    
    .course-stats-hero {
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .course-stat-item {
        font-size: 0.7rem;
        gap: 0.375rem;
    }
    
    .course-stat-item i {
        font-size: 0.75rem;
    }
    
    .breadcrumb-modern {
        padding: 0.45rem 0.5rem;
        margin-bottom: 0.625rem;
        margin-top: 0.5rem;
        font-size: 0.65rem;
    }
    
    .breadcrumb-modern .breadcrumb-item {
        font-size: 0.7rem;
    }
    
    .breadcrumb-modern .breadcrumb-item + .breadcrumb-item::before {
        padding: 0 0.375rem;
    }
    
    .course-badges {
        gap: 0.35rem;
        margin-bottom: 0.625rem;
    }
    
    .course-badge {
        padding: 0.25rem 0.5rem;
        font-size: 0.625rem;
    }
    
    .play-button-large {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
    
    .instructor-avatar {
        width: 80px;
        height: 80px;
    }
    
    .mobile-payment-btn {
        padding: 0.5rem 0.75rem !important;
        bottom: 60px;
    }
    
    .mobile-payment-btn .container-fluid {
        padding-left: 0px !important;
        padding-right: 110px !important;
    }
    
    .mobile-price-col {
        padding-right: 0.5rem;
    }
    
    .mobile-price {
        width: 100%;
    }
    
    .mobile-price-label {
        font-size: 0.65rem;
        margin-bottom: 0.1rem;
    }
    
    .mobile-price-value {
        font-size: 1.4rem;
    }
    
    .mobile-price-original {
        font-size: 0.85rem;
    }
    
    .mobile-price-discount {
        font-size: 0.7rem;
        margin-top: 0.1rem;
    }
    
    .mobile-countdown {
        font-size: 0.65rem;
        margin-top: 0.15rem;
    }
    
    .mobile-countdown .countdown-text {
        font-size: 0.65rem;
    }
    
    .mobile-payment-btn > .container > div:last-child {
        flex-shrink: 0;
        min-width: 110px;
        max-width: 130px;
        padding-top: 0;
        box-sizing: border-box;
    }
    
    .mobile-payment-btn .btn {
        padding: 0.4rem 0.55rem;
        font-size: 0.65rem;
        width: 100%;
        min-width: 110px;
        max-width: 130px;
        white-space: nowrap;
        line-height: 1.2;
        height: 34px;
        overflow: visible;
        text-overflow: clip;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
    }
    
    .mobile-payment-btn .btn i {
        font-size: 0.65rem;
        margin-right: 0.2rem;
        flex-shrink: 0;
    }
    
    .mobile-payment-btn .btn-group,
    .mobile-payment-btn .d-grid {
        max-width: 130px;
        min-width: 110px;
        width: 100%;
        box-sizing: border-box;
    }
    
    .mobile-payment-btn .btn-group .btn,
    .mobile-payment-btn .d-grid .btn {
        font-size: 0.6rem;
        padding: 0.3rem 0.5rem;
        min-width: auto;
        max-width: 100%;
        white-space: nowrap;
        line-height: 1.2;
        height: 32px;
        overflow: visible;
        text-overflow: clip;
        box-sizing: border-box;
    }
    
    .mobile-payment-btn .d-grid {
        gap: 0.25rem !important;
    }
    
    .mobile-payment-btn .d-grid .btn i {
        font-size: 0.6rem;
        margin-right: 0.2rem;
    }
    
    .promotion-countdown {
        padding: 0.5rem 0.625rem;
    }
    
    .countdown-label {
        font-size: 0.65rem;
        margin-bottom: 0.375rem;
    }
    
    .countdown-text {
        font-size: 0.8rem;
        gap: 0.125rem;
    }
    
    .countdown-text span {
        font-size: inherit;
    }
}
</style>
@endpush

@section('content')
@php
    // Toutes les sections sont déjà filtrées par is_published dans le contrôleur
    $publishedSections = $course->sections->where('is_published', true);
    
    $hasVideoPreview = $course->video_preview || $course->video_preview_youtube_id;
    $hasPreviewLessons = $publishedSections->flatMap(function($section) {
        return $section->lessons->where('is_preview', true)->where('type', 'video');
    })->count() > 0;
    $hasAnyPreview = $hasVideoPreview || $hasPreviewLessons;
    $learnings = $course->getWhatYouWillLearnArray();
    $requirements = $course->getRequirementsArray();
    
    // Calculer les statistiques depuis les données de la base de données
    $totalLessons = $publishedSections->sum(function($section) { 
        return $section->lessons->where('is_published', true)->count(); 
    });
    $totalDuration = $publishedSections->sum(function($section) { 
        return $section->lessons->where('is_published', true)->sum('duration'); 
    });
    
    $languageNames = [
        'fr' => 'Français', 'en' => 'Anglais', 'es' => 'Espagnol', 'de' => 'Allemand',
        'it' => 'Italien', 'pt' => 'Portugais', 'ar' => 'Arabe', 'zh' => 'Chinois',
        'ja' => 'Japonais', 'ko' => 'Coréen', 'ru' => 'Russe', 'nl' => 'Néerlandais',
    ];
    $displayLanguage = $languageNames[$course->language] ?? $course->language ?? 'Non spécifiée';
@endphp

<div class="course-details-page">
    <!-- Hero Section -->
    <section class="course-hero">
        <div class="container">
            <nav aria-label="breadcrumb" class="breadcrumb-modern">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">Cours</a></li>
                    @if($course->category)
                    <li class="breadcrumb-item"><a href="{{ route('courses.category', $course->category->slug) }}">{{ $course->category->name }}</a></li>
                    @endif
                    <li class="breadcrumb-item active">{{ Str::limit($course->title, 40) }}</li>
                </ol>
            </nav>

            <h1 class="course-title-hero">{{ $course->title }}</h1>

            <div class="course-stats-hero">
                <div class="course-stat-item">
                    <i class="fas fa-star"></i>
                    <span>{{ number_format($course->reviews->avg('rating') ?? 0, 1) }} ({{ $course->reviews->count() }} avis)</span>
                </div>
                <div class="course-stat-item">
                    <i class="fas fa-clock"></i>
                    <span>{{ $totalDuration }} min</span>
                </div>
                <div class="course-stat-item">
                    <i class="fas fa-play-circle"></i>
                    <span>{{ $totalLessons }} leçons</span>
                </div>
                <div class="course-stat-item">
                    <i class="fas fa-language"></i>
                    <span>{{ $displayLanguage }}</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container main-content">
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Video Preview -->
                @if($hasAnyPreview)
                <div class="content-card">
                    <div class="video-preview-wrapper" data-bs-toggle="modal" data-bs-target="#coursePreviewModal">
                        <div class="ratio ratio-16x9">
                            @if($course->thumbnail)
                                <img src="{{ $course->thumbnail }}" 
                                 alt="{{ $course->title }}" 
                                 class="img-fluid" 
                                 style="object-fit: cover;">
                            @else
                                <div class="d-flex align-items-center justify-content-center bg-primary text-white" style="font-size: 3rem; font-weight: bold;">
                                    {{ strtoupper(substr($course->title, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="video-preview-overlay">
                            <div class="play-button-large">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @elseif($course->thumbnail)
                <div class="content-card">
                    <img src="{{ $course->thumbnail }}" 
                         alt="{{ $course->title }}" 
                         class="img-fluid rounded" 
                         style="width: 100%; height: auto; border-radius: 16px;">
                </div>
                @endif

                <!-- Course Description -->
                <div class="content-card">
                    <h2 class="section-title-modern">
                        <i class="fas fa-book-open"></i>
                        Description du cours
                    </h2>
                    <div class="course-description">
                        {!! nl2br(e($course->description)) !!}
                    </div>
                </div>

                <!-- What You'll Learn -->
                @if(count($learnings) > 0)
                <div class="content-card">
                    <h2 class="section-title-modern">
                        <i class="fas fa-graduation-cap"></i>
                        Ce que vous allez apprendre
                    </h2>
                    <div class="row">
                        @foreach($learnings as $item)
                        <div class="col-md-6">
                            <div class="learning-item">
                                <i class="fas fa-check-circle"></i>
                                <span>{{ $item }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Requirements -->
                @if(count($requirements) > 0)
                <div class="content-card">
                    <h2 class="section-title-modern">
                        <i class="fas fa-list-check"></i>
                        Prérequis
                    </h2>
                    <div class="row">
                        @foreach($requirements as $requirement)
                        <div class="col-md-6">
                            <div class="requirement-item">
                                <i class="fas fa-circle-check"></i>
                                <span>{{ $requirement }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Course Curriculum -->
                @php
                    // Utiliser uniquement les sections publiées depuis la base de données
                    $publishedSectionsList = $course->sections->where('is_published', true)->sortBy('sort_order');
                @endphp
                @if($publishedSectionsList->count() > 0)
                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="section-title-modern mb-0">
                            <i class="fas fa-list-ul"></i>
                            Programme du cours
                        </h2>
                        <div class="text-muted">
                            <i class="fas fa-play-circle me-1"></i>{{ $totalLessons }} leçon{{ $totalLessons > 1 ? 's' : '' }}
                            <i class="fas fa-clock ms-3 me-1"></i>{{ $totalDuration }} min
                        </div>
                    </div>

                    @foreach($publishedSectionsList as $index => $section)
                    <div class="curriculum-section mb-3">
                        <div class="curriculum-section-header" 
                             data-section-id="section{{ $section->id }}"
                             aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                             aria-controls="section{{ $section->id }}"
                             role="button"
                             tabindex="0">
                            <h5>
                                <i class="fas fa-chevron-down"></i>
                                <i class="fas fa-chevron-right"></i>
                                {{ $section->title }}
                            </h5>
                            @php
                                // Calculer les statistiques de la section depuis les données de la base de données
                                $sectionPublishedLessons = $section->lessons->where('is_published', true);
                                $sectionLessonsCount = $sectionPublishedLessons->count();
                                $sectionDuration = $sectionPublishedLessons->sum('duration');
                            @endphp
                            <div class="curriculum-section-stats">
                                <span><i class="fas fa-play-circle me-1"></i>{{ $sectionLessonsCount }}</span>
                                <span><i class="fas fa-clock me-1"></i>{{ $sectionDuration }} min</span>
                            </div>
                        </div>
                        <div class="curriculum-section-content {{ $index === 0 ? 'is-open' : '' }}" id="section{{ $section->id }}">
                            <div class="curriculum-lessons">
                                @php
                                    // Filtrer et trier les leçons publiées depuis la base de données
                                    $publishedLessons = $section->lessons->where('is_published', true)->sortBy('sort_order');
                                @endphp
                                @foreach($publishedLessons as $lesson)
                                @php
                                    $isPreview = $lesson->is_preview;
                                    $isLocked = !$isEnrolled && !$isPreview;
                                    $lessonTypeClass = match($lesson->type) {
                                        'video' => 'video',
                                        'text' => 'text',
                                        'pdf' => 'pdf',
                                        'quiz' => 'quiz',
                                        default => 'video'
                                    };
                                @endphp
                                <div class="lesson-item {{ $isLocked ? 'locked' : '' }} {{ $isPreview && $lesson->type === 'video' ? 'preview-clickable' : '' }}" 
                                     @if($isPreview && $lesson->type === 'video' && ($lesson->youtube_video_id || $lesson->file_path || $lesson->content_url))
                                     data-preview-lesson="{{ $lesson->id }}"
                                     data-preview-title="{{ htmlspecialchars($lesson->title, ENT_QUOTES, 'UTF-8') }}"
                                     data-preview-youtube-id="{{ $lesson->youtube_video_id ?? '' }}"
                                     data-preview-is-unlisted="{{ $lesson->is_unlisted ? '1' : '0' }}"
                                     data-preview-video-url="{{ ($lesson->file_path ? \App\Helpers\FileHelper::lessonFile($lesson->file_path) : ($lesson->content_url && !filter_var($lesson->content_url, FILTER_VALIDATE_URL) ? \App\Helpers\FileHelper::lessonFile($lesson->content_url) : ($lesson->content_url ?? ''))) }}"
                                     data-preview-section="{{ htmlspecialchars($section->title, ENT_QUOTES, 'UTF-8') }}"
                                     style="cursor: pointer;"
                                     onclick="event.stopPropagation(); openPreviewLesson({{ $lesson->id }}, this); return false;"
                                     @endif>
                                    <div class="lesson-icon {{ $lessonTypeClass }}">
                                        <i class="fas fa-{{ $lesson->type === 'video' ? 'play' : ($lesson->type === 'text' ? 'file-text' : ($lesson->type === 'pdf' ? 'file-pdf' : 'question-circle')) }}"></i>
                                    </div>
                                    <div class="lesson-content">
                                        <div class="lesson-title">{{ $lesson->title }}</div>
                                        <div class="lesson-meta">
                                            @if($lesson->duration)
                                            <i class="fas fa-clock me-1"></i>{{ $lesson->duration }} min
                                            @endif
                                            @if($isPreview)
                                            <span class="lesson-preview-badge ms-2">
                                                <i class="fas fa-eye me-1"></i>Aperçu
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Instructor -->
                <div class="content-card">
                    <h2 class="section-title-modern">
                        <i class="fas fa-chalkboard-teacher"></i>
                        Votre instructeur
                    </h2>
                    <div class="instructor-card">
                        @if($course->instructor->avatar)
                            <img src="{{ $course->instructor->avatar }}" 
                             alt="{{ $course->instructor->name }}" 
                             class="instructor-avatar">
                        @else
                            <div class="instructor-avatar d-flex align-items-center justify-content-center bg-primary text-white" style="font-size: 2rem; font-weight: bold; border-radius: 50%;">
                                {{ strtoupper(substr($course->instructor->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="flex-grow-1">
                            <h5>{{ $course->instructor->name }}</h5>
                            @if($course->instructor->bio)
                            <p class="text-muted mb-0">{{ Str::limit($course->instructor->bio, 200) }}</p>
                            @endif
                            <div class="instructor-stats">
                                <span><i class="fas fa-book me-1"></i>{{ $course->instructor->courses_count ?? $course->instructor->courses->count() }} cours</span>
                                <span><i class="fas fa-users me-1"></i>{{ $course->instructor->courses->sum(function($c) { return $c->enrollments_count ?? $c->enrollments->count(); }) }} étudiants</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reviews -->
                @if($course->reviews->count() > 0)
                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="section-title-modern mb-0">
                            <i class="fas fa-star"></i>
                            Avis des étudiants
                        </h2>
                        <div class="rating-summary">
                            <div class="rating-score">{{ number_format($course->reviews->avg('rating') ?? 0, 1) }}</div>
                            <div>
                                <div class="rating-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= floor($course->reviews->avg('rating') ?? 0) ? '' : 'far' }}"></i>
                                    @endfor
                                </div>
                                <div class="rating-count">({{ $course->reviews->count() }} avis)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Rating Distribution -->
                    <div class="rating-distribution mb-4">
                        @for($i = 5; $i >= 1; $i--)
                        @php
                            $ratingCount = $course->reviews->where('rating', $i)->count();
                            $percentage = $course->reviews->count() > 0 ? ($ratingCount / $course->reviews->count()) * 100 : 0;
                        @endphp
                        <div class="rating-bar-item">
                            <div class="rating-bar-label">{{ $i }} étoiles</div>
                            <div class="rating-bar">
                                <div class="rating-bar-fill" style="width: {{ $percentage }}%"></div>
                            </div>
                            <div class="rating-bar-count">{{ $ratingCount }}</div>
                        </div>
                        @endfor
                    </div>

                    <!-- Recent Reviews -->
                    <div class="recent-reviews">
                        @foreach($course->reviews->take(5) as $review)
                        <div class="review-card">
                            <div class="review-header">
                                @if($review->user->avatar)
                                    <img src="{{ $review->user->avatar }}" 
                                     alt="{{ $review->user->name }}" 
                                     class="review-avatar">
                                @else
                                    <div class="review-avatar d-flex align-items-center justify-content-center bg-primary text-white" style="font-size: 1.25rem; font-weight: bold; border-radius: 50%; min-width: 50px; min-height: 50px;">
                                        {{ strtoupper(substr($review->user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="review-author">
                                    <div class="review-author-name">{{ $review->user->name }}</div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="rating-stars" style="font-size: 0.875rem;">
                                            @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $review->rating ? '' : 'far' }}"></i>
                                            @endfor
                                        </div>
                                    </div>
                                    <div class="review-date">{{ $review->created_at->format('d/m/Y') }}</div>
                                </div>
                            </div>
                            @if($review->comment)
                            <div class="review-comment">{{ $review->comment }}</div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Related Courses -->
                @if($relatedCourses->count() > 0)
                <div class="content-card">
                    <h2 class="section-title-modern">
                        <i class="fas fa-thumbs-up"></i>
                        Cours recommandés
                    </h2>
                    <div class="row g-3">
                        @foreach($relatedCourses as $relatedCourse)
                        @php
                            $relatedCourseStats = $relatedCourse->getCourseStats();
                        @endphp
                        <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                            <div class="course-card">
                                <div class="card">
                                    <!-- Lien invisible pour rendre toute la carte cliquable -->
                                    <a href="{{ route('courses.show', $relatedCourse->slug) }}" class="course-card-link"></a>
                                    <div class="position-relative">
                                        @if($relatedCourse->thumbnail)
                                            <img src="{{ $relatedCourse->thumbnail }}" 
                                             class="card-img-top" alt="{{ $relatedCourse->title }}">
                                        @else
                                            <div class="card-img-top d-flex align-items-center justify-content-center bg-primary text-white" style="height: 180px; font-size: 2rem; font-weight: bold;">
                                                {{ strtoupper(substr($relatedCourse->title, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1">
                                            @if($relatedCourse->is_featured)
                                            <span class="badge bg-warning">En vedette</span>
                                            @endif
                                            @if($relatedCourse->is_free)
                                            <span class="badge bg-success">Gratuit</span>
                                            @endif
                                            @if($relatedCourse->sale_price)
                                            <span class="badge bg-danger">
                                                -{{ round((($relatedCourse->price - $relatedCourse->sale_price) / $relatedCourse->price) * 100) }}%
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <a href="{{ route('courses.show', $relatedCourse->slug) }}">
                                                {{ Str::limit($relatedCourse->title, 50) }}
                                            </a>
                                        </h6>
                                        <p class="card-text">{{ Str::limit($relatedCourse->short_description ?? $relatedCourse->description, 100) }}</p>
                                        
                                        <div class="instructor-info">
                                            <small class="instructor-name">
                                                <i class="fas fa-user me-1"></i>{{ Str::limit($relatedCourse->instructor->name ?? 'Instructeur', 20) }}
                                            </small>
                                            <div class="rating">
                                                <i class="fas fa-star"></i>
                                                <span>{{ number_format($relatedCourseStats['average_rating'] ?? 0, 1) }}</span>
                                                <span class="text-muted">({{ $relatedCourseStats['total_reviews'] ?? 0 }})</span>
                                            </div>
                                        </div>
                                        
                                        <div class="price-duration">
                                            <div class="price">
                                                @if($relatedCourse->is_free)
                                                    <span class="text-success fw-bold">Gratuit</span>
                                                @else
                                                    @if($relatedCourse->sale_price)
                                                        <span class="text-primary fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($relatedCourse->sale_price) }}</span>
                                                        <small class="text-muted text-decoration-line-through ms-1">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($relatedCourse->price) }}</small>
                                                    @else
                                                        <span class="text-primary fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($relatedCourse->price) }}</span>
                                                    @endif
                                                @endif
                                            </div>
                                            @if($relatedCourse->sale_price && $relatedCourse->sale_end_at)
                                                <div class="promotion-countdown" data-sale-end="{{ $relatedCourse->sale_end_at->toIso8601String() }}">
                                                    <i class="fas fa-fire me-1 text-danger"></i>
                                                    <span class="countdown-text">
                                                        <span class="countdown-years">0</span>a 
                                                        <span class="countdown-months">0</span>m 
                                                        <span class="countdown-days">0</span>j 
                                                        <span class="countdown-hours">0</span>h 
                                                        <span class="countdown-minutes">0</span>min
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="card-actions">
                                            <x-course-button :course="$relatedCourse" size="small" :show-cart="false" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Sidebar -->
            <div class="col-lg-4">
                <div class="course-sidebar">
                    <!-- Purchase Card -->
                    <div class="sidebar-card">
                        <div class="price-display">
                            @if($course->is_free)
                                <div class="price-free">Gratuit</div>
                            @else
                                @if($course->sale_price)
                                    <div class="price-current">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->sale_price) }}</div>
                                    <div class="price-original">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</div>
                                    <div class="price-discount">
                                        -{{ round((($course->price - $course->sale_price) / $course->price) * 100) }}% de réduction
                                    </div>
                                    @if($course->sale_end_at)
                                    <div class="promotion-countdown mt-3" data-sale-end="{{ $course->sale_end_at->toIso8601String() }}">
                                        <div class="countdown-label text-muted small mb-1">
                                            <i class="fas fa-clock me-1"></i>Promotion se termine dans :
                                        </div>
                                        <div class="countdown-text text-danger fw-bold">
                                            <span class="countdown-days">0</span>j 
                                            <span class="countdown-hours">00</span>h 
                                            <span class="countdown-minutes">00</span>min
                                        </div>
                                    </div>
                                    @endif
                                @else
                                    <div class="price-current">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</div>
                                @endif
                            @endif
                        </div>

                        <x-course-button :course="$course" size="normal" :show-cart="true" />

                        <hr class="my-4">

                        <h6 class="fw-bold mb-3">Ce cours comprend :</h6>
                        <ul class="course-features-list">
                            @foreach($course->getCourseFeatures() as $feature)
                            <li>
                                <i class="fas {{ $feature['icon'] }}"></i>
                                <span>{{ $feature['text'] }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Share Card -->
                    <div class="sidebar-card">
                        <h6 class="fw-bold mb-3">Partager ce cours</h6>
                        <div class="share-buttons">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" 
                               target="_blank" 
                               class="share-btn" 
                               title="Partager sur Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($course->title) }}" 
                               target="_blank" 
                               class="share-btn" 
                               title="Partager sur Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}" 
                               target="_blank" 
                               class="share-btn" 
                               title="Partager sur LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <button type="button" 
                                    class="share-btn" 
                                    onclick="copyToClipboard('{{ request()->url() }}')" 
                                    title="Copier le lien">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Payment Button -->
<div class="mobile-payment-btn">
    <div class="container-fluid">
        <div class="row align-items-center g-2">
            <div class="col mobile-price-col">
                <div class="mobile-price">
            @if($course->is_free)
                <div class="mobile-price-label">Prix</div>
                <div class="mobile-price-value">Gratuit</div>
            @else
                @if($course->sale_price)
                    <div class="mobile-price-label">Prix promotionnel</div>
                    <div class="mobile-price-wrapper">
                        <div class="mobile-price-value">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->sale_price) }}</div>
                        <div class="mobile-price-original">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</div>
                        <div class="mobile-price-discount">
                            -{{ round((($course->price - $course->sale_price) / $course->price) * 100) }}% de réduction
                        </div>
                    </div>
                    @if($course->sale_end_at)
                    <div class="promotion-countdown mobile-countdown text-danger mt-1" data-sale-end="{{ $course->sale_end_at->toIso8601String() }}">
                        <i class="fas fa-clock me-1"></i>
                        <span class="countdown-text">
                            <span class="countdown-days">0</span>j 
                            <span class="countdown-hours">00</span>h 
                            <span class="countdown-minutes">00</span>min
                        </span>
                    </div>
                    @endif
                @else
                    <div class="mobile-price-label">Prix</div>
                    <div class="mobile-price-value">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</div>
                @endif
            @endif
                </div>
            </div>
            <div class="col-auto mobile-btn-col">
                <x-course-button :course="$course" size="normal" :show-cart="false" />
            </div>
        </div>
    </div>
</div>

<!-- Video Preview Modal -->
@if($hasAnyPreview)
<div class="modal fade" id="coursePreviewModal" tabindex="-1" aria-labelledby="coursePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fixed-height">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white border-0 flex-shrink-0" style="background-color: #003366;">
                <h5 class="modal-title fw-bold" id="coursePreviewModalLabel">
                    <i class="fas fa-play-circle me-2"></i>Aperçus du cours
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="max-height: calc(90vh - 73px);">
                <div class="row g-0 h-100">
                    <div class="col-lg-8" style="border-right: 1px solid #dee2e6;">
                        <div class="p-4">
                            <div class="ratio ratio-16x9 mb-3" id="previewVideoContainer">
                                @if($course->video_preview_youtube_id)
                                    @php
                                        $previewLesson = new \App\Models\CourseLesson();
                                        $previewLesson->id = 0;
                                        $previewLesson->youtube_video_id = $course->video_preview_youtube_id;
                                        $previewLesson->is_unlisted = $course->video_preview_is_unlisted;
                                        $previewLesson->is_preview = true;
                                    @endphp
                                    <div class="preview-player-wrapper active" data-preview-id="0">
                                    <x-plyr-player :lesson="$previewLesson" :course="$course" :isMobile="false" />
                                    </div>
                                @elseif($course->video_preview)
                                    <div class="preview-player-wrapper active" data-preview-id="0">
                                        <video id="coursePreviewVideo" controls class="rounded shadow w-100 h-100" style="background: #000; object-fit: contain;">
                                            <source src="{{ \App\Helpers\FileHelper::coursePreview($course->video_preview) }}" type="video/mp4">
                                        Votre navigateur ne supporte pas la lecture vidéo.
                                    </video>
                                    </div>
                                @endif
                                
                                @php
                                    // Récupérer toutes les leçons vidéo publiées qui ont du contenu vidéo
                                    $allVideoLessons = $course->sections->flatMap(function($section) {
                                        return $section->lessons->where('type', 'video')
                                            ->where('is_published', true)
                                            ->filter(function($lesson) {
                                                return $lesson->youtube_video_id || 
                                                       $lesson->file_path || 
                                                       $lesson->content_url;
                                            });
                                    });
                                @endphp
                                
                                @foreach($allVideoLessons as $previewLessonItem)
                                    <div class="preview-player-wrapper" data-preview-id="{{ $previewLessonItem->id }}" style="display: none;">
                                        <x-plyr-player :lesson="$previewLessonItem" :course="$course" :isMobile="false" />
                            </div>
                                @endforeach
                            </div>
                            <div class="bg-light rounded p-3" id="previewLessonInfo">
                                <h6 class="fw-bold mb-2" style="color: #003366;">Aperçu du cours</h6>
                                <p class="text-muted small mb-0" id="previewLessonTitle">{{ $course->title }}</p>
                                <p class="text-muted small mb-0" id="previewLessonSection" style="display: none;"></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 bg-light">
                        <div class="p-4">
                            <h6 class="fw-bold mb-3" style="color: #003366;">
                                <i class="fas fa-list me-2"></i>Autres aperçus
                            </h6>
                            <div id="previewListContainer">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                            </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>

<!-- Données pour le JavaScript -->
<script>
    window.coursePreviewData = {
        courseId: {{ $course->id }},
        previewUrl: '{{ route("courses.preview-data", $course->id) }}'
    };
</script>
@endif

@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Créer une notification toast moderne
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 start-50 translate-middle-x mt-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="alert alert-success alert-dismissible fade show shadow" role="alert" style="min-width: 300px;">
                <i class="fas fa-check-circle me-2"></i>Lien copié dans le presse-papiers !
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    });
}

// Fonction pour charger la liste des aperçus dynamiquement
function loadPreviewList() {
    const container = document.getElementById('previewListContainer');
    if (!container || !window.coursePreviewData) {
        console.error('Container ou coursePreviewData manquant');
        return;
    }
    
    fetch(window.coursePreviewData.previewUrl, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || err.error || 'Erreur HTTP: ' + response.status);
                }).catch(() => {
                    throw new Error('Erreur HTTP: ' + response.status);
                });
            }
            return response.json();
        })
        .then(data => {
            container.innerHTML = '';
            
            // Vérifier si c'est une réponse d'erreur
            if (data.error) {
                throw new Error(data.message || data.error);
            }
            
            if (!data.preview || data.preview.length === 0) {
                container.innerHTML = '<p class="text-muted text-center py-4">Aucun aperçu disponible</p>';
                return;
            }
            
            data.preview.forEach(preview => {
                const item = document.createElement('div');
                item.className = 'preview-item mb-3 p-3 border rounded';
                item.setAttribute('data-preview-lesson', preview.id);
                item.setAttribute('data-preview-title', preview.title);
                item.setAttribute('data-preview-youtube-id', preview.youtube_id || '');
                item.setAttribute('data-preview-is-unlisted', preview.is_unlisted ? '1' : '0');
                item.setAttribute('data-preview-video-url', preview.video_url || '');
                item.setAttribute('data-preview-section', preview.section || '');
                item.style.cssText = preview.is_main 
                    ? 'border-color: #003366 !important; background: rgba(0, 51, 102, 0.05); cursor: pointer; transition: all 0.2s ease;'
                    : 'cursor: pointer; transition: all 0.2s ease;';
                
                item.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = preview.is_main 
                        ? 'rgba(0, 51, 102, 0.15)' 
                        : 'rgba(0, 51, 102, 0.1)';
                    this.style.borderColor = '#003366';
                });
                
                item.addEventListener('mouseleave', function() {
                    if (preview.is_main) {
                        this.style.backgroundColor = 'rgba(0, 51, 102, 0.05)';
                    } else {
                        this.style.backgroundColor = '';
                        this.style.borderColor = '';
                    }
                });
                
                item.addEventListener('click', function() {
                    openPreviewLesson(preview.id);
                });
                
                const iconClass = preview.is_main ? 'fab fa-youtube text-danger fa-2x' : 'fas fa-play-circle fa-2x';
                const iconStyle = preview.is_main ? '' : 'color: #003366;';
                const isPreview = preview.is_preview || false;
                const previewBadge = isPreview ? '<span class="badge bg-warning text-dark ms-2" style="font-size: 0.65rem;"><i class="fas fa-eye me-1"></i>Aperçu</span>' : '';
                
                item.innerHTML = `
                    <div class="d-flex ${preview.is_main ? 'align-items-center' : 'align-items-start'}">
                        <div class="me-3">
                            <i class="${iconClass}" style="${iconStyle}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-1">
                                <h6 class="mb-0 fw-medium" style="color: #003366; flex: 1;">${preview.title}</h6>
                                ${previewBadge}
                            </div>
                            ${preview.is_main 
                                ? '<small class="text-muted"><i class="fas fa-video me-1"></i>Vidéo principale</small>'
                                : `<div class="d-flex flex-wrap gap-2 mb-1">
                                    ${preview.section ? `<small class="text-muted"><i class="fas fa-layer-group me-1"></i>${preview.section}</small>` : ''}
                                    ${preview.duration ? `<small class="text-muted"><i class="fas fa-clock me-1"></i>${preview.duration} min</small>` : ''}
                                </div>`
                            }
                        </div>
                    </div>
                `;
                
                container.appendChild(item);
            });
            
            // Activer l'aperçu principal par défaut
            const mainItem = container.querySelector('[data-preview-lesson="0"]');
            if (mainItem) {
                mainItem.classList.add('active');
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des aperçus:', error);
            console.error('URL:', window.coursePreviewData?.previewUrl);
            container.innerHTML = `
                <div class="text-center py-4">
                    <p class="text-danger mb-2">Erreur lors du chargement des aperçus</p>
                    <small class="text-muted">${error.message || 'Erreur inconnue'}</small>
                    <button class="btn btn-sm btn-primary mt-3" onclick="loadPreviewList()">
                        <i class="fas fa-redo me-1"></i>Réessayer
                    </button>
                </div>
            `;
        });
}


// Fonction pour ouvrir un aperçu de leçon dans le modal
function openPreviewLesson(lessonId, clickedElement = null) {
    // Si un élément a été cliqué, utiliser ses données directement
    let lessonElement = clickedElement;
    let title = '';
    let section = '';
    
    if (!lessonElement) {
        // Chercher l'élément dans la liste des aperçus chargés
        if (lessonId === 0) {
            lessonElement = document.querySelector('[data-preview-lesson="0"]');
        } else {
            lessonElement = document.querySelector('[data-preview-lesson="' + lessonId + '"]');
        }
    }
    
    // Si on a trouvé un élément, récupérer ses données
    if (lessonElement) {
        title = lessonElement.getAttribute('data-preview-title') || '';
        section = lessonElement.getAttribute('data-preview-section') || '';
    }
    
    // Mettre à jour le titre dans le modal
    const titleElement = document.getElementById('previewLessonTitle');
    const sectionElement = document.getElementById('previewLessonSection');
    if (titleElement && title) {
        titleElement.textContent = title;
    }
    if (sectionElement) {
        if (section) {
            sectionElement.textContent = section;
            sectionElement.style.display = 'block';
        } else {
            sectionElement.style.display = 'none';
        }
    }
    
    // Mettre à jour l'état actif dans la liste des aperçus (si elle existe)
    document.querySelectorAll('.preview-item').forEach(item => {
        item.classList.remove('active');
        if (item.getAttribute('data-preview-lesson') == lessonId) {
            item.classList.add('active');
        }
    });
    
    // Cacher tous les players
    const allWrappers = document.querySelectorAll('.preview-player-wrapper');
    allWrappers.forEach(wrapper => {
        wrapper.style.display = 'none';
        wrapper.classList.remove('active');
    });
    
    // Afficher le player correspondant
    const targetWrapper = document.querySelector('.preview-player-wrapper[data-preview-id="' + lessonId + '"]');
    if (targetWrapper) {
        targetWrapper.style.display = 'block';
        targetWrapper.classList.add('active');
        
        // Arrêter les autres players s'ils sont en cours de lecture
        allWrappers.forEach(wrapper => {
            if (wrapper !== targetWrapper) {
                const playerId = wrapper.querySelector('[id^="plyr-player-"], [id^="plyr-mobile-"]');
                if (playerId) {
                    const playerKey = 'plyr_' + playerId.id;
                    const existingPlayer = window[playerKey];
                    if (existingPlayer && typeof existingPlayer.pauseVideo === 'function') {
                        try {
                            existingPlayer.pauseVideo();
                        } catch (e) {
                            console.log('Could not pause player:', e);
                        }
                    }
                }
                // Arrêter aussi les vidéos HTML5
                const video = wrapper.querySelector('video');
                if (video) {
                    video.pause();
                }
            }
        });
    } else {
        console.warn('Player wrapper not found for lesson ID:', lessonId);
    }
    
    // Ouvrir le modal
    const modal = document.getElementById('coursePreviewModal');
    if (modal) {
        const bsModal = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
        
        // Si le modal n'est pas encore ouvert, attendre qu'il s'ouvre avant de changer le player
        if (!modal.classList.contains('show')) {
            // Écouter l'événement d'ouverture du modal
            const handleModalShown = () => {
                // Une fois le modal ouvert, afficher le bon player
                setTimeout(() => {
                    const targetWrapper = document.querySelector('.preview-player-wrapper[data-preview-id="' + lessonId + '"]');
                    if (targetWrapper) {
                        // Cacher tous les players
                        document.querySelectorAll('.preview-player-wrapper').forEach(wrapper => {
                            wrapper.style.display = 'none';
                            wrapper.classList.remove('active');
                        });
                        // Afficher le player correspondant
                        targetWrapper.style.display = 'block';
                        targetWrapper.classList.add('active');
                        
                        // Mettre à jour le titre et la section
                        if (titleElement && title) {
                            titleElement.textContent = title;
                        }
                        if (sectionElement) {
                            if (section) {
                                sectionElement.textContent = section;
                                sectionElement.style.display = 'block';
                            } else {
                                sectionElement.style.display = 'none';
                            }
                        }
                    }
                    // Retirer l'écouteur après utilisation
                    modal.removeEventListener('shown.bs.modal', handleModalShown);
                }, 100);
            };
            
            modal.addEventListener('shown.bs.modal', handleModalShown);
        }
        
        // Ouvrir le modal
        bsModal.show();
        
        // S'assurer que la liste des aperçus est chargée
        const previewListContainer = document.getElementById('previewListContainer');
        if (previewListContainer && typeof loadPreviewList === 'function') {
            // Charger la liste si elle n'a pas encore été chargée
            const spinner = previewListContainer.querySelector('.spinner-border');
            if (spinner) {
                loadPreviewList();
            }
        }
    }
}

// Initialiser la vidéo principale dans le modal
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('coursePreviewModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            // Charger la liste des aperçus
            loadPreviewList();
            
            @if($course->video_preview && !$course->video_preview_youtube_id)
                const video = document.getElementById('coursePreviewVideo');
                if (video) {
                    video.load();
                }
            @endif
            
            // Arrêter toutes les vidéos quand le modal s'ouvre (sauf celle active)
            document.querySelectorAll('.preview-player-wrapper').forEach(wrapper => {
                if (!wrapper.classList.contains('active')) {
                    const playerId = wrapper.querySelector('[id^="plyr-player-"], [id^="plyr-mobile-"]');
                    if (playerId) {
                        const playerKey = 'plyr_' + playerId.id;
                        const existingPlayer = window[playerKey];
                        if (existingPlayer && typeof existingPlayer.pauseVideo === 'function') {
                            try {
                                existingPlayer.pauseVideo();
                            } catch (e) {
                                // Ignorer les erreurs
                            }
                        }
                    }
                    const video = wrapper.querySelector('video');
                    if (video) {
                        video.pause();
                    }
                }
            });
        });
        
        // Réinitialiser à l'aperçu principal quand le modal se ferme
        modal.addEventListener('hidden.bs.modal', function() {
            // Arrêter toutes les vidéos
            document.querySelectorAll('.preview-player-wrapper').forEach(wrapper => {
                const playerId = wrapper.querySelector('[id^="plyr-player-"], [id^="plyr-mobile-"]');
                if (playerId) {
                    const playerKey = 'plyr_' + playerId.id;
                    const existingPlayer = window[playerKey];
                    if (existingPlayer && typeof existingPlayer.pauseVideo === 'function') {
                        try {
                            existingPlayer.pauseVideo();
                        } catch (e) {
                            // Ignorer les erreurs
                        }
                    }
                }
                const video = wrapper.querySelector('video');
                if (video) {
                    video.pause();
                }
            });
            
            @if($course->video_preview_youtube_id || $course->video_preview)
                // Réinitialiser à l'aperçu principal
                const allWrappers = document.querySelectorAll('.preview-player-wrapper');
                allWrappers.forEach(wrapper => {
                    wrapper.style.display = 'none';
                    wrapper.classList.remove('active');
                });
                const mainWrapper = document.querySelector('.preview-player-wrapper[data-preview-id="0"]');
                if (mainWrapper) {
                    mainWrapper.style.display = 'block';
                    mainWrapper.classList.add('active');
                }
                
                // Réinitialiser le titre
                const titleElement = document.getElementById('previewLessonTitle');
                const sectionElement = document.getElementById('previewLessonSection');
                if (titleElement) {
                    titleElement.textContent = '{{ addslashes($course->title) }}';
                }
                if (sectionElement) {
                    sectionElement.style.display = 'none';
                }
                
                // Réinitialiser l'état actif dans la liste
                document.querySelectorAll('.preview-item').forEach(item => {
                    item.classList.remove('active');
                });
                const mainPreviewItem = document.querySelector('[data-preview-lesson="0"]');
                if (mainPreviewItem) {
                    mainPreviewItem.classList.add('active');
                }
            @endif
        });
    }
    
    // Initialiser les compteurs à rebours pour les promotions
    if (typeof window.initPromotionCountdowns === 'function') {
        window.initPromotionCountdowns();
    }
    
    // Gérer le collapse des sections du curriculum avec JavaScript vanilla
    const curriculumHeaders = document.querySelectorAll('.curriculum-section-header');
    curriculumHeaders.forEach(function(header) {
        const sectionId = header.getAttribute('data-section-id');
        const targetElement = document.querySelector('#' + sectionId);
        
        if (!targetElement) return;
        
        // Initialiser l'état au chargement
        const isExpanded = header.getAttribute('aria-expanded') === 'true';
        if (isExpanded && !targetElement.classList.contains('is-open')) {
            targetElement.classList.add('is-open');
        } else if (!isExpanded && targetElement.classList.contains('is-open')) {
            targetElement.classList.remove('is-open');
        }
        
        // Fonction pour toggle
        function toggleSection() {
            const isCurrentlyExpanded = targetElement.classList.contains('is-open');
            
            if (isCurrentlyExpanded) {
                targetElement.classList.remove('is-open');
                header.setAttribute('aria-expanded', 'false');
            } else {
                targetElement.classList.add('is-open');
                header.setAttribute('aria-expanded', 'true');
            }
        }
        
        // Gérer le clic sur le header
        header.addEventListener('click', function(e) {
            // Empêcher si le clic est sur les stats
            if (e.target.closest('.curriculum-section-stats')) {
                return;
            }
            
            e.preventDefault();
            e.stopPropagation();
            toggleSection();
        });
        
        // Support clavier
        header.addEventListener('keydown', function(e) {
            if (e.target.closest('.curriculum-section-stats')) {
                return;
            }
            
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                e.stopPropagation();
                toggleSection();
            }
        });
    });
});
</script>
@endpush


@once
    <div id="upload-progress-modal" class="upload-progress-modal" aria-hidden="true">
        <div class="upload-progress-modal__backdrop" data-upload-modal-backdrop></div>
        <div class="upload-progress-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="upload-progress-modal-title">
            <header class="upload-progress-modal__header">
                <div class="upload-progress-modal__title">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <div>
                        <h2 id="upload-progress-modal-title">Téléversement en cours</h2>
                        <p id="upload-progress-modal-subtitle">Veuillez patienter pendant l'envoi de vos fichiers…</p>
                    </div>
                </div>
                <button type="button" class="upload-progress-modal__close" data-upload-modal-close aria-label="Fermer la fenêtre">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <div class="upload-progress-modal__body">
                <div class="upload-progress-modal__tasks" data-upload-modal-tasks>
                    <div class="upload-progress-modal__empty" data-upload-modal-empty>
                        <i class="fas fa-check-circle"></i>
                        <p>Tous les téléversements sont terminés.</p>
                    </div>
                </div>
            </div>
            <footer class="upload-progress-modal__footer">
                <small data-upload-modal-hint>Ne fermez pas cette page pendant l'envoi.</small>
            </footer>
        </div>
    </div>

    @push('styles')
        <style>
            .upload-progress-modal {
                position: fixed;
                inset: 0;
                z-index: 1080;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 1.5rem;
            }

            .upload-progress-modal.is-visible {
                display: flex;
            }

            .upload-progress-modal__backdrop {
                position: absolute;
                inset: 0;
                background: rgba(15, 23, 42, 0.55);
                backdrop-filter: blur(4px);
            }

            .upload-progress-modal__dialog {
                position: relative;
                width: min(520px, 100%);
                background: #ffffff;
                border-radius: 18px;
                box-shadow: 0 24px 64px rgba(15, 23, 42, 0.25);
                display: flex;
                flex-direction: column;
                overflow: hidden;
                border: 1px solid rgba(226, 232, 240, 0.8);
                max-height: 90vh;
            }

            .upload-progress-modal__header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 1rem 1.5rem;
                border-bottom: 1px solid rgba(226, 232, 240, 0.8);
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                color: #f8fafc;
            }

            .upload-progress-modal__title {
                display: flex;
                align-items: center;
                gap: 0.85rem;
            }

            .upload-progress-modal__title i {
                font-size: 1.65rem;
            }

            .upload-progress-modal__title h2 {
                margin: 0;
                font-size: 1.15rem;
                font-weight: 700;
                color: #f8fafc;
            }

            .upload-progress-modal__title p {
                margin: 0;
                font-size: 0.9rem;
                color: rgba(248, 250, 252, 0.75);
            }

            .upload-progress-modal__close {
                border: none;
                background: rgba(248, 250, 252, 0.12);
                color: #f8fafc;
                width: 38px;
                height: 38px;
                border-radius: 999px;
                display: grid;
                place-items: center;
                cursor: pointer;
                transition: background 0.2s ease, transform 0.2s ease;
            }

            .upload-progress-modal__close:hover {
                background: rgba(248, 250, 252, 0.2);
                transform: scale(1.05);
            }

            .upload-progress-modal__body {
                padding: 1.25rem 1.5rem;
                overflow-y: auto;
            }

            .upload-progress-modal__tasks {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .upload-progress-modal__empty {
                display: none;
                align-items: center;
                gap: 0.75rem;
                justify-content: center;
                color: #0f172a;
                font-weight: 600;
                padding: 1.25rem;
                background: rgba(16, 185, 129, 0.08);
                border-radius: 12px;
            }

            .upload-progress-modal__empty i {
                font-size: 1.5rem;
                color: #0f172a;
            }

            .upload-progress-modal__task {
                border: 1px solid rgba(226, 232, 240, 0.8);
                border-radius: 14px;
                padding: 1rem;
                background: rgba(248, 250, 252, 0.82);
                display: flex;
                flex-direction: column;
                gap: 0.65rem;
                position: relative;
                overflow: hidden;
            }

            .upload-progress-modal__task::before {
                content: "";
                position: absolute;
                inset: 0;
                opacity: 0;
                transition: opacity 0.2s ease;
                pointer-events: none;
                background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(147, 197, 253, 0.12));
            }

            .upload-progress-modal__task.is-active::before {
                opacity: 1;
            }

            .upload-progress-modal__task.is-error {
                border-color: rgba(248, 113, 113, 0.6);
                background: rgba(254, 226, 226, 0.4);
            }

            .upload-progress-modal__task-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .upload-progress-modal__task-title {
                font-weight: 600;
                color: #0f172a;
                margin: 0;
                word-break: break-word;
            }

            .upload-progress-modal__task-size {
                font-size: 0.85rem;
                color: #475569;
                white-space: nowrap;
            }

            .upload-progress-modal__task-desc {
                font-size: 0.9rem;
                color: #475569;
                margin: 0;
            }

            .upload-progress-modal__progress-track {
                width: 100%;
                height: 8px;
                border-radius: 999px;
                background: rgba(226, 232, 240, 0.8);
                overflow: hidden;
            }

            .upload-progress-modal__progress-bar {
                height: 100%;
                width: 0%;
                border-radius: 999px;
                background: linear-gradient(135deg, #2563eb, #38bdf8);
                transition: width 0.2s ease;
            }

            .upload-progress-modal__progress-bar.is-indeterminate {
                position: relative;
                width: 100%;
                animation: upload-progress-indeterminate 1.2s ease-in-out infinite;
            }

            @keyframes upload-progress-indeterminate {
                0% {
                    transform: translateX(-50%);
                }
                50% {
                    transform: translateX(50%);
                }
                100% {
                    transform: translateX(150%);
                }
            }

            .upload-progress-modal__status {
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-size: 0.85rem;
                color: #334155;
            }

            .upload-progress-modal__status.is-success {
                color: #0f766e;
            }

            .upload-progress-modal__status.is-error {
                color: #b91c1c;
            }

            .upload-progress-modal__footer {
                padding: 0.9rem 1.5rem;
                border-top: 1px solid rgba(226, 232, 240, 0.8);
                font-size: 0.85rem;
                color: #475569;
                background: rgba(248, 250, 252, 0.95);
            }

            @media (max-width: 600px) {
                .upload-progress-modal {
                    padding: 0.75rem;
                }

                .upload-progress-modal__dialog {
                    width: 100%;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function () {
                const modalEl = document.getElementById('upload-progress-modal');
                if (!modalEl) {
                    return;
                }

                const tasksContainer = modalEl.querySelector('[data-upload-modal-tasks]');
                const emptyState = modalEl.querySelector('[data-upload-modal-empty]');
                const subtitleEl = modalEl.querySelector('#upload-progress-modal-subtitle');
                const hintEl = modalEl.querySelector('[data-upload-modal-hint]');
                const closeBtn = modalEl.querySelector('[data-upload-modal-close]');
                const backdrop = modalEl.querySelector('[data-upload-modal-backdrop]');

                const tasks = new Map();
                let previouslyFocusedElement = null;

                const renderEmptyState = () => {
                    const hasTasks = tasks.size > 0;
                    if (emptyState) {
                        emptyState.style.display = hasTasks ? 'none' : 'flex';
                    }
                    if (!hasTasks) {
                        subtitleEl && (subtitleEl.textContent = 'Tous les téléversements sont terminés.');
                        hintEl && (hintEl.textContent = 'Vous pouvez poursuivre vos modifications.');
                    } else {
                        subtitleEl && (subtitleEl.textContent = 'Veuillez patienter pendant l\'envoi de vos fichiers…');
                        hintEl && (hintEl.textContent = 'Ne fermez pas cette page pendant l\'envoi.');
                    }
                };

                const ensureVisible = () => {
                    if (!modalEl.classList.contains('is-visible')) {
                        if (!modalEl.contains(document.activeElement)) {
                            previouslyFocusedElement = document.activeElement;
                        }
                        modalEl.classList.add('is-visible');
                        modalEl.setAttribute('aria-hidden', 'false');
                        if (closeBtn && typeof closeBtn.focus === 'function') {
                            closeBtn.focus();
                        }
                    }
                };

                const hideModal = () => {
                    const activeElement = document.activeElement;
                    if (activeElement && modalEl.contains(activeElement) && typeof activeElement.blur === 'function') {
                        activeElement.blur();
                    }
                    modalEl.classList.remove('is-visible');
                    modalEl.setAttribute('aria-hidden', 'true');
                    if (previouslyFocusedElement && typeof previouslyFocusedElement.focus === 'function') {
                        previouslyFocusedElement.focus();
                    }
                    previouslyFocusedElement = null;
                };

                const removeTask = (taskId) => {
                    const task = tasks.get(taskId);
                    if (!task) {
                        return;
                    }
                    tasks.delete(taskId);
                    task.element.remove();
                    renderEmptyState();
                    if (tasks.size === 0) {
                        hideModal();
                    }
                };

                const createTaskElement = (taskId, config) => {
                const element = document.createElement('div');
                    element.className = 'upload-progress-modal__task is-active';
                    element.innerHTML = `
                        <div class="upload-progress-modal__task-header">
                            <div>
                                <p class="upload-progress-modal__task-title">${config.label ?? 'Téléversement'}</p>
                                ${config.description ? `<p class="upload-progress-modal__task-desc">${config.description}</p>` : ''}
                            </div>
                            ${config.sizeLabel ? `<span class="upload-progress-modal__task-size">${config.sizeLabel}</span>` : ''}
                        </div>
                        <div class="upload-progress-modal__progress-track">
                            <div class="upload-progress-modal__progress-bar" data-upload-progress-bar style="width:0%"></div>
                        </div>
                        <div class="upload-progress-modal__status" data-upload-progress-status>
                            <span data-upload-progress-message>${config.initialMessage ?? 'Démarrage…'}</span>
                            <strong data-upload-progress-percent>0%</strong>
                        </div>
                    `;

                    const progressBar = element.querySelector('[data-upload-progress-bar]');
                    const statusEl = element.querySelector('[data-upload-progress-status]');
                    const messageEl = element.querySelector('[data-upload-progress-message]');
                    const percentEl = element.querySelector('[data-upload-progress-percent]');

                    tasksContainer.appendChild(element);
                    tasks.set(taskId, {
                        element,
                        progressBar,
                        statusEl,
                        messageEl,
                        percentEl,
                        meta: { ...config }
                    });
                    renderEmptyState();
                };

                const setIndeterminate = (taskId) => {
                    const task = tasks.get(taskId);
                    if (!task) {
                        return;
                    }
                    task.progressBar.classList.add('is-indeterminate');
                    task.percentEl.textContent = '…';
                };

                const updateTaskProgress = (taskId, percent, message) => {
                    const task = tasks.get(taskId);
                    if (!task) {
                        return;
                    }
                    if (typeof percent === 'number' && Number.isFinite(percent)) {
                        const normalized = Math.max(0, Math.min(100, Math.round(percent)));
                        task.progressBar.classList.remove('is-indeterminate');
                        task.progressBar.style.width = `${normalized}%`;
                        task.percentEl.textContent = `${normalized}%`;
                    }

                    if (message) {
                        task.messageEl.textContent = message;
                    }
                };

                const markTaskComplete = (taskId, message) => {
                    const task = tasks.get(taskId);
                    if (!task) {
                        return;
                    }
                    task.element.classList.remove('is-active');
                    task.statusEl.classList.add('is-success');
                    task.messageEl.textContent = message || 'Téléversement terminé';
                    task.percentEl.textContent = '100%';
                    task.progressBar.style.width = '100%';
                    task.progressBar.classList.remove('is-indeterminate');

                    setTimeout(() => removeTask(taskId), 1200);
                };

                const markTaskError = (taskId, message) => {
                    const task = tasks.get(taskId);
                    if (!task) {
                        return;
                    }
                    task.element.classList.add('is-error');
                    task.element.classList.remove('is-active');
                    task.statusEl.classList.add('is-error');
                    task.messageEl.textContent = message || 'Une erreur est survenue';
                    task.percentEl.textContent = '—';
                    task.progressBar.style.width = '0%';
                    task.progressBar.classList.remove('is-indeterminate');
                };

                const cancelTaskInternal = (taskId, options = {}) => {
                    const { skipCallback = false } = options;
                    const task = tasks.get(taskId);
                    if (!task) {
                        return;
                    }

                    if (!skipCallback) {
                        const onCancel = task.meta?.onCancel;
                        if (typeof onCancel === 'function') {
                            try {
                                onCancel(taskId);
                            } catch (error) {
                                console.error('Upload cancel handler failed', error);
                            }
                        }
                    }

                    removeTask(taskId);
                };

                const cancelAllTasks = () => {
                    const activeTaskIds = Array.from(tasks.keys());
                    activeTaskIds.forEach((taskId) => cancelTaskInternal(taskId));
                };

                const api = {
                    startTask(taskId, config = {}) {
                        if (!taskId) {
                            return;
                        }
                        ensureVisible();
                        createTaskElement(taskId, config);
                    },
                    updateTask(taskId, percent, message) {
                        updateTaskProgress(taskId, percent, message);
                    },
                    setIndeterminate(taskId) {
                        setIndeterminate(taskId);
                    },
                    completeTask(taskId, message) {
                        markTaskComplete(taskId, message);
                    },
                    errorTask(taskId, message) {
                        markTaskError(taskId, message);
                    },
                    cancelTask(taskId) {
                        cancelTaskInternal(taskId);
                    },
                    cancelTaskAndClose(taskId) {
                        cancelTaskInternal(taskId);
                        hideModal();
                    },
                    cancelAll() {
                        cancelAllTasks();
                    },
                    hideIfIdle() {
                        if (tasks.size === 0) {
                            hideModal();
                        }
                    },
                    hide() {
                        hideModal();
                    },
                    getActiveTaskIds() {
                        return Array.from(tasks.keys());
                    },
                    getTaskMeta(taskId) {
                        const task = tasks.get(taskId);
                        return task ? (task.meta || {}) : {};
                    }
                };

                if (closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        cancelAllTasks();
                        hideModal();
                    });
                }

                if (backdrop) {
                    backdrop.addEventListener('click', (event) => {
                        event.preventDefault();
                    });
                }

                window.UploadProgressModal = api;
                renderEmptyState();
            })();
        </script>
    @endpush
@endonce


@props(['lesson', 'course'])

<div class="quiz-viewer-container">
    <div class="quiz-content p-4">
        <div class="quiz-header mb-4 border-bottom pb-3">
            <h2 class="mb-2">{{ $lesson->title }}</h2>
            @if($lesson->description)
                <p class="text-muted mb-3">{{ $lesson->description }}</p>
            @endif
            <div class="d-flex align-items-center gap-3">
                @if($lesson->duration)
                    <span class="badge bg-primary"><i class="fas fa-clock"></i> {{ $lesson->duration }} min</span>
                @endif
                <span class="badge bg-warning"><i class="fas fa-question-circle"></i> Quiz</span>
            </div>
        </div>
        
        <div id="quiz-content-{{ $lesson->id }}">
            @if($lesson->quiz_data)
                <div class="quiz-questions">
                    @foreach(json_decode($lesson->quiz_data, true)['questions'] ?? [] as $index => $question)
                        <div class="quiz-question mb-4">
                            <h5 class="mb-3">
                                <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                {{ $question['question'] ?? '' }}
                            </h5>
                            <div class="quiz-options">
                                @foreach($question['options'] ?? [] as $optIndex => $option)
                                    <div class="form-check mb-2">
                                        <input 
                                            class="form-check-input" 
                                            type="radio" 
                                            name="quiz-{{ $lesson->id }}-q{{ $index }}" 
                                            id="quiz-{{ $lesson->id }}-q{{ $index }}-o{{ $optIndex }}"
                                            value="{{ $optIndex }}">
                                        <label class="form-check-label" for="quiz-{{ $lesson->id }}-q{{ $index }}-o{{ $optIndex }}">
                                            {{ $option }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="quiz-actions mt-4">
                    <button class="btn btn-primary btn-lg" onclick="submitQuiz({{ $lesson->id }})">
                        <i class="fas fa-paper-plane"></i> Soumettre le quiz
                    </button>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Aucun quiz disponible pour cette leçon.
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function submitQuiz(lessonId) {
    // Collecter les réponses
    const questions = document.querySelectorAll(`[name^="quiz-${lessonId}"]`);
    const answers = {};
    
    questions.forEach((input, index) => {
        if (input.checked) {
            const questionNum = index;
            answers[questionNum] = input.value;
        }
    });
    
    // Envoyer les réponses au serveur
    fetch(`/learning/courses/{{ $course->slug }}/lessons/${lessonId}/submit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ answers })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showQuizResults(data.results);
        } else {
            alert('Erreur lors de la soumission du quiz');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de la soumission du quiz');
    });
}

function showQuizResults(results) {
    // Afficher les résultats du quiz
    alert(`Votre score: ${results.score}/${results.total}\n${results.score === results.total ? 'Excellent!' : 'Continuez à apprendre!'}`);
}
</script>

<style>
.quiz-viewer-container {
    background: white;
    border-radius: 8px;
}

.quiz-question {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 1.5rem;
    background: #f9f9f9;
}

.quiz-options {
    margin-left: 2rem;
}

.form-check-label {
    cursor: pointer;
    font-size: 1.05rem;
}

.form-check-input:checked {
    background-color: #003366;
    border-color: #003366;
}
</style>


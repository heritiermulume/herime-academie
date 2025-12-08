<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'professional_experience',
        'teaching_experience',
        'specializations',
        'education_background',
        'cv_path',
        'motivation_letter_path',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the user who submitted the application
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who reviewed the application
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'under_review' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get the status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'under_review' => 'En cours d\'examen',
            'approved' => 'Approuvée',
            'rejected' => 'Rejetée',
            default => 'Inconnu',
        };
    }

    /**
     * Check if the application can be edited
     * Une candidature ne peut être modifiée que si elle est en statut 'pending'
     * Une fois soumise (status != 'pending'), elle ne peut plus être modifiée ou abandonnée
     */
    public function canBeEdited(): bool
    {
        return $this->status === 'pending';
    }
}

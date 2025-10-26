<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'email',
        'name',
        'status',
        'confirmation_token',
        'confirmed_at',
        'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUnsubscribed($query)
    {
        return $query->where('status', 'unsubscribed');
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isUnsubscribed()
    {
        return $this->status === 'unsubscribed';
    }

    public function generateConfirmationToken()
    {
        $this->confirmation_token = Str::random(32);
        $this->save();
        
        return $this->confirmation_token;
    }

    public function confirm()
    {
        $this->update([
            'status' => 'active',
            'confirmed_at' => now(),
            'confirmation_token' => null,
        ]);
    }

    public function unsubscribe()
    {
        $this->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'order_id',
        'content_id',
        'content_package_id',
        'price',
        'sale_price',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'content_id');
    }

    /**
     * Alias pour compatibilité avec le nouveau nom
     */
    public function content(): BelongsTo
    {
        return $this->course();
    }

    public function contentPackage(): BelongsTo
    {
        return $this->belongsTo(ContentPackage::class, 'content_package_id');
    }

    /**
     * Lignes affichables côté client (cours publiés ou achat d'un pack).
     */
    public function scopeForCustomerListing(Builder $query): Builder
    {
        return $query->where(function (Builder $sub) {
            $sub->whereHas('content', function (Builder $q) {
                $q->where('is_published', true);
            })->orWhereNotNull('content_package_id');
        });
    }
}

<?php

namespace App\Models;

use App\Notifications\OrderStatusUpdated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class Order extends Model
{
    use SoftDeletes;
    protected static function booted(): void
    {
        static::created(function (Order $order) {
            $order->notifyStatus($order->status ?? 'pending');
        });

        static::updated(function (Order $order) {
            if ($order->wasChanged('status')) {
                $order->notifyStatus($order->status ?? 'pending');
            }
        });
    }

    protected $fillable = [
        'order_number',
        'user_id',
        'affiliate_id',
        'coupon_id',
        'ambassador_id',
        'ambassador_promo_code_id',
        'subtotal',
        'discount',
        'tax',
        'total',
        'total_amount',
        'currency',
		'payment_currency',
		'payment_amount',
		'exchange_rate',
        'status',
        'payment_method',
        'payment_id',
        'payment_reference',
		'payment_provider',
		'payer_phone',
		'payer_country',
		'customer_ip',
		'user_agent',
        'billing_address',
        'billing_info',
        'order_items',
		'provider_fee',
		'provider_fee_currency',
		'net_total',
        'notes',
        'confirmed_at',
        'paid_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'total_amount' => 'decimal:2',
			'payment_amount' => 'decimal:2',
			'exchange_rate' => 'decimal:8',
			'provider_fee' => 'decimal:2',
			'net_total' => 'decimal:2',
            'billing_address' => 'array',
            'billing_info' => 'array',
            'order_items' => 'array',
            'confirmed_at' => 'datetime',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function ambassador(): BelongsTo
    {
        return $this->belongsTo(Ambassador::class);
    }

    public function ambassadorPromoCode(): BelongsTo
    {
        return $this->belongsTo(AmbassadorPromoCode::class, 'ambassador_promo_code_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function notifyStatus(string $status, ?string $message = null): void
    {
        if (!$this->relationLoaded('user')) {
            $this->load('user');
        }

        if ($this->user) {
            // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
            Notification::sendNow($this->user, new OrderStatusUpdated($this, $status, $message));
        }
    }

    /**
     * Relations à charger pour emails / résumés incluant les packs.
     *
     * @return list<string|array<string, mixed>>
     */
    public static function eagerLoadOrderItemsWithPackages(): array
    {
        return [
            'orderItems.course',
            'orderItems.contentPackage',
        ];
    }

    /**
     * Textes cohérents pour email, notification in-app et WhatsApp (lignes cours + packs).
     *
     * @param  Collection<int, OrderItem>  $orderItems
     * @return array{access_label: string, action_text: string, whatsapp_types_label: string}
     */
    public static function paymentConfirmationCopy(Collection $orderItems): array
    {
        $hasPack = $orderItems->contains(fn (OrderItem $i) => ! empty($i->content_package_id));
        $hasDownloadable = $orderItems->contains(fn (OrderItem $i) => $i->course && $i->course->is_downloadable);
        $hasInPerson = $orderItems->contains(fn (OrderItem $i) => $i->course && ($i->course->is_in_person_program ?? false));
        $hasOnline = $orderItems->contains(fn (OrderItem $i) => $i->course
            && ! $i->course->is_downloadable
            && ! ($i->course->is_in_person_program ?? false));

        $onlyPack = $hasPack && ! $hasDownloadable && ! $hasInPerson && ! $hasOnline;
        $onlyDl = ! $hasPack && $hasDownloadable && ! $hasInPerson && ! $hasOnline;
        $onlyIp = ! $hasPack && ! $hasDownloadable && $hasInPerson && ! $hasOnline;
        $onlyOnline = ! $hasPack && ! $hasDownloadable && ! $hasInPerson && $hasOnline;

        if ($onlyPack) {
            return [
                'access_label' => 'contenus',
                'action_text' => 'Ouvrez vos packs depuis « Mes contenus » dans votre espace personnel.',
                'whatsapp_types_label' => 'packs et formations',
            ];
        }

        if ($onlyDl) {
            return [
                'access_label' => 'contenus',
                'action_text' => 'Téléchargez-les maintenant depuis votre espace personnel.',
                'whatsapp_types_label' => 'contenus',
            ];
        }

        if ($onlyIp) {
            return [
                'access_label' => 'programmes',
                'action_text' => 'Consultez les détails de vos programmes et contactez les organisateurs via WhatsApp.',
                'whatsapp_types_label' => 'programmes',
            ];
        }

        if ($onlyOnline) {
            return [
                'access_label' => 'cours',
                'action_text' => 'Commencez votre apprentissage dès maintenant.',
                'whatsapp_types_label' => 'cours',
            ];
        }

        if ($hasPack) {
            $waLabels = array_unique(array_filter([
                $hasDownloadable ? 'contenus téléchargeables' : null,
                $hasInPerson ? 'programmes' : null,
                $hasOnline ? 'cours' : null,
                'packs',
            ]));

            return [
                'access_label' => 'contenus',
                'action_text' => 'Retrouvez vos packs et vos cours dans « Mes contenus » ou « Mes commandes ».',
                'whatsapp_types_label' => count($waLabels) > 0 ? implode(', ', $waLabels) : 'contenus',
            ];
        }

        $labels = array_values(array_unique(array_filter([
            $hasDownloadable ? 'contenus' : null,
            $hasInPerson ? 'programmes' : null,
            $hasOnline ? 'cours' : null,
        ])));
        $accessLabel = count($labels) > 1
            ? implode(', ', $labels)
            : ($labels[0] ?? 'contenus');

        return [
            'access_label' => $accessLabel,
            'action_text' => 'Accédez à vos contenus depuis votre espace personnel.',
            'whatsapp_types_label' => $accessLabel,
        ];
    }

    /**
     * Libellés d'aperçu pour listes de commandes : un pack acheté = une ligne (pas une par cours du pack).
     *
     * @param  Collection<int, OrderItem>  $orderItems
     * @return Collection<int, string>
     */
    public static function previewTitlesForOrderItems(Collection $orderItems): Collection
    {
        $titles = collect();
        $seenPackageIds = [];

        foreach ($orderItems->sortBy('id') as $item) {
            if (! $item instanceof OrderItem) {
                continue;
            }

            if (! empty($item->content_package_id)) {
                $pid = (int) $item->content_package_id;
                if (array_key_exists($pid, $seenPackageIds)) {
                    continue;
                }
                $seenPackageIds[$pid] = true;
                $pkg = $item->contentPackage;
                $titles->push($pkg?->title ? ('Pack : ' . $pkg->title) : 'Pack');

                continue;
            }

            if ($item->course) {
                $titles->push($item->course->title);
            }
        }

        return $titles->values();
    }

    /**
     * Nombre de « lignes » logiques pour l'affichage (pack = 1).
     *
     * @param  Collection<int, OrderItem>  $orderItems
     */
    public static function logicalOrderLineCount(Collection $orderItems): int
    {
        return self::previewTitlesForOrderItems($orderItems)->count();
    }

    /**
     * Prix du pack tel qu’enregistré sur la commande (forfait), jamais la somme des prix des contenus à l’unité.
     *
     * Règle métier : une ligne de commande par cours du pack, mais une seule ligne porte le `total` = prix du pack ;
     * les autres ont `total` = 0. Si plusieurs lignes ont un `total` &gt; 0 (cas exceptionnel, ex. remise répartie),
     * on additionne ces montants pour obtenir le total payé pour ce pack.
     *
     * @param  iterable<OrderItem>  $orderItems
     */
    public static function billedAmountForContentPackage(iterable $orderItems, int $contentPackageId): float
    {
        $col = $orderItems instanceof Collection
            ? $orderItems
            : collect($orderItems);

        $lines = $col->where('content_package_id', $contentPackageId)->sortBy('id')->values();
        if ($lines->isEmpty()) {
            return 0.0;
        }

        $withAmount = $lines->filter(fn (OrderItem $item) => (float) ($item->total ?? 0) > 0);
        $sum = (float) $lines->sum(fn (OrderItem $item) => (float) ($item->total ?? 0));

        // Cas normal : une seule ligne porte le prix forfait du pack.
        if ($withAmount->count() === 1) {
            return (float) ($withAmount->first()->total ?? 0);
        }

        // Cas rare : plusieurs lignes avec un montant (total payé pour ce pack sur la commande).
        return $sum;
    }

    /**
     * Lignes pour facture / tableaux : un pack = une ligne, au prix du pack (forfait).
     *
     * @return Collection<int, array{kind: string, label: string, meta: ?string, quantity: int, unit_price: float, line_total: float}>
     */
    public static function invoiceDisplayLines(Order $order): Collection
    {
        $order->loadMissing(['orderItems.course.provider', 'orderItems.contentPackage']);
        $items = $order->orderItems->sortBy('id')->values();
        $out = collect();
        $seenPack = [];

        foreach ($items as $item) {
            if (! $item instanceof OrderItem) {
                continue;
            }

            if (! empty($item->content_package_id)) {
                $pid = (int) $item->content_package_id;
                if (isset($seenPack[$pid])) {
                    continue;
                }
                $seenPack[$pid] = true;
                $pkg = $item->contentPackage;
                $billed = self::billedAmountForContentPackage($items, $pid);
                $out->push([
                    'kind' => 'package',
                    'label' => $pkg ? ('Pack : ' . $pkg->title) : 'Pack',
                    'meta' => 'Prix forfait du pack',
                    'quantity' => 1,
                    'unit_price' => $billed,
                    'line_total' => $billed,
                ]);

                continue;
            }

            $course = $item->course;
            $lineTotal = (float) ($item->total ?? 0);
            if ($lineTotal <= 0) {
                $lineTotal = (float) ($item->sale_price ?? $item->price ?? 0);
            }
            $unitPrice = (float) ($item->sale_price ?? $item->price ?? 0);

            $meta = null;
            if ($course && $course->provider) {
                $meta = $course->getProviderLabel() . ' : ' . $course->provider->name;
            }

            $out->push([
                'kind' => 'content',
                'label' => $course ? $course->title : 'Contenu',
                'meta' => $meta,
                'quantity' => 1,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ]);
        }

        return $out;
    }
}

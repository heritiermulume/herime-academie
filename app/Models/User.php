<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'date_of_birth',
        'gender',
        'bio',
        'avatar',
        'cover_image',
        'website',
        'linkedin',
        'twitter',
        'youtube',
        'role',
        'is_verified',
        'is_active',
        'last_login_at',
        'preferences',
        'sso_id',
        'sso_provider',
        'sso_metadata',
        'is_external_provider',
        'moneroo_phone',
        'moneroo_provider',
        'moneroo_country',
        'moneroo_currency',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the avatar URL
     */
    public function getAvatarUrlAttribute()
    {
        if (!$this->avatar) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=003366&color=fff&size=300';
        }
        
        // Si c'est déjà une URL complète, la retourner telle quelle
        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
            return $this->avatar;
        }
        
        $service = app(\App\Services\FileUploadService::class);
        return $service->getUrl($this->avatar, 'avatars');
    }

    /**
     * Get the SSO user ID from preferences
     */
    public function getSsoIdAttribute()
    {
        return $this->attributes['sso_id'] ?? null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'last_login_at' => 'datetime',
            'preferences' => 'array',
            'sso_metadata' => 'array',
        ];
    }

    // Relations
    public function courses()
    {
        return $this->hasMany(Course::class, 'provider_id');
    }

    /**
     * Alias pour compatibilité avec le nouveau nom
     */
    public function contents()
    {
        return $this->courses();
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function affiliate()
    {
        return $this->hasOne(Affiliate::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function downloads()
    {
        return $this->hasMany(CourseDownload::class);
    }

    public function providerApplication()
    {
        return $this->hasOne(ProviderApplication::class);
    }

    public function ambassadorApplication()
    {
        return $this->hasOne(AmbassadorApplication::class);
    }

    public function ambassador()
    {
        return $this->hasOne(Ambassador::class);
    }

    // Scopes
    public function scopeProviders($query)
    {
        return $query->where('role', 'provider');
    }

    public function scopeCustomers($query)
    {
        return $query->where('role', 'customer');
    }

    public function scopeAdmins($query)
    {
        // Inclure les admins et super_users (tous ceux qui ont accès à l'administration)
        return $query->whereIn('role', ['admin', 'super_user']);
    }

    public function scopeAffiliates($query)
    {
        return $query->where('role', 'affiliate');
    }

    /**
     * Alias pour compatibilité avec l'ancien nom
     */
    public function scopeInstructors($query)
    {
        return $query->where('role', 'provider');
    }

    /**
     * Alias pour compatibilité avec l'ancien nom
     */
    public function scopeStudents($query)
    {
        return $query->where('role', 'customer');
    }

    // Helper methods
    public function isProvider()
    {
        return $this->role === 'provider';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    public function isAdmin()
    {
        // Les rôles "admin" et "super_user" ont accès à l'administration
        return $this->role === 'admin' || $this->role === 'super_user';
    }
    
    /**
     * Vérifier si l'utilisateur peut accéder à l'administration
     * (Admin ou Super User)
     */
    public function canAccessAdmin()
    {
        return $this->isAdmin();
    }

    public function isAffiliate()
    {
        return $this->role === 'affiliate';
    }

    public function isExternalProvider()
    {
        return $this->is_external_provider && $this->isProvider();
    }

    /**
     * Alias pour compatibilité avec l'ancien nom
     */
    public function isExternalInstructor()
    {
        return $this->isExternalProvider();
    }

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     * 
     * @param string|array $role Le(s) rôle(s) à vérifier
     * @return bool
     */
    public function hasRole($role)
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }
        
        return $this->role === $role;
    }

    public function providerPayouts()
    {
        return $this->hasMany(ProviderPayout::class, 'provider_id');
    }
}

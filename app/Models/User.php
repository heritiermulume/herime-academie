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
        
        // Sinon, utiliser Storage::url()
        return \Storage::url($this->avatar);
    }

    /**
     * Get the SSO user ID from preferences
     */
    public function getSsoIdAttribute()
    {
        $preferences = $this->preferences ?? [];
        return $preferences['sso_id'] ?? null;
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
        ];
    }

    // Relations
    public function courses()
    {
        return $this->hasMany(Course::class, 'instructor_id');
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

    public function instructorApplication()
    {
        return $this->hasOne(InstructorApplication::class);
    }

    // Scopes
    public function scopeInstructors($query)
    {
        return $query->where('role', 'instructor');
    }

    public function scopeStudents($query)
    {
        return $query->where('role', 'student');
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

    // Helper methods
    public function isInstructor()
    {
        return $this->role === 'instructor';
    }

    public function isStudent()
    {
        return $this->role === 'student';
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
}

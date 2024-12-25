<?php

namespace App\Models;

use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    private static $isVerificationEmailSent = false;

    public function isAdmin()
    {
         return $this->is_admin;
    }

    /**
     * إرسال إشعار تأكيد البريد الإلكتروني.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        if (self::$isVerificationEmailSent) {
            Log::info('Skipping duplicate verification email', [
                'user_id' => $this->id,
                'email' => $this->email
            ]);
            return;
        }

        Log::info('Sending email verification notification', [
            'user_id' => $this->id,
            'email' => $this->email
        ]);
        
        self::$isVerificationEmailSent = true;
        $this->notify(new CustomVerifyEmail);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 
        'email',
        'google_id', 
        'password', 
        'phone', 
        'job_title', 
        'gender', 
        'country', 
        'social_links', 
        'bio', 
        'is_online'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_online' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];
}

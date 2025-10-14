<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Symfony\Component\Uid\Ulid;

/**
 * User Model - Platform user
 *
 * @property string $id (ULID)
 * @property string $username
 * @property string $email
 * @property string $phone
 * @property string|null $password_hash
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $user_type (USER, PLATFORM_ADMIN)
 * @property string $status (ACTIVE, INACTIVE, SUSPENDED)
 * @property \Carbon\Carbon|null $email_verified_at
 * @property \Carbon\Carbon|null $phone_verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class User extends Authenticatable implements JWTSubject
{
    protected $table = 'users';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'username',
        'email',
        'phone',
        'password_hash',
        'first_name',
        'last_name',
        'user_type',
        'status',
        'email_verified_at',
        'phone_verified_at',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) new Ulid();
            }
        });
    }

    // Relations

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'user_id');
    }

    public function otpVerifications(): HasMany
    {
        return $this->hasMany(OtpVerification::class, 'phone', 'phone');
    }

    // JWT Methods

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'username' => $this->username,
            'userType' => $this->user_type,
        ];
    }
}

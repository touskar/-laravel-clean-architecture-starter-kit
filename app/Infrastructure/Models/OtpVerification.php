<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Uid\Ulid;

/**
 * Modèle OtpVerification - Vérification OTP pour enregistrement
 *
 * @property string $id (ULID)
 * @property string $session_token (unique)
 * @property string $otp_code
 * @property string $phone_number
 * @property string $call_code
 * @property string|null $country_code
 * @property string|null $email
 * @property string|null $username
 * @property string|null $password
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $address
 * @property string|null $user_type (PLATFORM_ADMIN, ADVERTISER, CONTENT_CREATOR)
 * @property int $attempt_count
 * @property bool $verified
 * @property \Carbon\Carbon $expires_at
 * @property \Carbon\Carbon $created_at
 *
 * Indexes:
 * - idx_otp_session_token on session_token (unique)
 * - idx_otp_phone on (phone_number, call_code)
 * - idx_otp_expires_at on expires_at
 */
class OtpVerification extends Model
{
    protected $table = 'otp_verifications';

    protected $keyType = 'string';
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'session_token',
        'otp_code',
        'phone_number',
        'call_code',
        'country_code',
        'email',
        'username',
        'password',
        'first_name',
        'last_name',
        'address',
        'user_type',
        'attempt_count',
        'verified',
        'expires_at',
        'created_at',
    ];

    protected $hidden = [
        'password',
        'otp_code',
    ];

    protected $casts = [
        'attempt_count' => 'integer',
        'verified' => 'boolean',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) new Ulid();
            }
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }
}

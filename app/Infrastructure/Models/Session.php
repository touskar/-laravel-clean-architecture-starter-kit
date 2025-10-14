<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Uid\Ulid;

/**
 * Modèle Session - Session utilisateur
 *
 * @property string $id (ULID)
 * @property string $hashed_jwt (unique)
 * @property string $first_ip
 * @property string $last_ip
 * @property string|null $device_identifier
 * @property string|null $user_agent
 * @property bool $is_active
 * @property \Carbon\Carbon $expires_at
 * @property \Carbon\Carbon $last_access_at
 * @property string $user_id
 * @property string|null $created_by_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * Indexes:
 * - idx_session_hashed_jwt on hashed_jwt (unique)
 * - idx_session_user_id on user_id
 * - idx_session_is_active on is_active
 * - idx_session_device on device_identifier
 * - idx_session_expires_at on expires_at
 * - idx_session_last_access on last_access_at
 * - idx_session_user_active on (user_id, is_active)
 */
class Session extends Model
{
    protected $table = 'sessions';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'hashed_jwt',
        'first_ip',
        'last_ip',
        'device_identifier',
        'user_agent',
        'is_active',
        'expires_at',
        'last_access_at',
        'user_id',
        'created_by_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'last_access_at' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}

<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Symfony\Component\Uid\Ulid;

/**
 * Modèle User - Utilisateur de la plateforme
 *
 * @property string $id (ULID)
 * @property string $name
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $username
 * @property string $phone_number
 * @property string|null $password
 * @property string|null $address
 * @property string $user_type (PLATFORM_ADMIN, ADVERTISER, CONTENT_CREATOR)
 * @property string $status (ACTIVE, DELETED, DISABLED)
 * @property string $country_id
 * @property string|null $created_by_id
 * @property string|null $advertiser_company_id
 * @property string|null $content_creator_id
 * @property string|null $owned_advertiser_company_id
 * @property string|null $owned_content_creator_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * Indexes:
 * - idx_user_email on email (unique)
 * - idx_user_username on username (unique)
 * - idx_user_status on status
 * - idx_user_type on user_type
 * - idx_user_country on country_id
 * - idx_user_created_at on created_at
 */
class User extends Model implements JWTSubject
{
    protected $table = 'users';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'username',
        'phone_number',
        'password',
        'address',
        'user_type',
        'status',
        'country_id',
        'created_by_id',
        'advertiser_company_id',
        'content_creator_id',
        'owned_advertiser_company_id',
        'owned_content_creator_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
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

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function advertiserCompany(): BelongsTo
    {
        return $this->belongsTo(AdvertiserCompany::class, 'advertiser_company_id');
    }

    public function contentCreator(): BelongsTo
    {
        return $this->belongsTo(ContentCreator::class, 'content_creator_id');
    }

    public function ownedAdvertiserCompany(): HasOne
    {
        return $this->hasOne(AdvertiserCompany::class, 'owner_user_id');
    }

    public function ownedContentCreator(): HasOne
    {
        return $this->hasOne(ContentCreator::class, 'owner_user_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'user_id');
    }

    public function otps(): HasMany
    {
        return $this->hasMany(Otp::class, 'user_id');
    }

    public function createdCountries(): HasMany
    {
        return $this->hasMany(Country::class, 'created_by_id');
    }

    public function createdCampaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'created_by_id');
    }

    public function advertiserCompanyMemberships(): BelongsToMany
    {
        return $this->belongsToMany(
            AdvertiserCompany::class,
            'advertiser_company_members',
            'user_id',
            'company_id'
        );
    }

    public function contentCreatorMemberships(): BelongsToMany
    {
        return $this->belongsToMany(
            ContentCreator::class,
            'content_creator_members',
            'user_id',
            'creator_id'
        );
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

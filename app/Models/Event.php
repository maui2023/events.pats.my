<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    public static function categoryDefinitions(): array
    {
        return [
            ['key' => 'tech', 'label' => 'Tech', 'icon' => '💻'],
            ['key' => 'food', 'label' => 'Food & Drink', 'icon' => '🍔'],
            ['key' => 'ai', 'label' => 'AI', 'icon' => '🤖'],
            ['key' => 'arts', 'label' => 'Arts & Culture', 'icon' => '🎭'],
            ['key' => 'climate', 'label' => 'Climate', 'icon' => '🌿'],
            ['key' => 'fitness', 'label' => 'Fitness', 'icon' => '🏃'],
            ['key' => 'wellness', 'label' => 'Wellness', 'icon' => '🧘'],
            ['key' => 'crypto', 'label' => 'Crypto', 'icon' => '🪙'],
            ['key' => 'world', 'label' => 'World Topic', 'icon' => '🌍'],
        ];
    }

    public static function categoryKeys(): array
    {
        return array_map(fn (array $d) => $d['key'], self::categoryDefinitions());
    }

    protected $fillable = [
        'organizer_id',
        'organization_id',
        'title',
        'slug',
        'description',
        'start_at',
        'end_at',
        'location',
        'postcode',
        'country',
        'state_id',
        'banner_path',
        'icon',
        'category_keys',
        'is_published',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'category_keys' => 'array',
        'is_published' => 'boolean',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function staffs()
    {
        return $this->hasMany(EventStaff::class);
    }

    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function timezoneName(): string
    {
        $code = strtoupper((string) ($this->country ?? ''));
        if (strlen($code) === 2) {
            $tzs = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $code);
            if (! empty($tzs)) {
                return $tzs[0];
            }
        }

        return (string) config('app.timezone', 'UTC');
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        $orgIds = collect();
        $userId = null;
        if ($user) {
            $userId = (int) $user->id;
            $orgIds = $user->organizations()
                ->wherePivot('status', 'approved')
                ->pluck('organizations.id');
        }

        return $query->where(function ($q) use ($orgIds, $userId) {
            $q->whereNull('organization_id')
                ->orWhereHas('organization', function ($oq) {
                    $oq->where('status', 'approved')->where('is_public', true);
                })
                ->when($userId, function ($qq) use ($orgIds, $userId) {
                    $qq->orWhereIn('organization_id', $orgIds)
                        ->orWhere('organizer_id', $userId);
                });
        });
    }
}

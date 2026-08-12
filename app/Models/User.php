<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'code_name',
        'email',
        'password',
        'profile_picture',
        'is_verified',
        'role',
        'cannot_post',
        'cannot_claim',
        'is_banned',
        'login_blocked_until',
        'restriction_note',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'cannot_post' => 'boolean',
            'cannot_claim' => 'boolean',
            'is_banned' => 'boolean',
            'login_blocked_until' => 'datetime',
        ];
    }

    public function isBanned(): bool
    {
        return (bool) $this->is_banned;
    }

    public function isLoginBlocked(): bool
    {
        if ($this->isBanned()) {
            return true;
        }

        return $this->login_blocked_until !== null && $this->login_blocked_until->isFuture();
    }

    public function cannotPostItems(): bool
    {
        return $this->isBanned() || (bool) $this->cannot_post;
    }

    public function cannotClaimItems(): bool
    {
        return $this->isBanned() || (bool) $this->cannot_claim;
    }

    /**
     * Message shown when login is blocked (investigation / ban).
     */
    public function loginRestrictionMessage(): ?string
    {
        if ($this->isBanned()) {
            return 'Your account has been banned.';
        }

        if ($this->login_blocked_until !== null && $this->login_blocked_until->isFuture()) {
            return 'Your account is under investigation.';
        }

        return null;
    }

    /**
     * Get messages sent by this user
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get messages received by this user
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Get unread messages count
     */
    public function unreadMessagesCount()
    {
        return $this->receivedMessages()->where('is_read', false)->count();
    }

    /**
     * Get rewards for this user
     */
    public function rewards()
    {
        return $this->hasMany(Reward::class);
    }

    /**
     * Get available (unused and not expired) rewards
     */
    public function availableRewards()
    {
        return $this->rewards()
            ->where('is_used', false)
            ->where('status', 'active')
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
            });
    }
}

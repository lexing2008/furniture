<?php

namespace App\Models;

use App\Enums\Roles;
use App\Notifications\VerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'email_verified_at', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

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
            'role' => Roles::class,
        ];
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    /**
     * Where the user lands after login and email verification: staff in the admin panel, others in the account.
     */
    public function homePath(): string
    {
        return $this->isEmployee()
            ? route('admin.index', absolute: false)
            : route('account.index', absolute: false);
    }

    /**
     * The verification letter is the designed one, see App\Notifications\VerifyEmailNotification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    /**
     * Site staff: admin, manager, seo.
     */
    public function scopeEmployees(Builder $query): void
    {
        $query->whereIn('role', Roles::employeeRoles());
    }

    public function isEmployee(): bool
    {
        return $this->role?->isEmployee() ?? false;
    }

    public function isAdmin(): bool
    {
        return $this->role === Roles::ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === Roles::MANAGER;
    }

    public function isSeo(): bool
    {
        return $this->role === Roles::SEO;
    }

    public function isCustomer(): bool
    {
        return $this->role === Roles::CUSTOMER;
    }

    public function isAdminPanelRole(): bool
    {
        return $this->isAdmin()
            || $this->isManager()
            || $this->isSeo();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdmin(): bool
    {
        return $this->getRoleName() === 'admin';
    }

    public function isDoctor(): bool
    {
        return $this->getRoleName() === 'doctor';
    }

    public function isReceptionist(): bool
    {
        return $this->getRoleName() === 'receptionist';
    }

    public function hasRole(string $role): bool
    {
        return $this->getRoleName() === strtolower($role);
    }

    private function getRoleName(): string
    {
        return strtolower($this->role?->name ?? $this->role ?? '');
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use \App\Models\Concerns\Auditavel, \App\Models\Concerns\ControlaAcesso, HasFactory, HasRoles, Notifiable;

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
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->ativo && $this->perfisAtivos() !== [];
    }

    /**
     * Perfis do usuário que estão ativos. Um perfil inativado deixa de dar acesso a qualquer coisa.
     *
     * @return list<string>
     */
    public function perfisAtivos(): array
    {
        return $this->roles
            ->filter(fn ($perfil) => (bool) ($perfil->ativo ?? true))
            ->pluck('name')
            ->values()
            ->all();
    }
}

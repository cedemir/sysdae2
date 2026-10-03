<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Support\Administradores;
use App\Support\Perfis;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class UserForm
{
    public const ROTULOS_PERFIL = [
        Perfis::ADMIN => 'Administrador',
        Perfis::DAE_CENTRAL => 'DAE Central',
        Perfis::RESIDENCIA => 'Residência Estudantil',
        Perfis::PSICOSSOCIAL => 'Psicossocial',
        Perfis::SOMENTE_CONSULTA => 'SomenteConsulta',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do usuário')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('E-mail (usado para entrar)')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Select::make('roles')
                            ->label('Perfil de acesso')
                            ->relationship('roles', 'name')
                            ->getOptionLabelFromRecordUsing(
                                fn (Role $record) => self::ROTULOS_PERFIL[$record->name] ?? $record->name
                            )
                            ->multiple()
                            ->minItems(1)
                            ->maxItems(1)
                            ->preload()
                            ->required()
                            ->rules([
                                fn (?Model $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                                    if (! $record || ! $record->hasRole(Perfis::ADMIN)) {
                                        return;
                                    }
                                    $adminId = Role::where('name', Perfis::ADMIN)->where('guard_name', 'web')->value('id');
                                    $selecionados = array_map('intval', (array) $value);
                                    if (! in_array((int) $adminId, $selecionados, true)
                                        && Administradores::outrosAtivos($record->id) === 0) {
                                        $fail('Este é o único administrador ativo; não é possível trocar o perfil dele.');
                                    }
                                },
                            ]),
                        Toggle::make('ativo')
                            ->label('Usuário ativo')
                            ->helperText('Usuário inativo não consegue entrar, e as sessões abertas são encerradas.')
                            ->default(true)
                            ->rules([
                                fn (?Model $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                                    if (! $record || $value) {
                                        return;
                                    }
                                    if ($record->is(auth()->user())) {
                                        $fail('Você não pode inativar o seu próprio usuário.');
                                    } elseif ($record->hasRole(Perfis::ADMIN) && Administradores::outrosAtivos($record->id) === 0) {
                                        $fail('Deve existir pelo menos um administrador ativo.');
                                    }
                                },
                            ]),
                    ]),

                Section::make('Senha')
                    ->description('Na edição, deixe em branco para manter a senha atual.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText('Mínimo de 8 caracteres, com letras e números.')
                            ->rules([
                                fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                    if (filled($value) && ! preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,}$/', (string) $value)) {
                                        $fail('A senha deve ter no mínimo 8 caracteres, com letras e números.');
                                    }
                                },
                            ]),
                        TextInput::make('password_confirmation')
                            ->label('Confirmar senha')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->same('password')
                            ->validationMessages(['same' => 'A confirmação não confere com a senha.']),
                    ]),
            ]);
    }
}
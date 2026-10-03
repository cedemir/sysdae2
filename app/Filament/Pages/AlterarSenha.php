<?php

namespace App\Filament\Pages;

use App\Models\Auditoria;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/** O próprio usuário troca a sua senha (aberta pelo botão da página Painel). */
class AlterarSenha extends Page
{
    protected static ?string $title = 'Alterar senha';

    protected static ?string $slug = 'alterar-senha';

    // Não aparece no menu: abre pelo botão "Alterar senha" da página Painel.
    protected static bool $shouldRegisterNavigation = false;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->description('Por segurança, ao trocar a senha você continua conectado aqui, mas as sessões abertas em outros computadores são encerradas.')
                    ->schema([
                        TextInput::make('senha_atual')
                            ->label('Senha atual')
                            ->password()
                            ->revealable()
                            ->required()
                            ->autocomplete('current-password')
                            ->currentPassword(),
                        TextInput::make('nova_senha')
                            ->label('Nova senha')
                            ->password()
                            ->revealable()
                            ->required()
                            ->rule(Password::min(8)->letters()->numbers())
                            ->different('senha_atual')
                            ->autocomplete('new-password')
                            ->helperText('Mínimo de 8 caracteres, com letras e números.')
                            ->validationMessages(['different' => 'A nova senha precisa ser diferente da atual.']),
                        TextInput::make('confirmacao')
                            ->label('Confirme a nova senha')
                            ->password()
                            ->revealable()
                            ->required()
                            ->same('nova_senha')
                            ->autocomplete('new-password')
                            ->validationMessages(['same' => 'A confirmação não confere com a nova senha.']),
                    ])
                    ->maxWidth('xl'),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('salvar')
                    ->footer([
                        Actions::make([
                            Action::make('salvar')
                                ->label('Salvar nova senha')
                                ->submit('salvar'),
                        ]),
                    ]),
            ]);
    }

    public function salvar(): void
    {
        $dados = $this->form->getState();
        $usuario = auth()->user();

        $usuario->forceFill(['password' => Hash::make($dados['nova_senha'])])->save();

        $sessao = request()->hasSession() ? request()->session() : null;

        // Sem isto o middleware AuthenticateSession desconectaria o próprio usuário após a troca.
        $sessao?->put('password_hash_' . Filament::getAuthGuard(), $usuario->getAuthPassword());

        // Encerra as sessões do usuário em outros computadores.
        DB::table('sessions')
            ->where('user_id', $usuario->getAuthIdentifier())
            ->when($sessao, fn ($consulta) => $consulta->where('id', '!=', $sessao->getId()))
            ->delete();

        Auditoria::registrar('alterou_senha', 'Acesso', (int) $usuario->getAuthIdentifier(), $usuario->email);

        $this->form->fill();

        Notification::make()
            ->title('Senha alterada com sucesso.')
            ->success()
            ->send();
    }
}

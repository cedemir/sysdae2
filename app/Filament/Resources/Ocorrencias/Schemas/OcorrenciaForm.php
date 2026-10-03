<?php

namespace App\Filament\Resources\Ocorrencias\Schemas;

use App\Models\Aluno;
use App\Models\Ocorrencia;
use App\Support\Formatos;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class OcorrenciaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ocorrência')
                    ->columns(2)
                    ->schema([
                        Select::make('aluno_id')
                            ->label('Aluno')
                            ->relationship('aluno', 'nome')
                            ->getOptionLabelFromRecordUsing(
                                fn (Aluno $record) => $record->nome . ' (' . Formatos::cpf($record->cpf) . ')'
                            )
                            ->searchable(['nome', 'cpf'])
                            ->required(),
                        DatePicker::make('data_ocorrencia')
                            ->label('Data da ocorrência')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(fn () => now()->toDateString())
                            ->maxDate(now())
                            ->required(),
                        Textarea::make('descricao')
                            ->label('Descrição da situação')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Toggle::make('sigiloso')
                            ->label('Ocorrência sigilosa')
                            ->helperText('Só administradores e a equipe psicossocial veem ocorrências sigilosas.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Comissão disciplinar')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('data_reuniao')
                            ->label('Data da reunião')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Select::make('advertencia')
                            ->label('Advertência')
                            ->options(Ocorrencia::SITUACOES)
                            ->placeholder('Sem advertência'),
                        Textarea::make('medidas')
                            ->label('Medidas')
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('suspensao_residencia')
                            ->label('Suspensão da residência'),
                        Select::make('perda_vaga')
                            ->label('Perda da vaga na residência estudantil')
                            ->options(Ocorrencia::SITUACOES)
                            ->placeholder('Sem perda de vaga'),
                    ]),

                Section::make('Atividades orientadas')
                    ->columns(2)
                    ->schema([
                        Textarea::make('atividades_orientadas')
                            ->label('Atividades orientadas')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('horas_recebidas')
                            ->label('Total de horas recebidas')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1000),
                        TextInput::make('horas_cumpridas')
                            ->label('Horas cumpridas')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1000)
                            ->rules([
                                fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                    $recebidas = $get('horas_recebidas');
                                    if (filled($value) && filled($recebidas) && (int) $value > (int) $recebidas) {
                                        $fail('As horas cumpridas não podem passar das horas recebidas.');
                                    }
                                },
                            ]),
                        TextInput::make('setor')
                            ->label('Setor')
                            ->maxLength(100),
                        TextInput::make('servidor')
                            ->label('Servidor responsável')
                            ->maxLength(100),
                    ]),

                Section::make('Anexos')
                    ->description('Documentos da ocorrência (termos, atas, fotos). Ficam em área privada e só abrem para quem pode ver a ocorrência.')
                    ->schema([
                        FileUpload::make('anexos')
                            ->label('Arquivos')
                            ->multiple()
                            ->disk('local')
                            ->directory('anexos-disciplinar')
                            ->visibility('private')
                            ->storeFileNamesIn('anexos_nomes')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/jpeg',
                                'image/png',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ])
                            ->maxSize(10240)
                            ->maxFiles(10)
                            ->helperText('PDF, imagens ou documentos do Word, até 10 MB cada (no máximo 10 arquivos).'),
                    ]),

                Section::make('Práticas restaurativas e encaminhamentos')
                    ->schema([
                        Textarea::make('praticas_restaurativas')
                            ->label('Práticas restaurativas')
                            ->rows(3),
                        Textarea::make('outros_encaminhamentos')
                            ->label('Outros encaminhamentos')
                            ->rows(3),
                    ]),
            ]);
    }
}
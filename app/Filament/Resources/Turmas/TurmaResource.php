<?php

namespace App\Filament\Resources\Turmas;

use App\Filament\Resources\Turmas\Pages\CreateTurma;
use App\Filament\Resources\Turmas\Pages\EditTurma;
use App\Filament\Resources\Turmas\Pages\ListTurmas;
use App\Filament\Resources\Turmas\Pages\ViewTurma;
use App\Filament\Resources\Turmas\Schemas\TurmaForm;
use App\Filament\Resources\Turmas\Schemas\TurmaInfolist;
use App\Filament\Resources\Turmas\Tables\TurmasTable;
use App\Models\Turma;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TurmaResource extends Resource
{
    protected static ?string $model = Turma::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Turma';

    public static function form(Schema $schema): Schema
    {
        return TurmaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TurmaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TurmasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTurmas::route('/'),
            'create' => CreateTurma::route('/create'),
            'view' => ViewTurma::route('/{record}'),
            'edit' => EditTurma::route('/{record}/edit'),
        ];
    }
}

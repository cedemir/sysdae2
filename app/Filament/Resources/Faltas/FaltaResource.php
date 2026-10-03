<?php

namespace App\Filament\Resources\Faltas;

use App\Filament\Resources\Faltas\Pages\CreateFalta;
use App\Filament\Resources\Faltas\Pages\EditFalta;
use App\Filament\Resources\Faltas\Pages\ListFaltas;
use App\Filament\Resources\Faltas\Schemas\FaltaForm;
use App\Filament\Resources\Faltas\Tables\FaltasTable;
use App\Models\Falta;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FaltaResource extends Resource
{
    protected static ?string $model = Falta::class;

    protected static ?string $modelLabel = 'Falta na residência';

    protected static ?string $pluralModelLabel = 'Faltas na residência';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return FaltaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return \App\Filament\Resources\Faltas\Schemas\FaltaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FaltasTable::configure($table);
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
            'index' => ListFaltas::route('/'),
            'create' => CreateFalta::route('/create'),
            'view' => \App\Filament\Resources\Faltas\Pages\ViewFalta::route('/{record}'),
            'edit' => EditFalta::route('/{record}/edit'),
        ];
    }
}

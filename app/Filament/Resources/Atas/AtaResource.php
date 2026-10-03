<?php

namespace App\Filament\Resources\Atas;

use App\Filament\Resources\Atas\Pages\CreateAta;
use App\Filament\Resources\Atas\Pages\EditAta;
use App\Filament\Resources\Atas\Pages\ListAtas;
use App\Filament\Resources\Atas\Schemas\AtaForm;
use App\Filament\Resources\Atas\Tables\AtasTable;
use App\Models\Ata;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AtaResource extends Resource
{
    protected static ?string $model = Ata::class;

    protected static ?string $modelLabel = 'Ata de reunião';

    protected static ?string $pluralModelLabel = 'Atas de reunião';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AtaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return \App\Filament\Resources\Atas\Schemas\AtaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtasTable::configure($table);
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
            'index' => ListAtas::route('/'),
            'create' => CreateAta::route('/create'),
            'view' => \App\Filament\Resources\Atas\Pages\ViewAta::route('/{record}'),
            'edit' => EditAta::route('/{record}/edit'),
        ];
    }
}

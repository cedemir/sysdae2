<?php

namespace App\Filament\Resources\Pernoites;

use App\Filament\Resources\Pernoites\Pages\CreatePernoite;
use App\Filament\Resources\Pernoites\Pages\EditPernoite;
use App\Filament\Resources\Pernoites\Pages\ListPernoites;
use App\Filament\Resources\Pernoites\Schemas\PernoiteForm;
use App\Filament\Resources\Pernoites\Tables\PernoitesTable;
use App\Models\Pernoite;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PernoiteResource extends Resource
{
    protected static ?string $model = Pernoite::class;

    protected static ?string $modelLabel = 'Autorização de pernoite';

    protected static ?string $pluralModelLabel = 'Autorizações de pernoite';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PernoiteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return \App\Filament\Resources\Pernoites\Schemas\PernoiteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PernoitesTable::configure($table);
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
            'index' => ListPernoites::route('/'),
            'create' => CreatePernoite::route('/create'),
            'view' => \App\Filament\Resources\Pernoites\Pages\ViewPernoite::route('/{record}'),
            'edit' => EditPernoite::route('/{record}/edit'),
        ];
    }
}

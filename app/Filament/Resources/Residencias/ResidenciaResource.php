<?php

namespace App\Filament\Resources\Residencias;

use App\Filament\Resources\Residencias\Pages\CreateResidencia;
use App\Filament\Resources\Residencias\Pages\EditResidencia;
use App\Filament\Resources\Residencias\Pages\ListResidencias;
use App\Filament\Resources\Residencias\Pages\ViewResidencia;
use App\Filament\Resources\Residencias\Schemas\ResidenciaForm;
use App\Filament\Resources\Residencias\Schemas\ResidenciaInfolist;
use App\Filament\Resources\Residencias\Tables\ResidenciasTable;
use App\Models\Residencia;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ResidenciaResource extends Resource
{
    protected static ?string $model = Residencia::class;

    protected static ?string $modelLabel = 'Residência';

    protected static ?string $pluralModelLabel = 'Residências';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Residencia';

    public static function form(Schema $schema): Schema
    {
        return ResidenciaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ResidenciaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResidenciasTable::configure($table);
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
            'index' => ListResidencias::route('/'),
            'create' => CreateResidencia::route('/create'),
            'view' => ViewResidencia::route('/{record}'),
            'edit' => EditResidencia::route('/{record}/edit'),
        ];
    }
}

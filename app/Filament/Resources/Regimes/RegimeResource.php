<?php

namespace App\Filament\Resources\Regimes;

use App\Filament\Resources\Regimes\Pages\CreateRegime;
use App\Filament\Resources\Regimes\Pages\EditRegime;
use App\Filament\Resources\Regimes\Pages\ListRegimes;
use App\Filament\Resources\Regimes\Pages\ViewRegime;
use App\Filament\Resources\Regimes\Schemas\RegimeForm;
use App\Filament\Resources\Regimes\Schemas\RegimeInfolist;
use App\Filament\Resources\Regimes\Tables\RegimesTable;
use App\Models\Regime;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RegimeResource extends Resource
{
    protected static ?string $model = Regime::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Regime';

    public static function form(Schema $schema): Schema
    {
        return RegimeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RegimeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegimesTable::configure($table);
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
            'index' => ListRegimes::route('/'),
            'create' => CreateRegime::route('/create'),
            'view' => ViewRegime::route('/{record}'),
            'edit' => EditRegime::route('/{record}/edit'),
        ];
    }
}

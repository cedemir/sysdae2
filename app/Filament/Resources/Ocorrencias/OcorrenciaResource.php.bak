<?php

namespace App\Filament\Resources\Ocorrencias;

use App\Filament\Resources\Ocorrencias\Pages\CreateOcorrencia;
use App\Filament\Resources\Ocorrencias\Pages\EditOcorrencia;
use App\Filament\Resources\Ocorrencias\Pages\ListOcorrencias;
use App\Filament\Resources\Ocorrencias\Schemas\OcorrenciaForm;
use App\Filament\Resources\Ocorrencias\Tables\OcorrenciasTable;
use App\Models\Ocorrencia;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OcorrenciaResource extends Resource
{
    protected static ?string $model = Ocorrencia::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return OcorrenciaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OcorrenciasTable::configure($table);
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
            'index' => ListOcorrencias::route('/'),
            'create' => CreateOcorrencia::route('/create'),
            'edit' => EditOcorrencia::route('/{record}/edit'),
        ];
    }
}

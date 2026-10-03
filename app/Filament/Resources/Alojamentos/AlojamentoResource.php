<?php

namespace App\Filament\Resources\Alojamentos;

use App\Filament\Resources\Alojamentos\Pages\CreateAlojamento;
use App\Filament\Resources\Alojamentos\Pages\EditAlojamento;
use App\Filament\Resources\Alojamentos\Pages\ListAlojamentos;
use App\Filament\Resources\Alojamentos\Pages\ViewAlojamento;
use App\Filament\Resources\Alojamentos\Schemas\AlojamentoForm;
use App\Filament\Resources\Alojamentos\Schemas\AlojamentoInfolist;
use App\Filament\Resources\Alojamentos\Tables\AlojamentosTable;
use App\Models\Alojamento;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AlojamentoResource extends Resource
{
    protected static ?string $model = Alojamento::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Alojamento';

    public static function form(Schema $schema): Schema
    {
        return AlojamentoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AlojamentoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AlojamentosTable::configure($table);
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
            'index' => ListAlojamentos::route('/'),
            'create' => CreateAlojamento::route('/create'),
            'view' => ViewAlojamento::route('/{record}'),
            'edit' => EditAlojamento::route('/{record}/edit'),
        ];
    }
}

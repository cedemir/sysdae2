<?php

namespace App\Filament\Resources\Apartamentos;

use App\Filament\Resources\Apartamentos\Pages\CreateApartamento;
use App\Filament\Resources\Apartamentos\Pages\EditApartamento;
use App\Filament\Resources\Apartamentos\Pages\ListApartamentos;
use App\Filament\Resources\Apartamentos\Pages\ViewApartamento;
use App\Filament\Resources\Apartamentos\Schemas\ApartamentoForm;
use App\Filament\Resources\Apartamentos\Schemas\ApartamentoInfolist;
use App\Filament\Resources\Apartamentos\Tables\ApartamentosTable;
use App\Models\Apartamento;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ApartamentoResource extends Resource
{
    protected static ?string $model = Apartamento::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Apartamento';

    public static function form(Schema $schema): Schema
    {
        return ApartamentoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ApartamentoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApartamentosTable::configure($table);
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
            'index' => ListApartamentos::route('/'),
            'create' => CreateApartamento::route('/create'),
            'view' => ViewApartamento::route('/{record}'),
            'edit' => EditApartamento::route('/{record}/edit'),
        ];
    }
}

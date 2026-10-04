<?php

namespace App\Filament\Resources\Perfis;

use App\Filament\Resources\Perfis\Pages\CreatePerfil;
use App\Filament\Resources\Perfis\Pages\EditPerfil;
use App\Filament\Resources\Perfis\Pages\ListPerfis;
use App\Filament\Resources\Perfis\Schemas\PerfilForm;
use App\Filament\Resources\Perfis\Tables\PerfisTable;
use App\Models\Perfil;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PerfilResource extends Resource
{
    protected static ?string $model = Perfil::class;

    protected static ?string $slug = 'perfis';

    protected static ?string $modelLabel = 'Perfil';

    protected static ?string $pluralModelLabel = 'Perfis';

    protected static ?string $recordTitleAttribute = 'nome';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PerfilForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PerfisTable::configure($table);
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
            'index' => ListPerfis::route('/'),
            'create' => CreatePerfil::route('/create'),
            'edit' => EditPerfil::route('/{record}/edit'),
        ];
    }
}

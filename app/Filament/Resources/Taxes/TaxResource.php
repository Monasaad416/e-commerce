<?php

namespace App\Filament\Resources\Taxes;

use App\Filament\Resources\Taxes\Pages\CreateTax;
use App\Filament\Resources\Taxes\Pages\EditTax;
use App\Filament\Resources\Taxes\Pages\ListTaxes;
use App\Filament\Resources\Taxes\Pages\ViewTax;
use App\Filament\Resources\Taxes\Schemas\TaxForm;
use App\Filament\Resources\Taxes\Schemas\TaxInfolist;
use App\Filament\Resources\Taxes\Tables\TaxesTable;
use App\Models\Tax;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TaxResource extends Resource
{
    protected static ?string $model = Tax::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPercentBadge;

    protected static ?string $recordTitleAttribute = 'Tax';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return TaxForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TaxInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxesTable::configure($table);
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
            'index' => ListTaxes::route('/'),
            'create' => CreateTax::route('/create'),
            'view' => ViewTax::route('/{record}'),
            'edit' => EditTax::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament/admin/tax_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/tax_resource.plural_model_label');
    }
}

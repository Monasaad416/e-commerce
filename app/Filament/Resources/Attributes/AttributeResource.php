<?php

namespace App\Filament\Resources\Attributes;

use App\Filament\Resources\Attributes\Pages\CreateAttribute;
use App\Filament\Resources\Attributes\Pages\EditAttribute;
use App\Filament\Resources\Attributes\Pages\ListAttributes;
use App\Filament\Resources\Attributes\Schemas\AttributeForm;
use App\Filament\Resources\Attributes\Tables\AttributesTable;
use App\Models\Attribute;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $recordTitleAttribute = 'Attribute';

    public static function form(Schema $schema): Schema
    {
        return AttributeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttributesTable::configure($table);
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
            'index' => ListAttributes::route('/'),
        ];
    }


           
    public static function getModelLabel(): string
    {
        return __('filament/admin/attribute_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/attribute_resource.plural_model_label');
    }
}

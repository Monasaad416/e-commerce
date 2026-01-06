<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use App\Models\VariantAttributeValue;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ImageColumn;

class ProductVariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'productVariants';



    // public function isReadOnly(): bool
    // {
    //     return false;
    // }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('sku')
                    ->label(__('SKU'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('qty')
                    ->label(__('Quantity'))
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('purchase_price')
                    ->label(__('Purchase Price'))
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('selling_price')
                    ->label(__('Selling Price'))
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('discount_price')
                    ->label(__('Discount Price'))
                    ->numeric()
                    ->prefix('$'),
                Toggle::make('is_active')
                    ->label(__('Active'))
                    ->default(true),
                Toggle::make('is_featured')
                    ->label(__('Featured'))
                    ->default(false),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('product_id'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_id')
            ->columns([
            TextColumn::make('variant_attributes')
                ->label(__('Attributes'))
                ->state(function (Model $record) {

                    return \App\Models\VariantAttributeValue::with(['attribute', 'attributeValue'])
                        ->where('product_variant_id', $record->id)
                        ->get()
                        ->groupBy('attribute_id')
                        ->map(function ($items) {

                            $item = $items->first();

                            $name = is_array($item->attribute->name)
                                ? ($item->attribute->name['ar'] ?? collect($item->attribute->name)->first())
                                : json_decode($item->attribute->name, true)['ar'] ?? $item->attribute->name;

                            $value = is_array($item->attributeValue->value)
                                ? ($item->attributeValue->value['ar'] ?? collect($item->attributeValue->value)->first())
                                : json_decode($item->attributeValue->value, true)['ar'] ?? $item->attributeValue->value;

                            return "{$name}: {$value}";
                        })
                        ->values()
                        ->implode(', ') ?: '-';
                })
                ->html(),

                TextColumn::make('discount_price')
                    ->label(__('Discount Price'))
                    ->searchable(),
                ImageColumn::make('variant_image')
                    ->label(__('general.image'))
                ->getStateUsing(fn ($record) => 
                    $record->variantImages->first()?->image_path ?? asset('storage/No_Image_Available.jpg')
                )

                    ->extraImgAttributes(['class' => 'object-contain'])
                    ->square(),
                TextColumn::make('sku')
                    ->label(__('filament/admin/product_resource.sku'))
                    ->searchable(),
                TextColumn::make('qty')
                    ->label(__('filament/admin/product_resource.qty'))
                    ->searchable(),
                TextColumn::make('purchase_price')
                    ->label(__('filament/admin/product_resource.purchase_price'))
                    ->searchable(),
                TextColumn::make('selling_price')
                    ->label(__('filament/admin/product_resource.selling_price'))
                    ->searchable(),
                TextColumn::make('discount_price')
                    ->label(__('filament/admin/product_resource.discount_price'))
                    ->searchable(),
                TextColumn::make('is_active')
                    ->label(__('filament/admin/product_resource.is_active'))
                    ->searchable(),
                TextColumn::make('is_featured')
                    ->label(__('filament/admin/product_resource.is_featured'))
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->modelLabel('Variant')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['product_id'] = $this->getOwnerRecord()->id;
                        return $data;
                    })
                    ->using(function (array $data, string $model): Model {
                        return $this->getOwnerRecord()->productVariants()->create($data);
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                // DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

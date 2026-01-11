<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(
                Product::query()->with(['images','primaryImage', 'productVariants.variantPrimaryImage'])
            )
            ->columns([
                TextColumn::make('category.name')
                    ->label(__('filament/admin/product_resource.category_id'))
                    ->sortable(),
                TextColumn::make('sku')
                    ->label(__('filament/admin/product_resource.sku'))
                    ->searchable(),
         

                ImageColumn::make('primary_image')
                    ->disk('public') 
                    ->getStateUsing(function ($record) {

                        //Variable product → first variant with a primary image
                        if ($record->type === 'variable') {
                            $variant = $record->productVariants->first();
                            if ($variant?->variantPrimaryImage?->image_path) {
                                return asset('storage/' . $variant->variantPrimaryImage->image_path);
                            }
                        }

                        //Simple product → product primary image
                        if ($record->primaryImage) {

                            return asset('storage/' . ($record->primaryImage->image_path));
                        } else {
                            return asset('storage/No_Image_Available.jpg');
                        }

                    })
                    ->square(),
                        
                TextColumn::make('purchase_price')
                    ->label(__('filament/admin/product_resource.purchase_price'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('selling_price')
                    ->label(__('filament/admin/product_resource.selling_price'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('qty')
                    ->label(__('filament/admin/product_resource.qty'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_price')
                    ->label(__('filament/admin/product_resource.discount_price'))
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label(__('filament/admin/product_resource.is_active'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('filament/admin/product_resource.is_featured'))
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

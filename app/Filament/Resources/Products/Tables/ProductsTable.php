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
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament/admin/product_resource.name'))
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label(__('filament/admin/product_resource.category_id'))
                    ->sortable(),
                TextColumn::make('sku')
                    ->label(__('filament/admin/product_resource.sku'))
                    ->searchable(),


                ImageColumn::make('primary_image')
                    ->label(__('filament/admin/product_resource.primary_image'))
                    ->disk('public')
                    ->getStateUsing(function ($record) {

    $imagePath = null;

    if ($record->type === 'variable') {
        $variant = $record->productVariants->first();
        $imagePath = $variant?->variantPrimaryImage?->image_path;
    } else {
        $imagePath = $record->primaryImage?->image_path;
    }

    if (!$imagePath) {
        return asset('storage/No_Image_Available.jpg');
    }

    // External URL
    if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
        return $imagePath;
    }

    // Local storage path
    return asset('storage/' . ltrim($imagePath, '/'));
})
                    ->square(),
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

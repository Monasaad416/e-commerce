<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Filament\Infolists\Infolist;
use Filament\Schemas\Components\Grid;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }


    public static function infolist(Schema $schema): Schema
    {


        return $schema
            ->schema([
            /* ===============================
            |  Product Info
            =============================== */
            Section::make(__('Product Information'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            // First Column - Basic Info
                            Grid::make(1)
                                ->schema([
                                    TextEntry::make('name')
                                        ->label(__('Product Name'))
                                        ->weight('bold')

                                        ->columnSpanFull(),
                                        
                                    TextEntry::make('description')
                                        ->label('')
                                        ->columnSpanFull()
                                        ->markdown()
                                        ->prose(),
                                        
                                    TextEntry::make('sku')
                                        ->label(__('SKU'))
                                        ->badge()
                                        ->color('gray'),
                                ]),
                            
                            // Second Column - Pricing
                            Grid::make(1)
                                ->schema([
                                    TextEntry::make('purchase_price')
                                        ->label(__('Purchase Price'))
                                        ->money('USD')
                                        ->weight('bold')
                                        ->color('gray'),
                                        
                                    TextEntry::make('selling_price')
                                        ->label(__('Selling Price'))
                                        ->money('USD')
                                        ->weight('bold')
                                        ->color('success'),
                                        
                                    TextEntry::make('discount_price')
                                        ->label(__('Discount Price'))
                                        ->money('USD')
                                        ->color('danger'),
                                        
                
                                ]),
                            
                            // Third Column - Inventory & Status
                            Grid::make(1)
                                ->schema([
                                    TextEntry::make('quantity')
                                        ->label(__('Stock Quantity'))
                                        ->weight('bold')
                                        ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                                        ->formatStateUsing(fn ($state) => $state . ' in stock'),
                                        
                                    TextEntry::make('is_active')
                                        ->label(__('Status'))
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            '1' => 'success',
                                            '0' => 'danger',
                                            default => 'gray',
                                        })
                                        ->formatStateUsing(fn (string $state): string => $state ? __('general.active') : __('general.inactive')),
                                        
                                    TextEntry::make('created_at')
                                        ->label(__('Created At'))
                                        ->dateTime(),
                                        
                                    TextEntry::make('updated_at')
                                        ->label(__('Last Updated'))
                                        ->since(),
                                ]),
                        ])
                ])
                ->columnSpanFull()
                ->collapsible()
                ->collapsed(false),
                    
                // Image Gallery Section
                Section::make('Product Images')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        ViewEntry::make('images')
                            ->view('filament.infolists.components.image-gallery')
                            ->getStateUsing(function ($record) {
                                $record->loadMissing('images');
                                return [
                                    'images' => $record->images->map(fn ($image) => [
                                        'url' => Storage::url($image->image_path),
                                        'is_primary' => $image->is_primary ?? false,
                                    ])->toArray(),

                                    'debug' => true,
                                ];
                            })
                            ->columnSpanFull()
    

                    ])
            ]);
    }
    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view' => ViewProduct::route('/{record}'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    
    public static function getModelLabel(): string
    {
        return __('filament/admin/product_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/product_resource.plural_model_label');
    }





}

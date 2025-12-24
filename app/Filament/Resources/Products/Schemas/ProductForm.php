<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Filament\Forms\Components\FileUpload;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use App\Filament\Resources\Products\Schemas\AttributeOption;
use Filament\Forms\Components\Repeater;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make()
                ->schema([
                    Grid::make()
                        ->schema([
                        // Basic Info
                        Section::make(__('filament/admin/product_resource.basic_information'))
                        ->collapsible()
                        ->collapsed(false)
                            ->schema([
                                TextInput::make('name')
                                ->label(__('general.name'))
                                ->required()
                                ->translatableTabs(),

                                Grid::make(2)->schema([
                                Select::make('category_id')
                                    ->label(__('general.category'))
                                    ->relationship(
                                        name: 'category',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn (Builder $query) => $query->where('is_active', 1)
                                    )
                                    ->getOptionLabelUsing(fn ($value): ?string => 
                                        Category::find($value)?->getTranslation('name', app()->getLocale())
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label(__('general.name'))
                                            ->required()
                                            ->translatableTabs(),

                                        Textarea::make('description')
                                            ->label(__('general.description'))
                                            ->translatableTabs(),

                                        Select::make('parent_id')
                                            ->label(__('filament/admin/category_resource.main_category'))
                                            ->relationship(
                                                name: 'parent',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn (Builder $query) => $query->where('is_active', 1)
                                            )
                                            ->getOptionLabelUsing(fn ($value): ?string => 
                                                Category::find($value)?->getTranslation('name', app()->getLocale())
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->nullable(),

                                        Toggle::make('is_active')
                                            ->default(true),
                                    ])
                                    ->createOptionUsing(function (array $data) {
                                        return Category::create($data)->getKey();
                                    })
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label(__('general.name'))
                                            // ->required()
                                            ->translatableTabs(),

                                        Textarea::make('description')
                                            ->label(__('general.description'))
                                            ->translatableTabs(),

                                        Select::make('parent_id')
                                            ->label(__('filament/admin/category_resource.main_category'))
                                            ->relationship(
                                                name: 'parent',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn ($query) =>
                                                    $query->where('is_active', 1)
                                            )
                                            ->getOptionLabelUsing(fn ($record) =>
                                                $record ? $record->getTranslation('name', app()->getLocale()) : null
                                            )
                                            ->nullable()
                                            ->searchable()
                                            ->preload(),

                                        Toggle::make('is_active')->default(true),
                                    ])
                                    
                                    // ->createOptionUsing(function (array $data): int{
                                    //     $record = Category::create($data);
                                    //     return $record->getKey();
                                    // })

                                    ->editOptionForm([
                                                       TextInput::make('name')
                                            ->label(__('general.name'))
                                            // ->required()
                                            ->translatableTabs(),

                                        Textarea::make('description')
                                            ->label(__('general.description'))
                                            ->translatableTabs(),

                                        Select::make('parent_id')
                                            ->label(__('filament/admin/category_resource.main_category'))
                                            ->relationship(
                                                name: 'parent',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn ($query) =>
                                                    $query->whereNull('parent_id')->where('is_active', 1)
                                            )
                                            ->getOptionLabelUsing(fn ($record) =>
                                                $record ? $record->getTranslation('name', app()->getLocale()) : null
                                            )
                                            ->nullable()
                                            ->searchable()
                                            ->preload(),

                                        Toggle::make('is_active')->default(true),
                                    ]),

                                    TextInput::make('sku')
                                            ->label(__('filament/admin/product_resource.sku')),
                                    ]),

                                    Textarea::make('description')
                                    ->label(__('filament/admin/product_resource.description'))
                                    
                                        // ->required()
                                        ->translatableTabs(),

                                    Textarea::make('short_description')
                                        ->label(__('filament/admin/product_resource.short_description'))
                                        // ->required()
                                        ->translatableTabs(),
                                    ])->columnSpanFull(),
                                    
                        ])->columnSpanFull(), 

                        Section::make(__('filament/admin/product_resource.tags'))
                        ->collapsible()
                        ->collapsed(false)
                        ->schema([
                        Select::make('tag_id')
                            ->label(__('filament/admin/product_resource.tags'))
                            ->relationship(
                                name: 'tags',
                                titleAttribute: 'name',
                            )
                            ->getOptionLabelUsing(fn ($value): ?string => 
                                Tag::find($value)?->getTranslation('name', app()->getLocale())
                            )
                            ->searchable()
                            ->preload()
                            // ->required()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(__('general.name'))
                                    // ->required()        
                                    ->translatableTabs(),

                            ])
                            ->createOptionUsing(function (array $data) {
                                return Tag::create($data)->getKey();
                            })
                            ->editOptionForm([
                                TextInput::make('name')
                                    ->label(__('general.name'))
                                    // ->required()
                                    ->translatableTabs(),
                            ])
                             ->columnSpanFull(),
                        ])->columnSpanFull(),
                    ]),

                        
                    Grid::make()
                        ->schema([                                 
                            Section::make(__('filament/admin/product_resource.quantity_and_price'))
                                ->collapsible()
                                ->collapsed(false)
                                ->schema([
                                    Grid::make(4)->schema([
                                        TextInput::make('purchase_price')->numeric()->required()->label(__('filament/admin/product_resource.purchase_price')),
                                        TextInput::make('selling_price')->numeric()->required()->label(__('filament/admin/product_resource.selling_price')),
                                        TextInput::make('qty')->numeric()->default(0)->label(__('filament/admin/product_resource.qty')),
                                        TextInput::make('discount_price')->numeric()->label(__('filament/admin/product_resource.discount_price')),
                                    ]),
                                ])
                                ->columnSpanFull(),
                                
                            Section::make(__('filament/admin/product_resource.extra_info'))
                                ->collapsible()
                                ->collapsed(false)
                                ->schema([
                                    FileUpload::make('thumbnail')
                                        ->columnSpanFull()
                                        ->label(__('filament/admin/product_resource.thumbnail'))
                                        ->disk('public')
                                        ->directory('products')
                                        ->maxSize(2048),
                                        FileUpload::make('images')
                                            ->multiple()
                                            ->columnSpanFull()
                                            ->label(__('filament/admin/product_resource.image_gallery'))
                                            ->disk('public')
                                            ->directory('products')
                                            ->image()
                                            ->maxSize(10240) // 10MB in KB
                                            ->minSize(1) // 1KB
                                            ->acceptedFileTypes(['image/*'])
                                            ->saveRelationshipsUsing(function (Product $record, $state) {
                                                if (!is_array($state)) {
                                                    return;
                                                }
                                                
                                                // Add new images
                                                foreach ($state as $image) {
                                                    if (is_string($image) && Storage::disk('public')->exists($image)) {
                                                        $record->images()->create([
                                                            'image_path' => $image,
                                                            'is_primary' => $record->images()->count() === 0
                                                        ]);
                                                    }
                                                }
                                            })
                                            ->reorderable()
                                            ->appendFiles()
                                            ->downloadable()
                                            ->openable()
                                            ->preserveFilenames()
                                            ->imageEditor()
                                            ->imageCropAspectRatio('1:1')
                                            ->imageResizeTargetWidth('800')
                                            ->imageResizeTargetHeight('800')
                                            ->reorderable()
                                            ->appendFiles()
                                            ->downloadable()
                                            ->openable()
                                            ->preserveFilenames()
                                            ->imageEditor()
                                            ->imageCropAspectRatio('1:1')
                                            ->imageResizeTargetWidth('800')
                                            ->imageResizeTargetHeight('800'),

                                    TagsInput::make('meta_keywords')
                                            
                                        ->columnSpanFull(),
                                        
                                    TagsInput::make('meta_description')
                                        ->columnSpanFull(),

                                    Grid::make(2)->schema([
                                        Toggle::make('is_active')
                                            ->default(true),
                                            
                                        Toggle::make('is_featured')
                                            ->default(false),
                                    ])
                                    ->columns(2),
                                ])
                                ->columnSpanFull(),

                            Section::make(__('filament/admin/product_resource.attributes'))
                                ->collapsible()
                                ->collapsed(false)
                                ->schema([
       
                            Repeater::make('productAttributeValues')
                                ->relationship('productAttributeValues') 
                                ->label(__('Attributes'))
                                ->schema([
                                    Select::make('attribute_id')
                                        ->label(__('Attribute'))
                                        ->relationship('attribute', 'name')
                                        ->getOptionLabelUsing(fn ($value) =>
                                            Attribute::find($value)?->getTranslation('name', app()->getLocale())
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->reactive()
                                        ->afterStateUpdated(fn (callable $set) => $set('attribute_value_id', null))
                                        ->required()
                                        ,

                                    Select::make('attribute_value_id')
                                        ->label(__('Value'))
                                        ->options(function (callable $get, $context) {
                                            $attributeId = $get('attribute_id');
                                            if (!$attributeId) {
                                                return [];
                                            }
                                            return AttributeValue::where('attribute_id', $attributeId)
                                                ->pluck('value', 'id');
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                ])
                                ->columnSpanFull()
                                ->addActionLabel(__('Add Attribute'))
                                ->reorderable(false)   ->reorderable(false),

                                ])->columnSpanFull()


                        ])


                        
                    ->columnSpan(1),
        ]);
    }

}
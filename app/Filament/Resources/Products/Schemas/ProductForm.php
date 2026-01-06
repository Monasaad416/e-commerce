<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Tag;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Wizard;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(1)
                ->schema([
                Wizard::make([
                    Step::make(__('filament/admin/product_resource.basic_information'))
                        ->completedIcon(Heroicon::HandThumbUp)
                        ->icon(Heroicon::InformationCircle)
                        ->schema([
                            TextInput::make('name')
                            ->label(__('general.name'))
                            ->required()
                            ->translatableTabs(),

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




                                Grid::make(2)->schema([
                                    Textarea::make('description')
                                    ->label(__('filament/admin/product_resource.description'))
                                    ->rows(4)

                                        // ->required()
                                        ->translatableTabs(),

                                    Textarea::make('short_description')
                                        ->rows(3)
                                        ->label(__('filament/admin/product_resource.short_description'))
                                        // ->required()
                                        ->translatableTabs(),
                                ])->columnSpanFull(),

                            ]),


                    Step::make(__('filament/admin/product_resource.tags_and_attributes'))
                        ->completedIcon(Heroicon::HandThumbUp)
                        ->icon(Heroicon::Tag)
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('tag_id')
                                        ->multiple()
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
                                        ]),

                                        Select::make('type')
                                            ->label(__('filament/admin/product_resource.type'))
                                            ->options([
                                                'simple' => __('filament/admin/product_resource.simple'),
                                                'variable' => __('filament/admin/product_resource.variable'),
                                            ])
                                            ->default('simple')
                                            ->reactive()
                                       ->afterStateUpdated(function ($state, callable $set) {
                                            if ($state === 'simple') {
                                                $set('productVariants', []);
                                            }
                                        })

                                    ]),

                                // Show simple product fields when type is simple
                                Section::make(__('filament/admin/product_resource.quantity_and_price'))
                                    ->schema([
                                        Grid::make(5)
                                            ->schema([
                                                TextInput::make('qty')
                                                    ->label(__('filament/admin/product_resource.qty'))
                                                    ->required()
                                                    ->hidden(fn (callable $get) => $get('type') === 'variable'),
                                                TextInput::make('purchase_price')
                                                    ->label(__('filament/admin/product_resource.purchase_price'))
                                                    ->required()
                                                    ->hidden(fn (callable $get) => $get('type') === 'variable'),
                                                TextInput::make('selling_price')
                                                    ->label(__('filament/admin/product_resource.selling_price'))
                                                    ->required()
                                                    ->hidden(fn (callable $get) => $get('type') === 'variable'),
                                                TextInput::make('discount_price')
                                                    ->label(__('filament/admin/product_resource.discount_price'))
                                                    ->required()
                                                    ->hidden(fn (callable $get) => $get('type') === 'variable'),
                                                TextInput::make('sku')
                                                    ->label(__('filament/admin/product_resource.sku'))
                                                    ->required()
                                                    ->hidden(fn (callable $get) => $get('type') === 'variable'),
                                        ]),
                                    ])->hidden(fn (callable $get) => $get('type') === 'variable'),

                                    Section::make(__('filament/admin/product_resource.images'))
                                        ->schema([
                                            Grid::make(2)
                                                ->schema([
                                                    // For primary image
                                                    FileUpload::make('primaryImage')
                                                        ->label(__('Primary Image'))
                                                        ->image()
                                                        ->disk('public')
                                                        ->directory('products')
                                                        ->required()
                                                        ->maxSize(10240),

                                                    FileUpload::make('galleryImages')
                                                        ->label(__('Gallery'))
                                                        ->image()
                                                        ->multiple()
                                                        ->disk('public')
                                                        ->directory('products')
                                                        ->maxSize(10240),


                                                    ])
                                                ])->hidden(fn (callable $get) => $get('type') === 'variable'),


                                                        Grid::make(2)
                                                            ->schema([
                                                                Toggle::make('is_active')
                                                                ->label(__('filament/admin/product_resource.is_active'))
                                                                ->default(true)
                                                                ->hidden(fn (callable $get) => $get('type') === 'variable'),
                                                                Toggle::make('is_featured')
                                                                ->label(__('filament/admin/product_resource.is_featured'))
                                                                ->default(false)
                                                                ->hidden(fn (callable $get) => $get('type') === 'variable'),
                                                            ]),
                                                                // Show attributes and variant prices for variable products
                                                                Repeater::make('productVariants')
                                                                // ->relationship()
                                                                    ->label(__('filament/admin/product_resource.variants'))
                                                                    ->hidden(fn (callable $get) => $get('type') !== 'variable')
                                                                    ->schema([
                                                                        Repeater::make('productAttributes')
                                                                            ->label(__('filament/admin/product_resource.attributes'))
                                                                            ->schema([
                                                                                Select::make('attribute_id')
                                                                                    ->label(__('filament/admin/product_resource.attribute'))
                                                                                    ->options(Attribute::pluck('name', 'id'))
                                                                                    ->reactive()
                                                                                    ->afterStateUpdated(fn (callable $set) => $set('attribute_value_id', null))
                                                                                    ->required(),

                                                                                Select::make('attribute_value_id')
                                                                                    ->label(__('filament/admin/product_resource.value'))
                                                                                    ->options(fn (callable $get) =>
                                                                                        AttributeValue::where('attribute_id', $get('attribute_id'))
                                                                                            ->pluck('value', 'id')
                                                                                    )
                                                                                    ->required(),
                                                                            ])
                                                                            ->minItems(1)
                                                                            ->columns(2),
                                                                            Section::make(__('filament/admin/product_resource.quantity_and_price'))
                                                                                ->schema([
                                                                                    Grid::make(5)->schema([
                                                                                        TextInput::make('qty')
                                                                                        ->label(__('filament/admin/product_resource.qty'))
                                                                                        ->numeric()->required(),
                                                                                        TextInput::make('purchase_price')
                                                                                        ->label(__('filament/admin/product_resource.purchase_price'))
                                                                                        ->numeric()->required(),
                                                                                        TextInput::make('selling_price')
                                                                                        ->label(__('filament/admin/product_resource.selling_price'))
                                                                                        ->numeric()->required(),
                                                                                        TextInput::make('discount_price')
                                                                                        ->label(__('filament/admin/product_resource.discount_price'))
                                                                                        ->numeric(),
                                                                                        TextInput::make('sku')
                                                                                        ->label(__('filament/admin/product_resource.sku'))
                                                                                        ->required(),
                                                                                    ]),

                                                                                ]),

                                                                            Grid::make(2)
                                                                                ->schema([
                                                                                Toggle::make('is_active')
                                                                                ->label(__('filament/admin/product_resource.is_active'))
                                                                                ->default(true),
                                                                                    Toggle::make('is_featured')
                                                                                    ->label(__('filament/admin/product_resource.is_featured'))
                                                                                    ->default(false),
                                                                                ]),
                                                                            Section::make(__('filament/admin/product_resource.images'))
                                                                                ->schema([
                                                                                    Grid::make(2)->schema([
                                                                                        FileUpload::make('variantPrimaryImage')
                                                                                            ->label(__('Primary Image'))
                                                                                            ->image()
                                                                                            ->disk('public')
                                                                                            ->directory('variants')
                                                                                            ->required()
                                                                                            ->maxSize(10240),
                                                                                        FileUpload::make('variantImages')
                                                                                            ->label(__('Gallery'))
                                                                                            ->image()
                                                                                            ->multiple()
                                                                                            ->disk('public')
                                                                                            ->directory('variants')
                                                                                            ->maxSize(10240),
                                                                                    ])
                                                                                ])
                                                                    ])
                                                                    ->columnSpanFull()
                                                                    ->reorderable(false)
                                                                    ->addActionLabel(__('Add Variant')),
                                                            ])
                    ])->startOnStep(2)
                    ->submitAction(new HtmlString('<button class="btn btn-primary" type="submit">Submit</button>')),
            ])->columnSpan('full')
        ]);
    }
}
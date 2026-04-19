<?php

namespace App\Filament\Resources\HomePageContents\Schemas;
use Filament\Schemas\Schema;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class HomePageContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('page')
                ->default('home')
                ->hidden()
                ->required(),
            Tabs::make('Tabs')
                ->tabs([
                    Tab::make(__('filament/admin/page_content_resource.first_banner'))
                        ->schema([
                            Section::make(__('filament/admin/page_content_resource.main_banner_section'))
                                ->description(__('filament/admin/page_content_resource.front_banner_simulation'))
                                ->schema([
                                    Grid::make(12)->schema([
                                        Section::make(__('filament/admin/page_content_resource.content_side'))
                                            ->description(__('filament/admin/page_content_resource.content_side_hint'))
                                            ->columnSpan(7)
                                            ->schema([
                                                TextInput::make('content.main_banner_eyebrow')
                                                    ->label(__('filament/admin/page_content_resource.eyebrow'))
                                                    ->helperText(__('filament/admin/page_content_resource.eyebrow_hint'))
                                                    ->translatableTabs(),
                                                Textarea::make('content.main_banner_title')
                                                    ->label(__('filament/admin/page_content_resource.title'))
                                                    ->rows(3)
                                                    ->required()
                                                    ->translatableTabs(),
                                                Textarea::make('content.main_banner_subtitle')
                                                    ->label(__('filament/admin/page_content_resource.subtitle'))
                                                    ->rows(4)
                                                    ->required()
                                                    ->translatableTabs(),
                                                Grid::make(2)->schema([
                                                    TextInput::make('content.main_banner_button_text')
                                                        ->label(__('filament/admin/page_content_resource.button_text'))
                                                        ->required()
                                                        ->translatableTabs(),
                                                    TextInput::make('content.main_banner_button_link')
                                                        ->label(__('filament/admin/page_content_resource.button_link'))
                                                        ->required(),
                                                ]),
                                            ]),

                                        Section::make(__('filament/admin/page_content_resource.image_side'))
                                            ->description(__('filament/admin/page_content_resource.image_side_hint'))
                                            ->columnSpan(5)
                                            ->schema([
                                                FileUpload::make('content.main_banner_image')
                                                    ->label(fn() => app()->getLocale() === 'ar' ? 'الصورة' : 'Image')
                                                    ->disk('public')
                                                    ->directory('home-page')
                                                    ->image()
                                                    ->imageEditor()
                                                    ->helperText(__('filament/admin/page_content_resource.image_side_helper')),
                                            ]),

                                        Section::make(__('filament/admin/page_content_resource.trust_points'))
                                            ->description(__('filament/admin/page_content_resource.trust_points_hint'))
                                            ->columnSpan(7)
                                            ->schema([
                                                Repeater::make('content.main_banner_trust_points')
                                                    ->label(__('filament/admin/page_content_resource.trust_points'))
                                                    ->schema([
                                                        TextInput::make('text')
                                                            ->label(__('filament/admin/page_content_resource.label'))
                                                            ->required()
                                                            ->translatableTabs(),
                                                    ])
                                                    ->defaultItems(3)
                                                    ->reorderable(false)
                                                    ->addActionLabel(__('filament/admin/page_content_resource.add_trust_point')),
                                            ]),

                                        Section::make(__('filament/admin/page_content_resource.stats'))
                                            ->description(__('filament/admin/page_content_resource.stats_hint'))
                                            ->columnSpan(5)
                                            ->schema([
                                                Repeater::make('content.main_banner_stats')
                                                    ->label(__('filament/admin/page_content_resource.stats'))
                                                    ->schema([
                                                        TextInput::make('value')
                                                            ->label(__('filament/admin/page_content_resource.value'))
                                                            ->required(),
                                                        TextInput::make('label')
                                                            ->label(__('filament/admin/page_content_resource.label'))
                                                            ->required()
                                                            ->translatableTabs(),
                                                    ])
                                                    ->columns(2)
                                                    ->defaultItems(3)
                                                    ->reorderable(false)
                                                    ->addActionLabel(__('filament/admin/page_content_resource.add_stat')),
                                            ]),
                                    ]),
                                ]),
                        ]),
                    Tab::make(__('filament/admin/page_content_resource.second_banner'))
                        ->schema([
                            Grid::make(12)->schema([

                                // first half
                                Section::make(__('filament/admin/page_content_resource.first_half'))
                                    ->columnSpan(6)
                                    ->schema([

                                        Section::make(__('filament/admin/page_content_resource.part1'))
                                            ->schema([
                                                Textarea::make('content.sec_banner_first_half_part1_title')
                                                    ->label(__('filament/admin/page_content_resource.title'))
                                                    ->required()
                                                    ->translatableTabs(),
                                                Textarea::make('content.sec_banner_first_half_part1_subtitle')
                                                    ->label(__('filament/admin/page_content_resource.subtitle'))
                                                    ->required()
                                                    ->translatableTabs(),
                                                FileUpload::make('content.sec_banner_first_half_part1_image')
                                                    ->label(fn() => app()->getLocale() === 'ar' ? 'الصورة' : 'Image')
                                                    ->disk('public')
                                                    ->directory('home-page'),
                                            ]),

                                        Grid::make(12)->schema([
                                            Section::make(__('filament/admin/page_content_resource.part2'))
                                                ->columnSpan(6)
                                                ->schema([
                                                Textarea::make('content.sec_banner_first_half_part2_title')
                                                    ->label(__('filament/admin/page_content_resource.title'))
                                                    ->required()
                                                    ->translatableTabs(),
                                                Textarea::make('content.sec_banner_first_half_part2_subtitle')
                                                    ->label(__('filament/admin/page_content_resource.subtitle'))
                                                    ->required()
                                                    ->translatableTabs(),
                                                FileUpload::make('content.sec_banner_first_half_part2_image')
                                                    ->label(fn() => app()->getLocale() === 'ar' ? 'الصورة' : 'Image')
                                                    ->disk('public')
                                                    ->directory('home-page'),
                                                ]),

                                            Section::make(__('filament/admin/page_content_resource.part3'))
                                                ->columnSpan(6)
                                                ->schema([
                                                              Textarea::make('content.sec_banner_first_half_part3_title')
                                                    ->label(__('filament/admin/page_content_resource.title'))
                                                    ->required()
                                                    ->translatableTabs(),
                                                Textarea::make('content.sec_banner_first_half_part3_subtitle')
                                                    ->label(__('filament/admin/page_content_resource.subtitle'))
                                                    ->required()
                                                    ->translatableTabs(),
                                                FileUpload::make('content.sec_banner_first_half_part3_image')
                                                    ->label(fn() => app()->getLocale() === 'ar' ? 'الصورة' : 'Image')
                                                    ->disk('public')
                                                    ->directory('home-page'),
                                                ]),
                                        ]),
                                    ]),

                                // sechalf
                                Section::make(__('filament/admin/page_content_resource.sec_half'))
                                    ->columnSpan(6)
                                    ->schema([
                                        Textarea::make('content.sec_banner_sec_half_title')
                                            ->label(__('filament/admin/page_content_resource.title'))
                                            ->required()
                                            ->translatableTabs(),
                                        Textarea::make('content.sec_banner_sec_half_subtitle')
                                            ->label(__('filament/admin/page_content_resource.subtitle'))
                                            ->required()
                                            ->translatableTabs(),
                                        FileUpload::make('content.sec_banner_sec_half_image')
                                            ->label(fn() => app()->getLocale() === 'ar' ? 'الصورة' : 'Image')
                                            ->disk('public')
                                            ->directory('home-page'),
                                        TextInput::make('content.sec_banner_sec_half_button_link')
                                            ->label(__('filament/admin/page_content_resource.button_link'))
                                            ->required(),
                                        TextInput::make('content.sec_banner_sec_half_button_text')
                                            ->label(__('filament/admin/page_content_resource.button_link'))
                                            ->required()->translatableTabs(),


                                    ]),
                            ])
                        ]),
                ])->columnSpanFull()
   
        ]);
    }
}


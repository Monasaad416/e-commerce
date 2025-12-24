<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
        Section::make(__('filament/admin/category_resource.category_info'))
            ->icon('heroicon-o-list-bullet')
            ->schema([

                Section::make()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                ImageEntry::make('img')
                                    ->label(__('general.image'))
                                    ->imageSize(120)
                                    ->getStateUsing(fn ($record) =>
                                        $record->img
                                            ? asset('storage/' . $record->img)
                                            : asset('storage/No_Image_Available.jpg')
                                    )
                                    ->columnSpan(2),

                                ImageEntry::make('thumbnail')
                                    ->label(__('general.thumbnail'))
                                    ->imageHeight(80)
                                    ->getStateUsing(fn ($record) =>
                                        $record->thumbnail
                                            ? asset('storage/' . $record->thumbnail)
                                            : asset('storage/No_Image_Available.jpg')
                                    ),
                            ]),
                    ])
                    ->extraAttributes(['class' => 'pb-6 border-b']),

                Section::make()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')
                                ->label(__('general.name'))
                                ->weight('bold'),
                                TextEntry::make('parent.name')
                                ->label(__('filament/admin/category_resource.main_category'))
                                ->placeholder('—'),
                                IconEntry::make('is_active')
                                ->label(__('general.status'))
                                ->boolean(),
                            ]),
                    ])
                    ->extraAttributes(['class' => 'pt-6']),

            ])->columnSpanFull(),

        ]);
    }
}

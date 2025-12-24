<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(fn () => app()->getLocale() === 'ar' ? 'الاسم' : 'Name')
                    ->hint( '*' )
                    ->translatableTabs(),
                Textarea::make('description')
                    ->label(fn () => app()->getLocale() === 'ar' ? 'الوصف' : 'Description')
                    ->hint( '*' )
                    ->translatableTabs(),

                FileUpload::make('img')
                ->label(fn () => app()->getLocale() === 'ar' ? 'الصورة' : 'Image'),
                FileUpload::make('thumbnail')
                ->label(fn () => app()->getLocale() === 'ar' ? 'الصورة المصغرة' : 'Thumbnail'),
                TagsInput::make('meta_keywords')
                ->label(fn () => app()->getLocale() === 'ar' ? 'الكلمات المفتاحية' : 'Meta Keywords'),
                TagsInput::make('meta_description')
                ->label(fn () => app()->getLocale() === 'ar' ? 'الوصف' : 'Description'),
                Select::make('parent_id')
                    ->label(__('filament/admin/category_resource.main_category'))
                    ->options(
                        \App\Models\Category::where('parent_id', null)->pluck('name', 'id')->toArray()
                    )
                    ->relationship('parent', 'name',function(Builder $query)  {
                        $query->where('is_active', 1);
                    })
                    ->createOptionModalHeading(__('filament/admin/category_resource.create'))
                    ->createOptionForm([
                         TextInput::make('name')
                        ->label(fn () => app()->getLocale() === 'ar' ? 'الاسم' : 'Name')
                        ->hint( '*' )
                        ->translatableTabs(),
                            Textarea::make('description')
                                ->label(fn () => app()->getLocale() === 'ar' ? 'الوصف' : 'Description')
                                ->hint( '*' )
                                ->translatableTabs(),

                            FileUpload::make('img')
                            ->label(fn () => app()->getLocale() === 'ar' ? 'الصورة' : 'Image'),
                            FileUpload::make('thumbnail')
                            ->label(fn () => app()->getLocale() === 'ar' ? 'الصورة المصغرة' : 'Thumbnail'),
                            TagsInput::make('meta_keywords')
                            ->label(fn () => app()->getLocale() === 'ar' ? 'الكلمات المفتاحية' : 'Meta Keywords'),
                            TagsInput::make('meta_description')
                            ->label(fn () => app()->getLocale() === 'ar' ? 'الوصف' : 'Description'),
                            Select::make('parent_id')
                                ->label(__('filament/admin/category_resource.main_category'))
                                ->options(
                                    \App\Models\Category::where('parent_id', null)->pluck('name', 'id')->toArray()
                                )
                                ->relationship('parent', 'name',function(Builder $query)  {
                                    $query->where('is_active', 1);
                                })
                
                                ->helperText(__('filament/admin/category_resource.select_main_category'))
                                ->searchable(['name'])
                                ->preload(),
                            Toggle::make('is_active')->default(true)
                    ])

                    ->editOptionForm([
                            TextInput::make('name')
                            ->label(fn () => app()->getLocale() === 'ar' ? 'الاسم' : 'Name')
                            ->hint( '*' )
                            ->translatableTabs(),
                                Textarea::make('description')
                                    ->label(fn () => app()->getLocale() === 'ar' ? 'الوصف' : 'Description')
                                    ->hint( '*' )
                                    ->translatableTabs(),

                                FileUpload::make('img')
                                ->label(fn () => app()->getLocale() === 'ar' ? 'الصورة' : 'Image'),
                                FileUpload::make('thumbnail')
                                ->label(fn () => app()->getLocale() === 'ar' ? 'الصورة المصغرة' : 'Thumbnail'),
                                TagsInput::make('meta_keywords')
                                ->label(fn () => app()->getLocale() === 'ar' ? 'الكلمات المفتاحية' : 'Meta Keywords'),
                                TagsInput::make('meta_description')
                                ->label(fn () => app()->getLocale() === 'ar' ? 'الوصف' : 'Description'),
                                Select::make('parent_id')
                                    ->label(__('filament/admin/category_resource.main_category'))
                                    ->options(
                                        \App\Models\Category::where('parent_id', null)->pluck('name', 'id')->toArray()
                                    )
                                    ->relationship('parent', 'name',function(Builder $query)  {
                                        $query->where('is_active', 1);
                                    })
                    
                                    ->helperText(__('filament/admin/category_resource.select_main_category'))
                                    ->searchable(['name'])
                                    ->preload(),
                                Toggle::make('is_active')->default(true)
                    ])
                    ->helperText(__('filament/admin/category_resource.select_main_category'))
                    ->searchable(['name'])
                    ->preload(),
                Toggle::make('is_active')
                ->label(fn () => app()->getLocale() === 'ar' ? 'الحالة' : 'Status')
                    ->required()->inline(false)->default(true),
            ]);
    }
}

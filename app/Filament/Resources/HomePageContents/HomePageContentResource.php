<?php

namespace App\Filament\Resources\HomePageContents;

use App\Filament\Resources\HomePageContents\Pages\CreateHomePageContent;
use App\Filament\Resources\HomePageContents\Pages\EditHomePageContent;
use App\Filament\Resources\HomePageContents\Pages\ListHomePageContents;
use App\Filament\Resources\HomePageContents\Pages\ViewHomePageContent;
use App\Filament\Resources\HomePageContents\Schemas\HomePageContentForm;
use App\Filament\Resources\HomePageContents\Schemas\HomePageContentInfolist;
use App\Filament\Resources\HomePageContents\Tables\HomePageContentsTable;
use App\Models\PageContent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HomePageContentResource extends Resource
{
    protected static ?string $model = PageContent::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'page';

    public static function getNavigationGroup(): ?string
    {
        return __('general.pages_content');
    }


    public static function form(Schema $schema): Schema
    {
        return HomePageContentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HomePageContentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HomePageContentsTable::configure($table);
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
            'index' => ListHomePageContents::route('/'),
            'create' => CreateHomePageContent::route('/create'),
            'view' => ViewHomePageContent::route('/{record}'),
            'edit' => EditHomePageContent::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament/admin/page_content_resource.home_page_model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/page_content_resource.home_page_model_label');
    }



    public static function getEloquentQuery(): Builder
    {
        if (!PageContent::where('page', 'home')->exists()) {
            PageContent::create([
                'page' => 'home',
                'content' => [],
            ]);
        }

        return parent::getEloquentQuery()->where('page', 'home');
    }

}

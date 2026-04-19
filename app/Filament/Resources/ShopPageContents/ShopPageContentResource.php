<?php

namespace App\Filament\Resources\ShopPageContents;

use App\Filament\Resources\ShopPageContents\Pages\CreateShopPageContent;
use App\Filament\Resources\ShopPageContents\Pages\EditShopPageContent;
use App\Filament\Resources\ShopPageContents\Pages\ListShopPageContents;
use App\Filament\Resources\ShopPageContents\Pages\ViewShopPageContent;
use App\Filament\Resources\ShopPageContents\Schemas\ShopPageContentForm;
use App\Filament\Resources\ShopPageContents\Schemas\ShopPageContentInfolist;
use App\Filament\Resources\ShopPageContents\Tables\ShopPageContentsTable;
use App\Models\PageContent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShopPageContentResource extends Resource
{
    protected static ?string $model = PageContent::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    public static function getNavigationGroup(): ?string
    {
        return __('general.pages_content');
    }
    protected static ?string $recordTitleAttribute = 'page';

    public static function form(Schema $schema): Schema
    {
        return ShopPageContentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ShopPageContentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShopPageContentsTable::configure($table);
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
            'index' => ListShopPageContents::route('/'),
            'create' => CreateShopPageContent::route('/create'),
            'view' => ViewShopPageContent::route('/{record}'),
            'edit' => EditShopPageContent::route('/{record}/edit'),
        ];
    }


    public static function getModelLabel(): string
    {
        return __('filament/admin/page_content_resource.shop_page_model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/page_content_resource.shop_page_model_label');
    }
}

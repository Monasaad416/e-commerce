<?php

namespace App\Filament\Resources\Languages;

use App\Filament\Resources\Languages\Pages\CreateLanguage;
use App\Filament\Resources\Languages\Pages\EditLanguage;
use App\Filament\Resources\Languages\Pages\ListLanguages;
use App\Filament\Resources\Languages\Pages\ViewLanguage;
use App\Filament\Resources\Languages\Schemas\LanguageForm;
use App\Filament\Resources\Languages\Schemas\LanguageInfolist;
use App\Filament\Resources\Languages\Tables\LanguagesTable;
use App\Models\Language;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LanguageResource extends Resource
{
    protected static ?string $model = Language::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return LanguageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LanguageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LanguagesTable::configure($table);
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
            'index' => ListLanguages::route('/'),
            // 'create' => CreateLanguage::route('/create'),
            // 'view' => ViewLanguage::route('/{record}'),
            // 'edit' => EditLanguage::route('/{record}/edit'),
        ];
    }


    public static function getModelLabel(): string
    {
        return __('filament/admin/language_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/language_resource.plural_model_label');
    }
}

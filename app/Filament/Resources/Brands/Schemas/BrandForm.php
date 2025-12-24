<?php

namespace App\Filament\Resources\Brands\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(fn () => app()->getLocale() === 'ar' ? 'الاسم' : 'Name')
                    ->required()
                    ->string()
                    ->maxLength(255)
                    ->columnSpan('full')
                    ->validationMessages([
                        'required' => app()->getLocale() === 'ar' ? 'حقل الاسم مطلوب' : 'The name field is required.',
                        'string' => app()->getLocale() === 'ar' ? 'يجب أن يكون الاسم نصيًا' : 'The name must be a string.',
                        'max' => [
                            'string' => app()->getLocale() === 'ar' 
                                ? 'يجب ألا يزيد الاسم عن :max حرف' 
                                : 'The name must not be greater than :max characters.'
                        ]
                    ])
                    ->translatableTabs()
                    ->columnSpan('full'),
            ]);
    }
}

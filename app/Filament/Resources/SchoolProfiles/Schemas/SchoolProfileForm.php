<?php

namespace App\Filament\Resources\SchoolProfiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SchoolProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('short_name'),
                TextInput::make('npsn'),
                TextInput::make('nss'),
                Select::make('school_type')
                    ->options(['SMA' => 'S m a', 'MA' => 'M a', 'SMK' => 'S m k'])
                    ->default('SMK')
                    ->required(),
                Select::make('accreditation')
                    ->options(['A' => 'A', 'B' => 'B', 'C' => 'C', 'not_accredited' => 'Not accredited']),
                Select::make('school_category')
                    ->options(['negeri' => 'Negeri', 'swasta' => 'Swasta'])
                    ->default('negeri')
                    ->required(),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('province'),
                TextInput::make('city'),
                TextInput::make('district'),
                TextInput::make('village'),
                TextInput::make('postal_code'),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('website')
                    ->url(),
                TextInput::make('logo'),
                TextInput::make('favicon'),
                TextInput::make('letterhead'),
                TextInput::make('established_year'),
                Select::make('curriculum')
                    ->options(['merdeka' => 'Merdeka', 'k13' => 'K13'])
                    ->default('merdeka')
                    ->required(),
                TextInput::make('academic_year_start_month')
                    ->required()
                    ->numeric()
                    ->default(7),
                TextInput::make('timezone')
                    ->required()
                    ->default('Asia/Jakarta'),
            ]);
    }
}

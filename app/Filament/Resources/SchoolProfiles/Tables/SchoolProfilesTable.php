<?php

namespace App\Filament\Resources\SchoolProfiles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SchoolProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('short_name')
                    ->searchable(),
                TextColumn::make('npsn')
                    ->searchable(),
                TextColumn::make('nss')
                    ->searchable(),
                TextColumn::make('school_type')
                    ->badge(),
                TextColumn::make('accreditation')
                    ->badge(),
                TextColumn::make('school_category')
                    ->badge(),
                TextColumn::make('province')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('district')
                    ->searchable(),
                TextColumn::make('village')
                    ->searchable(),
                TextColumn::make('postal_code')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('website')
                    ->searchable(),
                TextColumn::make('logo')
                    ->searchable(),
                TextColumn::make('favicon')
                    ->searchable(),
                TextColumn::make('letterhead')
                    ->searchable(),
                TextColumn::make('established_year'),
                TextColumn::make('curriculum')
                    ->badge(),
                TextColumn::make('academic_year_start_month')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('timezone')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

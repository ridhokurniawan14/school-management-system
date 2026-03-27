<?php

namespace App\Filament\Resources\Concerns;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

trait HasDocumentsTab
{
    protected static function makeDocumentsTab(string $directory = 'documents/teachers'): Tab
    {
        return Tab::make('Berkas & Dokumen')
            ->icon('heroicon-o-paper-clip')
            ->schema([
                Section::make()
                    ->description('Upload berkas penting. Klik "+ Tambah Berkas" untuk menambah dokumen baru.')
                    ->schema([
                        Repeater::make('documents')
                            ->label('')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Berkas')
                                    ->placeholder('Ijazah Terakhir')
                                    ->helperText('Contoh: Ijazah SD, FC KTP Ortu, FC KK, Akta Lahir, dll.')
                                    ->required()
                                    ->maxLength(100),

                                FileUpload::make('file')
                                    ->label('File')
                                    ->helperText('Format: PDF, JPG, PNG. Maksimal 5MB per file.')
                                    ->disk('public')
                                    ->directory($directory)
                                    ->acceptedFileTypes([
                                        'application/pdf',
                                        'image/jpeg',
                                        'image/png',
                                    ])
                                    ->maxSize(5120)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->addActionLabel('+ Tambah Berkas')
                            ->reorderable(false)
                            ->defaultItems(0)
                            ->grid(1),
                    ]),
            ]);
    }
}

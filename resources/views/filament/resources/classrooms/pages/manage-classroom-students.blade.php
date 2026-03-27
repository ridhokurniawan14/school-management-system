<x-filament-panels::page>
    <x-filament-panels::header :heading="$this->getTitle()" :subheading="'Kelas: ' .
        $record->name .
        ' | Tahun Ajaran: ' .
        $record->academicYear?->name .
        ' | Kapasitas: ' .
        $record->capacity" :actions="$this->getCachedHeaderActions()" />

    {{ $this->table }}
</x-filament-panels::page>

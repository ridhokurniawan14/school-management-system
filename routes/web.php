<?php

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/download/template-siswa-kelas', function () {
    $headers = [
        'Content-Type'        => 'text/csv',
        'Content-Disposition' => 'attachment; filename="template-import-siswa-kelas.csv"',
    ];

    $content = "NIS\n2024001\n2024002\n2024003";

    return Response::make($content, 200, $headers);
})->middleware(['auth'])->name('download.template.siswa.kelas');

Route::get('/download/template-siswa-kelas-excel', function () {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Header
    $sheet->setCellValue('A1', 'NIS');

    // Contoh data
    $sheet->setCellValue('A2', '2024001');
    $sheet->setCellValue('A3', '2024002');
    $sheet->setCellValue('A4', '2024003');

    // Style header
    $sheet->getStyle('A1')->getFont()->setBold(true);
    $sheet->getColumnDimension('A')->setWidth(20);

    // Tambah catatan di kolom B
    $sheet->setCellValue('B1', 'CATATAN');
    $sheet->setCellValue('B2', 'Isi kolom NIS dengan NIS siswa yang terdaftar di sistem');
    $sheet->setCellValue('B3', 'Hapus baris contoh (2024001, dst) sebelum upload');
    $sheet->setCellValue('B4', 'Pastikan tidak ada spasi di awal/akhir NIS');
    $sheet->getColumnDimension('B')->setWidth(55);
    $sheet->getStyle('B2:B4')->getFont()->setColor(
        new \PhpOffice\PhpSpreadsheet\Style\Color('FF888888')
    );

    $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $filename = 'template-import-siswa-kelas.xlsx';
    $path     = storage_path('app/tmp/' . $filename);

    // Pastikan folder tmp ada
    if (!file_exists(storage_path('app/tmp'))) {
        mkdir(storage_path('app/tmp'), 0755, true);
    }

    $writer->save($path);

    return response()->download($path, $filename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ])->deleteFileAfterSend(true);
})->middleware(['auth'])->name('download.template.siswa.kelas.excel');

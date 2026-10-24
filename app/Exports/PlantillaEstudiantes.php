<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PlantillaEstudiantes implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        return [
            'codigo', 'nombres', 'apellidos', 'documento',
            'fecha_nacimiento', 'sexo', 'telefono', 'direccion','beca',
        ];
    }

    public function array(): array
    {
        return [[
            'E-0001', 'María José', 'Hernández López', '05095331-8',
            '2009-03-15', 'F', '7000-0000', 'Col. Centro, Usulután',,''
        ]];
    }

    public function styles(Worksheet $sheet): array
    {
        // Todo como texto, para que Excel no borre ceros ni cambie fechas
        $sheet->getStyle('A:I')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        return [1 => ['font' => ['bold' => true]]];
    }
}
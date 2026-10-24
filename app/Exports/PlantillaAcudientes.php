<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PlantillaAcudientes implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        return [
            'documento_acudiente', 'nombre_acudiente', 'email', 'telefono', 'direccion',
            'ocupacion', 'lugar_trabajo',
            'codigo_estudiante', 'documento_estudiante', 'parentesco', 'principal',
        ];
    }

    public function array(): array
    {
        // Ejemplo: una madre con dos hijos (dos filas, mismo documento_acudiente)
        return [
            ['05095331-8', 'Ana Lucía Martínez', 'ana.martinez@correo.com', '7000-1111', 'Col. Centro, Usulután',
             'Comerciante', 'Mercado Municipal', 'E-0001', '', 'Madre', 'si'],
            ['05095331-8', '', '', '', '', '', '', 'E-0002', '', 'Madre', 'no'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Todo como texto, para que Excel no borre ceros ni cambie formatos
        $sheet->getStyle('A:K')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        return [1 => ['font' => ['bold' => true]]];
    }
}
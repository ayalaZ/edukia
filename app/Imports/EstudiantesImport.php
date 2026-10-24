<?php

namespace App\Imports;

use App\Models\Estudiante;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Str;

class EstudiantesImport implements ToCollection, WithHeadingRow
{
    public const MAX_FILAS = 1000;

    public array $errores = [];
    public int $importados = 0;

    public function collection(Collection $filas): void
    {
        // La plantilla trae celdas vacías con formato: se descartan antes de contar.
    // filter() conserva las posiciones originales, así los números de fila de los errores siguen siendo correctos.
    $filas = $filas->filter(fn ($fila) => $fila->filter(fn ($v) => filled($v))->isNotEmpty());

    if ($filas->isEmpty()) {
        $this->errores[] = ['fila' => 2, 'mensajes' => ['El archivo no tiene datos.']];
        return;
    }

    if ($filas->count() > self::MAX_FILAS) {
        $this->errores[] = ['fila' => 2, 'mensajes' => ['El archivo supera el máximo de ' . self::MAX_FILAS . ' filas.']];
        return;
    }

    $requeridas = ['nombres', 'apellidos', 'fecha_nacimiento'];
    $faltan = array_diff($requeridas, $filas->first()->keys()->all());

    if ($faltan) {
        $this->errores[] = ['fila' => 1, 'mensajes' => [
            'Faltan columnas obligatorias: ' . implode(', ', $faltan) . '. No cambies los nombres de la plantilla.',
        ]];
        return;
    }
        if ($filas->isEmpty()) {
            $this->errores[] = ['fila' => 2, 'mensajes' => ['El archivo no tiene datos.']];
            return;
        }

        if ($filas->count() > self::MAX_FILAS) {
            $this->errores[] = ['fila' => 2, 'mensajes' => ['El archivo supera el máximo de ' . self::MAX_FILAS . ' filas.']];
            return;
        }

        $requeridas = ['nombres', 'apellidos', 'fecha_nacimiento'];
        $faltan = array_diff($requeridas, $filas->first()->keys()->all());

        if ($faltan) {
            $this->errores[] = ['fila' => 1, 'mensajes' => [
                'Faltan columnas obligatorias: ' . implode(', ', $faltan) . '. No cambies los nombres de la plantilla.',
            ]];
            return;
        }

        $validos = [];
        $vistosCodigo = [];
        $vistosDocumento = [];

        foreach ($filas as $indice => $fila) {
            // Saltar filas completamente vacías
            if ($fila->filter(fn ($v) => filled($v))->isEmpty()) {
                continue;
            }

            $numero = $indice + 2; // la fila 1 del Excel es el encabezado

            $datos = [
                'codigo'           => $this->texto($fila->get('codigo')),
                'nombres'          => $this->texto($fila->get('nombres')),
                'apellidos'        => $this->texto($fila->get('apellidos')),
                'documento'        => $this->texto($fila->get('documento')),
                'fecha_nacimiento' => $this->fecha($fila->get('fecha_nacimiento')),
                'sexo'             => $this->sexo($fila->get('sexo')),
                'telefono'         => $this->texto($fila->get('telefono')),
                'direccion'        => $this->texto($fila->get('direccion')),
                'beca' => $this->beca($fila->get('beca')),
            ];

            $mensajes = Validator::make($datos, $this->reglas(), $this->mensajes(), $this->atributos())
                ->errors()->all();

            // Repetidos dentro del mismo archivo
            if ($datos['codigo']) {
                if (isset($vistosCodigo[$datos['codigo']])) {
                    $mensajes[] = "El código {$datos['codigo']} está repetido (ya aparece en la fila {$vistosCodigo[$datos['codigo']]}).";
                } else {
                    $vistosCodigo[$datos['codigo']] = $numero;
                }
            }

            if ($datos['documento']) {
                if (isset($vistosDocumento[$datos['documento']])) {
                    $mensajes[] = "El documento {$datos['documento']} está repetido (ya aparece en la fila {$vistosDocumento[$datos['documento']]}).";
                } else {
                    $vistosDocumento[$datos['documento']] = $numero;
                }
            }

            if ($mensajes) {
                $this->errores[] = ['fila' => $numero, 'mensajes' => $mensajes];
            } else {
                $validos[] = $datos + ['estado' => 'activo'];
            }
        }

        if ($this->errores) {
            return; // todo o nada: no se guarda nada
        }

        if (empty($validos)) {
            $this->errores[] = ['fila' => 2, 'mensajes' => ['El archivo no tiene filas con datos.']];
            return;
        }

        DB::transaction(function () use ($validos) {
            foreach ($validos as $datos) {
                Estudiante::create($datos);
            }
        });

        $this->importados = count($validos);
    }

    private function reglas(): array
    {
        return [
            'codigo'           => ['nullable', 'string', 'max:30', Rule::unique('estudiantes', 'codigo')],
            'nombres'          => ['required', 'string', 'max:255'],
            'apellidos'        => ['required', 'string', 'max:255'],
            'documento'        => ['nullable', 'string', 'max:20', Rule::unique('estudiantes', 'documento')],
            'fecha_nacimiento' => ['required', 'date_format:Y-m-d', 'before:today'],
            'sexo'             => ['nullable', Rule::in(['M', 'F'])],
            'telefono'         => ['nullable', 'string', 'max:20'],
            'direccion'        => ['nullable', 'string', 'max:255'],
            'beca' => ['nullable', Rule::in(array_keys(Estudiante::BECAS))],
        ];
    }

    private function mensajes(): array
    {
        return [
            'fecha_nacimiento.date_format' => 'La fecha de nacimiento no es válida (usa AAAA-MM-DD, por ejemplo 2009-03-15).',
            'sexo.in'                      => 'El sexo debe ser M o F.',
            'beca.in' => 'La beca debe ser "becado" o "beca especial" (o dejarla vacía).',
        ];
    }

    private function beca($valor): ?string
{
    $v = $this->texto($valor);

    if (! $v) {
        return null;
    }

    $v = Str::of($v)->lower()->ascii()->replaceMatches('/[^a-z]/', '')->toString();

    if (in_array($v, ['ninguna', 'no', 'sin', 'nada'], true)) {
        return null;
    }

    return [
        'becado' => 'becado', 'beca' => 'becado',
        'especial' => 'beca_especial', 'becaespecial' => 'beca_especial',
    ][$v] ?? $v; // si no se reconoce, la validación mostrará el error
}
    private function atributos(): array
    {
        return [
            'codigo'           => 'código',
            'fecha_nacimiento' => 'fecha de nacimiento',
            'telefono'         => 'teléfono',
            'direccion'        => 'dirección',
        ];
    }

    private function texto($valor): ?string
    {
        return blank($valor) ? null : trim((string) $valor);
    }

    private function sexo($valor): ?string
    {
        $valor = $this->texto($valor);

        return $valor ? strtoupper(substr($valor, 0, 1)) : null; // "Masculino" → M
    }

    private function fecha($valor): ?string
    {
        if (blank($valor)) {
            return null;
        }

        try {
            // Excel guarda las fechas reales como número de serie
            if (is_numeric($valor)) {
                return Date::excelToDateTimeObject($valor)->format('Y-m-d');
            }
        } catch (\Throwable $e) {
            return (string) $valor;
        }

        $valor = trim((string) $valor);

        foreach (['Y-m-d', 'j/n/Y', 'j-n-Y'] as $formato) {
            $fecha = \DateTime::createFromFormat('!' . $formato, $valor);
            $errores = \DateTime::getLastErrors();

            if ($fecha && (! $errores || ($errores['warning_count'] === 0 && $errores['error_count'] === 0))) {
                return $fecha->format('Y-m-d');
            }
        }

        return $valor; // inválida: la validación mostrará el mensaje
    }
}
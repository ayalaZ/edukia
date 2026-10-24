<?php

namespace App\Imports;

use App\Models\Acudiente;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AcudientesImport implements ToCollection, WithHeadingRow
{
    public const MAX_FILAS = 1000;

    public array $errores = [];
    public int $acudientesNuevos = 0;
    public int $vinculos = 0;

    public function __construct(private string $passwordTemporal)
    {
    }

    public function collection(Collection $filas): void
    {
        // Descartar filas vacías antes de contar (la plantilla trae celdas con formato)
        $filas = $filas->filter(fn ($fila) => $fila->filter(fn ($v) => filled($v))->isNotEmpty());

        if ($filas->isEmpty()) {
            $this->errores[] = ['fila' => 2, 'mensajes' => ['El archivo no tiene datos.']];
            return;
        }

        if ($filas->count() > self::MAX_FILAS) {
            $this->errores[] = ['fila' => 2, 'mensajes' => ['El archivo supera el máximo de ' . self::MAX_FILAS . ' filas.']];
            return;
        }

        $requeridas = ['documento_acudiente', 'nombre_acudiente', 'email', 'codigo_estudiante', 'documento_estudiante', 'parentesco'];
        $faltan = array_diff($requeridas, $filas->first()->keys()->all());

        if ($faltan) {
            $this->errores[] = ['fila' => 1, 'mensajes' => [
                'Faltan columnas: ' . implode(', ', $faltan) . '. No cambies los nombres de la plantilla.',
            ]];
            return;
        }

        // Datos existentes, cargados una sola vez
        $porCodigo  = Estudiante::whereNotNull('codigo')->pluck('id', 'codigo');
        $porDocEst  = Estudiante::whereNotNull('documento')->pluck('id', 'documento');
        $usuarios   = User::select('id', 'documento', 'email', 'rol', 'activo')->get();
        $porDocumento = $usuarios->whereNotNull('documento')->keyBy('documento');
        $porCorreo    = $usuarios->keyBy(fn ($u) => mb_strtolower($u->email));

        $grupos = [];        // acudientes del archivo, por documento
        $operaciones = [];   // vínculos, en el orden del archivo
        $correosVistos = [];
        $paresVistos = [];

        foreach ($filas as $indice => $fila) {
            $numero = $indice + 2; // la fila 1 del Excel es el encabezado

            $d = [
                'documento'     => $this->texto($fila->get('documento_acudiente')),
                'nombre'        => $this->texto($fila->get('nombre_acudiente')),
                'email'         => ($e = $this->texto($fila->get('email'))) ? mb_strtolower($e) : null,
                'telefono'      => $this->texto($fila->get('telefono')),
                'direccion'     => $this->texto($fila->get('direccion')),
                'ocupacion'     => $this->texto($fila->get('ocupacion')),
                'lugar_trabajo' => $this->texto($fila->get('lugar_trabajo')),
                'codigo_est'    => $this->texto($fila->get('codigo_estudiante')),
                'documento_est' => $this->texto($fila->get('documento_estudiante')),
                'parentesco'    => $this->parentesco($fila->get('parentesco')),
                'principal'     => $this->esSi($fila->get('principal')),
            ];

            $validador = Validator::make($d, [
                'documento'     => ['required', 'string', 'max:20'],
                'nombre'        => ['nullable', 'string', 'max:255'],
                'email'         => ['nullable', 'email', 'max:255'],
                'telefono'      => ['nullable', 'string', 'max:20'],
                'direccion'     => ['nullable', 'string', 'max:255'],
                'ocupacion'     => ['nullable', 'string', 'max:255'],
                'lugar_trabajo' => ['nullable', 'string', 'max:255'],
                'parentesco'    => ['required', Rule::in(array_keys(Acudiente::PARENTESCOS))],
            ], $this->mensajes(), $this->atributos());

            if ($validador->fails()) {
                $this->errores[] = ['fila' => $numero, 'mensajes' => $validador->errors()->all()];
                continue;
            }

            $mensajes = [];

            // 1. Estudiante
            $estudianteId = null;

            if (! $d['codigo_est'] && ! $d['documento_est']) {
                $mensajes[] = 'Indica el código o el documento del estudiante.';
            } else {
                $idCodigo = $d['codigo_est'] ? $porCodigo->get($d['codigo_est']) : null;
                $idDoc    = $d['documento_est'] ? $porDocEst->get($d['documento_est']) : null;

                if ($d['codigo_est'] && ! $idCodigo) {
                    $mensajes[] = "No existe un estudiante con el código {$d['codigo_est']}.";
                }
                if ($d['documento_est'] && ! $idDoc) {
                    $mensajes[] = "No existe un estudiante con el documento {$d['documento_est']}.";
                }
                if ($idCodigo && $idDoc && $idCodigo !== $idDoc) {
                    $mensajes[] = 'El código y el documento indicados pertenecen a estudiantes distintos.';
                }

                $estudianteId = $idCodigo ?: $idDoc;
            }

            // 2. Acudiente
            $clave = $d['documento'];

            if (! isset($grupos[$clave])) {
                $existente = $porDocumento->get($clave);
                $completarDocumento = false;
                $conflictoCorreo = false;

                if (! $existente && $d['email'] && ($u = $porCorreo->get($d['email']))) {
                    if ($u->documento && $u->documento !== $clave) {
                        $mensajes[] = "El correo {$d['email']} ya está registrado para otra persona (documento {$u->documento}).";
                        $conflictoCorreo = true;
                    } else {
                        $existente = $u;
                        $completarDocumento = ! $u->documento;
                    }
                }

                if ($existente) {
                    if ($existente->rol !== 'acudiente') {
                        $mensajes[] = "La persona con documento {$clave} ya existe en el sistema con otro rol ({$existente->rol}).";
                    } elseif (! $existente->activo) {
                        $mensajes[] = "El acudiente con documento {$clave} está desactivado. Actívalo antes de vincularlo.";
                    }
                } elseif (! $conflictoCorreo) {
                    if (! $d['nombre']) {
                        $mensajes[] = 'Falta el nombre del acudiente (obligatorio la primera vez que aparece).';
                    }
                    if (! $d['email']) {
                        $mensajes[] = 'Falta el correo del acudiente (obligatorio la primera vez que aparece).';
                    }
                }

                if ($d['email'] && isset($correosVistos[$d['email']]) && $correosVistos[$d['email']]['clave'] !== $clave) {
                    $mensajes[] = "El correo {$d['email']} ya se usó para otro acudiente en la fila {$correosVistos[$d['email']]['fila']}.";
                }

                if (! $mensajes || $estudianteId === null) {
                    // se registra el grupo aunque falle el estudiante; los errores abortan todo igualmente
                }

                if (! $conflictoCorreo && (! $existente || ($existente->rol === 'acudiente' && $existente->activo))) {
                    if ($d['email']) {
                        $correosVistos[$d['email']] = ['clave' => $clave, 'fila' => $numero];
                    }

                    $grupos[$clave] = [
                        'existente_id'        => $existente?->id,
                        'completar_documento' => $completarDocumento,
                        'fila'                => $numero,
                        'email'               => $d['email'],
                        'datos'               => $d,
                    ];
                }
            } else {
                $g = $grupos[$clave];

                if ($d['email'] && $g['email'] && $d['email'] !== $g['email']) {
                    $mensajes[] = "El correo no coincide con el de la fila {$g['fila']}, donde ya aparece este acudiente.";
                }
            }

            // 3. Vínculo repetido
            if ($estudianteId && isset($grupos[$clave])) {
                $par = $clave . '|' . $estudianteId;

                if (isset($paresVistos[$par])) {
                    $mensajes[] = "Este acudiente ya está vinculado a ese estudiante en la fila {$paresVistos[$par]}.";
                } else {
                    $paresVistos[$par] = $numero;
                    $userId = $grupos[$clave]['existente_id'];

                    if ($userId && DB::table('acudiente_estudiante')
                        ->join('acudientes', 'acudientes.id', '=', 'acudiente_estudiante.acudiente_id')
                        ->where('acudientes.user_id', $userId)
                        ->where('acudiente_estudiante.estudiante_id', $estudianteId)
                        ->exists()) {
                        $mensajes[] = 'Este acudiente ya está vinculado a ese estudiante en el sistema.';
                    }
                }
            }

            if ($mensajes) {
                $this->errores[] = ['fila' => $numero, 'mensajes' => $mensajes];
                continue;
            }

            $operaciones[] = [
                'clave'      => $clave,
                'estudiante' => $estudianteId,
                'parentesco' => $d['parentesco'],
                'principal'  => $d['principal'],
            ];
        }

        if ($this->errores) {
            return; // todo o nada: no se guarda nada
        }

        if (empty($operaciones)) {
            $this->errores[] = ['fila' => 2, 'mensajes' => ['El archivo no tiene filas con datos.']];
            return;
        }

        DB::transaction(function () use ($grupos, $operaciones) {
            $hash = \Illuminate\Support\Facades\Hash::make($this->passwordTemporal); // un solo hash para todos
            $acudienteIds = [];

            foreach ($grupos as $clave => $g) {
                if ($g['existente_id']) {
                    if ($g['completar_documento']) {
                        User::whereKey($g['existente_id'])->update(['documento' => $clave]);
                    }

                    $acudienteIds[$clave] = Acudiente::firstOrCreate(['user_id' => $g['existente_id']])->id;
                    continue;
                }

                $d = $g['datos'];

                $user = new User;
                $user->forceFill([
                    'name'              => $d['nombre'],
                    'email'             => $d['email'],
                    'password'          => $hash,
                    'documento'         => $clave,
                    'telefono'          => $d['telefono'],
                    'direccion'         => $d['direccion'],
                    'rol'               => 'acudiente',
                    'activo'            => true,
                    'cambiar_password'  => true,
                    'email_verified_at' => now(),
                ])->save();

                $acudienteIds[$clave] = Acudiente::create([
                    'user_id'       => $user->id,
                    'ocupacion'     => $d['ocupacion'],
                    'lugar_trabajo' => $d['lugar_trabajo'],
                ])->id;

                $this->acudientesNuevos++;
            }

            // Los vínculos se aplican en el orden del archivo
            foreach ($operaciones as $op) {
                $tieneAcudientes = DB::table('acudiente_estudiante')
                    ->where('estudiante_id', $op['estudiante'])
                    ->exists();

                $principal = $op['principal'] || ! $tieneAcudientes;

                if ($principal) {
                    DB::table('acudiente_estudiante')
                        ->where('estudiante_id', $op['estudiante'])
                        ->update(['es_principal' => false]);
                }

                DB::table('acudiente_estudiante')->insert([
                    'acudiente_id' => $acudienteIds[$op['clave']],
                    'estudiante_id' => $op['estudiante'],
                    'parentesco'   => $op['parentesco'],
                    'es_principal' => $principal,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            $this->vinculos = count($operaciones);
        });
    }

    private function mensajes(): array
    {
        return [
            'documento.required'  => 'Falta el documento del acudiente.',
            'email.email'         => 'El correo no es válido.',
            'parentesco.required' => 'Falta el parentesco.',
            'parentesco.in'       => 'El parentesco no es válido (usa Madre, Padre, Abuelo/a, Tío/a, Hermano/a, Tutor legal u Otro).',
        ];
    }

    private function atributos(): array
    {
        return [
            'documento'     => 'documento del acudiente',
            'nombre'        => 'nombre',
            'email'         => 'correo',
            'telefono'      => 'teléfono',
            'direccion'     => 'dirección',
            'ocupacion'     => 'ocupación',
            'lugar_trabajo' => 'lugar de trabajo',
        ];
    }

    private function texto($valor): ?string
    {
        return blank($valor) ? null : trim((string) $valor);
    }

    private function esSi($valor): bool
    {
        $v = Str::of((string) $valor)->lower()->ascii()->trim()->toString();

        return in_array($v, ['si', 's', '1', 'x', 'true', 'yes'], true);
    }

    private function parentesco($valor): ?string
    {
        $v = $this->texto($valor);

        if (! $v) {
            return null;
        }

        // "Tío/a" → "tioa", "Mamá" → "mama"
        $v = Str::of($v)->lower()->ascii()->replaceMatches('/[^a-z]/', '')->toString();

        return [
            'madre' => 'madre', 'mama' => 'madre',
            'padre' => 'padre', 'papa' => 'padre',
            'abuelo' => 'abuelo', 'abuela' => 'abuelo', 'abueloa' => 'abuelo',
            'tio' => 'tio', 'tia' => 'tio', 'tioa' => 'tio',
            'hermano' => 'hermano', 'hermana' => 'hermano', 'hermanoa' => 'hermano',
            'tutor' => 'tutor', 'tutora' => 'tutor', 'tutorlegal' => 'tutor',
            'otro' => 'otro', 'otra' => 'otro',
        ][$v] ?? $v;
    }
}
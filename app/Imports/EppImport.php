<?php

namespace App\Imports;

use App\Models\Epp;
use App\Models\Categoria;
use Maatwebsite\Excel\Concerns\ToModel;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Facades\Log;

class EppImport implements ToModel, WithStartRow, SkipsEmptyRows
{
    private $imagenesPorNombre = [];
    private $fechaRegistro = null;

    public function __construct($imagenesPorNombre = [], $fechaRegistro = null)
    {
        $this->imagenesPorNombre = $imagenesPorNombre;
        $this->fechaRegistro = $fechaRegistro; // ✅ CORRECCIÓN: asignar la fecha
        Log::info('EppImport inicializado con ' . count($imagenesPorNombre) . ' imágenes');
        if (count($imagenesPorNombre) > 0) {
            Log::info('EPPs con imagen: ' . implode(', ', array_slice(array_keys($imagenesPorNombre), 0, 5)));
        }
    }

    public function startRow(): int
    {
        return 3;
    }

    public function model(array $row)
    {
        if (!isset($row[1]) || empty(trim($row[1]))) {
            return null;
        }

        $nombreEpp = trim($row[1]);

        if (str_contains(strtoupper($nombreEpp), 'EQUIPOS DE PROTECCIÓN COLECTIVO')) {
            return null;
        }

        $imagenPath = $this->imagenesPorNombre[$nombreEpp] ?? null;

        if ($imagenPath) {
            Log::info("✓ Imagen encontrada para: {$nombreEpp} -> {$imagenPath}");
        } else {
            Log::info("✗ Sin imagen en mapeo para: {$nombreEpp}");
        }

        $categoriaId = $this->obtenerCategoriaPorNombre($nombreEpp);
        $cantidadInicial = is_numeric($row[11]) ? (int)$row[11] : 0;

        $frecuenciaTexto = strtolower($row[4] ?? '');
        $vidaUtilMeses = 12;

        if (preg_match('/\d+/', $frecuenciaTexto, $matches)) {
            $numero = (int)$matches[0];
            if (str_contains($frecuenciaTexto, 'año') || str_contains($frecuenciaTexto, 'ano')) {
                $vidaUtilMeses = $numero * 12;
            } else {
                $vidaUtilMeses = $numero;
            }
        }

        // Detectar fecha de ingreso por fila si existe en el Excel; si no, usar la pasada al import; si no, now()
        $fechaDetec = $this->detectarFechaIngreso($row);
        $fechaBase = $fechaDetec ?: ($this->fechaRegistro ? Carbon::parse($this->fechaRegistro) : now());
        $fechaVencimiento = $fechaBase->copy()->addMonths($vidaUtilMeses);

        $imagenFinal = $imagenPath ?? $this->generarImagenAutomatica($nombreEpp);

        // ✅ CORRECCIÓN: separar la creación del modelo y asignar created_at por separado
        $epp = new Epp([
            'nombre'             => $nombreEpp,
            'imagen'             => $imagenFinal,
            'descripcion'        => $row[2] ?? null,
            'frecuencia_entrega' => $row[4] ?? null,
            'codigo_logistica'   => $row[5] ?? null,
            'marca_modelo'       => $row[6] ?? null,
            'precio'             => is_numeric($row[9]) ? (float)$row[9] : 0,
            'cantidad'           => $cantidadInicial,
            'stock'              => $cantidadInicial,
            'entregado'          => 0,
            'deteriorado'        => 0,
            'tipo'               => 'Protección de seguridad',
            'vida_util_meses'    => $vidaUtilMeses,
            'fecha_vencimiento'  => $fechaVencimiento,
            'categoria_id'       => $categoriaId,
            'estado'             => 'disponible',
        ]);

        $epp->created_at = $fechaBase;

        return $epp;
    }

    private function generarImagenAutomatica($nombreEpp)
    {
        $terminoBusqueda = urlencode($nombreEpp . ' safety equipment');
        return "https://source.unsplash.com/featured/400x400?{$terminoBusqueda}";
    }

    private function obtenerCategoriaPorNombre($nombre)
    {
        $nombreLower = strtolower($nombre);
        $nombreCategoria = 'Otros';

        if (str_contains($nombreLower, 'casco') || str_contains($nombreLower, 'craneal')) {
            $nombreCategoria = 'Protección Craneal';
        } elseif (str_contains($nombreLower, 'lente') || str_contains($nombreLower, 'gafa') || str_contains($nombreLower, 'careta')) {
            $nombreCategoria = 'Protección Visual';
        } elseif (str_contains($nombreLower, 'guante')) {
            $nombreCategoria = 'Protección Manual';
        } elseif (str_contains($nombreLower, 'bota') || str_contains($nombreLower, 'zapato') || str_contains($nombreLower, 'calzado')) {
            $nombreCategoria = 'Protección de Pies';
        } elseif (str_contains($nombreLower, 'tapon') || str_contains($nombreLower, 'orejera') || str_contains($nombreLower, 'auditivo')) {
            $nombreCategoria = 'Protección Auditiva';
        } elseif (str_contains($nombreLower, 'chaleco') || str_contains($nombreLower, 'mameluco') || str_contains($nombreLower, 'ropa')) {
            $nombreCategoria = 'Ropa de Trabajo';
        }

        $categoria = Categoria::firstOrCreate(
            ['nombre' => $nombreCategoria],
            ['descripcion' => 'Categoría generada automáticamente por importación']
        );

        return $categoria->id;
    }

    /**
     * Intenta detectar una fecha de ingreso en la fila.
     * Busca celdas con formatos comunes de fecha (dd/mm/yyyy, yyyy-mm-dd, etc.).
     */
    private function detectarFechaIngreso(array $row): ?Carbon
    {
        foreach ($row as $cell) {
            if (!is_string($cell) && !is_numeric($cell)) continue;

            $value = trim((string)$cell);
            if ($value === '') continue;

            // Heurística: si contiene separadores típicos de fecha
            if (preg_match('/\\d{1,4}[\\\/\\-]\\d{1,2}[\\\/\\-]\\d{1,4}/', $value)) {
                try {
                    return Carbon::parse($value);
                } catch (\Throwable $e) {
                    // ignorar y seguir
                }
            }

            // Si es número (fechas Excel serial), intentar convertir (Excel base 1899-12-30)
            if (is_numeric($cell) && $cell > 30000 && $cell < 60000) {
                try {
                    return Carbon::create(1899, 12, 30)->addDays((int)$cell);
                } catch (\Throwable $e) {
                    // ignorar
                }
            }
        }
        return null;
    }
}
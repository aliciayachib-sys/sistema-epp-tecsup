<?php

namespace App\Imports;

use App\Models\Personal;
use App\Models\Taller;
use App\Models\Departamento;
use App\Models\Epp;
use App\Models\Asignacion;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PersonalDataImport implements ToCollection, WithStartRow
{
    private $currentDepartamentoId = null;
    private $isHeaderRow = false;
    private $eppColumnHeaders = [];
    private $personalesImportados = 0;
    private $lastTaller = ''; // Para manejar celdas combinadas en la columna de talleres
    private $lastTipo = '';   // Para manejar celdas combinadas en la columna de tipo

    public function startRow(): int
    {
        return 1;
    }

    public function collection(Collection $rows)
    {
        \Log::info("🟢 [PersonalDataImport] collection() iniciado con " . $rows->count() . " filas");
        
        if ($rows->isEmpty()) {
            \Log::warning("⚠️  La colección está vacía");
            return;
        }

        // DEBUG: Mostrar las primeras 10 filas para entender la estructura
        \Log::info("📋 PRIMERAS FILAS DEL EXCEL:");
        foreach ($rows->slice(0, 10) as $idx => $row) {
            $rowArray = $row->toArray();
            \Log::info("   Fila " . ($idx + 1) . ": " . json_encode(array_slice($rowArray, 0, 3))); // Mostrar primeras 3 columnas
        }
        \Log::info("═══════════════════════════════════════════");

        foreach ($rows as $idx => $row) {
            try {
                $rowArray = $row->toArray();
                $col1 = trim($rowArray[1] ?? '');  // Columna B (datos comienzan aquí, no en A)
                
                if (empty($col1)) {
                    continue;
                }

                $normalized = $this->normalizar($col1);
                
                // Detect HEADER ROW
                if (Str::contains($normalized, 'PUESTO')) {
                    \Log::info("📋 [Fila " . ($idx + 1) . "] ENCABEZADO detectado");
                    $this->isHeaderRow = true;
                    $this->lastTaller = ''; // Reiniciar en cada nuevo encabezado
                    $this->lastTipo = '';   // Reiniciar en cada nuevo encabezado
                    $this->captureEppHeaders($rowArray);
                    continue;
                }
                
                // Detect DEPARTMENT
                if (Str::contains($normalized, 'DEPARTAMENTO')) {
                    $deptName = preg_replace('/DEPARTAMENTO\s+(DE\s+)?/i', '', $col1);
                    $deptName = Str::title(trim($deptName));
                    $depto = Departamento::firstOrCreate(['nombre' => $deptName]);
                    $this->currentDepartamentoId = $depto->id;
                    $this->lastTaller = ''; // Reiniciar en cada nuevo departamento
                    $this->lastTipo = '';   // Reiniciar en cada nuevo departamento
                    \Log::info("🏢 [Fila " . ($idx + 1) . "] Departamento: $deptName (ID: " . $depto->id . ")");
                    continue;
                }

                // Process PERSONAL ROWS
                if ($this->isHeaderRow && !empty($col1)) {
                    if (Str::contains($normalized, ['TOTAL', 'SUBTOTAL', 'MATRIZ', 'RESUMEN', 'DOCENTE NUEVO'])) {
                        \Log::info("⏭️  [Fila " . ($idx + 1) . "] Fila ignorada (placeholder): $col1");
                        continue;
                    }

                    if (!$this->currentDepartamentoId) {
                        $depto = Departamento::firstOrCreate(['nombre' => 'General']);
                        $this->currentDepartamentoId = $depto->id;
                    }

                    $nombreCompleto = trim($rowArray[1] ?? '');  // Columna B
                    $tipoRaw = trim($rowArray[2] ?? '');      // Columna C
                    $tallerRaw = trim($rowArray[3] ?? '');          // Columna D

                    // --- LÓGICA PARA CELDAS COMBINADAS ---
                    // Si la celda de la fila actual tiene un valor, lo "recordamos".
                    if (!empty($tipoRaw)) {
                        $this->lastTipo = $tipoRaw;
                    }
                    if (!empty($tallerRaw)) {
                        $this->lastTaller = $tallerRaw;
                    }
                    // Usamos el último valor "recordado" para esta y las siguientes filas vacías.
                    $tipo = $this->normalizarTipo($this->lastTipo);
                    $taller = $this->lastTaller;

                    $personal = Personal::updateOrCreate(
                        ['nombre_completo' => $nombreCompleto], // Clave única: el nombre completo
                        [
                            // Datos para actualizar o crear:
                            'departamento_id' => $this->currentDepartamentoId,
                            'tipo_contrato' => $tipo,
                        ]
                    );

                    $this->personalesImportados++;

                    if (!empty($taller)) {
                        $tallerObj = Taller::firstOrCreate(
                            ['nombre' => $taller, 'departamento_id' => $this->currentDepartamentoId],
                            ['activo' => true]
                        );
                        $personal->talleres()->syncWithoutDetaching([$tallerObj->id]);
                    }

                    // EPP assignment disabled - users will assign manually
                    // $this->processEpps($personal, $rowArray);
                }
            } catch (\Exception $e) {
                \Log::error("❌ Error en fila " . ($idx + 1) . ": " . $e->getMessage());
            }
        }

        \Log::info("✅ [PersonalDataImport] Finalizado. Total importados: " . $this->personalesImportados);
    }

    private function captureEppHeaders($rowArray)
    {
        $this->eppColumnHeaders = [];
        \Log::info("📊 CAPTURANDO ENCABEZADOS DE EPP (Columna E en adelante):");
        
        for ($i = 4; $i < count($rowArray); $i++) {  // EPPs comienzan en columna E (índice 4)
            $headerName = trim($rowArray[$i] ?? '');
            if (!empty($headerName)) {
                $this->eppColumnHeaders[$i] = $headerName;
                $colLetter = chr(65 + $i);
                \Log::info("   [{$colLetter}] {$headerName}");
            }
        }
        \Log::info("   └─ TOTAL: " . count($this->eppColumnHeaders) . " columnas de EPP detectadas");
        \Log::info("═══════════════════════════════════════════════════════════════");
    }

    private function processEpps(Personal $personal, $rowArray)
    {
        if (empty($this->eppColumnHeaders)) {
            \Log::warning("   ⚠️ No hay headers de EPP capturados para " . $personal->nombre_completo);
            return;
        }
        
        $eppAsignados = 0;
        
        foreach ($this->eppColumnHeaders as $colIdx => $eppName) {
            $cellVal = trim($rowArray[$colIdx] ?? '');
            
            // Solo asignar si la celda tiene contenido (celda verde/marcada en Excel)
            if (!empty($cellVal) && !in_array(strtoupper($cellVal), ['0', 'NO', 'N', 'FALSE', '-'])) {
                try {
                    // Búsqueda exacta primero (sin espacios), luego aproximada
                    $eppNormalized = trim($eppName);
                    $epp = Epp::where('nombre', '=', $eppNormalized)->first();
                    
                    // Si no encuentra exactamente, buscar con LIKE pero más restrictivo
                    if (!$epp) {
                        $epp = Epp::whereRaw('LOWER(nombre) = LOWER(?)', [$eppNormalized])->first();
                    }
                    
                    // Si aún no encuentra, NO crear - solo loguear
                    if (!$epp) {
                        \Log::warning("   ⚠️ EPP NO ENCONTRADO: '$eppNormalized' para " . $personal->nombre_completo);
                        continue; // No crear automáticamente
                    }

                    // Verificar si ya existe la asignación
                    if (!Asignacion::where(['personal_id' => $personal->id, 'epp_id' => $epp->id])->exists()) {
                        Asignacion::create([
                            'personal_id' => $personal->id,
                            'epp_id' => $epp->id,
                            'estado' => 'Entregado',
                            'fecha_entrega' => now(),
                            'cantidad' => 1,
                        ]);
                        $eppAsignados++;
                        \Log::info("       → EPP asignado: '$eppNormalized'");
                    }
                } catch (\Exception $e) {
                    \Log::error("   ❌ Error asignando EPP '$eppName': " . $e->getMessage());
                }
            }
        }
        
        if ($eppAsignados > 0) {
            \Log::info("   ✅ " . $eppAsignados . " EPP(s) asignado(s) a " . $personal->nombre_completo);
        }
    }

    private function normalizar($texto)
    {
        $reemplazos = ['Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ñ'=>'N'];
        return strtr(strtoupper($texto), $reemplazos);
    }

    private function normalizarTipo($tipo)
    {
        $tipo = strtoupper(trim($tipo));
        if (empty($tipo)) {
            return 'Docente TC'; // Valor por defecto si no se ha encontrado ninguno
        }
        if (Str::contains($tipo, ['TC', 'TIEMPO COMPLETO'])) return 'Docente TC';
        if (Str::contains($tipo, ['TP', 'TIEMPO PARCIAL'])) return 'Docente TP';
        if (Str::contains($tipo, 'ADMIN')) return 'Administrativo';
        return 'Docente TC'; // Valor por defecto para casos no reconocidos
    }
}

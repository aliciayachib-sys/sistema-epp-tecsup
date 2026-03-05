<?php

namespace App\Http\Controllers;

use App\Models\Personal;
use App\Models\Departamento;
use App\Models\Epp;
use App\Models\Taller;
use App\Models\Asignacion;
use Illuminate\Http\Request;
use App\Imports\PersonalImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class PersonalController extends Controller
{
    public function index()
    {
        $personals = Personal::with(['departamento', 'asignaciones' => function($query) {
            $query->where('estado', 'Entregado');
        }])->orderBy('nombre_completo', 'asc')->get();
        
        return view('personals.index', compact('personals'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'dni' => 'nullable|string|unique:personals,dni',
            'carrera' => 'nullable|string|max:255',
            'tipo_contrato' => 'nullable|string',
        ]);

        Personal::create([
            'nombre_completo' => $request->nombre_completo,
            'dni' => $request->dni,
            'departamento_id' => null, 
            'carrera' => $request->carrera ?? 'Sin carrera',
            'tipo_contrato' => $request->tipo_contrato ?? 'Docente TC',
        ]);

        return back()->with('success', 'Docente registrado correctamente.');
    }

    public function show($id)
    {
        $departamento = Departamento::with('personals')->findOrFail($id);
        $epps = Epp::where('stock', '>', 0)->orderBy('nombre', 'asc')->get();
        return view('departamentos.show', compact('departamento', 'epps'));
    }

    /**
     * Actualizar datos del personal (Carrera, DNI, Nombre)
     */
    public function update(Request $request, $id)
    {
        $personal = Personal::findOrFail($id);

        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'dni' => 'nullable|string|max:20|unique:personals,dni,' . $id,
            'carrera' => 'nullable|string|max:255',
            'tipo_contrato' => 'nullable|string',
        ]);

        $personal->update($request->only([
            'nombre_completo',
            'dni',
            'carrera',
            'tipo_contrato'
        ]));

        return back()->with('success', 'Datos del docente actualizados.');
    }

    // NUEVO: Para borrar personal de la lista maestra
    public function destroy($id)
    {
        $personal = Personal::findOrFail($id);
        $personal->delete();
        return back()->with('success', 'Docente eliminado de la base de datos.');
    }

    /**
     * Eliminar docentes seleccionados
     */
    public function deleteMultiple(Request $request)
    {
        $idsString = $request->input('ids', '');
        $ids = array_filter(explode(',', $idsString)); // Convertir string a array
        
        if (empty($ids)) {
            return back()->with('error', 'Selecciona al menos un docente para eliminar.');
        }

        try {
            // Desactivar restricciones de clave foránea temporalmente
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Primero eliminar todas las asignaciones de estos docentes
            Asignacion::whereIn('personal_id', $ids)->delete();
            
            // Luego eliminar los docentes
            Personal::whereIn('id', $ids)->delete();
            
            // Reactivar restricciones de clave foránea
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            return back()->with('success', '✅ ' . count($ids) . ' docente(s) y sus asignaciones de EPP eliminado(s) correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar todos los docentes
     */
    public function deleteAll(Request $request)
    {
        try {
            $count = Personal::count();
            
            // Desactivar restricciones de clave foránea temporalmente
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Primero eliminar todas las asignaciones
            Asignacion::truncate();
            
            // Luego truncate la tabla de personales
            Personal::truncate();
            
            // Reactivar restricciones de clave foránea
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            return back()->with('success', '✅ Se han eliminado ' . $count . ' docente(s) y todas sus asignaciones de EPP. Total actual: 0');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al vaciar: ' . $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        try {
            \Log::info("═══════════════════════════════════════════");
            \Log::info("INICIANDO IMPORTACIÓN DE EXCEL");
            \Log::info("Archivo: " . $request->file('file')->getClientOriginalName());
            \Log::info("═══════════════════════════════════════════");
            
            Excel::import(new PersonalImport, $request->file('file'));
            
            $personalsCount = Personal::count();

            // --- INICIO CÓDIGO INVISIBLE PARA LA MATRIZ ---
            // Una vez importado el personal y creados los departamentos,
            // ejecutamos el seeder de la matriz automáticamente.
            try {
                \Illuminate\Support\Facades\Artisan::call('db:seed', [
                    '--class' => 'MatrizDinamicaSeeder',
                    '--force' => true
                ]);
                \Log::info("✅ Matriz de EPP sincronizada automáticamente.");
            } catch (\Exception $seederError) {
                \Log::error("⚠️ El Excel cargó pero la Matriz falló: " . $seederError->getMessage());
            }
            // --- FIN CÓDIGO INVISIBLE ---



            
            \Log::info("═══════════════════════════════════════════");
            \Log::info("✅ IMPORTACIÓN COMPLETADA");
            \Log::info("Total de personals en BD: " . $personalsCount);
            \Log::info("═══════════════════════════════════════════");
            
            return redirect()->route('personals.index')->with('success', '✅ ¡Personal importado correctamente! Total en BD: ' . $personalsCount);
        } catch (\Exception $e) {
            \Log::error("═══════════════════════════════════════════");
            \Log::error("❌ ERROR EN IMPORTACIÓN");
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());
            \Log::error("═══════════════════════════════════════════");
            
            return redirect()->route('personals.index')->with('error', '❌ Error al importar: ' . $e->getMessage());
        }
    }
}
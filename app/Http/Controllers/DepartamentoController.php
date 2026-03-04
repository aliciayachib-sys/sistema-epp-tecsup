<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Personal;
use App\Models\Epp;
use App\Models\Taller;
use App\Models\MatrizHomologacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartamentoController extends Controller
{
    /**
     * Muestra las "Cards" de los departamentos en el Panel.
     */
    public function index()
    {
        // Contamos cuántos personals (docentes) tiene cada departamento
        $departamentos = Departamento::withCount('personals')->get(); 
        
        return view('departamentos.index', compact('departamentos'));
    }

    /**
     * Guarda un nuevo departamento creado desde el modal de Jiancarlo.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|unique:departamentos,nombre|max:255',
            'imagen' => 'nullable|image|max:2048', // Máx 2MB
            'imagen_url_text' => 'nullable|url',
        ]);

        $imagenUrl = null;

        // Lógica para determinar qué imagen usar
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('departamentos', 'public');
            $imagenUrl = 'storage/' . $path; // Se guarda la ruta relativa para usar con el helper asset()
        } elseif ($request->filled('imagen_url_text')) {
            $imagenUrl = $request->imagen_url_text;
        }

        // Usamos una instancia nueva para asegurar que se guarde aunque no esté en $fillable
        $departamento = new Departamento();
        $departamento->nombre = $request->nombre;
        $departamento->nivel_riesgo = 'Bajo';
        $departamento->imagen_url = $imagenUrl;
        $departamento->save();

        return back()->with('success', '¡Departamento creado con éxito!');
    }

    /**
     * Actualiza un departamento existente.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:departamentos,nombre,' . $id,
            'imagen' => 'nullable|image|max:2048',
            'imagen_url_text' => 'nullable|url',
        ]);

        $departamento = Departamento::findOrFail($id);
        $departamento->nombre = $request->nombre;

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('departamentos', 'public');
            $departamento->imagen_url = 'storage/' . $path;
        } elseif ($request->filled('imagen_url_text')) {
            $departamento->imagen_url = $request->imagen_url_text;
        }

        $departamento->save();

        return back()->with('success', 'Departamento actualizado correctamente.');
    }

    /**
     * Muestra los detalles de un departamento y su lista de docentes asignados.
     */
    public function show(string $id)
    {
        // Cargamos el departamento y sus docentes, pero ORDENADOS por Carrera y luego por Nombre
        $departamento = Departamento::with(['personals' => function($query) {
            $query->orderBy('carrera', 'asc')
                  ->orderBy('nombre_completo', 'asc');
        }, 'personals.asignaciones.epp', 'personals.talleres'])->findOrFail($id);

        $epps = Epp::where('stock', '>', 0)->orderBy('nombre', 'asc')->get();
        $talleres = Taller::where('departamento_id', $id)->where('activo', true)->orderBy('nombre')->get();
        $matriz = MatrizHomologacion::where('departamento_id', $id)->where('activo', true)->get();

        return view('departamentos.show', compact('departamento', 'epps', 'talleres', 'matriz'));
    }

    /**
     * Elimina un departamento específico.
     */
    public function destroy(string $id)
    {
        // Desasignamos al personal de este departamento antes de borrar
        Personal::where('departamento_id', $id)->update(['departamento_id' => null]);
        
        // Desasignamos los talleres de este departamento antes de borrar
        Taller::where('departamento_id', $id)->update(['departamento_id' => null]);
        
        $departamento = Departamento::findOrFail($id);
        $departamento->delete();

        return back()->with('success', 'Departamento eliminado correctamente.');
    }

    /**
     * Elimina todos los departamentos y deja a los docentes "sin asignar".
     */
    public function destroyAll()
    {
        // Primero dejamos a todos los docentes sin departamento (en lugar de borrarlos)
        // Así Jiancarlo no pierde su "Lista Maestra"
        Personal::query()->update(['departamento_id' => null]);
        
        // Desasignamos todos los talleres
        Taller::query()->update(['departamento_id' => null]);
        
        // Luego borramos los departamentos
        Departamento::query()->delete();

        return back()->with('success', 'Departamentos eliminados. El personal ha vuelto a la Lista Maestra.');
    }

    /**
     * Elimina departamentos seleccionados.
     */
    public function destroySelected(Request $request)
    {
        if (!$request->ids) {
            return back()->with('error', 'No has seleccionado nada.');
        }

        // Desasignamos los talleres de esos departamentos antes de borrar
        Taller::whereIn('departamento_id', $request->ids)->update(['departamento_id' => null]);
        
        // Desasignamos al personal de esos departamentos antes de borrar
        Personal::whereIn('departamento_id', $request->ids)->update(['departamento_id' => null]);
        
        Departamento::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'Departamentos eliminados correctamente.');
    }

    /**
     * Asigna un EPP a todo el personal del departamento.
     */
    public function asignarMasivo(Request $request, $id)
    {
        $request->validate([
            'epps' => 'required|array',
        ]);

        $departamento = Departamento::with('personals')->findOrFail($id);
        $totalPersonas = $departamento->personals->count();
        
        if ($totalPersonas === 0) {
            return back()->with('error', 'No hay personal en este departamento para asignar.');
        }

        // Filtrar solo los EPPs marcados
        $seleccionados = collect($request->epps)->filter(function($item) {
            return isset($item['checked']);
        });

        if ($seleccionados->isEmpty()) {
            return back()->with('error', 'No seleccionaste ningún EPP.');
        }

        try {
            DB::beginTransaction();
            $nombresAsignados = [];

            foreach ($seleccionados as $eppId => $data) {
                $epp = Epp::lockForUpdate()->find($eppId);
                if (!$epp) continue;

                $cantidad = max(1, intval($data['cantidad']));
                $totalRequerido = $cantidad * $totalPersonas;

                if ($epp->stock < $totalRequerido) {
                    throw new \Exception("Stock insuficiente para '{$epp->nombre}'. Se requieren {$totalRequerido} (Stock: {$epp->stock}).");
                }

                foreach ($departamento->personals as $personal) {
                    \App\Models\Asignacion::create([
                        'personal_id' => $personal->id,
                        'epp_id'      => $epp->id,
                        'cantidad'    => $cantidad,
                        'fecha_entrega' => now(),
                        'estado'      => 'Entregado'
                    ]);
                }

                $epp->decrement('stock', $totalRequerido);
                $nombresAsignados[] = $epp->nombre;
            }

            DB::commit();
            return back()->with('success', "Asignación masiva completada para: " . implode(', ', $nombresAsignados));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
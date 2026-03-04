<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Personal;
use App\Models\Epp;
use App\Models\Taller;
use App\Models\MatrizHomologacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntregaController extends Controller
{
    /**
     * Muestra la lista de TODO el personal para entregas de EPP.
     */
    public function index(Request $request)
    {
        $departamentoIdFiltro = $request->input('departamento_id');

        // Obtener todo el personal ordenado por carrera y nombre
        $personalsQuery = Personal::orderBy('carrera', 'asc')
            ->orderBy('nombre_completo', 'asc')
            ->with(['asignaciones.epp', 'talleres', 'departamento']);

        // Si se pasa un ID de departamento, filtramos la lista de personal
        if ($departamentoIdFiltro) {
            $personalsQuery->where('departamento_id', $departamentoIdFiltro);
        }

        $personals = $personalsQuery->get();

        $epps = Epp::orderBy('nombre', 'asc')->get();
        $talleres = Taller::where('activo', true)->orderBy('nombre')->get();
        $matriz = MatrizHomologacion::where('activo', true)->get();
        $departamentos = Departamento::orderBy('nombre')->get();

        return view('entregas.index', compact('personals', 'epps', 'talleres', 'matriz', 'departamentos', 'departamentoIdFiltro'));
    }

    /**
     * Asigna un EPP a todo el personal o al personal de un departamento específico.
     */
    public function asignarMasivo(Request $request)
    {
        $request->validate([
            'epps' => 'required|array',
            'fecha_entrega' => 'nullable|date',
        ]);

        $departamentoIdFiltro = $request->input('departamento_id');
        
        $query = Personal::query();
        if ($departamentoIdFiltro) {
            $query->where('departamento_id', $departamentoIdFiltro);
        }
        
        $personals = $query->get();
        $totalPersonas = $personals->count();
        
        if ($totalPersonas === 0) {
            return back()->with('error', 'No hay personal para asignar.');
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
            $fechaEntrega = $request->filled('fecha_entrega') ? \Carbon\Carbon::parse($request->fecha_entrega) : now();

            foreach ($seleccionados as $eppId => $data) {
                $epp = Epp::lockForUpdate()->find($eppId);
                if (!$epp) continue;

                $cantidad = max(1, intval($data['cantidad']));
                $totalRequerido = $cantidad * $totalPersonas;

                if ($epp->stock < $totalRequerido) {
                    throw new \Exception("Stock insuficiente para '{$epp->nombre}'. Se requieren {$totalRequerido} (Stock: {$epp->stock}).");
                }

                foreach ($personals as $personal) {
                    \App\Models\Asignacion::create([
                        'personal_id' => $personal->id,
                        'epp_id'      => $epp->id,
                        'cantidad'    => $cantidad,
                        'fecha_entrega' => $fechaEntrega,
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

    public function show(string $id)
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }



    /**
     * Obtiene los EPPs requeridos según el taller seleccionado.
     * Respuesta para AJAX.
     */
    public function getMatrizPorTaller($taller)
    {
        try {
            // Buscamos los EPP_ID y el tipo (obligatorio/especifico) en la matriz
            $matriz = DB::table('matriz_homologacions')
                ->where('taller', $taller)
                ->where('activo', true)
                ->select('epp_id', 'tipo_requerimiento')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $matriz
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar la matriz'
            ], 500);
        }
    }
}
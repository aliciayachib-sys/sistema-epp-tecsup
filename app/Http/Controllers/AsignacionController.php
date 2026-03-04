<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Epp;
use App\Models\Personal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsignacionController extends Controller
{
    // ESTE ES EL MÉTODO QUE FALTA
    public function index()
    {
        $asignaciones = Asignacion::with(['personal.talleres', 'epp'])
            ->whereNotNull('personal_id') // Solo asignaciones con personal asociado
            ->orderBy('fecha_entrega', 'desc')
            ->get();
        
        return view('epps.asignaciones', compact('asignaciones')); 
    }

    public function store(Request $request)
    {
        $request->validate([
            'personal_id' => 'required|exists:personals,id',
            'epps'        => 'required|array',
            'fecha_entrega' => 'nullable|date',
        ]);

        $personal = Personal::findOrFail($request->personal_id);

        // Filtrar solo los seleccionados
        $seleccionados = collect($request->epps)->filter(function($item) {
            return isset($item['checked']);
        });

        if ($seleccionados->isEmpty()) {
            return back()->with('error', 'Debes seleccionar al menos un equipo.');
        }

        try {
            DB::beginTransaction();
            $nombresEntregados = [];
            $fechaEntrega = $request->filled('fecha_entrega') ? \Carbon\Carbon::parse($request->fecha_entrega) : now();

            foreach ($seleccionados as $eppId => $data) {
                $epp = Epp::lockForUpdate()->find($eppId);
                if (!$epp) continue;

                $cantidad = max(1, intval($data['cantidad']));

                if ($epp->stock < $cantidad) {
                    throw new \Exception("Stock insuficiente para '{$epp->nombre}'. (Stock: {$epp->stock})");
                }

                Asignacion::create([
                    'personal_id' => $personal->id,
                    'epp_id'      => $epp->id,
                    'cantidad'    => $cantidad,
                    'fecha_entrega' => $fechaEntrega,
                    'estado'      => 'Entregado'
                ]);

                $epp->decrement('stock', $cantidad);
                $nombresEntregados[] = $epp->nombre;
            }

            DB::commit();
            return back()->with('success', 'Entrega registrada: ' . implode(', ', $nombresEntregados));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Marcar como DEVUELTO (El docente entrega el EPP en buen estado)
     */
    public function devolver($id)
    {
        $asignacion = Asignacion::findOrFail($id);
        
        if ($asignacion->estado == 'Entregado') {
            $asignacion->update(['estado' => 'Devuelto']);
            
            // Opcional: Si es devuelto en buen estado, ¿regresa al stock?
            // Para EPPs como cascos sí, para guantes usados quizás no.
            // Por simplicidad y control, lo sumamos al stock.
            $asignacion->epp->increment('stock', $asignacion->cantidad);
        }

        return back()->with('success', 'Equipo marcado como devuelto. Stock actualizado.');
    }

    /**
     * Marcar como DAÑADO/PERDIDO (Baja)
     */
    public function reportarIncidencia($id, Request $request)
    {
        $asignacion = Asignacion::findOrFail($id);
        
        // Si estaba en posesión, cambiamos estado pero NO sumamos al stock (se pierde)
        if ($asignacion->estado == 'Entregado') {
            $estado = $request->input('estado', 'Dañado'); // Dañado o Perdido
            $asignacion->update(['estado' => $estado]);
            
            // Incrementamos el contador de deteriorados/bajas en el inventario global
            $asignacion->epp->increment('deteriorado', $asignacion->cantidad);
        }

        return back()->with('warning', 'Equipo marcado como ' . $request->input('estado') . '.');
    }
}
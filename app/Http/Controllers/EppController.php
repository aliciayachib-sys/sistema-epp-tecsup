<?php

namespace App\Http\Controllers;

use App\Models\Epp;
use App\Models\Departamento;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Imports\EppImport;
use App\Services\ExcelImageExtractor;
use Maatwebsite\Excel\Facades\Excel;

class EppController extends Controller
{
    public function index()
    {
        $epps = Epp::with('categoria')->get();
        // La vista de filtros y el modal de creación necesitan las categorías
        $categorias = Categoria::all();
        return view('epps.index', compact('epps', 'categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'cantidad' => 'nullable|integer|min:0',
            'vida_util_meses' => 'nullable|integer|min:1',
            'fecha_ingreso_almacen' => 'nullable|date',
            'fecha_registro' => 'nullable|date',
        ]);

        $data = $request->except(['fecha_vencimiento']); // No permitimos setear vencimiento manualmente

        // Valores por defecto inteligentes
        $data['tipo'] = $request->tipo ?? 'Protección de seguridad';
        $data['stock'] = $request->cantidad ?? 0;
        $data['entregado'] = 0;
        $data['deteriorado'] = 0;
        $data['vida_util_meses'] = $request->vida_util_meses ?? 12; // Valor por defecto: 1 año

        // Base date: preferir fecha_ingreso_almacen; si no, fecha_registro; si no, now
        $baseDate = null;
        if ($request->filled('fecha_ingreso_almacen')) {
            $baseDate = Carbon::parse($request->fecha_ingreso_almacen);
        } elseif ($request->filled('fecha_registro')) {
            $baseDate = Carbon::parse($request->fecha_registro);
        } else {
            $baseDate = now();
        }

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('epps', 'public');
        }

        $epp = Epp::create($data);

        // Sincronizar created_at para mantener consistencia histórica en accesorios/reportes
        $epp->update(['created_at' => $baseDate]);

        return redirect()->route('epps.index')->with('success', 'EPP registrado correctamente');
    }

    public function show($id)
    {
        $epp = Epp::with('departamento')->findOrFail($id);
        return view('epps.show', compact('epp'));
    }

    public function edit($id)
    {
        $epp = Epp::findOrFail($id);
        $categorias = Categoria::all();
        $departamentos = Departamento::all(); 
        return view('epps.edit', compact('epp', 'categorias', 'departamentos'));
    }

    public function update(Request $request, $id)
    {
        $epp = Epp::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'cantidad' => 'nullable|integer|min:0',
            'vida_util_meses' => 'nullable|integer|min:1',
            'codigo_logistica' => 'nullable|string',
            'fecha_ingreso_almacen' => 'nullable|date',
            'fecha_registro' => 'nullable|date',
        ]);

        $data = $request->except(['fecha_vencimiento', 'fecha_ingreso_almacen']); // Bloquear edición manual y campo inexistente

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('epps', 'public');
        }

        // Si se actualiza la cantidad total, recalcular el stock disponible
        if ($request->has('cantidad')) {
            $entregado = $epp->entregado ?? 0;
            $deteriorado = $epp->deteriorado ?? 0;
            $data['stock'] = $request->cantidad - $entregado - $deteriorado;
        }

        $epp->fill($data);

        // Sincronizar fechas base
        if ($request->filled('fecha_ingreso_almacen')) {
            $fecha = Carbon::parse($request->fecha_ingreso_almacen);
            // Opcional: si quieres alinear created_at:
            $epp->created_at = $fecha;
        } elseif ($request->filled('fecha_registro')) {
            $epp->created_at = Carbon::parse($request->fecha_registro);
        }

        $epp->save(); // El modelo recalculará fecha_vencimiento en saving
        return redirect()->route('epps.index')->with('success', 'EPP actualizado correctamente');
    }

    public function destroy($id)
    {
        Epp::findOrFail($id)->delete();
        return redirect()->route('epps.index')->with('success', 'EPP eliminado');
    }

    public function import(Request $request) 
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        $request->validate(['fecha_registro' => 'nullable|date']);
        try {
            $fechaRegistro = $request->input('fecha_registro');
            // Paso 1: Extraer imágenes del Excel por nombre de EPP
            $archivoPath = $request->file('file')->getRealPath();
            $imagenesPorNombre = ExcelImageExtractor::extraerImagenesConNombres($archivoPath);
            
            \Log::info('Imágenes extraídas: ' . count($imagenesPorNombre));
            // Paso 2: Importar datos con el mapeo de imágenes por nombre
            Excel::import(new EppImport($imagenesPorNombre, $fechaRegistro), $request->file('file'));
            
            return back()->with('success', '¡Matriz importada correctamente con imágenes!');
        } catch (\Exception $e) {
            \Log::error('Error en importación de EPP: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function clearAll()
{
    // Obtenemos todos los EPP que tienen imagen
    $eppsConImagen = Epp::whereNotNull('imagen')->get();
    
    foreach ($eppsConImagen as $epp) {
        \Storage::disk('public')->delete($epp->imagen);
    }

    // Ahora sí, vaciamos la tabla
    Epp::query()->delete(); 
    
    return back()->with('success', 'Inventario y archivos vaciados correctamente');
}
}
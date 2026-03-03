<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Epp extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 'tipo', 'descripcion', 'vida_util_meses', 'ficha_tecnica',
        'imagen', 'frecuencia_entrega', 'codigo_logistica', 'marca_modelo',
        'precio', 'cantidad', 'stock', 'entregado', 'deteriorado',
        'departamento_id', 'categoria_id', 'subcategoria_id', 'estado', 'fecha_vencimiento', 'activo',
        'fecha_ingreso_almacen',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'datetime',
        'fecha_ingreso_almacen' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Lógica Automática: Antes de guardar, traduce la frecuencia a meses numéricos.
     */
    protected static function booted()
    {
        static::saving(function ($epp) {
            // Mapear frecuencia a meses si viene en texto
            if ($epp->frecuencia_entrega) {
                $f = strtolower($epp->frecuencia_entrega);
                if (str_contains($f, '5 años')) $epp->vida_util_meses = 60;
                elseif (str_contains($f, '4 años')) $epp->vida_util_meses = 48;
                elseif (str_contains($f, '3 años')) $epp->vida_util_meses = 36;
                elseif (str_contains($f, '2 años')) $epp->vida_util_meses = 24;
                elseif (str_contains($f, '1 año'))  $epp->vida_util_meses = 12;
                elseif (str_contains($f, '6 meses')) $epp->vida_util_meses = 6;
            }

            // Recalcular fecha_vencimiento SIEMPRE en base a fecha_ingreso_almacen o created_at
            $base = null;
            if (!empty($epp->fecha_ingreso_almacen)) {
                $base = $epp->fecha_ingreso_almacen instanceof Carbon
                    ? $epp->fecha_ingreso_almacen
                    : Carbon::parse($epp->fecha_ingreso_almacen);
            } elseif ($epp->created_at instanceof Carbon) {
                $base = $epp->created_at->copy();
            }

            if ($base && !empty($epp->vida_util_meses)) {
                $epp->fecha_vencimiento = $base->copy()->addMonths((int) $epp->vida_util_meses);
            }
        });
    }

    /**
     * Accessor: Fecha de vencimiento efectiva.
     * Prioridad 1: fecha_vencimiento almacenada.
     * Prioridad 2: fecha de ingreso/registro (usamos created_at como proxy) + vida_util_meses.
     */
    public function getVencimientoRealAttribute()
    {
        if ($this->fecha_vencimiento instanceof Carbon) {
            return $this->fecha_vencimiento;
        }

        // Usamos created_at como fecha de ingreso si no hay un campo específico
        if ($this->created_at instanceof Carbon && !empty($this->vida_util_meses)) {
            return $this->created_at->copy()->addMonths((int) $this->vida_util_meses);
        }

        return null;
    }

    // Relaciones
    public function categoria() { return $this->belongsTo(Categoria::class); }
    public function subcategoria() { return $this->belongsTo(Subcategoria::class); }
    public function departamento() { return $this->belongsTo(Departamento::class); }
}
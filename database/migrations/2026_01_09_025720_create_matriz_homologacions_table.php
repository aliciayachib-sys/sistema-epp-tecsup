<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('matriz_homologacions', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('departamento_id')->constrained('departamentos')->onDelete('cascade');
            $table->foreignId('epp_id')->constrained('epps')->onDelete('cascade');
            
            // Configuración
            $table->string('puesto')->nullable()->comment('Puesto o rol específico');
            $table->string('taller')->nullable()->comment('Taller o laboratorio');
            $table->enum('tipo_requerimiento', ['obligatorio', 'especifico', 'opcional'])->default('especifico');
            $table->text('observaciones')->nullable();
            
            // Estado
            $table->boolean('activo')->default(true);
            
            $table->timestamps();
            
            // Índices
            $table->unique(['departamento_id', 'epp_id', 'puesto', 'taller'], 'matriz_unique_custom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matriz_homologacions');
    }
};

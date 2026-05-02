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
        Schema::create('evento_calendarios', function (Blueprint $table) {
            $table->id(); // La llave primaria automática
            $table->string('titulo'); // Ej: "Prueba RCP"
            $table->date('fecha'); // El día en el calendario
            $table->time('hora')->nullable(); // Hora (opcional)
            $table->text('descripcion')->nullable(); // Detalles extra (opcional)
            $table->timestamps(); // Guarda automáticamente cuándo se creó y actualizó
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evento_calendarios');
    }
};

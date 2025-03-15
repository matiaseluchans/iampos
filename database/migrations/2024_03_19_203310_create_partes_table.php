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
        Schema::create('partes', function (Blueprint $table) {
            $table->id();
            $table->string("estructura_organica_id", 50);
            $table->string("estructura_organica", 1000);
            $table->integer('numero');
            $table->integer('numero_unidad');
            $table->integer('anio');
            //json con los datos del usuario que creo el parte
            $table->string("completado_por", 1000)->nullable();
            $table->datetime("fecha_informe")->nullable();
            $table->datetime("fecha_hecho")->nullable();
            $table->text("relato");
            $table->string("parte_relacionado", 50)->nullable();
            $table->string("observaciones", 1000)->nullable();
            $table->string("resumido", 1000)->nullable();
            $table->unsignedBigInteger('estado_id');
            $table->string("motivo_estado", 1000)->nullable();
            //json con los datos del usuario d drlpa q autoriza el parte
            $table->string("responsable_drlpa", 1000)->nullable();
            //json con los datos del usuario d dopa q autoriza el parte
            $table->string("responsable_dopa", 1000)->nullable();
            $table->string("created_by", 500)->nullable();
            $table->string("last_modified_by", 500)->nullable();
            $table->string("deleted_by", 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('estado_id')->references('id')->on('estados');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partes');
    }
};

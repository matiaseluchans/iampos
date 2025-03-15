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
        Schema::create('clasificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clasificacion_id')->nullable();                        
            $table->string('detalle',1000);
            $table->boolean('activo')->nullable()->default(1);                                    
            $table->string("created_by", 1000)->nullable();
            $table->string("last_modified_by", 1000)->nullable();
            $table->string("deleted_by", 1000)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('clasificacion_id')->references('id')->on('clasificaciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clasificaciones');
    }
};

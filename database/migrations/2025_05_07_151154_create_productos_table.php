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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();


            $table->string('nombre');
            $table->unsignedBigInteger('productos_categorias_id')->nullable();
            //$table->integer('proveedor_id');
            $table->string('codigo')->nullable();
            //$table->string('deposito')->nullable();
            $table->string('image')->nullable();

            /*
            $table->date('fecha_compra')->nullable();
            $table->string('fecha_vencimiento')->nullable();
            */
            $table->float('precio_compra')->nullable();
            $table->float('precio_venta')->nullable();

            $table->boolean('activo')->nullable()->default(1);
            $table->string('created_by', 1000)->nullable();
            $table->string('last_modified_by', 1000)->nullable();
            $table->string('deleted_by', 1000)->nullable();

            $table->unsignedBigInteger('tenant_id');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};

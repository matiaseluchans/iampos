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
        Schema::create('products', function (Blueprint $table) {
            $table->id();


            $table->string('name');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            //$table->integer('proveedor_id');
            $table->string('code')->nullable();
            //$table->string('deposito')->nullable();
            $table->string('image')->nullable();

            /*
            $table->date('fecha_compra')->nullable();
            $table->string('fecha_vencimiento')->nullable();
            */
            $table->float('purchase_price')->nullable();
            $table->float('sale_price')->nullable();

            $table->boolean('active')->nullable()->default(1);
            $table->string('created_by', 1000)->nullable();
            $table->string('last_modified_by', 1000)->nullable();
            $table->string('deleted_by', 1000)->nullable();

            $table->unsignedBigInteger('tenant_id');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('set null'); // Cambiado a set null para mantener el producto

            $table->foreign('brand_id')
                ->references('id')
                ->on('brands')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

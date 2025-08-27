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
        Schema::create('price_list_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('price_list_id');
            $table->unsignedBigInteger('product_id');
            $table->float('sale_price');


            // Claves foráneas
            $table->foreign('price_list_id')
                ->references('id')
                ->on('price_lists')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');

            // Asegurar que la combinación de product_id y price_list_id sea única
            $table->unique(['price_list_id', 'product_id']);

            $table->unsignedBigInteger('tenant_id');

            $table->string('created_by', 1000)->nullable();
            $table->string('last_modified_by', 1000)->nullable();
            $table->string('deleted_by', 1000)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            $table->index(['tenant_id']); // para búsquedas rápidas

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_list_product');
    }
};

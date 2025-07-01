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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();

            // Relación con el producto
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');

            // Relación con el depósito/ubicación
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('set null');

            // Cantidades
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0); // Para pedidos pendientes
            $table->integer('minimum_stock')->nullable();
            $table->integer('maximum_stock')->nullable();

            // Información de lote/caducidad (opcional)
            //$table->string('batch_number')->nullable();
            //$table->date('expiration_date')->nullable();

            // Auditoría
            $table->unsignedBigInteger('tenant_id');
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');



            $table->string('created_by', 1000)->nullable();
            $table->string('last_modified_by', 1000)->nullable();
            $table->string('deleted_by', 1000)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices para mejor performance
            $table->index(['product_id', 'warehouse_id']);
            //$table->index(['expiration_date']);
        });

        // Si necesitas historial de movimientos de stock
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('stock_id');
            $table->foreign('stock_id')
                ->references('id')
                ->on('stocks')
                ->onDelete('cascade');

            $table->enum('movement_type', ['entrada', 'salida', 'ajuste', 'transferencia']);
            $table->integer('quantity');
            $table->integer('previous_quantity');
            $table->integer('new_quantity');

            // Relación con documento origen (factura, pedido, etc.)
            //$table->string('source_document_type')->nullable();
            //$table->unsignedBigInteger('source_document_id')->nullable();

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('tenant_id');
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->string('created_by', 1000)->nullable();
            $table->string('last_modified_by', 1000)->nullable();
            $table->string('deleted_by', 1000)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stocks');
    }
};

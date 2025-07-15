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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->dateTime('order_date');
            $table->string('order_number', 50);
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->text('customer_details')->nullable();
            $table->text('shipping_address')->nullable();                        
            $table->foreignId('status_id')->nullable()->constrained('statuses')->onDelete('set null');            
            $table->foreignId('order_type_id')->nullable()->constrained('order_types')->onDelete('set null');
            $table->boolean('shipping')->default(0);
            //$table->foreignId('shipping_status_id')->nullable()->constrained('shipping_statuses')->onDelete('set null');
            $table->integer('quantity_products')->default(0);            
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);  
            $table->decimal('total_cost', 10, 2)->default(0);  
            $table->decimal('total_profit', 10, 2)->default(0);  
            //$table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->onDelete('set null');
            //$table->foreignId('payment_status_id')->nullable()->constrained('payment_statuses')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('tenant_id');
            
            $table->string('created_by', 1000)->nullable();
            $table->string('last_modified_by', 1000)->nullable();
            $table->string('deleted_by', 1000)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('customer_id');
            $table->index('status_id');
            $table->index(['order_number', 'tenant_id']); // para búsquedas rápidas
            $table->unique(['order_number', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

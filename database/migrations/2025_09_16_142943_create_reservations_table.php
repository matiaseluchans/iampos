<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('service_type_id')->constrained();
            $table->foreignId('resource_id')->nullable()->constrained();
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->integer('time_units')->default(1);
            $table->integer('required_capacity')->default(1);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])->default('pending');
            $table->integer('participants_count')->default(1);
            $table->text('special_requirements')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->text('notes')->nullable();
            
            $table->unsignedBigInteger('tenant_id');
            $table->string('created_by', 1000)->nullable();
            $table->string('last_modified_by', 1000)->nullable();
            $table->string('deleted_by', 1000)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index(['tenant_id']); // para búsquedas rápidas

            // Índices para optimizar consultas
            $table->index(['resource_id', 'start_time', 'end_time']);
            $table->index(['customer_id', 'start_time']);
            $table->index(['status', 'start_time']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reservations');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->integer('duration_minutes');
            $table->enum('time_unit', ['minutes', 'hours', 'days'])->default('hours');
            $table->integer('min_units')->default(1);
            $table->integer('max_units')->nullable();
            $table->integer('max_participants')->nullable();
            $table->boolean('requires_resource')->default(false);
            $table->foreignId('resource_type_id')->nullable()->constrained();
            $table->boolean('requires_capacity')->default(false);
            $table->integer('max_capacity_per_reservation')->nullable();
            
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

    public function down()
    {
        Schema::dropIfExists('service_types');
    }
};
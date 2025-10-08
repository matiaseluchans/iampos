<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_type_id')->constrained()->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('conditions');
            $table->enum('modification_type', ['fixed_amount', 'percentage', 'fixed_rate']);
            $table->decimal('modification_value', 10, 2);
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->datetime('valid_from')->nullable();
            $table->datetime('valid_until')->nullable();
            
            $table->unsignedBigInteger('tenant_id');
            $table->string('created_by', 1000)->nullable();
            $table->string('last_modified_by', 1000)->nullable();
            $table->string('deleted_by', 1000)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index(['tenant_id']); // para búsquedas rápidas

            $table->index(['service_type_id', 'is_active']);
            $table->index(['valid_from', 'valid_until']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pricing_rules');
    }
};
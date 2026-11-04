<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('business_name')->nullable();
            $table->string('tax_id', 20)->nullable();
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone', 50)->nullable();
            $table->string('address')->nullable();
            $table->foreignId('locality_id')->nullable()->constrained('localities')->onDelete('set null');
            $table->unsignedSmallInteger('payment_term_days')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('active')->default(1);

            $table->unsignedBigInteger('tenant_id');
            $table->string('created_by', 1000)->nullable();
            $table->string('last_modified_by', 1000)->nullable();
            $table->string('deleted_by', 1000)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tax_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};

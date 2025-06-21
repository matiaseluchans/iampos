<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('address');
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->unsignedBigInteger('locality_id')->nullable();
            $table->string('companyname')->nullable();

            $table->string('created_by', 1000)->nullable();
            $table->string('last_modified_by', 1000)->nullable();
            $table->string('deleted_by', 1000)->nullable();

            $table->unsignedBigInteger('tenant_id');
            $table->boolean('active')->nullable()->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('locality_id')
                ->references('id')
                ->on('localities')
                ->onDelete('set null');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

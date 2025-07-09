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
        Schema::table('statuses', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
            $table->index(['code', 'tenant_id']); // para búsquedas rápidas
            $table->unique(['code', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->dropIndex(['code', 'tenant_id']);
            $table->dropColumn('code');
        });
    }
};

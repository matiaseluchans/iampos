<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Eliminar campos específicos y usar solo JSON
            $table->json('features')->nullable()->after('participants_count');
            
            // Opcional: mantener algunos campos comunes si se desea
            $table->string('customer_reference')->nullable()->after('features');
        });
    }

    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['features', 'customer_reference']);
        });
    }
};
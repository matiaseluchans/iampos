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
        Schema::table('orders', function (Blueprint $table) {
            // Si el vendedor es un ID de usuario (relación)
            /*$table->foreignId('seller_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('ID del vendedor asociado a la orden');*/
            
            // O si es simplemente un nombre de vendedor (string)
            // $table->string('seller_name')->nullable()->comment('Nombre del vendedor');
            
            // O ambos campos si necesitas:
             //$table->foreignId('seller_id')->nullable()->constrained('users')->onDelete('set null');
             $table->foreignId('seller_id')->nullable();
             $table->string('seller_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Eliminar la relación primero si existe
            //$table->dropForeign(['seller_id']);
            
            // Luego eliminar las columnas
            //$table->dropColumn(['seller_id']);
            // $table->dropColumn(['seller_name']);
            // O ambos si los agregaste:
            $table->dropColumn(['seller_id', 'seller_name']);
        });
    }
};
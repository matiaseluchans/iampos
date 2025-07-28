<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_paid', 10, 2)->nullable()->after('total_amount');
            // Adjust the 'after' clause to place the column where you want it
            // You can also use ->nullable() instead of ->default(0) if appropriate
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('total_paid');
        });
    }
};

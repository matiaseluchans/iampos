<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {       
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->onDelete('cascade');
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->onDelete('cascade');
            $table->string('transaction_id')->nullable();            
        });
    }

    public function down()
    {        
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('reservation_id');
            $table->dropColumn('quotation_id');
            $table->dropColumn('transaction_id');
            $table->dropForeign(['reservation_id']);
            $table->dropForeign(['quotation_id']);
        });
    }
};
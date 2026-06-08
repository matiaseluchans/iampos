<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('tenant_id', 'products_tenant_id_idx');
            $table->index('category_id', 'products_category_id_idx');
            $table->index('brand_id', 'products_brand_id_idx');
            $table->index(['tenant_id', 'active'], 'products_tenant_active_idx');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->index('tenant_id', 'stocks_tenant_id_idx');
            $table->index('warehouse_id', 'stocks_warehouse_id_idx');
            $table->index(['tenant_id', 'product_id'], 'stocks_tenant_product_idx');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index('tenant_id', 'stock_movements_tenant_id_idx');
            $table->index(['stock_id', 'created_at'], 'stock_movements_stock_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_tenant_id_idx');
            $table->dropIndex('products_category_id_idx');
            $table->dropIndex('products_brand_id_idx');
            $table->dropIndex('products_tenant_active_idx');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropIndex('stocks_tenant_id_idx');
            $table->dropIndex('stocks_warehouse_id_idx');
            $table->dropIndex('stocks_tenant_product_idx');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('stock_movements_tenant_id_idx');
            $table->dropIndex('stock_movements_stock_date_idx');
        });
    }
};

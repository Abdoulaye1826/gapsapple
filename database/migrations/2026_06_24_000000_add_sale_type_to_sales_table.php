<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sales', 'sale_type')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->enum('sale_type', ['vente', 'echange'])
                    ->default('vente')
                    ->after('sale_date')
                    ->comment('Type de transaction : vente ou échange');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales', 'sale_type')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('sale_type');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'sold_at')) {
                $table->timestamp('sold_at')->nullable()->after('sale_date')->comment('Horodatage serveur de la vente');
            }

            if (! Schema::hasColumn('sales', 'exchange_voucher_number')) {
                $table->string('exchange_voucher_number', 50)->nullable()->after('notes')->comment('Numéro du bon d\'échange');
            }

            if (! Schema::hasColumn('sales', 'exchange_details')) {
                $table->json('exchange_details')->nullable()->after('exchange_voucher_number')->comment('Détails du produit reçu lors de l\'échange');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'exchange_details')) {
                $table->dropColumn('exchange_details');
            }
            if (Schema::hasColumn('sales', 'exchange_voucher_number')) {
                $table->dropColumn('exchange_voucher_number');
            }
            if (Schema::hasColumn('sales', 'sold_at')) {
                $table->dropColumn('sold_at');
            }
        });
    }
};

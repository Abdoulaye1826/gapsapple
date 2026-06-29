<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : prix de vente spécifique lorsque le produit est vendu à un
 * fournisseur agissant comme client (certains fournisseurs revendent aussi
 * à titre personnel).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('supplier_sale_price', 12, 2)->nullable()->after('sale_price')
                ->comment('Prix de vente appliqué quand le client est en réalité un fournisseur');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('supplier_sale_price');
        });
    }
};

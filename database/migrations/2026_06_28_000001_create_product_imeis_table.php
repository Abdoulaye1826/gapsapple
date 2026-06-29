<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : traçabilité unité par unité des produits suivis par IMEI
 * (téléphones). Un téléphone = une ligne, jamais une simple quantité.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_imeis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('imei', 20)->unique();
            $table->enum('status', ['available', 'reserved', 'sold'])->default('available');

            // Vente ayant sorti ce téléphone du stock (IMEI vendu).
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();

            // Vente d'échange via laquelle ce téléphone est entré en stock
            // (apporté par un client). Il n'existe pas de table "exchanges"
            // séparée dans ce projet : un échange est une vente avec
            // sale_type = echange.
            $table->foreignId('exchange_sale_id')->nullable()->constrained('sales')->nullOnDelete();

            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_imeis');
    }
};

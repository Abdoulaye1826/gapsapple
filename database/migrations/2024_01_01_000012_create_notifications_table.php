<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : notifications Système (stock faible, rupture, commandes, livraisons).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->comment('Destinataire de la notification');
            $table->string('type', 100)->comment('Type : low_stock, out_of_stock, new_order');
            $table->string('title', 200);
            $table->text('message');
            $table->json('data')->nullable()->comment('Données contextuelles (product_id, delivery_id, etc.)');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('type');
            $table->index('read_at');
            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

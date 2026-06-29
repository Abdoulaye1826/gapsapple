<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : traçabilité réelle des paiements de factures
 * (Wave, Orange Money, Espèces).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->comment('Montant encaissé');
            $table->enum('method', ['wave', 'orange_money', 'cash'])->comment('Mode de paiement');
            $table->date('paid_at')->comment('Date de l\'encaissement');
            $table->string('reference', 100)->nullable()->comment('Référence transaction Wave/Orange Money');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('method');
            $table->index('paid_at');
        });

        // Élargissement de la colonne enum "status" pour accepter "partial".
        if (DB::connection()->getDriverName() === 'mysql') {
            // Sans doctrine/dbal : SQL natif.
            DB::statement("ALTER TABLE invoices MODIFY status ENUM('issued', 'partial', 'paid', 'cancelled') NOT NULL DEFAULT 'issued' COMMENT 'Statut de la facture (recalculé automatiquement selon les paiements)'");
        } else {
            // SQLite représente l'enum via une contrainte CHECK figée à la création :
            // on recrée la colonne (pas de données à préserver en environnement de test).
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropIndex('invoices_status_index');
                $table->dropColumn('status');
            });
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('status')->default('issued');
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY status ENUM('issued', 'paid', 'cancelled') NOT NULL DEFAULT 'issued'");
        }

        Schema::dropIfExists('payments');
    }
};

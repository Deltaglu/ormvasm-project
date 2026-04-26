<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agriculteurs', function (Blueprint $table) {
            $table->index(['nom', 'prenom']);
            $table->index('cin');
        });

        Schema::table('paiements', function (Blueprint $table) {
            $table->index('reference');
            $table->index('date_paiement');
        });

        Schema::table('titres_recettes', function (Blueprint $table) {
            $table->index('numero');
            $table->index('statut');
            $table->index('date_echeance');
        });

        Schema::table('prestations', function (Blueprint $table) {
            $table->index('libelle');
        });
    }

    public function down(): void
    {
        Schema::table('agriculteurs', function (Blueprint $table) {
            $table->dropIndex(['nom', 'prenom']);
            $table->dropIndex(['cin']);
        });
        // ... and so on for others if needed
    }
};

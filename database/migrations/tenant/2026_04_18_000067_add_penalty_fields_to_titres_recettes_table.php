<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->table('titres_recettes', function (Blueprint $table) {
            $table->date('date_echeance')->nullable()->after('date_emission');
            $table->decimal('montant_penalite', 12, 2)->default(0)->after('solde_restant');
            $table->boolean('penalite_appliquee')->default(false)->after('montant_penalite');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('titres_recettes', function (Blueprint $table) {
            $table->dropColumn(['date_echeance', 'montant_penalite', 'penalite_appliquee']);
        });
    }
};

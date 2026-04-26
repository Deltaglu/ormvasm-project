<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('titres_recettes', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->date('date_emission');
            $table->decimal('montant_total', 12, 2);
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->decimal('solde_restant', 12, 2);
            $table->string('statut', 32)->default('PARTIEL');
            $table->text('objet')->nullable();
            $table->foreignId('agriculteur_id')->constrained('agriculteurs');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('titres_recettes');
    }
};


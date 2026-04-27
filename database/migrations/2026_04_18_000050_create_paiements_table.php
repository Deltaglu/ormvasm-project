<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->string('reference')->unique();
            $table->date('date_paiement');
            $table->decimal('montant', 12, 2);
            $table->enum('type_paiement', ['ESPECES', 'CHEQUE', 'VIREMENT']);
            $table->string('statut', 32)->default('VALIDE');
            $table->string('numero_cheque', 64)->nullable();
            $table->foreignId('titre_recette_id')->constrained('titres_recettes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quittances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->string('numero')->unique();
            $table->dateTime('date_generation');
            $table->string('chemin_pdf');
            $table->decimal('montant_paye', 12, 2);
            $table->foreignId('paiement_id')->unique()->constrained('paiements');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quittances');
    }
};


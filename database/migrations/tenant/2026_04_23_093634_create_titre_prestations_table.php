<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('titre_prestations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('titre_recette_id')->constrained('titres_recettes')->onDelete('cascade');
            $table->foreignId('prestation_id')->constrained('prestations')->onDelete('cascade');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
            
            $table->index(['titre_recette_id', 'prestation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('titre_prestations');
    }
};

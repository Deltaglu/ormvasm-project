<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('penalty_percentage', 5, 2)->default(0)->comment('Pourcentage de pénalité sur le solde restant après échéance');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('settings');
    }
};

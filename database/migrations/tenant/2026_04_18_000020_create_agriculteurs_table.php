<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('agriculteurs', function (Blueprint $table) {
            $table->id();
            $table->string('cin', 32)->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->text('adresse')->nullable();
            $table->string('telephone', 32)->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('agriculteurs');
    }
};


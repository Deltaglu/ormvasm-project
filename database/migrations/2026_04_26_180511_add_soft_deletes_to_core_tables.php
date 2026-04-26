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
        $tables = ['agriculteurs', 'prestations', 'paiements', 'titres_recettes'];

        foreach ($tables as $table) {
            Schema::connection('tenant')->table($table, function (Blueprint $table) {
                if (!Schema::connection('tenant')->hasColumn($table->getTable(), 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['agriculteurs', 'prestations', 'paiements', 'titres_recettes'];

        foreach ($tables as $table) {
            Schema::connection('tenant')->table($table, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};

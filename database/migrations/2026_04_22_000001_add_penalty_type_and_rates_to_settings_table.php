<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Only add columns if they don't exist
            if (!Schema::hasColumn('settings', 'penalty_type')) {
                $table->enum('penalty_type', ['monthly_recurring', 'one_time'])
                    ->default('monthly_recurring')
                    ->after('penalty_percentage');
            }
            
            if (!Schema::hasColumn('settings', 'monthly_penalty_rate')) {
                $table->decimal('monthly_penalty_rate', 5, 2)->default(5.00)
                    ->after('penalty_type');
            }
            
            if (!Schema::hasColumn('settings', 'one_time_penalty_rate')) {
                $table->decimal('one_time_penalty_rate', 5, 2)->default(2.00)
                    ->after('monthly_penalty_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['penalty_type', 'monthly_penalty_rate', 'one_time_penalty_rate']);
        });
    }
};
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
        Schema::table('agriculteurs', function (Blueprint $table) {
            $table->enum('type', ['individual', 'society'])->default('individual')->after('id');
            $table->foreignId('parent_id')->nullable()->after('type')->constrained('agriculteurs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agriculteurs', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['type', 'parent_id']);
        });
    }
};

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
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('overall_rating', 3, 1)->nullable()->default(0);
            $table->dropColumn('rating');
            $table->tinyInteger('performance_rating')->default(0);
            $table->tinyInteger('professional_skills_rating')->default(0);
            $table->tinyInteger('teamwork_communication_rating')->default(0);
            $table->tinyInteger('attitude_behaviour_rating')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('overall_rating');
            $table->tinyInteger('rating')->nullable()->default(0);
            $table->dropColumn([
                'performance_rating',
                'professional_skills_rating',
                'teamwork_communication_rating',
                'attitude_behaviour_rating',
            ]);
        });
    }
};

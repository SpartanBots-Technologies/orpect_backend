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
            $table->string('position')->nullable()->change();
            $table->date('date_of_birth')->nullable();
            $table->string('emp_pan')->nullable();
            $table->longText('permanent_address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('linked_in')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('position')->nullable(false)->change();
            $table->dropColumn('date_of_birth');
            $table->dropColumn('emp_pan');
            $table->dropColumn('permanent_address');
            $table->dropColumn('city');
            $table->dropColumn('country');
            $table->dropColumn('state');
            $table->dropColumn('linked_in');
        });
    }
};

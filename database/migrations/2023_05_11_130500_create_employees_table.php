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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id');
            $table->string('emp_name');
            $table->string('email');
            $table->string('phone');
            $table->string('position');
            $table->date('date_of_joining')->nullable();
            $table->string('profile_image')->nullable();
            $table->tinyInteger('ex_employee')->default(0);
            $table->tinyInteger('non_joiner')->default(0);
            $table->date('date_of_leaving')->nullable();
            $table->tinyInteger('rating')->nullable()->default(0);
            $table->longText('review')->nullable();
            $table->bigInteger('added_by');
            $table->timestamps();
            $table->tinyInteger('is_deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

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
        Schema::create('employee_logins', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('username')->nullable();
            $table->string('gender');
            $table->string('phone');
            $table->string('email');
            $table->string('password');
            $table->string('profile_image')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('linked_in')->nullable();
            $table->tinyInteger('taken_membership')->default(0);
            $table->tinyInteger('is_deleted')->default(0);
            $table->tinyInteger('deleted_at')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_logins');
    }
};

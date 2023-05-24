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
        Schema::table('users', function (Blueprint $table) {
            $table->string('company_phone')->nullable();
            $table->string('webmaster_email')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_city')->nullable();
            $table->string('company_state')->nullable();
            $table->string('company_country')->nullable();
            $table->string('company_postal_code')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('company_social_link')->nullable();
            $table->tinyInteger('is_account_verified')->nullable()->default(0);
            $table->Integer('deleted_by')->nullable()->after('is_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('company_phone');
            $table->dropColumn('webmaster_email');
            $table->dropColumn('company_address');
            $table->dropColumn('company_city');
            $table->dropColumn('company_state');
            $table->dropColumn('company_country');
            $table->dropColumn('company_postal_code');
            $table->dropColumn('registration_number');
            $table->dropColumn('company_social_link');
            $table->dropColumn('is_account_verified');
            $table->dropColumn('deleted_by');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Basic Information
            $table->string('company_size')->nullable()->after('industry'); // dropdown value
            $table->string('city')->nullable()->after('company_size');
            $table->string('country')->nullable()->after('city');
            $table->year('founded_year')->nullable()->after('country');

            // About Company
            $table->text('short_description')->nullable()->after('description');
            $table->longText('overview')->nullable()->after('short_description');
            $table->text('why_join_us')->nullable()->after('overview');

            // Workforce & Departments
            $table->unsignedInteger('total_employees')->nullable()->after('why_join_us');
            $table->unsignedInteger('dept_it')->nullable()->after('total_employees');
            $table->unsignedInteger('dept_hr')->nullable()->after('dept_it');
            $table->unsignedInteger('dept_marketing')->nullable()->after('dept_hr');
            $table->unsignedInteger('dept_sales')->nullable()->after('dept_marketing');
            $table->unsignedInteger('dept_finance')->nullable()->after('dept_sales');
            $table->unsignedInteger('dept_operations')->nullable()->after('dept_finance');

            // Contact & Online Presence
            $table->string('company_email')->nullable()->after('website');
            $table->string('linkedin_url')->nullable()->after('company_email');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'company_size','city','country','founded_year',
                'short_description','overview','why_join_us',
                'total_employees','dept_it','dept_hr','dept_marketing','dept_sales','dept_finance','dept_operations',
                'company_email','linkedin_url'
            ]);
        });
    }
};
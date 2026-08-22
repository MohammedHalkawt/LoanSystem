<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->string('drive_folder_id')->nullable()->after('status');
            $table->string('purchase_receipt_file_id')->nullable()->after('drive_folder_id');
        });

        Schema::table('rent_payments', function (Blueprint $table) {
            $table->string('covered_month_from', 7)->nullable()->after('amount');
            $table->string('covered_month_to', 7)->nullable()->after('covered_month_from');
            $table->unsignedInteger('months_count')->default(1)->after('covered_month_to');
            $table->string('receipt_drive_file_id')->nullable()->after('receipt_path');
        });
    }

    public function down(): void
    {
        Schema::table('rent_payments', function (Blueprint $table) {
            $table->dropColumn([
                'covered_month_from',
                'covered_month_to',
                'months_count',
                'receipt_drive_file_id',
            ]);
        });

        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn([
                'drive_folder_id',
                'purchase_receipt_file_id',
            ]);
        });
    }
};

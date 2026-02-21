<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('car_name');
            $table->integer('model_year');
            $table->decimal('overall_price', 10, 2);
            $table->decimal('basic_price', 10, 2);
            $table->decimal('upfront_payment', 10, 2);
            $table->timestamp('purchase_date')->useCurrent();
            $table->timestamps();

            // Optional indexes for faster searching
            $table->index('purchase_date');
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 100)->unique();
            $table->integer('weight_grams'); // 200, 500, 1000
            $table->string('grind_size', 50); // Whole Bean, Fine, Medium, Coarse
            $table->decimal('price', 12, 2);
            $table->integer('stock')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'weight_grams', 'grind_size']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
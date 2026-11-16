<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('barcode', 100)->unique()->index();
            $table->string('sku', 100)->unique()->index();
            $table->string('name');
            $table->string('unit', 30)->default('pcs');
            $table->decimal('purchase_price', 15, 2)->default(0); // Harga modal/beli
            $table->decimal('selling_price', 15, 2)->default(0);  // Harga jual ke pelanggan
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['name', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};


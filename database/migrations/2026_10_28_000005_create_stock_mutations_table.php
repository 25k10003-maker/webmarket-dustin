<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users'); // Kasir / Gudang yang melakukan perubahan
            $table->enum('type', ['in', 'out_sale', 'adjustment_in', 'adjustment_out']);
            $table->integer('quantity'); // Jumlah perubahan
            $table->integer('previous_stock'); // Stok sebelum mutasi
            $table->integer('current_stock');  // Stok setelah mutasi
            $table->string('reference_number')->nullable()->index(); // No Nota/Invoice/PO
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_mutations');
    }
};


<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Role;
use App\Models\StockMutation;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RetailSystemTest extends TestCase
{
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Webmarket Dustin');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/kasir');
        $response->assertRedirect('/login');

        $response = $this->get('/gudang/products');
        $response->assertRedirect('/login');

        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_kasir_role_access(): void
    {
        $kasir = User::whereHas('role', fn($q) => $q->where('name', 'kasir'))->first();
        $this->actingAs($kasir);

        // Can access kasir
        $response = $this->get('/kasir');
        $response->assertStatus(200);

        // Cannot access admin dashboard -> redirected to kasir POS
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect(route('kasir.pos'));
    }

    public function test_db_transaction_and_atomic_stock_deduction(): void
    {
        $kasir = User::whereHas('role', fn($q) => $q->where('name', 'kasir'))->first();
        $product = Product::first();

        $initialStock = $product->stock;
        $qtyToBuy = 2;

        DB::beginTransaction();
        try {
            // Pessimistic lock
            $lockedProduct = Product::where('id', $product->id)->lockForUpdate()->first();
            $this->assertGreaterThanOrEqual($qtyToBuy, $lockedProduct->stock);

            $prevStock = $lockedProduct->stock;
            $newStock = $prevStock - $qtyToBuy;
            $lockedProduct->update(['stock' => $newStock]);

            $trx = Transaction::create([
                'invoice_code' => 'TRX-TEST-' . time(),
                'user_id' => $kasir->id,
                'customer_name' => 'Pelanggan Uji',
                'total_items' => $qtyToBuy,
                'subtotal' => $qtyToBuy * $product->selling_price,
                'discount' => 0,
                'grand_total' => $qtyToBuy * $product->selling_price,
                'paid_amount' => $qtyToBuy * $product->selling_price,
                'change_amount' => 0,
                'payment_method' => 'tunai',
                'payment_status' => 'paid',
            ]);

            TransactionDetail::create([
                'transaction_id' => $trx->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'purchase_price' => $product->purchase_price,
                'selling_price' => $product->selling_price,
                'quantity' => $qtyToBuy,
                'subtotal' => $qtyToBuy * $product->selling_price,
            ]);

            StockMutation::create([
                'product_id' => $product->id,
                'user_id' => $kasir->id,
                'type' => 'out_sale',
                'quantity' => $qtyToBuy,
                'previous_stock' => $prevStock,
                'current_stock' => $newStock,
                'reference_number' => $trx->invoice_code,
                'notes' => 'Testing atomic checkout',
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->fail('Transaction failed: ' . $e->getMessage());
        }

        $product->refresh();
        $this->assertEquals($initialStock - $qtyToBuy, $product->stock);
    }
}


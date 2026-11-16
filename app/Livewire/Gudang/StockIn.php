<?php

namespace App\Livewire\Gudang;

use App\Models\Product;
use App\Models\StockMutation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StockIn extends Component
{
    public string $referenceNumber = '';
    public string $supplier = '';
    public string $notes = '';
    public string $searchProduct = '';

    public array $items = []; // list of products being received
    public string $successMessage = '';
    public string $errorMessage = '';

    public function mount()
    {
        $this->referenceNumber = 'SJ-' . date('Ymd') . '-' . rand(100, 999);
    }

    public function addProduct(int $productId)
    {
        $this->errorMessage = '';
        $product = Product::find($productId);

        if (!$product) {
            $this->errorMessage = 'Produk tidak ditemukan!';
            return;
        }

        if (isset($this->items[$productId])) {
            $this->items[$productId]['quantity'] += 1;
        } else {
            $this->items[$productId] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'unit' => $product->unit,
                'current_stock' => $product->stock,
                'quantity' => 1,
                'purchase_price' => (float) $product->purchase_price,
            ];
        }

        $this->searchProduct = '';
    }

    public function updateItemQty(int $productId, int $qty)
    {
        if (isset($this->items[$productId])) {
            if ($qty <= 0) {
                unset($this->items[$productId]);
            } else {
                $this->items[$productId]['quantity'] = $qty;
            }
        }
    }

    public function removeItem(int $productId)
    {
        unset($this->items[$productId]);
    }

    public function processStockIn()
    {
        $this->errorMessage = '';

        if (empty($this->items)) {
            $this->errorMessage = 'Pilih minimal satu produk untuk penerimaan barang!';
            return;
        }

        if (empty(trim($this->referenceNumber))) {
            $this->errorMessage = 'Nomor Referensi / Surat Jalan wajib diisi!';
            return;
        }

        try {
            DB::beginTransaction();

            $ref = trim($this->referenceNumber);
            $supplierName = trim($this->supplier) ?: 'Supplier Umum';
            $fullNotes = "Terima dari {$supplierName}. " . trim($this->notes);

            foreach ($this->items as $item) {
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->firstOrFail();

                $prevStock = $product->stock;
                $newStock = $prevStock + $item['quantity'];

                $product->stock = $newStock;
                if (!empty($item['purchase_price']) && $item['purchase_price'] > 0) {
                    $product->purchase_price = $item['purchase_price'];
                }
                $product->save();

                StockMutation::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'type' => 'in',
                    'quantity' => $item['quantity'],
                    'previous_stock' => $prevStock,
                    'current_stock' => $newStock,
                    'reference_number' => $ref,
                    'notes' => $fullNotes,
                ]);
            }

            DB::commit();

            $this->successMessage = "Penerimaan barang [{$ref}] berhasil diproses ke stok gudang!";
            $this->items = [];
            $this->supplier = '';
            $this->notes = '';
            $this->referenceNumber = 'SJ-' . date('Ymd') . '-' . rand(100, 999);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Gagal menyimpan penerimaan barang: ' . $e->getMessage();
        }
    }

    public function render()
    {
        $searchResults = [];
        if (strlen(trim($this->searchProduct)) >= 2) {
            $searchResults = Product::search($this->searchProduct)->limit(6)->get();
        }

        return view('livewire.gudang.stock-in', [
            'searchResults' => $searchResults,
        ]);
    }
}


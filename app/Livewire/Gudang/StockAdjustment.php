<?php

namespace App\Livewire\Gudang;

use App\Models\Product;
use App\Models\StockMutation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StockAdjustment extends Component
{
    public ?int $selectedProductId = null;
    public ?Product $selectedProduct = null;
    public string $searchProduct = '';

    public int $physicalStock = 0;
    public string $adjustmentReason = 'Selisih Hasil Stock Opname Fisik';
    public string $notes = '';

    public string $successMessage = '';
    public string $errorMessage = '';

    public function selectProduct(int $id)
    {
        $this->selectedProduct = Product::find($id);
        if ($this->selectedProduct) {
            $this->selectedProductId = $this->selectedProduct->id;
            $this->physicalStock = $this->selectedProduct->stock;
        }
        $this->searchProduct = '';
        $this->errorMessage = '';
        $this->successMessage = '';
    }

    public function processAdjustment()
    {
        $this->errorMessage = '';

        if (!$this->selectedProduct) {
            $this->errorMessage = 'Pilih produk yang akan disesuaikan stoknya!';
            return;
        }

        if ($this->physicalStock < 0) {
            $this->errorMessage = 'Stok fisik aktual tidak boleh bernilai negatif!';
            return;
        }

        try {
            DB::beginTransaction();

            $product = Product::where('id', $this->selectedProduct->id)->lockForUpdate()->firstOrFail();

            $previousStock = $product->stock;
            $currentStock = $this->physicalStock;
            $difference = $currentStock - $previousStock;

            if ($difference === 0) {
                $this->errorMessage = 'Stok fisik sama dengan stok sistem, tidak ada selisih yang disesuaikan.';
                DB::rollBack();
                return;
            }

            $type = $difference > 0 ? 'adjustment_in' : 'adjustment_out';
            $ref = 'OPNAME-' . date('Ymd') . '-' . rand(1000, 9999);
            $fullNotes = "[{$this->adjustmentReason}] " . trim($this->notes);

            $product->stock = $currentStock;
            $product->save();

            StockMutation::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => $type,
                'quantity' => abs($difference),
                'previous_stock' => $previousStock,
                'current_stock' => $currentStock,
                'reference_number' => $ref,
                'notes' => $fullNotes,
            ]);

            DB::commit();

            $diffText = ($difference > 0 ? "+{$difference}" : "{$difference}") . " {$product->unit}";
            $this->successMessage = "Penyesuaian stok produk '{$product->name}' berhasil! Selisih: {$diffText}. Stok saat ini: {$currentStock} {$product->unit}.";

            $this->selectedProduct = null;
            $this->selectedProductId = null;
            $this->physicalStock = 0;
            $this->notes = '';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Gagal memproses penyesuaian stok: ' . $e->getMessage();
        }
    }

    public function render()
    {
        $searchResults = [];
        if (strlen(trim($this->searchProduct)) >= 2) {
            $searchResults = Product::search($this->searchProduct)->limit(6)->get();
        }

        return view('livewire.gudang.stock-adjustment', [
            'searchResults' => $searchResults,
        ]);
    }
}


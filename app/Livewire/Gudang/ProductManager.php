<?php

namespace App\Livewire\Gudang;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMutation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ProductManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $selectedCategory = '';
    public string $stockFilter = 'all'; // all, low, out

    public bool $showModal = false;
    public ?int $editingProductId = null;

    // Form inputs
    public string $barcode = '';
    public string $sku = '';
    public string $name = '';
    public ?int $category_id = null;
    public string $unit = 'pcs';
    public float $purchase_price = 0;
    public float $selling_price = 0;
    public int $stock = 0;
    public int $min_stock = 5;
    public bool $is_active = true;

    public string $successMessage = '';
    public string $errorMessage = '';

    protected function rules(): array
    {
        return [
            'barcode' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'barcode')->ignore($this->editingProductId),
            ],
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($this->editingProductId),
            ],
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'required|string|max:30',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedCategory()
    {
        $this->resetPage();
    }

    public function updatedStockFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->barcode = '899' . rand(1000000000, 9999999999);
        $this->sku = 'SKU-' . strtoupper(substr(md5(time()), 0, 6));
        $this->editingProductId = null;
        $this->showModal = true;
    }

    public function editProduct(int $id)
    {
        $this->resetValidation();
        $product = Product::findOrFail($id);
        $this->editingProductId = $product->id;
        $this->barcode = $product->barcode;
        $this->sku = $product->sku;
        $this->name = $product->name;
        $this->category_id = $product->category_id;
        $this->unit = $product->unit;
        $this->purchase_price = (float) $product->purchase_price;
        $this->selling_price = (float) $product->selling_price;
        $this->stock = $product->stock;
        $this->min_stock = $product->min_stock;
        $this->is_active = $product->is_active;

        $this->showModal = true;
    }

    public function saveProduct()
    {
        $validated = $this->validate();

        try {
            DB::beginTransaction();

            if ($this->editingProductId) {
                $product = Product::findOrFail($this->editingProductId);
                $oldStock = $product->stock;
                $product->update($validated);

                // If stock was directly altered in product edit, log as adjustment
                if ($oldStock !== $product->stock) {
                    $diff = $product->stock - $oldStock;
                    StockMutation::create([
                        'product_id' => $product->id,
                        'user_id' => Auth::id(),
                        'type' => $diff > 0 ? 'adjustment_in' : 'adjustment_out',
                        'quantity' => abs($diff),
                        'previous_stock' => $oldStock,
                        'current_stock' => $product->stock,
                        'reference_number' => 'EDIT-PROD-' . $product->id,
                        'notes' => 'Koreksi stok langsung via edit data master barang',
                    ]);
                }

                $this->successMessage = "Produk '{$product->name}' berhasil diperbarui.";
            } else {
                $product = Product::create($validated);

                if ($product->stock > 0) {
                    StockMutation::create([
                        'product_id' => $product->id,
                        'user_id' => Auth::id(),
                        'type' => 'in',
                        'quantity' => $product->stock,
                        'previous_stock' => 0,
                        'current_stock' => $product->stock,
                        'reference_number' => 'NEW-PROD-' . $product->id,
                        'notes' => 'Stok perdana saat penambahan master produk baru',
                    ]);
                }

                $this->successMessage = "Produk baru '{$product->name}' berhasil ditambahkan ke database.";
            }

            DB::commit();
            $this->showModal = false;
            $this->resetForm();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Gagal menyimpan produk: ' . $e->getMessage();
        }
    }

    public function toggleActive(int $id)
    {
        $product = Product::findOrFail($id);
        $product->is_active = !$product->is_active;
        $product->save();

        $this->successMessage = "Status aktif produk '{$product->name}' diubah menjadi: " . ($product->is_active ? 'Aktif' : 'Nonaktif');
    }

    public function resetForm()
    {
        $this->barcode = '';
        $this->sku = '';
        $this->name = '';
        $this->category_id = null;
        $this->unit = 'pcs';
        $this->purchase_price = 0;
        $this->selling_price = 0;
        $this->stock = 0;
        $this->min_stock = 5;
        $this->is_active = true;
        $this->editingProductId = null;
    }

    public function render()
    {
        $query = Product::with('category')->latest();

        if (!empty($this->search)) {
            $query->search($this->search);
        }

        if (!empty($this->selectedCategory)) {
            $query->where('category_id', $this->selectedCategory);
        }

        if ($this->stockFilter === 'low') {
            $query->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0);
        } elseif ($this->stockFilter === 'out') {
            $query->where('stock', '<=', 0);
        }

        $products = $query->paginate(10);
        $categories = Category::orderBy('name')->get();

        return view('livewire.gudang.product-manager', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}


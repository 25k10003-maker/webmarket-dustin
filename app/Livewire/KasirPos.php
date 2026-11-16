<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\StockMutation;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class KasirPos extends Component
{
    public string $barcodeInput = '';
    public string $searchQuery = '';
    public array $cart = [];
    public string $customerName = 'Pelanggan Umum';
    public string $paymentMethod = 'tunai'; // 'tunai' atau 'qris'
    public float $paidAmount = 0;
    public float $discount = 0;
    public ?string $notes = '';

    public bool $showPaymentModal = false;
    public bool $showReceiptModal = false;
    public ?Transaction $completedTransaction = null;

    public string $errorMessage = '';
    public string $successMessage = '';

    protected $listeners = [
        'onBarcodeScanned' => 'handleScannedBarcode',
        'triggerPayment' => 'openPaymentModal',
        'cancelModal' => 'closeModals',
    ];

    public function mount()
    {
        $this->cart = [];
        $this->customerName = 'Pelanggan Umum';
        $this->paymentMethod = 'tunai';
    }

    public function scanBarcode()
    {
        $this->errorMessage = '';
        $this->successMessage = '';

        $code = trim($this->barcodeInput);
        if (empty($code)) {
            return;
        }

        $this->addItemByCode($code);
        $this->barcodeInput = '';
        $this->dispatch('focus-barcode-input');
    }

    public function handleScannedBarcode($code)
    {
        $this->errorMessage = '';
        $code = trim($code);
        if (!empty($code)) {
            $this->addItemByCode($code);
        }
    }

    protected function addItemByCode(string $code)
    {
        $product = Product::active()
            ->where(function ($q) use ($code) {
                $q->where('barcode', $code)
                  ->orWhere('sku', $code);
            })
            ->first();

        if (!$product) {
            $this->errorMessage = "Produk dengan barcode/SKU '{$code}' tidak ditemukan!";
            return;
        }

        if ($product->stock <= 0) {
            $this->errorMessage = "Stok untuk produk '{$product->name}' habis (0 {$product->unit})!";
            return;
        }

        $this->addToCart($product);
    }

    public function selectFromSearch(int $productId)
    {
        $product = Product::active()->find($productId);
        if ($product) {
            $this->addToCart($product);
        }
        $this->searchQuery = '';
        $this->dispatch('focus-barcode-input');
    }

    public function addToCart(Product $product, int $quantity = 1)
    {
        $this->errorMessage = '';

        // Check if item already exists in cart
        if (isset($this->cart[$product->id])) {
            $newQty = $this->cart[$product->id]['qty'] + $quantity;
            if ($newQty > $product->stock) {
                $this->errorMessage = "Maksimal stok tercapai ({$product->stock} {$product->unit}) untuk {$product->name}!";
                return;
            }
            $this->cart[$product->id]['qty'] = $newQty;
            $this->cart[$product->id]['subtotal'] = $newQty * $product->selling_price;
        } else {
            if ($quantity > $product->stock) {
                $this->errorMessage = "Stok tidak mencukupi! Tersedia hanya {$product->stock} {$product->unit}.";
                return;
            }

            $this->cart[$product->id] = [
                'product_id' => $product->id,
                'barcode' => $product->barcode,
                'sku' => $product->sku,
                'name' => $product->name,
                'unit' => $product->unit,
                'price' => (float) $product->selling_price,
                'purchase_price' => (float) $product->purchase_price,
                'qty' => $quantity,
                'stock' => $product->stock,
                'subtotal' => (float) ($quantity * $product->selling_price),
            ];
        }

        $this->successMessage = "{$product->name} ditambahkan ke keranjang.";
    }

    public function updateQty(int $productId, int $newQty)
    {
        $this->errorMessage = '';

        if (!isset($this->cart[$productId])) {
            return;
        }

        if ($newQty <= 0) {
            $this->removeItem($productId);
            return;
        }

        $product = Product::find($productId);
        if (!$product || $newQty > $product->stock) {
            $this->errorMessage = "Stok tersisa hanya " . ($product->stock ?? 0) . "!";
            return;
        }

        $this->cart[$productId]['qty'] = $newQty;
        $this->cart[$productId]['subtotal'] = $newQty * $this->cart[$productId]['price'];
    }

    public function incrementQty(int $productId)
    {
        if (isset($this->cart[$productId])) {
            $this->updateQty($productId, $this->cart[$productId]['qty'] + 1);
        }
    }

    public function decrementQty(int $productId)
    {
        if (isset($this->cart[$productId])) {
            $this->updateQty($productId, $this->cart[$productId]['qty'] - 1);
        }
    }

    public function removeItem(int $productId)
    {
        unset($this->cart[$productId]);
        $this->dispatch('focus-barcode-input');
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->customerName = 'Pelanggan Umum';
        $this->errorMessage = '';
        $this->successMessage = 'Keranjang belanja dikosongkan.';
        $this->dispatch('focus-barcode-input');
    }

    public function openPaymentModal()
    {
        $this->errorMessage = '';
        if (empty($this->cart)) {
            $this->errorMessage = 'Keranjang belanja masih kosong! Scan produk terlebih dahulu.';
            return;
        }

        $this->showPaymentModal = true;
        $this->paidAmount = (float) $this->grandTotal; // Default set ke Uang Pas
        $this->dispatch('focus-paid-input');
    }

    public function closeModals()
    {
        $this->showPaymentModal = false;
        $this->showReceiptModal = false;
        $this->dispatch('focus-barcode-input');
    }

    public function setQuickMoney(float $amount)
    {
        $this->paidAmount = $amount;
    }

    public function setExactPayment()
    {
        $this->paidAmount = (float) $this->grandTotal;
    }

    /**
     * Logika transaksi database (DB Transaction) dengan pessimistic locking
     * untuk menjaga konsistensi stok saat terjadi pembelian bersamaan (race condition).
     */
    public function processCheckout()
    {
        $this->errorMessage = '';

        if (empty($this->cart)) {
            $this->errorMessage = 'Keranjang kosong!';
            return;
        }

        if ($this->paymentMethod === 'tunai' && $this->paidAmount < $this->grandTotal) {
            $this->errorMessage = 'Nominal pembayaran tunai kurang dari total tagihan!';
            return;
        }

        if ($this->paymentMethod === 'qris') {
            $this->paidAmount = $this->grandTotal; // QRIS otomatis pas
        }

        try {
            DB::beginTransaction();

            $productIds = array_keys($this->cart);

            // Pessimistic Lock (SELECT FOR UPDATE) untuk mencegah race-condition antar-kasir
            $lockedProducts = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 1. Validasi stok aktual terkunci
            foreach ($this->cart as $productId => $item) {
                $product = $lockedProducts->get($productId);

                if (!$product) {
                    throw new \Exception("Produk ID #{$productId} tidak ditemukan di sistem database.");
                }

                if (!$product->is_active) {
                    throw new \Exception("Produk '{$product->name}' sedang dinonaktifkan dari penjualan.");
                }

                if ($product->stock < $item['qty']) {
                    throw new \Exception("Stok tidak mencukupi untuk '{$product->name}'! Stok fisik tersisa: {$product->stock} {$product->unit}, diminta: {$item['qty']}.");
                }
            }

            // 2. Generate No Invoice Unik
            $invoiceCode = 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            // 3. Simpan data Header Transaksi
            $transaction = Transaction::create([
                'invoice_code' => $invoiceCode,
                'user_id' => Auth::id(),
                'customer_name' => $this->customerName ?: 'Pelanggan Umum',
                'total_items' => $this->totalItems,
                'subtotal' => $this->subtotal,
                'discount' => $this->discount,
                'grand_total' => $this->grandTotal,
                'paid_amount' => $this->paidAmount,
                'change_amount' => max(0, $this->paidAmount - $this->grandTotal),
                'payment_method' => $this->paymentMethod,
                'payment_status' => 'paid',
                'notes' => $this->notes,
            ]);

            // 4. Proses Item Transaksi, Pengurangan Stok, dan Catat Riwayat Mutasi Stok
            foreach ($this->cart as $productId => $item) {
                $product = $lockedProducts->get($productId);

                $previousStock = $product->stock;
                $currentStock = $previousStock - $item['qty'];

                // Update stok produk
                $product->stock = $currentStock;
                $product->save();

                // Simpan detail transaksi dengan snapshot harga beli modal (untuk audit laba)
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'purchase_price' => $product->purchase_price,
                    'selling_price' => $item['price'],
                    'quantity' => $item['qty'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Catat mutasi stok keluar (out_sale)
                StockMutation::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'type' => 'out_sale',
                    'quantity' => $item['qty'],
                    'previous_stock' => $previousStock,
                    'current_stock' => $currentStock,
                    'reference_number' => $invoiceCode,
                    'notes' => "Penjualan Kasir POS ({$invoiceCode})",
                ]);
            }

            // Commit transaksi database secara atomik
            DB::commit();

            // Setup modal struk cetak
            $this->completedTransaction = $transaction->load(['details.product', 'user']);
            $this->showPaymentModal = false;
            $this->showReceiptModal = true;

            // Reset state form belanja
            $this->cart = [];
            $this->customerName = 'Pelanggan Umum';
            $this->discount = 0;
            $this->notes = '';
            $this->successMessage = "Transaksi {$invoiceCode} berhasil diselesaikan!";

            // Trigger cetak struk thermal otomatis via JavaScript
            $this->dispatch('print-receipt', invoice: $invoiceCode);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Gagal memproses transaksi: ' . $e->getMessage();
        }
    }

    public function getSubtotalProperty(): float
    {
        return (float) array_sum(array_column($this->cart, 'subtotal'));
    }

    public function getGrandTotalProperty(): float
    {
        return (float) max(0, $this->subtotal - $this->discount);
    }

    public function getTotalItemsProperty(): int
    {
        return (int) array_sum(array_column($this->cart, 'qty'));
    }

    public function getChangeAmountProperty(): float
    {
        return (float) max(0, $this->paidAmount - $this->grandTotal);
    }

    public function render()
    {
        $searchResults = [];
        if (strlen(trim($this->searchQuery)) >= 2) {
            $searchResults = Product::active()
                ->search($this->searchQuery)
                ->limit(6)
                ->get();
        }

        return view('livewire.kasir-pos', [
            'searchResults' => $searchResults,
        ]);
    }
}


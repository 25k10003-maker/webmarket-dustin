# Sistem Aplikasi Web Retail Market (POS, Gudang, & Analitik Admin)

Aplikasi Retail Market modern, aman, dan berkinerja tinggi yang dibangun menggunakan **Laravel (v13)**, **MySQL**, **Livewire (v4)**, dan **Tailwind CSS**.

---

## 1. Arsitektur & Peran Pengguna (Role & Privileges)

Sistem dirancang dengan pembagian hak akses ketat menggunakan Middleware `role`:

| Peran | Akun Login | Antarmuka | Fitur Utama |
|---|---|---|---|
| **UKasir** | `kasir@retail.com` / `password123` | **Desktop PC / Laptop** | • Scan Barcode/QR (USB Scanner & Kamera Web)<br>• Pencarian autocomplete nama barang<br>• Keranjang belanja reaktif tanpa reload (Livewire SPA)<br>• Pembayaran Tunai (Fast Cash & Otomatis Hitung Kembalian)<br>• Pembayaran QRIS Dinamis<br>• Pemotongan stok otomatis atomik dengan Pessimistic Lock<br>• Cetak Struk Belanja Thermal ESC/POS (58mm/80mm) cepat |
| **UGudang** | `gudang@retail.com` / `password123` | **Desktop / Laptop** | • Manajemen Master Produk (CRUD, Barcode, SKU, Harga Beli, Harga Jual, Min Stock)<br>• Modul Terima Barang Masuk (Restock Supplier & Update Modal)<br>• Penyesuaian Stok (Stock Opname Fisik, Koreksi Selisih, Rusak/Hilang)<br>• Log Riwayat Mutasi Stok lengkap (Audit Trail) |
| **UAdmin** | `admin@retail.com` / `password123` | **Mobile-First Web (Smartphone)** | • Hak Akses **Read-Only**<br>• Kartu KPI: Total Omzet, Laba Kotor (Gross Profit), Total Transaksi Kasir, Alert Stok Menipis<br>• Grafik Interaktif Penjualan & Laba Harian (Chart.js)<br>• Leaderboard Produk Terlaris (Top 5)<br>• Peringatan Stok Kritis & Live Feed Transaksi Kasir |

---

## 2. Struktur Skema Database MySQL

1. **`roles`**:
   - `id`, `name` (admin, kasir, gudang), `display_name`, `description`, `timestamps`.
2. **`users`**:
   - `id`, `role_id` (foreign key ke `roles`), `name`, `email`, `phone`, `password`, `is_active`, `timestamps`.
3. **`categories`**:
   - `id`, `name`, `slug`, `description`, `timestamps`.
4. **`products`**:
   - `id`, `category_id`, `barcode` (unique, index), `sku` (unique, index), `name`, `unit`, `purchase_price`, `selling_price`, `stock`, `min_stock`, `is_active`, `timestamps`.
5. **`transactions`**:
   - `id`, `invoice_code` (unique, index), `user_id` (kasir), `customer_name`, `total_items`, `subtotal`, `discount`, `grand_total`, `paid_amount`, `change_amount`, `payment_method` (`tunai`/`qris`), `payment_status` (`paid`), `notes`, `timestamps`.
6. **`transaction_details`**:
   - `id`, `transaction_id`, `product_id`, `product_name`, `purchase_price` (snapshot modal), `selling_price`, `quantity`, `subtotal`, `timestamps`.
7. **`stock_mutations`**:
   - `id`, `product_id`, `user_id`, `type` (`in`, `out_sale`, `adjustment_in`, `adjustment_out`), `quantity`, `previous_stock`, `current_stock`, `reference_number`, `notes`, `timestamps`.

---

## 3. Logika Transaksi & Konsistensi Stok (DB Transaction & Lock)

Untuk mencegah **Race Condition** saat beberapa kasir menjual produk yang sama secara bersamaan ketika sisa stok menipis, aplikasi menerapkan **Pessimistic Locking (`lockForUpdate()`)** di dalam `DB::transaction()`:

```php
DB::beginTransaction();
try {
    $productIds = array_keys($this->cart);

    // Kunci baris data produk terpilih untuk mencegah modifikasi konkruen
    $lockedProducts = Product::whereIn('id', $productIds)
        ->lockForUpdate()
        ->get()
        ->keyBy('id');

    // 1. Validasi kecukupan stok fisik terkunci
    foreach ($this->cart as $productId => $item) {
        $product = $lockedProducts->get($productId);
        if ($product->stock < $item['qty']) {
            throw new \Exception("Stok {$product->name} tidak mencukupi (sisa {$product->stock})");
        }
    }

    // 2. Buat Transaksi Header & Kurangi Stok
    // ...
    $product->stock = $previousStock - $item['qty'];
    $product->save();

    // 3. Catat audit log mutasi stok
    // ...

    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

---

## 4. Panduan Menjalankan Aplikasi

1. **Jalankan Web Server Laravel**:
   ```bash
   php artisan serve
   ```
2. **Akses Browser**:
   - Buka `http://127.0.0.1:8000`
   - Tersedia tombol **1-Klik Demo Akun** pada halaman login untuk masuk sebagai:
     * **UKasir**: `kasir@retail.com` / `password123`
     * **UGudang**: `gudang@retail.com` / `password123`
     * **UAdmin**: `admin@retail.com` / `password123`


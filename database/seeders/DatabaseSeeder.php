<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockMutation;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles
        $roleAdmin = Role::create([
            'name' => 'admin',
            'display_name' => 'UAdmin (Owner/Executive)',
            'description' => 'Akses Mobile-First analitik laporan penjualan, grafik laba, dan stok (Read-Only)',
        ]);

        $roleKasir = Role::create([
            'name' => 'kasir',
            'display_name' => 'UKasir (Kasir Toko)',
            'description' => 'Akses POS Desktop: scan barcode/QR, transaksi, cetak struk thermal',
        ]);

        $roleGudang = Role::create([
            'name' => 'gudang',
            'display_name' => 'UGudang (Staff Inventaris)',
            'description' => 'Akses modul Gudang: CRUD produk, restock barang masuk, penyesuaian stok, log mutasi',
        ]);

        // 2. Users
        $admin = User::create([
            'role_id' => $roleAdmin->id,
            'name' => 'Budi Santoso (Admin)',
            'email' => 'admin@retail.com',
            'phone' => '081234567890',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $kasir = User::create([
            'role_id' => $roleKasir->id,
            'name' => 'Siti Rahma (Kasir 1)',
            'email' => 'kasir@retail.com',
            'phone' => '081234567891',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $gudang = User::create([
            'role_id' => $roleGudang->id,
            'name' => 'Agus Prasetyo (Gudang)',
            'email' => 'gudang@retail.com',
            'phone' => '081234567892',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        // 3. Categories
        $catSembako = Category::create(['name' => 'Sembako & Kebutuhan Pokok', 'slug' => 'sembako']);
        $catMinuman = Category::create(['name' => 'Minuman & Kopi', 'slug' => 'minuman']);
        $catMakanan = Category::create(['name' => 'Makanan Ringan & Mie', 'slug' => 'makanan-snack']);
        $catKebersihan = Category::create(['name' => 'Pembersih & Perawatan Diri', 'slug' => 'kebersihan-perawatan']);

        // 4. Products with realistic retail barcodes
        $productsData = [
            [
                'category_id' => $catMakanan->id,
                'barcode' => '8998866200118',
                'sku' => 'MIE-001',
                'name' => 'Indomie Goreng Spesial 85g',
                'unit' => 'pcs',
                'purchase_price' => 2800,
                'selling_price' => 3500,
                'stock' => 120,
                'min_stock' => 20,
            ],
            [
                'category_id' => $catMakanan->id,
                'barcode' => '8998866200224',
                'sku' => 'MIE-002',
                'name' => 'Indomie Kuah Ayam Bawang 75g',
                'unit' => 'pcs',
                'purchase_price' => 2750,
                'selling_price' => 3500,
                'stock' => 85,
                'min_stock' => 15,
            ],
            [
                'category_id' => $catMinuman->id,
                'barcode' => '8886008101053',
                'sku' => 'MIN-001',
                'name' => 'Aqua Air Mineral Botol 600ml',
                'unit' => 'botol',
                'purchase_price' => 2900,
                'selling_price' => 4000,
                'stock' => 96,
                'min_stock' => 24,
            ],
            [
                'category_id' => $catMinuman->id,
                'barcode' => '8991389220014',
                'sku' => 'MIN-002',
                'name' => 'Teh Botol Sosro Kotak 250ml',
                'unit' => 'kotak',
                'purchase_price' => 3100,
                'selling_price' => 4500,
                'stock' => 60,
                'min_stock' => 12,
            ],
            [
                'category_id' => $catMinuman->id,
                'barcode' => '8991102120011',
                'sku' => 'MIN-003',
                'name' => 'Ultra Milk Cokelat 250ml',
                'unit' => 'kotak',
                'purchase_price' => 5800,
                'selling_price' => 7500,
                'stock' => 48,
                'min_stock' => 10,
            ],
            [
                'category_id' => $catSembako->id,
                'barcode' => '8992759110034',
                'sku' => 'SMB-001',
                'name' => 'Bimoli Minyak Goreng Refill 2L',
                'unit' => 'pouch',
                'purchase_price' => 33500,
                'selling_price' => 38500,
                'stock' => 30,
                'min_stock' => 10,
            ],
            [
                'category_id' => $catSembako->id,
                'barcode' => '8993175110012',
                'sku' => 'SMB-002',
                'name' => 'Gulaku Gula Pasir Premium 1kg',
                'unit' => 'kg',
                'purchase_price' => 16000,
                'selling_price' => 18500,
                'stock' => 40,
                'min_stock' => 8,
            ],
            [
                'category_id' => $catSembako->id,
                'barcode' => '8993175110099',
                'sku' => 'SMB-003',
                'name' => 'Beras Ramos Super Pandan Wangi 5kg',
                'unit' => 'sak',
                'purchase_price' => 68000,
                'selling_price' => 76000,
                'stock' => 18,
                'min_stock' => 5,
            ],
            [
                'category_id' => $catKebersihan->id,
                'barcode' => '8999999042211',
                'sku' => 'KBR-001',
                'name' => 'Sunlight Jeruk Nipis 750ml',
                'unit' => 'pouch',
                'purchase_price' => 13500,
                'selling_price' => 16500,
                'stock' => 25,
                'min_stock' => 8,
            ],
            [
                'category_id' => $catKebersihan->id,
                'barcode' => '8999999511122',
                'sku' => 'KBR-002',
                'name' => 'Lifebuoy Sabun Cair Total 10 450ml',
                'unit' => 'pouch',
                'purchase_price' => 21000,
                'selling_price' => 26000,
                'stock' => 22,
                'min_stock' => 6,
            ],
            [
                'category_id' => $catMakanan->id,
                'barcode' => '8996001301144',
                'sku' => 'SNK-001',
                'name' => 'Chitato Sapi Panggang 68g',
                'unit' => 'pcs',
                'purchase_price' => 8500,
                'selling_price' => 11000,
                'stock' => 35,
                'min_stock' => 10,
            ],
            [
                'category_id' => $catMakanan->id,
                'barcode' => '8992775210033',
                'sku' => 'SNK-002',
                'name' => 'Oreo Vanilla Cream 133g',
                'unit' => 'roll',
                'purchase_price' => 7800,
                'selling_price' => 10000,
                'stock' => 4, // Intentionally low stock to show alerts!
                'min_stock' => 10,
            ],
            [
                'category_id' => $catMinuman->id,
                'barcode' => '8993175550011',
                'sku' => 'MIN-004',
                'name' => 'Nescafe Kopi Klasik Kaleng 240ml',
                'unit' => 'kaleng',
                'purchase_price' => 9000,
                'selling_price' => 11500,
                'stock' => 3, // Intentionally low stock!
                'min_stock' => 8,
            ],
        ];

        $createdProducts = [];
        foreach ($productsData as $data) {
            $p = Product::create($data);
            $createdProducts[] = $p;

            // Catat mutasi awal (barang masuk)
            StockMutation::create([
                'product_id' => $p->id,
                'user_id' => $gudang->id,
                'type' => 'in',
                'quantity' => $p->stock,
                'previous_stock' => 0,
                'current_stock' => $p->stock,
                'reference_number' => 'PO-INIT-2026',
                'notes' => 'Stok awal setup sistem retail',
                'created_at' => Carbon::now()->subDays(5),
            ]);
        }

        // 5. Seed sample past transactions for realistic dashboard analytics
        $today = Carbon::today();
        $sampleTrx = [
            [
                'days_ago' => 2,
                'customer' => 'Ibu Siti',
                'method' => 'tunai',
                'items' => [
                    ['prod' => $createdProducts[0], 'qty' => 5], // Indomie
                    ['prod' => $createdProducts[2], 'qty' => 3], // Aqua
                    ['prod' => $createdProducts[5], 'qty' => 1], // Minyak Bimoli
                ],
            ],
            [
                'days_ago' => 1,
                'customer' => 'Pak Joko',
                'method' => 'qris',
                'items' => [
                    ['prod' => $createdProducts[7], 'qty' => 1], // Beras 5kg
                    ['prod' => $createdProducts[6], 'qty' => 2], // Gulaku
                    ['prod' => $createdProducts[8], 'qty' => 1], // Sunlight
                ],
            ],
            [
                'days_ago' => 0,
                'customer' => 'Budi Santoso',
                'method' => 'tunai',
                'items' => [
                    ['prod' => $createdProducts[0], 'qty' => 10], // Indomie
                    ['prod' => $createdProducts[3], 'qty' => 4],  // Teh botol
                    ['prod' => $createdProducts[10], 'qty' => 2], // Chitato
                ],
            ],
            [
                'days_ago' => 0,
                'customer' => 'Pelanggan Umum',
                'method' => 'qris',
                'items' => [
                    ['prod' => $createdProducts[4], 'qty' => 2], // Ultra milk
                    ['prod' => $createdProducts[2], 'qty' => 2], // Aqua
                ],
            ],
        ];

        foreach ($sampleTrx as $index => $trxData) {
            $trxDate = Carbon::now()->subDays($trxData['days_ago'])->subHours(rand(1, 5));
            $invoice = 'TRX-' . $trxDate->format('Ymd') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);

            $subtotal = 0;
            $totalItems = 0;
            foreach ($trxData['items'] as $item) {
                $subtotal += ($item['prod']->selling_price * $item['qty']);
                $totalItems += $item['qty'];
            }
            $grandTotal = $subtotal;
            $paid = $trxData['method'] === 'tunai' ? ceil($grandTotal / 50000) * 50000 : $grandTotal;
            $change = $paid - $grandTotal;

            $trx = Transaction::create([
                'invoice_code' => $invoice,
                'user_id' => $kasir->id,
                'customer_name' => $trxData['customer'],
                'total_items' => $totalItems,
                'subtotal' => $subtotal,
                'discount' => 0,
                'grand_total' => $grandTotal,
                'paid_amount' => $paid,
                'change_amount' => $change,
                'payment_method' => $trxData['method'],
                'payment_status' => 'paid',
                'notes' => 'Transaksi kasir shift reguler',
                'created_at' => $trxDate,
                'updated_at' => $trxDate,
            ]);

            foreach ($trxData['items'] as $item) {
                $prod = $item['prod'];
                $itemSubtotal = $prod->selling_price * $item['qty'];

                TransactionDetail::create([
                    'transaction_id' => $trx->id,
                    'product_id' => $prod->id,
                    'product_name' => $prod->name,
                    'purchase_price' => $prod->purchase_price,
                    'selling_price' => $prod->selling_price,
                    'quantity' => $item['qty'],
                    'subtotal' => $itemSubtotal,
                    'created_at' => $trxDate,
                    'updated_at' => $trxDate,
                ]);

                StockMutation::create([
                    'product_id' => $prod->id,
                    'user_id' => $kasir->id,
                    'type' => 'out_sale',
                    'quantity' => $item['qty'],
                    'previous_stock' => $prod->stock + $item['qty'],
                    'current_stock' => $prod->stock,
                    'reference_number' => $invoice,
                    'notes' => 'Penjualan Nota ' . $invoice,
                    'created_at' => $trxDate,
                    'updated_at' => $trxDate,
                ]);
            }
        }
    }
}

<div class="flex-1 flex overflow-hidden relative" x-data="posApp()">
    
    <!-- LEFT PANEL: Product Scanner & Cart (70% width) -->
    <div class="flex-1 flex flex-col border-r border-slate-800 bg-slate-900/60 p-4 overflow-hidden">
        
        <!-- Alerts Notification Banner -->
        @if($errorMessage)
            <div class="mb-3 bg-red-500/20 border border-red-500/40 text-red-300 px-4 py-2.5 rounded-xl text-sm flex items-center justify-between animate-pulse">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-circle-exclamation text-red-400"></i>
                    <span>{{ $errorMessage }}</span>
                </div>
                <button type="button" wire:click="$set('errorMessage', '')" class="text-red-400 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if($successMessage)
            <div class="mb-3 bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 px-4 py-2.5 rounded-xl text-sm flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-circle-check text-emerald-400"></i>
                    <span>{{ $successMessage }}</span>
                </div>
                <button type="button" wire:click="$set('successMessage', '')" class="text-emerald-400 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        <!-- TOP BAR: Barcode/QR Scanner & Search Bar -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-4 flex-shrink-0">
            <!-- 1. Barcode Scanner Input (USB Scanner & Physical Scanner Friendly) -->
            <div class="md:col-span-7 relative">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-400">
                        <i class="fa-solid fa-barcode text-lg"></i>
                    </div>
                    <input type="text"
                           id="barcode-input"
                           wire:model="barcodeInput"
                           wire:keydown.enter.prevent="scanBarcode"
                           placeholder="Scan Barcode (USB Scanner) atau ketik kode lalu tekan Enter..."
                           autofocus
                           class="w-full pl-11 pr-24 py-3 bg-slate-800 border-2 border-slate-700 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30 rounded-xl text-white font-mono text-sm placeholder-slate-400 transition">
                    
                    <button type="button"
                            wire:click="scanBarcode"
                            class="absolute inset-y-1.5 right-1.5 px-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-semibold flex items-center space-x-1.5 transition">
                        <i class="fa-solid fa-plus"></i>
                        <span>Input</span>
                    </button>
                </div>
                <div class="mt-1 flex items-center justify-between text-[11px] text-slate-400 px-1">
                    <span>Mendukung Scanner USB Fisik (Otomatis deteksi Enter)</span>
                    <button type="button" @click="toggleCameraScanner()" class="text-emerald-400 hover:underline flex items-center space-x-1 font-medium">
                        <i class="fa-solid fa-camera"></i>
                        <span x-text="cameraOpen ? 'Tutup Kamera' : 'Buka Scanner Kamera Web'"></span>
                    </button>
                </div>
            </div>

            <!-- 2. Manual Product Search (Autocomplete Dropdown) -->
            <div class="md:col-span-5 relative">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <input type="text"
                           wire:model.live.debounce.300ms="searchQuery"
                           placeholder="Cari nama barang manual..."
                           class="w-full pl-10 pr-3 py-3 bg-slate-800 border border-slate-700 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30 rounded-xl text-white text-sm placeholder-slate-400 transition">
                </div>

                <!-- Autocomplete Dropdown List -->
                @if(!empty($searchResults) && count($searchResults) > 0)
                    <div class="absolute left-0 right-0 top-full mt-1 bg-slate-800 border border-slate-700 rounded-xl shadow-2xl z-30 max-h-64 overflow-y-auto divide-y divide-slate-700/60">
                        @foreach($searchResults as $res)
                            <div wire:click="selectFromSearch({{ $res->id }})"
                                 class="p-2.5 hover:bg-slate-700/80 cursor-pointer flex items-center justify-between transition">
                                <div>
                                    <div class="text-xs font-semibold text-white">{{ $res->name }}</div>
                                    <div class="text-[11px] font-mono text-slate-400">
                                        {{ $res->barcode }} • Stok: <span class="{{ $res->stock <= $res->min_stock ? 'text-amber-400 font-bold' : 'text-emerald-400' }}">{{ $res->stock }} {{ $res->unit }}</span>
                                    </div>
                                </div>
                                <div class="text-xs font-bold text-emerald-400 font-mono">
                                    Rp {{ number_format($res->selling_price, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Camera Scanner Live Modal / Container -->
        <div x-show="cameraOpen" x-cloak class="mb-4 bg-slate-800 border border-emerald-500/50 p-3 rounded-2xl relative shadow-xl">
            <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-700">
                <div class="text-xs font-bold text-emerald-400 flex items-center space-x-1.5">
                    <i class="fa-solid fa-video animate-pulse"></i>
                    <span>Scanner Kamera Aktif (Arahkan Barcode / QR Code Produk ke Lensa)</span>
                </div>
                <button type="button" @click="toggleCameraScanner()" class="text-slate-400 hover:text-white text-xs">
                    <i class="fa-solid fa-xmark"></i> Tutup
                </button>
            </div>
            <div id="reader" class="mx-auto rounded-xl overflow-hidden max-w-sm"></div>
        </div>

        <!-- CART ITEMS TABLE (SCROLLABLE) -->
        <div class="flex-1 bg-slate-800/80 rounded-2xl border border-slate-700/80 flex flex-col overflow-hidden shadow-inner">
            <!-- Table Header -->
            <div class="grid grid-cols-12 bg-slate-800 px-4 py-3 border-b border-slate-700 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                <div class="col-span-5">Produk</div>
                <div class="col-span-2 text-right">Harga</div>
                <div class="col-span-2 text-center">Jumlah (Qty)</div>
                <div class="col-span-2 text-right">Subtotal</div>
                <div class="col-span-1 text-center">Hapus</div>
            </div>

            <!-- Table Body -->
            <div class="flex-1 overflow-y-auto divide-y divide-slate-700/50">
                @forelse($cart as $id => $item)
                    <div class="grid grid-cols-12 px-4 py-2.5 items-center text-xs hover:bg-slate-700/30 transition">
                        <!-- Product Info -->
                        <div class="col-span-5">
                            <div class="font-bold text-white text-sm truncate">{{ $item['name'] }}</div>
                            <div class="text-[11px] font-mono text-slate-400 flex items-center space-x-2">
                                <span>{{ $item['barcode'] }}</span>
                                <span>•</span>
                                <span>Sisa: {{ $item['stock'] }} {{ $item['unit'] }}</span>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="col-span-2 text-right font-mono font-medium text-slate-300">
                            Rp {{ number_format($item['price'], 0, ',', '.') }}
                        </div>

                        <!-- Qty Controls (+ / - / direct input) -->
                        <div class="col-span-2 flex items-center justify-center space-x-1.5">
                            <button type="button"
                                    wire:click="decrementQty({{ $id }})"
                                    class="w-6 h-6 rounded bg-slate-700 hover:bg-slate-600 text-slate-300 flex items-center justify-center font-bold transition">
                                -
                            </button>
                            <input type="number"
                                   min="1"
                                   max="{{ $item['stock'] }}"
                                   wire:change="updateQty({{ $id }}, $event.target.value)"
                                   value="{{ $item['qty'] }}"
                                   class="w-12 text-center py-1 bg-slate-900 border border-slate-600 rounded text-white font-mono text-xs font-bold focus:ring-1 focus:ring-emerald-500">
                            <button type="button"
                                    wire:click="incrementQty({{ $id }})"
                                    class="w-6 h-6 rounded bg-slate-700 hover:bg-emerald-600 text-slate-300 hover:text-white flex items-center justify-center font-bold transition">
                                +
                            </button>
                        </div>

                        <!-- Subtotal -->
                        <div class="col-span-2 text-right font-mono font-bold text-emerald-400 text-sm">
                            Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                        </div>

                        <!-- Delete Button -->
                        <div class="col-span-1 text-center">
                            <button type="button"
                                    wire:click="removeItem({{ $id }})"
                                    title="Hapus dari keranjang"
                                    class="text-slate-500 hover:text-red-400 p-1.5 rounded transition">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="h-full flex flex-col items-center justify-center text-slate-500 py-16">
                        <i class="fa-solid fa-basket-shopping text-5xl mb-3 text-slate-600"></i>
                        <p class="text-sm font-medium">Keranjang belanja masih kosong.</p>
                        <p class="text-xs text-slate-400 mt-1">Scan barcode produk dengan scanner fisik atau ketik nama produk di atas.</p>
                    </div>
                @endforelse
            </div>

            <!-- Table Footer Quick Bar -->
            <div class="bg-slate-800/90 border-t border-slate-700 px-4 py-2.5 flex items-center justify-between text-xs text-slate-400">
                <div class="flex items-center space-x-4">
                    <span>Total Baris: <strong class="text-white">{{ count($cart) }}</strong> jenis barang</span>
                    <span>Total Kuantitas: <strong class="text-white">{{ $this->totalItems }}</strong> pcs</span>
                </div>
                @if(count($cart) > 0)
                    <button type="button"
                            wire:click="clearCart"
                            wire:confirm="Yakin ingin mengosongkan seluruh keranjang belanja?"
                            class="text-xs text-red-400 hover:text-red-300 flex items-center space-x-1">
                        <i class="fa-solid fa-trash"></i>
                        <span>Kosongkan Keranjang</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL: Checkout Summary & Payment Panel (30% width) -->
    <div class="w-80 md:w-96 bg-slate-800 flex flex-col border-l border-slate-700/80 p-5 flex-shrink-0 justify-between">
        <div class="space-y-4">
            <!-- Customer Name Input -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                    Nama Pelanggan
                </label>
                <div class="relative">
                    <input type="text"
                           wire:model="customerName"
                           placeholder="Pelanggan Umum"
                           class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-white text-xs font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>

            <!-- Ringkasan Nilai Transaksi -->
            <div class="bg-slate-900/90 rounded-2xl p-4 border border-slate-700/80 space-y-3">
                <div class="flex justify-between text-xs text-slate-400">
                    <span>Subtotal Produk</span>
                    <span class="font-mono text-slate-200">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-xs text-slate-400">
                    <span>Diskon (Rp)</span>
                    <input type="number"
                           min="0"
                           wire:model.live.debounce.300ms="discount"
                           class="w-24 text-right py-1 px-2 bg-slate-800 border border-slate-700 rounded text-xs font-mono text-white focus:ring-1 focus:ring-emerald-500">
                </div>
                <div class="pt-3 border-t border-slate-800">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Tagihan</div>
                    <div class="text-3xl font-extrabold text-emerald-400 font-mono tracking-tight">
                        Rp {{ number_format($this->grandTotal, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Fast Payment Buttons (Cash Shortcuts) -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                    Metode Pembayaran
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button"
                            wire:click="$set('paymentMethod', 'tunai')"
                            class="py-2.5 px-3 rounded-xl border text-xs font-bold flex items-center justify-center space-x-2 transition {{ $paymentMethod === 'tunai' ? 'bg-emerald-600 text-white border-emerald-500 shadow-lg shadow-emerald-600/30' : 'bg-slate-900 text-slate-400 border-slate-700 hover:bg-slate-700' }}">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <span>Tunai / Cash</span>
                    </button>
                    <button type="button"
                            wire:click="$set('paymentMethod', 'qris')"
                            class="py-2.5 px-3 rounded-xl border text-xs font-bold flex items-center justify-center space-x-2 transition {{ $paymentMethod === 'qris' ? 'bg-indigo-600 text-white border-indigo-500 shadow-lg shadow-indigo-600/30' : 'bg-slate-900 text-slate-400 border-slate-700 hover:bg-slate-700' }}">
                        <i class="fa-solid fa-qrcode"></i>
                        <span>QRIS Dinamis</span>
                    </button>
                </div>
            </div>

            <!-- Catatan Transaksi -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">
                    Catatan (Opsional)
                </label>
                <input type="text"
                       wire:model="notes"
                       placeholder="Catatan tambahan nota..."
                       class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-white text-xs placeholder-slate-500 focus:ring-1 focus:ring-emerald-500">
            </div>
        </div>

        <!-- Big Action Checkout Button (F4 / Enter) -->
        <div class="pt-4 border-t border-slate-700/80">
            <button type="button"
                    wire:click="openPaymentModal"
                    {{ empty($cart) ? 'disabled' : '' }}
                    class="w-full py-4 px-4 rounded-2xl font-extrabold text-base flex items-center justify-center space-x-2 shadow-xl transition active:scale-[0.98] {{ empty($cart) ? 'bg-slate-700 text-slate-500 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-600/30' }}">
                <i class="fa-solid fa-circle-check text-lg"></i>
                <span>Bayar Transaksi (F4)</span>
            </button>
            <p class="text-[11px] text-center text-slate-400 mt-2">
                Tekan <kbd class="px-1.5 py-0.5 bg-slate-900 rounded font-mono text-white text-[10px]">F4</kbd> untuk langsung bayar
            </p>
        </div>
    </div>


    <!-- ========================================== -->
    <!-- MODAL 1: PEMBAYARAN (TUNAI / QRIS)         -->
    <!-- ========================================== -->
    @if($showPaymentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
            <div class="bg-slate-800 border border-slate-700 rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-150">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-slate-800/80 border-b border-slate-700 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl {{ $paymentMethod === 'tunai' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-indigo-500/20 text-indigo-400' }} flex items-center justify-center font-bold text-lg">
                            <i class="{{ $paymentMethod === 'tunai' ? 'fa-solid fa-money-bill-wave' : 'fa-solid fa-qrcode' }}"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Pembayaran {{ ucfirst($paymentMethod) }}</h3>
                            <p class="text-xs text-slate-400">Total Tagihan: <span class="font-mono font-bold text-emerald-400">Rp {{ number_format($this->grandTotal, 0, ',', '.') }}</span></p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeModals" class="text-slate-400 hover:text-white text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-5">
                    @if($errorMessage)
                        <div class="bg-red-500/20 border border-red-500/40 text-red-300 p-3 rounded-xl text-xs flex items-center space-x-2">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>{{ $errorMessage }}</span>
                        </div>
                    @endif

                    @if($paymentMethod === 'tunai')
                        <!-- Uang Tunai Flow -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                                Masukkan Uang Diterima (Rp)
                            </label>
                            <input type="number"
                                   id="paid-amount-input"
                                   wire:model.live="paidAmount"
                                   wire:keydown.enter.prevent="processCheckout"
                                   class="w-full px-4 py-3 bg-slate-900 border-2 border-emerald-500/50 rounded-2xl text-2xl font-mono font-extrabold text-white text-right focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
                        </div>

                        <!-- Tombol Cepat Nominal Uang (Fast Cash) -->
                        <div>
                            <span class="block text-xs font-semibold text-slate-400 mb-2">Pilihan Cepat Uang Pecahan:</span>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" wire:click="setExactPayment"
                                        class="py-2 px-2 bg-emerald-600/30 hover:bg-emerald-600 border border-emerald-500/40 text-emerald-300 hover:text-white rounded-xl text-xs font-mono font-bold transition">
                                    Uang Pas (Rp {{ number_format($this->grandTotal, 0, ',', '.') }})
                                </button>
                                <button type="button" wire:click="setQuickMoney(20000)"
                                        class="py-2 px-2 bg-slate-700/60 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-mono font-bold transition">
                                    Rp 20.000
                                </button>
                                <button type="button" wire:click="setQuickMoney(50000)"
                                        class="py-2 px-2 bg-slate-700/60 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-mono font-bold transition">
                                    Rp 50.000
                                </button>
                                <button type="button" wire:click="setQuickMoney(100000)"
                                        class="py-2 px-2 bg-slate-700/60 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-mono font-bold transition">
                                    Rp 100.000
                                </button>
                                <button type="button" wire:click="setQuickMoney(200000)"
                                        class="py-2 px-2 bg-slate-700/60 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-mono font-bold transition">
                                    Rp 200.000
                                </button>
                                <button type="button" wire:click="setQuickMoney(500000)"
                                        class="py-2 px-2 bg-slate-700/60 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-mono font-bold transition">
                                    Rp 500.000
                                </button>
                            </div>
                        </div>

                        <!-- Box Kembalian -->
                        <div class="bg-slate-900 rounded-2xl p-4 border border-slate-700 flex items-center justify-between">
                            <div>
                                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kembalian Uang</div>
                                <div class="text-xs text-slate-500">Otomatis dihitung oleh sistem</div>
                            </div>
                            <div class="text-2xl font-extrabold font-mono {{ $this->changeAmount >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                Rp {{ number_format($this->changeAmount, 0, ',', '.') }}
                            </div>
                        </div>
                    @else
                        <!-- QRIS Dinamis Flow -->
                        <div class="text-center py-4 bg-slate-900 rounded-2xl border border-slate-700 p-6 space-y-3">
                            <div class="inline-block p-3 bg-white rounded-2xl shadow-lg">
                                <!-- Simulated dynamic QR code -->
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=QRIS_MARKET_TRX_{{ time() }}_{{ $this->grandTotal }}"
                                     alt="QRIS Dinamis"
                                     class="w-44 h-44 object-contain">
                            </div>
                            <div class="text-sm font-bold text-white">QRIS Dinamis - Webmarket Dustin</div>
                            <div class="text-xs text-slate-400 max-w-xs mx-auto">
                                Minta pelanggan melakukan scan QR menggunakan BCA, GoPay, OVO, ShopeePay, DANA, atau Livin' Mandiri.
                            </div>
                            <div class="inline-block px-3 py-1 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-full text-xs font-mono font-bold">
                                Nominal: Rp {{ number_format($this->grandTotal, 0, ',', '.') }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-slate-900/90 border-t border-slate-700 flex items-center justify-between">
                    <button type="button" wire:click="closeModals"
                            class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-xl text-xs font-bold transition">
                        Batal (ESC)
                    </button>
                    <button type="button"
                            wire:click="processCheckout"
                            wire:loading.attr="disabled"
                            class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-600/30 flex items-center space-x-2 transition active:scale-95">
                        <span wire:loading.remove>
                            <i class="fa-solid fa-print mr-1"></i> Simpan & Cetak Struk (Enter)
                        </span>
                        <span wire:loading>
                            <i class="fa-solid fa-spinner fa-spin mr-1"></i> Memproses Transaksi...
                        </span>
                    </button>
                </div>

            </div>
        </div>
    @endif


    <!-- ======================================================== -->
    <!-- MODAL 2: CETAK STRUK THERMAL BELANJA (ESC/POS 58mm/80mm) -->
    <!-- ======================================================== -->
    @if($showReceiptModal && $completedTransaction)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
            <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-150 text-slate-900">
                
                <!-- Receipt Preview Frame (Formatted for 58mm / 80mm ESC/POS Thermal Printers) -->
                <div id="printable-receipt" class="p-6 font-mono text-xs leading-relaxed">
                    <div class="text-center pb-3 border-b border-dashed border-slate-400">
                        <h2 class="text-base font-extrabold uppercase tracking-wider">WEBMARKET DUSTIN</h2>
                        <p class="text-[10px] text-slate-600">Jl. Retail Raya No. 88, Jakarta</p>
                        <p class="text-[10px] text-slate-600">Telp/WA: 0812-3456-7890</p>
                    </div>

                    <div class="py-2.5 border-b border-dashed border-slate-400 text-[10px] space-y-1">
                        <div class="flex justify-between">
                            <span>No Nota:</span>
                            <span class="font-bold">{{ $completedTransaction->invoice_code }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Tanggal:</span>
                            <span>{{ $completedTransaction->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Kasir:</span>
                            <span>{{ $completedTransaction->user->name ?? 'Kasir' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pelanggan:</span>
                            <span>{{ $completedTransaction->customer_name }}</span>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="py-2.5 border-b border-dashed border-slate-400 space-y-2">
                        @foreach($completedTransaction->details as $det)
                            <div>
                                <div class="font-bold text-[11px]">{{ $det->product_name }}</div>
                                <div class="flex justify-between text-[10px] text-slate-600">
                                    <span>{{ $det->quantity }} x {{ number_format($det->selling_price, 0, ',', '.') }}</span>
                                    <span class="font-bold text-slate-900">Rp {{ number_format($det->subtotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Totals -->
                    <div class="py-2.5 border-b border-dashed border-slate-400 text-[11px] space-y-1">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>Rp {{ number_format($completedTransaction->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($completedTransaction->discount > 0)
                            <div class="flex justify-between text-red-600">
                                <span>Diskon:</span>
                                <span>-Rp {{ number_format($completedTransaction->discount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-xs font-extrabold pt-1 border-t border-slate-300">
                            <span>GRAND TOTAL:</span>
                            <span>Rp {{ number_format($completedTransaction->grand_total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] pt-1 text-slate-600">
                            <span>Metode:</span>
                            <span class="uppercase font-bold">{{ $completedTransaction->payment_method }}</span>
                        </div>
                        <div class="flex justify-between text-[10px]">
                            <span>Dibayar:</span>
                            <span>Rp {{ number_format($completedTransaction->paid_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] font-bold">
                            <span>Kembalian:</span>
                            <span>Rp {{ number_format($completedTransaction->change_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Receipt Footer -->
                    <div class="text-center pt-3 text-[10px] text-slate-500 space-y-1">
                        <p class="font-semibold">Terima Kasih Atas Kunjungan Anda!</p>
                        <p class="text-[9px]">Barang yang sudah dibeli tidak dapat ditukar kecuali ada perjanjian.</p>
                        <div class="pt-1 text-[8px] text-slate-400 font-mono">
                            --- SISTEM RETAIL DUSTIN POS ---
                        </div>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="p-4 bg-slate-100 border-t border-slate-200 flex space-x-2 no-print">
                    <button type="button" onclick="window.print()"
                            class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center justify-center space-x-1 shadow transition">
                        <i class="fa-solid fa-print"></i>
                        <span>Cetak Struk Thermal</span>
                    </button>
                    <button type="button" wire:click="closeModals"
                            class="px-4 py-3 bg-slate-300 hover:bg-slate-400 text-slate-800 rounded-xl text-xs font-bold transition">
                        Selesai / Tutup
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>

<!-- JavaScript Integration for POS Keyboard Shortcuts & Camera Scanner -->
<script>
    function posApp() {
        return {
            cameraOpen: false,
            html5QrCode: null,

            init() {
                // Focus barcode on load
                this.focusBarcode();

                // Livewire event listeners
                window.addEventListener('focus-barcode-input', () => this.focusBarcode());
                window.addEventListener('focus-paid-input', () => {
                    setTimeout(() => {
                        const el = document.getElementById('paid-amount-input');
                        if (el) el.focus();
                    }, 100);
                });

                // Auto-print receipt trigger
                window.addEventListener('print-receipt', (event) => {
                    setTimeout(() => {
                        window.print();
                    }, 300);
                });

                // Global Keyboard Shortcuts (F2, F4, ESC)
                window.addEventListener('keydown', (e) => {
                    // F2 -> Focus Barcode Scanner Input
                    if (e.key === 'F2') {
                        e.preventDefault();
                        this.focusBarcode();
                    }
                    // F4 -> Open Payment Modal
                    if (e.key === 'F4') {
                        e.preventDefault();
                        @this.openPaymentModal();
                    }
                    // Escape -> Close Modals
                    if (e.key === 'Escape') {
                        @this.closeModals();
                    }
                });
            },

            focusBarcode() {
                setTimeout(() => {
                    const el = document.getElementById('barcode-input');
                    if (el) el.focus();
                }, 50);
            },

            toggleCameraScanner() {
                this.cameraOpen = !this.cameraOpen;
                if (this.cameraOpen) {
                    this.$nextTick(() => {
                        this.startCamera();
                    });
                } else {
                    this.stopCamera();
                }
            },

            startCamera() {
                const config = { fps: 10, qrbox: { width: 250, height: 180 } };
                this.html5QrCode = new Html5Qrcode("reader");

                this.html5QrCode.start(
                    { facingMode: "environment" },
                    config,
                    (decodedText) => {
                        // Success callback when barcode/QR is scanned
                        @this.dispatch('onBarcodeScanned', { code: decodedText });
                        // Brief pause / feedback
                        const beep = new AudioContext();
                        const osc = beep.createOscillator();
                        osc.connect(beep.destination);
                        osc.start();
                        osc.stop(beep.currentTime + 0.1);
                    },
                    (errorMessage) => {
                        // Scanner searching... ignore
                    }
                ).catch((err) => {
                    console.error("Gagal menyalakan kamera:", err);
                    alert("Kamera tidak ditemukan atau izin akses kamera ditolak.");
                    this.cameraOpen = false;
                });
            },

            stopCamera() {
                if (this.html5QrCode) {
                    this.html5QrCode.stop().then(() => {
                        this.html5QrCode.clear();
                    }).catch(err => console.error(err));
                }
            }
        };
    }
</script>


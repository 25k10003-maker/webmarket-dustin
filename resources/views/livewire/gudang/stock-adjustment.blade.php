<div>
    @if($successMessage)
        <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span>{{ $successMessage }}</span>
            </div>
            <button type="button" wire:click="$set('successMessage', '')" class="text-emerald-500 hover:text-emerald-800">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if($errorMessage)
        <div class="mb-5 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-2xl text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-circle-exclamation text-red-600"></i>
                <span>{{ $errorMessage }}</span>
            </div>
            <button type="button" wire:click="$set('errorMessage', '')" class="text-red-500 hover:text-red-800">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <div class="max-w-3xl mx-auto bg-white p-8 rounded-3xl border border-slate-200 shadow-sm space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center space-x-2">
                <i class="fa-solid fa-sliders text-amber-500"></i>
                <span>Form Koreksi & Penyesuaian Selisih Stok Fisik</span>
            </h3>
            <p class="text-xs text-slate-500 mt-1">
                Gunakan modul ini jika terdapat perbedaan antara stok di aplikasi dan stok fisik di rak/gudang (akibat opname, rusak, susut, atau hilang).
            </p>
        </div>

        <!-- 1. Search Product -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Cari Produk yang Mengalami Selisih</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="searchProduct"
                       placeholder="Ketik barcode atau nama barang..."
                       class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500">
            </div>

            @if(!empty($searchResults) && count($searchResults) > 0)
                <div class="mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-20 max-h-56 overflow-y-auto divide-y divide-slate-100">
                    @foreach($searchResults as $res)
                        <div wire:click="selectProduct({{ $res->id }})"
                             class="p-2.5 hover:bg-amber-50 cursor-pointer flex items-center justify-between transition">
                            <div>
                                <div class="text-xs font-bold text-slate-800">{{ $res->name }}</div>
                                <div class="text-[10px] font-mono text-slate-400">Barcode: {{ $res->barcode }} • Stok Sistem: {{ $res->stock }} {{ $res->unit }}</div>
                            </div>
                            <span class="text-xs font-bold text-amber-600 bg-amber-100 px-2.5 py-1 rounded-md">
                                Pilih
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if($selectedProduct)
            <!-- 2. Selected Product Information Card -->
            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div class="md:text-left">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase">Produk Terpilih</div>
                    <div class="text-sm font-bold text-slate-900">{{ $selectedProduct->name }}</div>
                    <div class="text-[10px] font-mono text-slate-500">{{ $selectedProduct->barcode }}</div>
                </div>

                <div>
                    <div class="text-[11px] font-semibold text-slate-400 uppercase">Stok Sistem Saat Ini</div>
                    <div class="text-2xl font-extrabold font-mono text-slate-700">
                        {{ $selectedProduct->stock }} <span class="text-xs font-normal text-slate-500">{{ $selectedProduct->unit }}</span>
                    </div>
                </div>

                <div>
                    <div class="text-[11px] font-semibold text-slate-400 uppercase">Selisih Stok</div>
                    @php
                        $diff = $physicalStock - $selectedProduct->stock;
                    @endphp
                    <div class="text-2xl font-extrabold font-mono {{ $diff > 0 ? 'text-emerald-600' : ($diff < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        {{ $diff > 0 ? "+{$diff}" : $diff }} <span class="text-xs font-normal text-slate-500">{{ $selectedProduct->unit }}</span>
                    </div>
                </div>
            </div>

            <!-- 3. Physical Input & Reason -->
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Jumlah Stok Fisik Hasil Penghitungan Aktual ({{ $selectedProduct->unit }})
                    </label>
                    <input type="number" min="0" wire:model.live="physicalStock"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-base font-mono font-bold text-slate-800 focus:ring-2 focus:ring-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Penyesuaian / Selisih</label>
                    <select wire:model="adjustmentReason"
                            class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500">
                        <option value="Selisih Hasil Stock Opname Fisik">Selisih Hasil Stock Opname Fisik</option>
                        <option value="Barang Rusak / Kadaluarsa">Barang Rusak / Kadaluarsa (Expired)</option>
                        <option value="Barang Hilang / Dugaan Pencurian">Barang Hilang / Dugaan Pencurian</option>
                        <option value="Koreksi Salah Input Nota Sebelumnya">Koreksi Salah Input Nota Sebelumnya</option>
                        <option value="Bonus / Sample dari Supplier">Bonus / Sample dari Supplier</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <input type="text" wire:model="notes" placeholder="No Berita Acara / Keterangan rak lokasi..."
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500">
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                    <button type="button" wire:click="$set('selectedProduct', null)"
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="button"
                            wire:click="processAdjustment"
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-xl text-xs font-bold shadow-md shadow-amber-500/20 flex items-center space-x-1.5 transition">
                        <span wire:loading.remove><i class="fa-solid fa-check mr-1"></i> Simpan Penyesuaian Stok</span>
                        <span wire:loading><i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan...</span>
                    </button>
                </div>
            </div>
        @else
            <div class="p-12 text-center text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                <i class="fa-solid fa-barcode text-4xl mb-3 text-slate-300"></i>
                <p class="text-xs font-medium">Silakan cari dan pilih produk di atas untuk memulai penyesuaian stok.</p>
            </div>
        @endif
    </div>
</div>


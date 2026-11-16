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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- LEFT: Header Dokumen Penerimaan Barang -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center space-x-2 text-sm font-bold text-slate-800 pb-3 border-b border-slate-100">
                <i class="fa-solid fa-file-invoice text-amber-500"></i>
                <span>Informasi Surat Jalan & Supplier</span>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">No. Referensi / Surat Jalan (PO)</label>
                <input type="text" wire:model="referenceNumber" required
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-amber-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pemasok / Distributor</label>
                <input type="text" wire:model="supplier" placeholder="Contoh: PT Indofood Sukses Makmur"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Penerimaan (Opsional)</label>
                <textarea wire:model="notes" rows="3" placeholder="Kondisi dus rapi, segel utuh..."
                          class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <button type="button"
                        wire:click="processStockIn"
                        wire:loading.attr="disabled"
                        class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-xl text-xs font-bold shadow-md shadow-amber-500/20 flex items-center justify-center space-x-2 transition">
                    <span wire:loading.remove><i class="fa-solid fa-check-double mr-1"></i> Simpan & Update Stok Masuk</span>
                    <span wire:loading><i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan ke Database...</span>
                </button>
            </div>
        </div>

        <!-- RIGHT: Cari & Daftar Barang Masuk -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="text-sm font-bold text-slate-800 flex items-center space-x-2">
                    <i class="fa-solid fa-boxes-stacked text-amber-500"></i>
                    <span>Daftar Barang yang Diterima</span>
                </div>
                <div class="text-xs text-slate-500 font-medium">
                    Total Item: <strong class="text-slate-800">{{ count($items) }}</strong> jenis
                </div>
            </div>

            <!-- Product Search Input -->
            <div class="relative">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <input type="text"
                           wire:model.live.debounce.300ms="searchProduct"
                           placeholder="Ketik barcode atau nama barang yang diterima..."
                           class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500">
                </div>

                @if(!empty($searchResults) && count($searchResults) > 0)
                    <div class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-20 max-h-56 overflow-y-auto divide-y divide-slate-100">
                        @foreach($searchResults as $res)
                            <div wire:click="addProduct({{ $res->id }})"
                                 class="p-2.5 hover:bg-amber-50 cursor-pointer flex items-center justify-between transition">
                                <div>
                                    <div class="text-xs font-bold text-slate-800">{{ $res->name }}</div>
                                    <div class="text-[10px] font-mono text-slate-400">Barcode: {{ $res->barcode }} • Stok Saat Ini: {{ $res->stock }} {{ $res->unit }}</div>
                                </div>
                                <span class="text-xs font-bold text-amber-600 bg-amber-100 px-2 py-0.5 rounded-md">
                                    + Tambah
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Items Table -->
            <div class="flex-1 overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-2.5">Produk</th>
                            <th class="px-4 py-2.5 text-center">Stok Lama</th>
                            <th class="px-4 py-2.5 text-center">Jumlah Masuk (+)</th>
                            <th class="px-4 py-2.5 text-center">Stok Akhir</th>
                            <th class="px-4 py-2.5 text-right">Harga Beli Baru (Rp)</th>
                            <th class="px-4 py-2.5 text-center">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($items as $id => $item)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-800">{{ $item['name'] }}</div>
                                    <div class="text-[10px] font-mono text-slate-400">{{ $item['barcode'] }}</div>
                                </td>
                                <td class="px-4 py-3 text-center font-mono text-slate-500">
                                    {{ $item['current_stock'] }} {{ $item['unit'] }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number" min="1"
                                           value="{{ $item['quantity'] }}"
                                           wire:change="updateItemQty({{ $id }}, $event.target.value)"
                                           class="w-16 text-center py-1 bg-amber-50/60 border border-amber-300 rounded font-bold font-mono text-amber-900 focus:ring-1 focus:ring-amber-500">
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-bold text-emerald-600">
                                    {{ $item['current_stock'] + $item['quantity'] }} {{ $item['unit'] }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <input type="number" min="0" step="100"
                                           wire:model="items.{{ $id }}.purchase_price"
                                           class="w-24 text-right py-1 px-2 bg-slate-50 border border-slate-300 rounded font-mono text-xs">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" wire:click="removeItem({{ $id }})" class="text-slate-400 hover:text-red-500">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-truck-ramp-box text-3xl mb-2 text-slate-300"></i>
                                    <p class="text-xs">Belum ada barang dipilih. Gunakan pencarian di atas untuk menambah barang masuk.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>


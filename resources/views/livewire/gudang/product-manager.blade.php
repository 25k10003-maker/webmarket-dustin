<div>
    <!-- Feedback Alerts -->
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

    <!-- Controls & Filters Toolbar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
            <!-- Search -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Cari nama, barcode, atau SKU..."
                       class="w-full pl-10 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
            </div>

            <!-- Category Filter -->
            <div>
                <select wire:model.live="selectedCategory"
                        class="w-full py-2 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Stock Condition Filter -->
            <div>
                <select wire:model.live="stockFilter"
                        class="w-full py-2 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    <option value="all">Semua Kondisi Stok</option>
                    <option value="low">Peringatan: Stok Menipis (&le; Min)</option>
                    <option value="out">Kritis: Stok Habis (0)</option>
                </select>
            </div>
        </div>

        <div>
            <button type="button"
                    wire:click="openCreateModal"
                    class="w-full md:w-auto px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow-md shadow-amber-500/20 flex items-center justify-center space-x-2 transition">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Produk Baru</span>
            </button>
        </div>
    </div>

    <!-- Products Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-200 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Kode Barcode / SKU</th>
                        <th class="px-5 py-3.5">Nama Produk</th>
                        <th class="px-5 py-3.5">Kategori</th>
                        <th class="px-5 py-3.5 text-right">Harga Beli</th>
                        <th class="px-5 py-3.5 text-right">Harga Jual</th>
                        <th class="px-5 py-3.5 text-center">Stok Fisik</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($products as $prod)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Barcode / SKU -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="font-mono font-bold text-slate-800">{{ $prod->barcode }}</div>
                                <div class="text-[10px] font-mono text-slate-400">SKU: {{ $prod->sku }}</div>
                            </td>

                            <!-- Name -->
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900">{{ $prod->name }}</div>
                                <div class="text-[10px] text-slate-400">Satuan: {{ $prod->unit }}</div>
                            </td>

                            <!-- Category -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 text-[11px] font-medium">
                                    {{ $prod->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>

                            <!-- Purchase Price -->
                            <td class="px-5 py-3.5 text-right font-mono text-slate-600 whitespace-nowrap">
                                Rp {{ number_format($prod->purchase_price, 0, ',', '.') }}
                            </td>

                            <!-- Selling Price -->
                            <td class="px-5 py-3.5 text-right font-mono font-bold text-emerald-600 whitespace-nowrap">
                                Rp {{ number_format($prod->selling_price, 0, ',', '.') }}
                            </td>

                            <!-- Stock Status Badge -->
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                @if($prod->stock <= 0)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-100 text-red-800 border border-red-200">
                                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Habis (0)
                                    </span>
                                @elseif($prod->stock <= $prod->min_stock)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $prod->stock }} {{ $prod->unit }} (Menipis)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        {{ $prod->stock }} {{ $prod->unit }}
                                    </span>
                                @endif
                            </td>

                            <!-- Active Status -->
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <button type="button"
                                        wire:click="toggleActive({{ $prod->id }})"
                                        class="text-xs px-2.5 py-0.5 rounded-full font-bold transition {{ $prod->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                                    {{ $prod->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <button type="button"
                                        wire:click="editProduct({{ $prod->id }})"
                                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-box-open text-4xl mb-2 text-slate-300"></i>
                                <p class="text-sm font-medium">Tidak ada produk ditemukan sesuai filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
            {{ $products->links() }}
        </div>
    </div>


    <!-- ============================================== -->
    <!-- MODAL: TAMBAH / EDIT PRODUK MASTER             -->
    <!-- ============================================== -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white rounded-3xl w-full max-w-2xl shadow-2xl overflow-hidden border border-slate-200 animate-in fade-in zoom-in duration-150">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800">
                        {{ $editingProductId ? 'Edit Data Master Produk' : 'Tambah Produk Baru ke Database' }}
                    </h3>
                    <button type="button" wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form wire:submit.prevent="saveProduct" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Barcode -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Barcode (EAN-13 / Scanner)</label>
                            <input type="text" wire:model="barcode" required
                                   placeholder="899..."
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-medium focus:ring-2 focus:ring-amber-500">
                            @error('barcode') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- SKU -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kode SKU Barang</label>
                            <input type="text" wire:model="sku" required
                                   placeholder="SKU-..."
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-medium focus:ring-2 focus:ring-amber-500">
                            @error('sku') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Product Name -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Produk Lengkap</label>
                            <input type="text" wire:model="name" required
                                   placeholder="Contoh: Indomie Goreng Spesial 85g"
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500">
                            @error('name') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Barang</label>
                            <select wire:model="category_id"
                                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Unit -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Satuan Kemasan</label>
                            <input type="text" wire:model="unit" required
                                   placeholder="pcs, botol, kg, sak, box"
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500">
                            @error('unit') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Purchase Price -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Beli Modal (Rp)</label>
                            <input type="number" step="100" min="0" wire:model="purchase_price" required
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-medium focus:ring-2 focus:ring-amber-500">
                            @error('purchase_price') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Selling Price -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Jual Toko (Rp)</label>
                            <input type="number" step="100" min="0" wire:model="selling_price" required
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-medium focus:ring-2 focus:ring-amber-500">
                            @error('selling_price') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Current Stock -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Stok Fisik Awal</label>
                            <input type="number" min="0" wire:model="stock" required
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-medium focus:ring-2 focus:ring-amber-500">
                            @error('stock') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Min Stock Alert Threshold -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Batas Minimum Stok (Alert)</label>
                            <input type="number" min="0" wire:model="min_stock" required
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-medium focus:ring-2 focus:ring-amber-500">
                            @error('min_stock') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-2">
                        <button type="button" wire:click="$set('showModal', false)"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-xl text-xs font-bold shadow-md shadow-amber-500/20 transition">
                            Simpan ke Database
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>


<div>
    <!-- Filters Toolbar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
        <!-- Search Keyword -->
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Cari produk, barcode, no nota..."
                   class="w-full pl-10 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500">
        </div>

        <!-- Mutation Type -->
        <div>
            <select wire:model.live="typeFilter"
                    class="w-full py-2 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500">
                <option value="all">Semua Jenis Mutasi</option>
                <option value="in">Barang Masuk (Restock)</option>
                <option value="out_sale">Penjualan Kasir (POS)</option>
                <option value="adjustment_in">Penyesuaian (+)</option>
                <option value="adjustment_out">Penyesuaian (-)</option>
            </select>
        </div>

        <!-- Date Range: Start Date -->
        <div>
            <input type="date" wire:model.live="startDate"
                   class="w-full py-2 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500">
        </div>

        <!-- Date Range: End Date & Reset -->
        <div class="flex space-x-2">
            <input type="date" wire:model.live="endDate"
                   class="w-full py-2 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500">
            <button type="button" wire:click="resetFilters" title="Reset Filter"
                    class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                <i class="fa-solid fa-rotate-left"></i>
            </button>
        </div>
    </div>

    <!-- Mutations Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-200 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Waktu Mutasi</th>
                        <th class="px-5 py-3.5">Nama Produk</th>
                        <th class="px-5 py-3.5 text-center">Tipe Transaksi</th>
                        <th class="px-5 py-3.5 text-center">Perubahan Qty</th>
                        <th class="px-5 py-3.5 text-center">Perubahan Stok (Awal &rarr; Akhir)</th>
                        <th class="px-5 py-3.5">No Referensi</th>
                        <th class="px-5 py-3.5">Keterangan</th>
                        <th class="px-5 py-3.5">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($mutations as $m)
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Timestamp -->
                            <td class="px-5 py-3.5 whitespace-nowrap text-slate-500 font-mono text-[11px]">
                                {{ $m->created_at->format('d/m/Y H:i') }}
                            </td>

                            <!-- Product -->
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-800">{{ $m->product->name ?? 'Produk Dihapus' }}</div>
                                <div class="text-[10px] font-mono text-slate-400">{{ $m->product->barcode ?? '-' }}</div>
                            </td>

                            <!-- Type Badge -->
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                @if($m->type === 'in')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Barang Masuk
                                    </span>
                                @elseif($m->type === 'out_sale')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        Penjualan Kasir
                                    </span>
                                @elseif($m->type === 'adjustment_in')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Penyesuaian (+)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">
                                        Penyesuaian (-)
                                    </span>
                                @endif
                            </td>

                            <!-- Qty Change -->
                            <td class="px-5 py-3.5 text-center font-mono font-bold whitespace-nowrap text-sm {{ in_array($m->type, ['in', 'adjustment_in']) ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ in_array($m->type, ['in', 'adjustment_in']) ? "+{$m->quantity}" : "-{$m->quantity}" }}
                            </td>

                            <!-- Previous -> Current -->
                            <td class="px-5 py-3.5 text-center font-mono text-slate-600 whitespace-nowrap">
                                {{ $m->previous_stock }} &rarr; <span class="font-bold text-slate-900">{{ $m->current_stock }}</span>
                            </td>

                            <!-- Reference -->
                            <td class="px-5 py-3.5 font-mono text-slate-700 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 font-semibold text-[11px]">
                                    {{ $m->reference_number ?? '-' }}
                                </span>
                            </td>

                            <!-- Notes -->
                            <td class="px-5 py-3.5 text-slate-600 text-xs">
                                {{ $m->notes ?? '-' }}
                            </td>

                            <!-- User -->
                            <td class="px-5 py-3.5 text-slate-700 whitespace-nowrap font-medium text-xs">
                                {{ $m->user->name ?? 'Sistem' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-clock-rotate-left text-3xl mb-2 text-slate-300"></i>
                                <p class="text-xs">Tidak ada riwayat mutasi stok ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
            {{ $mutations->links() }}
        </div>
    </div>
</div>


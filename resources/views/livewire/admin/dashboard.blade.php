<div class="space-y-6" id="summary-section" x-data="adminDashboard()">
    
    <!-- 1. Period Selector Filter Pills (Mobile Touch Friendly) -->
    <div class="bg-white p-2 rounded-2xl shadow-sm border border-slate-200 flex space-x-1">
        <button type="button"
                wire:click="setPeriod('today')"
                class="flex-1 py-2 rounded-xl text-xs font-bold transition text-center {{ $period === 'today' ? 'bg-indigo-600 text-white shadow' : 'text-slate-600 hover:bg-slate-100' }}">
            Hari Ini
        </button>
        <button type="button"
                wire:click="setPeriod('this_week')"
                class="flex-1 py-2 rounded-xl text-xs font-bold transition text-center {{ $period === 'this_week' ? 'bg-indigo-600 text-white shadow' : 'text-slate-600 hover:bg-slate-100' }}">
            7 Hari
        </button>
        <button type="button"
                wire:click="setPeriod('this_month')"
                class="flex-1 py-2 rounded-xl text-xs font-bold transition text-center {{ $period === 'this_month' ? 'bg-indigo-600 text-white shadow' : 'text-slate-600 hover:bg-slate-100' }}">
            Bulan Ini
        </button>
    </div>

    <!-- Active Filter Indicator -->
    <div class="flex items-center justify-between px-1 text-xs text-slate-500 font-medium">
        <span>Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong></span>
        <span class="text-emerald-600 font-bold flex items-center">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-ping"></span> Live Update
        </span>
    </div>

    <!-- 2. KPI Analytical Metric Cards (2x2 Grid) -->
    <div class="grid grid-cols-2 gap-3.5">
        
        <!-- Total Omzet -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm mb-2">
                <i class="fa-solid fa-rupiah-sign"></i>
            </div>
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Omzet</div>
            <div class="text-base md:text-xl font-extrabold text-slate-900 font-mono mt-0.5 truncate">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-slate-400 mt-1">Bruto Penjualan Toko</div>
        </div>

        <!-- Keuntungan Bersih (Gross Profit) -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm mb-2">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Keuntungan Bersih</div>
            <div class="text-base md:text-xl font-extrabold text-indigo-600 font-mono mt-0.5 truncate">
                Rp {{ number_format($grossProfit, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-slate-400 mt-1">Laba Kotor (Jual - Modal)</div>
        </div>

        <!-- Total Transaksi Kasir -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-sm mb-2">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Transaksi</div>
            <div class="text-base md:text-xl font-extrabold text-slate-900 font-mono mt-0.5">
                {{ $totalTransactions }} <span class="text-xs font-normal text-slate-500">Nota</span>
            </div>
            <div class="text-[10px] text-slate-400 mt-1">Rata-rata: Rp {{ number_format($avgTicket, 0, ',', '.') }}</div>
        </div>

        <!-- Peringatan Stok Menipis -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-sm mb-2">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Stok Menipis</div>
            <div class="text-base md:text-xl font-extrabold {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-emerald-600' }} font-mono mt-0.5">
                {{ $lowStockCount }} <span class="text-xs font-normal text-slate-500">Item</span>
            </div>
            <div class="text-[10px] text-slate-400 mt-1">&le; Ambang Minimum Gudang</div>
        </div>

    </div>

    <!-- 3. Metode Pembayaran Perbandingan -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <div class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Distribusi Metode Pembayaran</div>
        <div class="grid grid-cols-2 gap-3">
            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                <div class="flex items-center space-x-1.5 text-xs text-emerald-800 font-bold">
                    <i class="fa-solid fa-money-bill-wave"></i>
                    <span>Tunai / Cash</span>
                </div>
                <div class="text-sm font-extrabold text-emerald-900 font-mono mt-1">
                    Rp {{ number_format($cashTotal, 0, ',', '.') }}
                </div>
            </div>

            <div class="p-3 rounded-xl bg-indigo-50 border border-indigo-100">
                <div class="flex items-center space-x-1.5 text-xs text-indigo-800 font-bold">
                    <i class="fa-solid fa-qrcode"></i>
                    <span>QRIS Dinamis</span>
                </div>
                <div class="text-sm font-extrabold text-indigo-900 font-mono mt-1">
                    Rp {{ number_format($qrisTotal, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Interactive Trend Chart (Sales & Profit) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm" id="chart-section">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Grafik Omzet & Keuntungan</h3>
                <p class="text-[11px] text-slate-400">Tren performa harian (7 hari terakhir)</p>
            </div>
            <div class="flex items-center space-x-3 text-[10px]">
                <span class="flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-indigo-500 mr-1"></span> Omzet</span>
                <span class="flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-1"></span> Laba</span>
            </div>
        </div>

        <div class="relative h-56 w-full">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- 5. Top 5 Best Selling Products Leaderboard -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm" id="top-products-section">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
            <div class="flex items-center space-x-2">
                <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-fire"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Produk Terlaris</h3>
                    <p class="text-[10px] text-slate-400">Leaderboard item paling laku terjual</p>
                </div>
            </div>
            <span class="text-[10px] font-bold text-indigo-600 uppercase">Top 5</span>
        </div>

        <div class="space-y-3">
            @forelse($topProducts as $index => $top)
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                    <div class="flex items-center space-x-3">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs {{ $index === 0 ? 'bg-amber-400 text-slate-900' : ($index === 1 ? 'bg-slate-300 text-slate-800' : ($index === 2 ? 'bg-amber-600 text-white' : 'bg-slate-200 text-slate-600')) }}">
                            {{ $index + 1 }}
                        </span>
                        <div>
                            <div class="font-bold text-slate-800 truncate max-w-[150px] md:max-w-xs">{{ $top->product_name }}</div>
                            <div class="text-[10px] text-slate-500">Total Omzet: Rp {{ number_format($top->total_sales, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 rounded-md bg-emerald-100 text-emerald-800 font-bold font-mono text-xs">
                            {{ $top->total_qty }} terjual
                        </span>
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-slate-400 text-xs">
                    Belum ada transaksi penjualan pada periode ini.
                </div>
            @endforelse
        </div>
    </div>

    <!-- 6. Low Stock Alerts List -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm" id="stock-alerts-section">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
            <div class="flex items-center space-x-2">
                <div class="w-7 h-7 rounded-lg bg-red-500/10 text-red-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Peringatan Stok Menipis</h3>
                    <p class="text-[10px] text-slate-400">Segera restock untuk mencegah kehabisan</p>
                </div>
            </div>
            <span class="text-xs font-bold font-mono text-red-600">{{ $lowStockCount }} item</span>
        </div>

        <div class="space-y-2.5">
            @forelse($lowStockProducts as $low)
                <div class="p-3 rounded-xl border {{ $low->stock <= 0 ? 'bg-red-50 border-red-200' : 'bg-amber-50 border-amber-200' }} flex items-center justify-between text-xs">
                    <div>
                        <div class="font-bold {{ $low->stock <= 0 ? 'text-red-900' : 'text-amber-900' }}">{{ $low->name }}</div>
                        <div class="text-[10px] font-mono text-slate-500">Barcode: {{ $low->barcode }} • Min: {{ $low->min_stock }} {{ $low->unit }}</div>
                    </div>
                    <div class="text-right">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold font-mono {{ $low->stock <= 0 ? 'bg-red-600 text-white' : 'bg-amber-500 text-slate-950' }}">
                            {{ $low->stock <= 0 ? 'Habis (0)' : 'Sisa ' . $low->stock . ' ' . $low->unit }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-slate-400 text-xs">
                    <i class="fa-solid fa-circle-check text-emerald-500 text-xl mb-1"></i>
                    <p>Semua stok produk dalam kondisi aman di atas batas minimum.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- 7. Recent Transactions Feed (Read-Only) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">Aktivitas Transaksi Kasir</h3>
            <span class="text-[10px] text-slate-400">Live Feed</span>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($recentTransactions as $rt)
                <div class="py-3 flex items-center justify-between text-xs">
                    <div>
                        <div class="font-mono font-bold text-slate-800">{{ $rt->invoice_code }}</div>
                        <div class="text-[10px] text-slate-400">{{ $rt->created_at->format('d M H:i') }} • Kasir: {{ $rt->user->name ?? 'Kasir' }}</div>
                    </div>
                    <div class="text-right flex items-center space-x-3">
                        <div>
                            <div class="font-bold font-mono text-slate-900">Rp {{ number_format($rt->grand_total, 0, ',', '.') }}</div>
                            <span class="text-[10px] px-1.5 py-0.5 rounded uppercase font-semibold {{ $rt->payment_method === 'tunai' ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800' }}">
                                {{ $rt->payment_method }}
                            </span>
                        </div>
                        <button type="button"
                                wire:click="showTransactionDetail({{ $rt->id }})"
                                class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-slate-400 text-xs">
                    Tidak ada transaksi terbaru.
                </div>
            @endforelse
        </div>
    </div>

    <!-- MODAL: Detail Transaksi (Read-Only Nota Preview) -->
    @if($showDetailModal && $viewingTransaction)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
            <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-150 text-slate-900">
                <div class="px-5 py-3.5 bg-slate-100 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-800">Detail Nota #{{ $viewingTransaction->invoice_code }}</h3>
                    <button type="button" wire:click="closeDetailModal" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-5 text-xs font-mono space-y-3">
                    <div class="text-center pb-2 border-b border-dashed border-slate-300">
                        <div class="font-bold text-sm">WEBMARKET DUSTIN</div>
                        <div class="text-[10px] text-slate-500">Tanggal: {{ $viewingTransaction->created_at->format('d/m/Y H:i:s') }}</div>
                        <div class="text-[10px] text-slate-500">Kasir: {{ $viewingTransaction->user->name ?? '-' }}</div>
                    </div>

                    <div class="space-y-1.5 py-1 border-b border-dashed border-slate-300">
                        @foreach($viewingTransaction->details as $d)
                            <div class="flex justify-between">
                                <span class="truncate max-w-[180px]">{{ $d->product_name }}</span>
                                <span>{{ $d->quantity }} x {{ number_format($d->selling_price, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="space-y-1 pt-1">
                        <div class="flex justify-between font-bold text-sm">
                            <span>TOTAL:</span>
                            <span class="text-indigo-600">Rp {{ number_format($viewingTransaction->grand_total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-[11px] text-slate-600">
                            <span>Metode:</span>
                            <span class="uppercase font-bold">{{ $viewingTransaction->payment_method }}</span>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 border-t border-slate-200 text-center">
                    <button type="button" wire:click="closeDetailModal"
                            class="w-full py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-xl text-xs font-bold transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

<!-- Chart.js Initialization for Mobile Admin -->
<script>
    function adminDashboard() {
        return {
            chart: null,
            init() {
                this.renderChart();
                window.addEventListener('periodUpdated', () => {
                    setTimeout(() => this.renderChart(), 100);
                });
            },
            renderChart() {
                const ctx = document.getElementById('salesChart');
                if (!ctx) return;

                if (this.chart) {
                    this.chart.destroy();
                }

                const labels = @json($chartLabels);
                const sales = @json($chartSales);
                const profits = @json($chartProfits);

                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Omzet Penjualan',
                                data: sales,
                                borderColor: '#6366f1',
                                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                                fill: true,
                                tension: 0.35,
                                borderWidth: 2.5,
                                pointRadius: 3,
                            },
                            {
                                label: 'Laba Bersih',
                                data: profits,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.05)',
                                fill: true,
                                tension: 0.35,
                                borderWidth: 2,
                                pointRadius: 3,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let val = context.parsed.y || 0;
                                        return context.dataset.label + ': Rp ' + val.toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10 } }
                            },
                            y: {
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    font: { size: 10 },
                                    callback: function(value) {
                                        return 'Rp ' + (value >= 1000 ? (value / 1000) + 'k' : value);
                                    }
                                }
                            }
                        }
                    }
                });
            }
        };
    }
</script>


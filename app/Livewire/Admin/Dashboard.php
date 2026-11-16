<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public string $period = 'this_month'; // 'today', 'this_week', 'this_month', 'custom'
    public string $startDate = '';
    public string $endDate = '';

    public ?Transaction $viewingTransaction = null;
    public bool $showDetailModal = false;

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->toDateString();
        $this->endDate = Carbon::now()->toDateString();
    }

    public function setPeriod(string $p)
    {
        $this->period = $p;

        if ($p === 'today') {
            $this->startDate = Carbon::today()->toDateString();
            $this->endDate = Carbon::today()->toDateString();
        } elseif ($p === 'this_week') {
            $this->startDate = Carbon::now()->subDays(6)->toDateString();
            $this->endDate = Carbon::today()->toDateString();
        } elseif ($p === 'this_month') {
            $this->startDate = Carbon::now()->startOfMonth()->toDateString();
            $this->endDate = Carbon::today()->toDateString();
        }

        $this->dispatch('periodUpdated');
    }

    public function showTransactionDetail(int $id)
    {
        $this->viewingTransaction = Transaction::with(['details.product', 'user'])->find($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->viewingTransaction = null;
    }

    public function render()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        // 1. Transactions in period
        $trxQuery = Transaction::paid()->whereBetween('created_at', [$start, $end]);

        $totalRevenue = (float) (clone $trxQuery)->sum('grand_total');
        $totalTransactions = (int) (clone $trxQuery)->count();
        $avgTicket = $totalTransactions > 0 ? ($totalRevenue / $totalTransactions) : 0;

        // 2. Gross profit calculation: sum((selling_price - purchase_price) * quantity)
        $grossProfit = (float) TransactionDetail::whereHas('transaction', function ($q) use ($start, $end) {
            $q->paid()->whereBetween('created_at', [$start, $end]);
        })->selectRaw('SUM((selling_price - purchase_price) * quantity) as total_profit')
          ->value('total_profit') ?? 0;

        // 3. Payment Method breakdown
        $cashTotal = (float) (clone $trxQuery)->where('payment_method', 'tunai')->sum('grand_total');
        $qrisTotal = (float) (clone $trxQuery)->where('payment_method', 'qris')->sum('grand_total');

        // 4. Low stock products alert
        $lowStockProducts = Product::active()
            ->lowStock()
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get();
        $lowStockCount = Product::active()->lowStock()->count();

        // 5. Top 5 Best Selling Products in period
        $topProducts = TransactionDetail::whereHas('transaction', function ($q) use ($start, $end) {
            $q->paid()->whereBetween('created_at', [$start, $end]);
        })
        ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
        ->groupBy('product_name')
        ->orderByDesc('total_qty')
        ->limit(5)
        ->get();

        // 6. Recent Transactions Feed
        $recentTransactions = (clone $trxQuery)->with('user')->latest()->limit(8)->get();

        // 7. Trend chart for last 7 days
        $trendLabels = [];
        $trendSales = [];
        $trendProfits = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $dayLabel = $day->isoFormat('D MMM');
            $trendLabels[] = $dayLabel;

            $dayStart = $day->copy()->startOfDay();
            $dayEnd = $day->copy()->endOfDay();

            $daySale = (float) Transaction::paid()
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->sum('grand_total');
            $trendSales[] = $daySale;

            $dayProfit = (float) TransactionDetail::whereHas('transaction', function ($q) use ($dayStart, $dayEnd) {
                $q->paid()->whereBetween('created_at', [$dayStart, $dayEnd]);
            })->selectRaw('SUM((selling_price - purchase_price) * quantity) as total_profit')
              ->value('total_profit') ?? 0;
            $trendProfits[] = $dayProfit;
        }

        return view('livewire.admin.dashboard', [
            'totalRevenue' => $totalRevenue,
            'totalTransactions' => $totalTransactions,
            'grossProfit' => $grossProfit,
            'avgTicket' => $avgTicket,
            'cashTotal' => $cashTotal,
            'qrisTotal' => $qrisTotal,
            'lowStockCount' => $lowStockCount,
            'lowStockProducts' => $lowStockProducts,
            'topProducts' => $topProducts,
            'recentTransactions' => $recentTransactions,
            'chartLabels' => $trendLabels,
            'chartSales' => $trendSales,
            'chartProfits' => $trendProfits,
        ]);
    }
}


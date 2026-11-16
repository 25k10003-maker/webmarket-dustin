@extends('layouts.base')

@section('title', 'Kasir POS Desktop - Webmarket Dustin')

@section('body')
<div class="h-screen flex flex-col overflow-hidden bg-slate-900 text-slate-100">
    <!-- Kasir Header Navigation -->
    <header class="h-14 bg-slate-800 border-b border-slate-700/80 px-6 flex items-center justify-between flex-shrink-0 z-20">
        <div class="flex items-center space-x-4">
            <div class="w-9 h-9 rounded-lg bg-emerald-500 flex items-center justify-center text-white shadow-md shadow-emerald-500/20">
                <i class="fa-solid fa-cash-register text-lg"></i>
            </div>
            <div>
                <h1 class="text-base font-bold tracking-tight text-white flex items-center space-x-2">
                    <span>Webmarket Dustin</span>
                    <span class="text-xs px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-semibold border border-emerald-500/30">POS Desktop</span>
                </h1>
            </div>
        </div>

        <!-- Realtime Clock & Shortcuts reminder -->
        <div class="hidden md:flex items-center space-x-6 text-xs text-slate-400">
            <div class="flex items-center space-x-2">
                <span class="px-1.5 py-0.5 bg-slate-700 text-slate-300 font-mono rounded text-[11px] border border-slate-600">F2</span>
                <span>Fokus Barcode</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="px-1.5 py-0.5 bg-slate-700 text-slate-300 font-mono rounded text-[11px] border border-slate-600">F4</span>
                <span>Bayar Transaksi</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="px-1.5 py-0.5 bg-slate-700 text-slate-300 font-mono rounded text-[11px] border border-slate-600">ESC</span>
                <span>Batal / Tutup</span>
            </div>
            <div class="h-4 w-px bg-slate-700"></div>
            <div id="pos-clock" class="font-mono text-emerald-400 font-semibold"></div>
        </div>

        <!-- Cashier Profile & Logout -->
        <div class="flex items-center space-x-3">
            <div class="text-right">
                <div class="text-xs font-bold text-white">{{ Auth::user()->name }}</div>
                <div class="text-[11px] text-slate-400">UKasir #{{ Auth::id() }}</div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" title="Keluar / Logout"
                        class="p-2 text-slate-400 hover:text-red-400 hover:bg-slate-700/60 rounded-lg transition">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </header>

    <!-- Main Livewire POS Screen -->
    <main class="flex-1 overflow-hidden flex flex-col">
        <livewire:kasir-pos />
    </main>
</div>

<script>
    // Live Clock display
    function updateClock() {
        const el = document.getElementById('pos-clock');
        if (el) {
            const now = new Date();
            el.innerText = now.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' }) + ' ' + 
                           now.toLocaleTimeString('id-ID');
        }
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
@endsection


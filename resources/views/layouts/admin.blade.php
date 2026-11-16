@extends('layouts.base')

@section('body')
<div class="min-h-screen bg-slate-900 flex justify-center">
    
    <!-- Mobile-First Responsive Container (App-like layout) -->
    <div class="w-full max-w-lg md:max-w-4xl bg-slate-50 min-h-screen flex flex-col shadow-2xl relative pb-20">
        
        <!-- Mobile App Header Bar -->
        <header class="bg-indigo-600 text-white px-5 py-4 sticky top-0 z-30 shadow-md flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white shadow-inner">
                    <i class="fa-solid fa-chart-pie text-lg"></i>
                </div>
                <div>
                    <h1 class="text-base font-extrabold tracking-tight">Webmarket Dustin</h1>
                    <div class="flex items-center space-x-1.5 text-[11px] text-indigo-100 font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span>UAdmin Mobile Dashboard</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <span class="text-xs px-2.5 py-1 rounded-full bg-indigo-700/60 text-indigo-100 font-medium border border-indigo-500/30">
                    Read-Only
                </span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Logout"
                            class="p-2 text-indigo-200 hover:text-white hover:bg-indigo-700/80 rounded-xl transition">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </header>

        <!-- Main Dashboard Body -->
        <main class="flex-1 p-4 md:p-6">
            @yield('content')
        </main>

        <!-- Mobile Bottom Tab Navigation -->
        <nav class="fixed bottom-0 left-0 right-0 max-w-lg md:max-w-4xl mx-auto bg-white/95 backdrop-blur-md border-t border-slate-200 px-6 py-2.5 flex justify-around items-center z-30 shadow-lg">
            <a href="#summary-section" class="flex flex-col items-center text-indigo-600 text-[11px] font-bold">
                <i class="fa-solid fa-gauge-high text-base mb-0.5"></i>
                <span>Ringkasan</span>
            </a>
            <a href="#chart-section" class="flex flex-col items-center text-slate-500 hover:text-indigo-600 text-[11px] font-medium transition">
                <i class="fa-solid fa-chart-line text-base mb-0.5"></i>
                <span>Grafik</span>
            </a>
            <a href="#top-products-section" class="flex flex-col items-center text-slate-500 hover:text-indigo-600 text-[11px] font-medium transition">
                <i class="fa-solid fa-fire text-base mb-0.5"></i>
                <span>Terlaris</span>
            </a>
            <a href="#stock-alerts-section" class="flex flex-col items-center text-slate-500 hover:text-indigo-600 text-[11px] font-medium transition">
                <i class="fa-solid fa-bell text-base mb-0.5"></i>
                <span>Stok Kritis</span>
            </a>
        </nav>

    </div>

</div>
@endsection


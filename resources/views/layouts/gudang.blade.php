@extends('layouts.base')

@section('body')
<div class="min-h-screen flex bg-slate-50">
    
    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col justify-between flex-shrink-0">
        <div>
            <!-- Header Brand -->
            <div class="h-16 flex items-center px-6 border-b border-slate-800 space-x-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500 flex items-center justify-center text-white shadow-lg shadow-amber-500/20">
                    <i class="fa-solid fa-boxes-stacked text-base"></i>
                </div>
                <div>
                    <h1 class="text-sm font-bold text-white tracking-tight">Webmarket Dustin</h1>
                    <span class="text-[10px] px-2 py-0.5 rounded bg-amber-500/20 text-amber-400 font-semibold border border-amber-500/30">Modul Gudang</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1">
                <a href="{{ route('gudang.products') }}"
                   class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('gudang.products') ? 'bg-amber-500 text-slate-950 font-bold shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-box text-sm w-5 text-center"></i>
                    <span>Daftar Produk</span>
                </a>

                <a href="{{ route('gudang.stock-in') }}"
                   class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('gudang.stock-in') ? 'bg-amber-500 text-slate-950 font-bold shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-truck-ramp-box text-sm w-5 text-center"></i>
                    <span>Terima Barang Masuk</span>
                </a>

                <a href="{{ route('gudang.adjustment') }}"
                   class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('gudang.adjustment') ? 'bg-amber-500 text-slate-950 font-bold shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-sliders text-sm w-5 text-center"></i>
                    <span>Penyesuaian Stok (Opname)</span>
                </a>

                <a href="{{ route('gudang.mutations') }}"
                   class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('gudang.mutations') ? 'bg-amber-500 text-slate-950 font-bold shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-clock-rotate-left text-sm w-5 text-center"></i>
                    <span>Riwayat Mutasi Stok</span>
                </a>
            </nav>
        </div>

        <!-- User Profile & Logout -->
        <div class="p-4 border-t border-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold text-white">{{ Auth::user()->name }}</div>
                    <div class="text-[11px] text-slate-400">UGudang #{{ Auth::id() }}</div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-red-400 hover:bg-slate-800 rounded-lg transition">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-x-hidden">
        <!-- Topbar Header -->
        <header class="h-16 bg-white border-b border-slate-200 px-8 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-800">@yield('page_title', 'Manajemen Gudang')</h2>
                <p class="text-xs text-slate-500">Kelola inventaris fisik, restock barang, dan kontrol mutasi.</p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                    Koneksi Database Aktif
                </span>
            </div>
        </header>

        <!-- Page Body Content -->
        <main class="flex-1 p-8">
            @yield('content')
        </main>
    </div>

</div>
@endsection


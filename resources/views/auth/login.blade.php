@extends('layouts.base')

@section('title', 'Login Sistem Retail Market')

@section('body')
<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center px-4">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-500 shadow-xl shadow-emerald-500/20 text-white mb-4">
            <i class="fa-solid fa-cash-register text-3xl"></i>
        </div>
        <h2 class="text-3xl font-extrabold text-white tracking-tight">
            Webmarket Dustin
        </h2>
        <p class="mt-2 text-sm text-slate-300">
            Sistem Retail Terpadu: Kasir POS, Gudang Stok & Analitik Admin
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4">
        <div class="bg-white py-8 px-6 shadow-2xl rounded-2xl sm:px-10 border border-slate-100">
            
            @if(session('error'))
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded text-sm text-red-700 flex items-center">
                    <i class="fa-solid fa-triangle-exclamation mr-2 text-red-500"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded text-sm text-emerald-700 flex items-center">
                    <i class="fa-solid fa-circle-check mr-2 text-emerald-500"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 p-4 rounded-xl text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="space-y-5" action="{{ route('login.post') }}" method="POST">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700">Email Pengguna</label>
                    <div class="mt-1 relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required
                               value="{{ old('email', 'kasir@retail.com') }}"
                               placeholder="nama@retail.com"
                               class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700">Kata Sandi (Password)</label>
                    <div class="mt-1 relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               value="password123"
                               placeholder="••••••••"
                               class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" checked class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-slate-300 rounded">
                        <label for="remember" class="ml-2 block text-xs text-slate-600">Ingat Saya</label>
                    </div>
                    <span class="text-xs text-slate-400">Keamanan Enkripsi SHA-256</span>
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg shadow-emerald-600/30 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all active:scale-[0.99]">
                        <i class="fa-solid fa-right-to-bracket mr-2"></i> Masuk ke Sistem
                    </button>
                </div>
            </form>

            <!-- Quick Demo Login Switcher -->
            <div class="mt-6 pt-6 border-t border-slate-200">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3 text-center">
                    Akses Cepat Demo Role (1-Klik)
                </p>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="setDemo('kasir@retail.com')"
                            class="p-2.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl text-center text-xs font-semibold text-emerald-800 transition hover:shadow">
                        <i class="fa-solid fa-cash-register block text-base mb-1 text-emerald-600"></i>
                        UKasir
                    </button>
                    <button type="button" onclick="setDemo('gudang@retail.com')"
                            class="p-2.5 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl text-center text-xs font-semibold text-amber-800 transition hover:shadow">
                        <i class="fa-solid fa-boxes-stacked block text-base mb-1 text-amber-600"></i>
                        UGudang
                    </button>
                    <button type="button" onclick="setDemo('admin@retail.com')"
                            class="p-2.5 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-xl text-center text-xs font-semibold text-indigo-800 transition hover:shadow">
                        <i class="fa-solid fa-chart-pie block text-base mb-1 text-indigo-600"></i>
                        UAdmin
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function setDemo(email) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = 'password123';
    }
</script>
@endsection


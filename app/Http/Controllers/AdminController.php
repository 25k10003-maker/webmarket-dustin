<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function transactionDetail($id)
    {
        $transaction = Transaction::with(['details.product', 'user'])->findOrFail($id);
        return response()->json($transaction);
    }
}


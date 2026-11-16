<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class KasirController extends Controller
{
    public function pos()
    {
        return view('kasir.pos');
    }

    public function receipt($invoice_code)
    {
        $transaction = Transaction::with(['details.product', 'user'])
            ->where('invoice_code', $invoice_code)
            ->firstOrFail();

        return view('kasir.receipt', compact('transaction'));
    }
}


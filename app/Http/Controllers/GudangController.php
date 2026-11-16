<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMutation;
use Illuminate\Http\Request;

class GudangController extends Controller
{
    public function products()
    {
        return view('gudang.products');
    }

    public function stockIn()
    {
        return view('gudang.stock-in');
    }

    public function adjustment()
    {
        return view('gudang.adjustment');
    }

    public function mutations()
    {
        return view('gudang.mutations');
    }
}


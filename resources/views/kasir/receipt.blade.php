<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk #{{ $transaction->invoice_code }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            width: 76mm;
            margin: 2mm auto;
            padding: 2mm;
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .dashed-line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .flex { display: flex; justify-content: space-between; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print text-center" style="margin-bottom: 10px;">
        <button onclick="window.print()" style="padding: 5px 15px; cursor: pointer;">Cetak Ulang</button>
        <button onclick="window.close()" style="padding: 5px 15px; cursor: pointer;">Tutup</button>
    </div>

    <div class="text-center">
        <h2 style="margin: 0; font-size: 14px;">WEBMARKET DUSTIN</h2>
        <div>Jl. Retail Raya No. 88, Jakarta</div>
        <div>Telp/WA: 0812-3456-7890</div>
    </div>

    <div class="dashed-line"></div>

    <div class="flex">
        <span>No Nota:</span>
        <span class="font-bold">{{ $transaction->invoice_code }}</span>
    </div>
    <div class="flex">
        <span>Tanggal:</span>
        <span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
    </div>
    <div class="flex">
        <span>Kasir:</span>
        <span>{{ $transaction->user->name ?? 'Kasir' }}</span>
    </div>
    <div class="flex">
        <span>Pelanggan:</span>
        <span>{{ $transaction->customer_name }}</span>
    </div>

    <div class="dashed-line"></div>

    @foreach($transaction->details as $det)
        <div style="margin-bottom: 4px;">
            <div class="font-bold">{{ $det->product_name }}</div>
            <div class="flex">
                <span>{{ $det->quantity }} x {{ number_format($det->selling_price, 0, ',', '.') }}</span>
                <span>{{ number_format($det->subtotal, 0, ',', '.') }}</span>
            </div>
        </div>
    @endforeach

    <div class="dashed-line"></div>

    <div class="flex">
        <span>Subtotal:</span>
        <span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
    </div>
    @if($transaction->discount > 0)
        <div class="flex">
            <span>Diskon:</span>
            <span>-Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
        </div>
    @endif
    <div class="flex font-bold" style="font-size: 13px; margin: 3px 0;">
        <span>TOTAL:</span>
        <span>Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</span>
    </div>
    <div class="flex">
        <span>Metode:</span>
        <span style="text-transform: uppercase;">{{ $transaction->payment_method }}</span>
    </div>
    <div class="flex">
        <span>Bayar:</span>
        <span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
    </div>
    <div class="flex font-bold">
        <span>Kembalian:</span>
        <span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
    </div>

    <div class="dashed-line"></div>

    <div class="text-center" style="font-size: 10px; margin-top: 8px;">
        <div>Terima Kasih Atas Kunjungan Anda!</div>
        <div style="font-size: 8px; margin-top: 5px;">--- SISTEM POS DUSTIN ---</div>
    </div>

</body>
</html>


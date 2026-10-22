<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $transaction->invoice_number }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; color: #000; padding: 20px; font-size: 14px; }
        .invoice-box { max-width: 400px; margin: auto; border: 1px dashed #ccc; padding: 20px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 15px; }
        th, td { padding: 5px 0; border-bottom: 1px dashed #ccc; }
        th { text-align: left; }
        .total-row td { border-top: 2px dashed #000; border-bottom: none; padding-top: 10px; font-size: 16px; font-weight: bold;}
    </style>
</head>
<body>

    <div class="invoice-box">
        <h2 class="text-center" style="margin-bottom: 5px;">TOKO DUA PUTRA</h2>
        <p class="text-center" style="margin-top: 0;">Struk Pembelian</p>
        <hr style="border-top: 1px dashed #000;">
        
        <p>
            <strong>Invoice:</strong> {{ $transaction->invoice_number }}<br>
            <strong>Date:</strong> {{ date('d-m-Y H:i', strtotime($transaction->created_at)) }}<br>
            <strong>Customer:</strong> {{ $transaction->customer?->name ?? 'Umum' }}
        </p>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->details as $detail)
                <tr>
                    <td>
                        {{ $detail->product?->name }}<br>
                        <small>@ Rp {{ number_format($detail->price, 0, '', '.') }}</small>
                    </td>
                    <td class="text-center">{{ $detail->qty }}</td>
                    <td class="text-right">Rp {{ number_format($detail->subtotal, 0, '', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2" class="text-right">TOTAL:</td>
                    <td class="text-right">Rp {{ number_format($transaction->total, 0, '', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <p class="text-center" style="margin-top: 30px;">
            Terima kasih atas kunjungan Anda!
        </p>
    </div>

    <script>
        // Otomatis membuka dialog print saat halaman dimuat
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
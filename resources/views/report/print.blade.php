<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi - Toko Dua Putra</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; color: #333; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        header { margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { border: 1px solid #000; padding: 8px; vertical-align: top; }
        .bg-light { background-color: #f2f2f2; }
        .footer-total { background-color: #eee; font-weight: bold; }
        .product-list { margin: 0; padding: 0; list-style: none; }
        .product-item { font-size: 9pt; border-bottom: 1px solid #ddd; margin-bottom: 2px; }
    </style>
</head>
<body>
    <header class="text-center">
        <h2 style="margin-bottom: 5px;">TOKO SEMBAKO DUA PUTRA</h2>
        <p style="margin: 0;">Laporan Transaksi Penjualan</p>
        <p style="margin: 5px 0; font-size: 9pt;">
            Periode: {{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }}
        </p>
    </header>

    <table>
        <thead>
            <tr class="bg-light">
                <th width="30">No</th>
                <th width="100">Invoice / Tgl</th>
                <th width="120">Pelanggan</th>
                <th>Rincian Barang (Qty x Harga)</th>
                <th width="120">Total</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $no = 1; 
                $grandTotal = 0;
            @endphp
            @forelse ($transactions as $transaction)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>
                        <span class="fw-bold">{{ $transaction->invoice_number }}</span><br>
                        {{ date('d/m/Y', strtotime($transaction->date)) }}
                    </td>
                    <td>{{ $transaction->customer->name ?? 'Umum' }}</td>
                    <td>
                        {{-- PERBAIKAN: Gunakan looping detail produk --}}
                        <div class="product-list">
                            @foreach ($transaction->details as $detail)
                                <div class="product-item">
                                    - {{ $detail->product->name }} 
                                    ({{ $detail->qty }} x {{ number_format($detail->price, 0, ',', '.') }})
                                </div>
                            @endforeach
                        </div>
                    </td>
                    <td class="text-end">
                        Rp {{ number_format($transaction->total, 0, ',', '.') }}
                    </td>
                </tr>
                @php $grandTotal += $transaction->total; @endphp
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data transaksi.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="footer-total">
                <td colspan="4" class="text-end">TOTAL PENDAPATAN (OMZET)</td>
                <td class="text-end">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 30px; text-align: right;">
        <p>Semarang, {{ date('d F Y') }}</p>
        <br><br><br>
        <p>( Admin Toko Dua Putra )</p>
    </div>
</body>
</html>
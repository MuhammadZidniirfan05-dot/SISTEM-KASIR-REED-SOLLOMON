<x-app-layout>
    {{-- Page Title --}}
    <x-page-title>Laporan Transaksi - Toko Dua Putra</x-page-title>

    {{-- Pesan Peringatan Error (Global Alert) --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="ti ti-alert-triangle fs-4 me-2"></i>
                <div>
                    <strong>Peringatan!</strong> Filter tanggal tidak valid:
                    <ul class="mb-0 mt-1 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="ti ti-alert-circle fs-4 me-2"></i>
                <div>
                    <strong>Gagal!</strong> {{ session('error') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Form Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold text-primary">
                <i class="ti ti-filter me-2"></i>Filter Rentang Tanggal
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('report.filter') }}" method="GET">
                <div class="row align-items-start g-3"> {{-- Ubah align-items-end menjadi align-items-start agar tinggi serasi saat ada pesan error --}}
                    <div class="col-lg-4">
                        <label class="form-label small fw-bold text-muted">TANGGAL MULAI</label>
                        <input type="date" name="start_date" class="form-control border-2 @error('start_date') is-invalid @enderror" value="{{ request('start_date') }}" required>
                        {{-- Feedback Error per Input --}}
                        @error('start_date')
                            <div class="invalid-feedback fw-medium">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label small fw-bold text-muted">TANGGAL SELESAI</label>
                        <input type="date" name="end_date" class="form-control border-2 @error('end_date') is-invalid @enderror" value="{{ request('end_date') }}" required>
                        {{-- Feedback Error per Input --}}
                        @error('end_date')
                            <div class="invalid-feedback fw-medium">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label small fw-bold text-muted d-none d-lg-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                            <i class="ti ti-search me-1"></i> Tampilkan Laporan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if (isset($transactions))
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <div>
                    <h5 class="mb-1 fw-bold text-dark">Hasil Laporan Transaksi</h5>
                    <p class="mb-0 text-muted small">
                        Periode: <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            {{ date('d M Y', strtotime(request('start_date'))) }}
                        </span> 
                        <i class="ti ti-arrow-right mx-1"></i>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            {{ date('d M Y', strtotime(request('end_date'))) }}
                        </span>
                    </p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('report.print', [request('start_date'), request('end_date')]) }}" target="_blank" class="btn btn-warning px-4 fw-bold shadow-sm">
                        <i class="ti ti-printer me-2"></i> Cetak PDF
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted uppercase-tracking">
                            <tr>
                                <th class="text-center py-3" style="width: 5%">NO</th>
                                <th class="py-3" style="width: 15%">INVOICE & TGL</th>
                                <th class="py-3" style="width: 15%">PELANGGAN</th>
                                <th class="py-3">RINCIAN BARANG</th>
                                <th class="text-end py-3" style="width: 20%">TOTAL TRANSAKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $grandTotal = 0; @endphp
                            @forelse ($transactions as $transaction)
                                <tr>
                                    <td class="text-center fw-medium text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="fw-bold text-dark mb-0">{{ $transaction->invoice_number }}</div>
                                        <div class="text-muted" style="font-size: 0.8rem;">
                                            <i class="ti ti-calendar-event me-1"></i>{{ date('d/m/Y', strtotime($transaction->date)) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                <i class="ti ti-user text-primary"></i>
                                            </div>
                                            <span class="fw-semibold text-dark">{{ $transaction->customer->name ?? 'Umum' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="detail-container rounded-3 p-2 bg-light-subtle border">
                                            @foreach ($transaction->details as $detail)
                                                <div class="d-flex justify-content-between align-items-center py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                                                    <span class="text-dark small">
                                                        <i class="ti ti-minus me-1 text-muted"></i>{{ $detail->product->name }}
                                                    </span>
                                                    <span class="text-muted small fw-medium">
                                                        {{ $detail->qty }} <small>x</small> <span class="text-dark">Rp{{ number_format($detail->price, 0, '', '.') }}</span>
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="fs-5 fw-bold text-dark">Rp {{ number_format($transaction->total, 0, '', '.') }}</span>
                                    </td>
                                </tr>
                                @php $grandTotal += $transaction->total; @endphp
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <img src="{{ asset('images/empty-state.svg') }}" alt="Empty" style="width: 150px;" class="mb-3 opacity-50">
                                        <h6 class="text-muted fw-normal">Tidak ada data ditemukan untuk periode ini.</h6>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($transactions->count() > 0)
                            <tfoot class="bg-dark text-white">
                                <tr>
                                    <th colspan="4" class="text-end py-4 fs-6">TOTAL PENDAPATAN (OMZET)</th>
                                    <th class="text-end py-4 fs-4 fw-bolder">
                                        Rp {{ number_format($grandTotal, 0, '', '.') }}
                                    </th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>

<style>
    .uppercase-tracking {
        font-size: 0.7rem;
        letter-spacing: 1px;
        text-transform: uppercase;
        font-weight: 700;
    }
    .bg-light-subtle {
        background-color: #fcfcfc;
    }
    .bg-primary-subtle {
        background-color: #eef2ff !important;
    }
    .border-2 {
        border-width: 2px !important;
    }
    .fw-bolder {
        font-weight: 900;
    }
    .detail-container {
        max-width: 400px;
    }
    /* Zebra Striping */
    tbody tr:nth-child(even) {
        background-color: #fafafa;
    }
</style>
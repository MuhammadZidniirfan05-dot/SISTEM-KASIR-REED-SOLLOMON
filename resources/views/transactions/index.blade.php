<x-app-layout>
    <x-page-title>Transactions</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-4">
        <div class="row">
            <div class="d-grid d-lg-block col-lg-5 col-xl-6 mb-4 mb-lg-0">
                <a href="{{ route('transactions.create') }}" class="btn btn-primary py-2 px-3">
                    <i class="ti ti-plus me-2"></i> Add Transaction
                </a>
            </div>
            <div class="col-lg-7 col-xl-6">
                <form action="{{ route('transactions.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control form-search py-2" value="{{ request('search') }}" placeholder="Search invoice or customer..." autocomplete="off">
                        <button class="btn btn-primary py-2" type="submit">Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2 shadow-sm pt-4 px-4 pb-3 mb-5">
        <div class="table-responsive mb-3">
            <table class="table table-bordered table-striped table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center" width="50">No.</th>
                        <th class="text-center">Invoice</th>
                        <th class="text-center">Date</th>
                        <th class="text-center">Customer</th>
                        <th class="text-center">Grand Total</th>
                        <th class="text-center" width="200">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($transactions as $transaction)
                    <tr>
                        <td class="text-center">{{ $transactions->firstItem() + $loop->index }}</td>
                        <td class="text-center fw-bold">{{ $transaction->invoice_number ?? '-' }}</td>
                        <td class="text-center">{{ date('d M Y', strtotime($transaction->date)) }}</td>
                        <td>{{ $transaction->customer?->name ?? 'General Customer' }}</td>
                        <td class="text-end fw-bold text-primary">{{ 'Rp ' . number_format($transaction->total ?? 0, 0, '', '.') }}</td>
                        <td class="text-center">
                            {{-- Tombol Detail --}}
                            <button type="button" class="btn btn-info btn-sm m-1 text-white" data-bs-toggle="modal" data-bs-target="#modalDetail{{ $transaction->id }}" title="Detail"> 
                                <i class="ti ti-eye"></i> Detail
                            </button>
                            
                            {{-- Tombol Print --}}
                            <a href="{{ route('transactions.print', $transaction->id) }}" target="_blank" class="btn btn-secondary btn-sm m-1" title="Print Invoice">
                                <i class="ti ti-printer"></i> Print
                            </a>

                            {{-- Tombol Delete --}}
                           
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="d-flex justify-content-center align-items-center py-4">
                                <i class="ti ti-info-circle fs-5 me-2"></i>
                                <div>No transactions found.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-links">{{ $transactions->links() }}</div>
    </div>

    {{-- KUMPULAN MODAL (Detail & Delete) --}}
    @foreach ($transactions as $transaction)
        
        {{-- MODAL DETAIL TRANSAKSI --}}
        <div class="modal fade" id="modalDetail{{ $transaction->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h1 class="modal-title fs-5">
                            <i class="ti ti-receipt me-2"></i> Detail Transaction: {{ $transaction->invoice_number }}
                        </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <strong>Date:</strong> {{ date('d F Y', strtotime($transaction->date)) }}<br>
                                <strong>Customer:</strong> {{ $transaction->customer?->name ?? 'General Customer' }}
                            </div>
                        </div>
                        <table class="table table-bordered table-sm">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Product Name</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transaction->details as $detail)
                                <tr>
                                    <td>{{ $detail->product?->name ?? 'Unknown Product' }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->price, 0, '', '.') }}</td>
                                    <td class="text-center">{{ $detail->qty }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->subtotal, 0, '', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">GRAND TOTAL</th>
                                    <th class="text-end fs-5 text-primary">Rp {{ number_format($transaction->total, 0, '', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('transactions.print', $transaction->id) }}" target="_blank" class="btn btn-primary"><i class="ti ti-printer"></i> Print Invoice</a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL DELETE --}}
        <div class="modal fade" id="modalDelete{{ $transaction->id }}" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5"><i class="ti ti-trash me-2"></i> Delete Transaction</h1>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure to delete transaction <b>{{ $transaction->invoice_number }}</b>? This will restore the product stock.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger">Yes, delete it!</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

</x-app-layout>
<x-app-layout>
    {{-- Page Title --}}
    <x-page-title>Dashboard Admin</x-page-title>

    {{-- Heroes Section --}}
    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-3 text-center">
                <i class="ti ti-building-store text-primary opacity-85" style="font-size: 8rem;"></i>
            </div>
            <div class="col-lg-9">
                <h4 class="text-primary mb-2">
                    Selamat datang di Panel Manajemen <span class="fw-semibold">Toko Sembako Dua Putra</span>!
                </h4>
                <p class="lead-dashboard mb-4">
                    Pantau ketersediaan stok barang, kelola data pelanggan setia, dan tinjau performa penjualan harian toko Anda dalam satu tempat dengan cepat dan akurat.
                </p>
                <div class="d-grid gap-3 d-md-flex justify-content-md-start">
                    <a href="{{ route('transactions.create') }}" class="btn btn-primary py-2 px-4 shadow-sm">
                        <i class="ti ti-plus align-middle me-2"></i> Transaksi Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistik Ringkas --}}
    <div class="row mb-5">
        <div class="col-lg-6 col-xl-3">
            <div class="bg-white rounded-2 shadow-sm p-4 mb-4 border-start border-primary border-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="ti ti-layout-grid fs-1 bg-primary-subtle text-primary rounded-2 p-2"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 small uppercase fw-bold">Kategori</p>
                        <h4 class="fw-bold mb-0">{{ $totalCategory }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-xl-3">
            <div class="bg-white rounded-2 shadow-sm p-4 mb-4 border-start border-success border-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="ti ti-package fs-1 bg-success-subtle text-success rounded-2 p-2"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 small uppercase fw-bold">Produk</p>
                        <h4 class="fw-bold mb-0">{{ $totalProduct }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-xl-3">
            <div class="bg-white rounded-2 shadow-sm p-4 mb-4 border-start border-warning border-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="ti ti-user-heart fs-1 bg-warning-subtle text-warning rounded-2 p-2"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 small uppercase fw-bold">Pelanggan</p>
                        <h4 class="fw-bold mb-0">{{ $totalCustomer }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-xl-3">
            <div class="bg-white rounded-2 shadow-sm p-4 mb-4 border-start border-info border-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="ti ti-receipt fs-1 bg-info-subtle text-info rounded-2 p-2"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 small uppercase fw-bold">Transaksi</p>
                        <h4 class="fw-bold mb-0">{{ $totalTransaction }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Section Produk Terlaris (Visual Baru) --}}
    <div class="mb-5">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="ti ti-award fs-4 me-2 text-warning"></i> 
                5 Produk Paling Laris (Best Seller)
            </h5>
        </div>

        <div class="row g-4 justify-content-center">
            @forelse ($transactions as $index => $transaction)
                <div class="col-md-6 col-lg-4 col-xl-2.4">
                    <div class="card border-0 shadow-sm h-100 overflow-hidden position-relative product-card">
                        {{-- Badge Ranking --}}
                        <div class="position-absolute top-0 start-0 m-2" style="z-index: 2;">
                            <span class="badge {{ $index == 0 ? 'bg-warning' : 'bg-dark' }} text-white shadow-sm fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border: 2px solid white;">
                                {{ $index + 1 }}
                            </span>
                        </div>

                        {{-- Image --}}
                        <div class="p-3 bg-light text-center">
                            @if($transaction->product->image)
                                <img src="{{ Str::startsWith($transaction->product->image, 'http') ? $transaction->product->image : asset('storage/products/' . $transaction->product->image) }}" 
                                     class="img-fluid rounded-3" 
                                     style="height: 140px; width: 100%; object-fit: cover;" alt="Produk">
                            @else
                                <div class="d-flex align-items-center justify-content-center bg-white rounded-3 shadow-inner" style="height: 140px;">
                                    <i class="ti ti-photo-off fs-1 text-muted"></i>
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="card-body p-3 text-center">
                            <h6 class="text-truncate mb-1 fw-bold text-dark" title="{{ $transaction->product->name }}">
                                {{ $transaction->product->name }}
                            </h6>
                            <p class="text-primary fw-bold mb-3">
                                Rp {{ number_format($transaction->product->price, 0, '', '.') }}
                            </p>
                            
                            <div class="bg-primary-subtle rounded-3 py-2 px-1">
                                <span class="d-block small text-primary fw-bold">Total Terjual</span>
                                <h5 class="mb-0 text-primary fw-extrabold">{{ $transaction->transactions_sum_qty }} <small class="fs-6">Unit</small></h5>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="bg-white rounded-3 shadow-sm p-5 text-center">
                        <i class="ti ti-database-off fs-1 text-muted mb-3 d-block"></i>
                        <p class="text-muted">Data penjualan belum tersedia.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <style>
        .product-card {
            transition: all 0.3s ease-in-out;
        }
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
        }
        
        /* Custom Grid untuk 5 Kolom Sejajar */
        @media (min-width: 1200px) {
            .col-xl-2.4 {
                flex: 0 0 auto;
                width: 20%;
            }
        }
        
        .bg-primary-subtle { background-color: #e7f1ff; }
        .text-primary { color: #0d6efd !important; }
        .fw-extrabold { font-weight: 800; }
    </style>
</x-app-layout>
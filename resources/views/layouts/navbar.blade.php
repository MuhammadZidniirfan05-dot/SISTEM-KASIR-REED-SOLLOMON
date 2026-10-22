{{-- Navbar Menu --}}
<nav class="navbar navbar-expand sticky-top shadow-sm py-2" style="background-color: #ffffff; border-bottom: 3px solid #0d6efd;">
    <div class="container-fluid px-4">

        {{-- Logo / Brand Toko --}}
        <a class="navbar-brand d-flex align-items-center me-auto" href="{{ route('dashboard') }}">
            <div class="bg-primary text-white p-2 rounded-3 me-2 d-flex align-items-center justify-content-center shadow-sm">
                <i class="ti ti-building-store fs-4"></i>
            </div>
            <div>
                <span class="fw-bolder text-primary mb-0 d-block" style="font-size: 1.2rem; line-height: 1.1;">
                    DUA PUTRA
                </span>
                <small class="text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">
                    RETAIL SYSTEM
                </small>
            </div>
        </a>

        {{-- Area Menu --}}
        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav ms-auto py-0 gap-1 align-items-center">

                {{-- Dashboard --}}
                <li class="nav-item">
                    <x-navbar-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        <i class="ti ti-smart-home fs-5"></i>
                        <span>Dashboard</span>
                    </x-navbar-link>
                </li>

                {{-- Customers (Admin & Kasir) --}}
                <li class="nav-item">
                    <x-navbar-link href="{{ route('customers.index') }}" :active="request()->routeIs('customers.*')">
                        <i class="ti ti-users fs-5"></i>
                        <span>Customers</span>
                    </x-navbar-link>
                </li>

                {{-- Menu Admin --}}
                @if(auth()->user()->isAdmin())

                    <li class="nav-item">
                        <x-navbar-link href="{{ route('categories.index') }}" :active="request()->routeIs('categories.*')">
                            <i class="ti ti-category fs-5"></i>
                            <span>Categories</span>
                        </x-navbar-link>
                    </li>

                    <li class="nav-item">
                        <x-navbar-link href="{{ route('products.index') }}" :active="request()->routeIs('products.*')">
                            <i class="ti ti-package fs-5"></i>
                            <span>Products</span>
                        </x-navbar-link>
                    </li>

                    <li class="nav-item">
                        <x-navbar-link href="{{ route('report.index') }}" :active="request()->routeIs('report.*')">
                            <i class="ti ti-file-analytics fs-5"></i>
                            <span>Reports</span>
                        </x-navbar-link>
                    </li>

                @endif

               {{-- Menu Kasir --}}
@if(auth()->user()->isKasir())

    <li class="nav-item">
        <x-navbar-link href="{{ route('transactions.index') }}" :active="request()->routeIs('transactions.*')">
            <i class="ti ti-receipt fs-5"></i>
            <span>Transactions</span>
        </x-navbar-link>
    </li>

@endif

            </ul>

            {{-- Logout --}}
            <div class="d-flex align-items-center border-start ms-3 ps-3">
                <form method="POST" action="{{ route('logout') }}" class="mb-0">
                    @csrf
                    <button
                        type="submit"
                        class="btn btn-danger btn-sm rounded-pill px-4 py-2 fw-bold border-0 shadow-sm d-flex align-items-center"
                        onclick="return confirm('Yakin ingin keluar dari sistem?')">
                        <i class="ti ti-logout fs-5 me-1"></i>
                        Logout
                    </button>
                </form>
            </div>

        </div>
    </div>
</nav>

<style>
    .nav-link {
        display: flex !important;
        align-items: center;
        gap: 8px;
        padding: 0.6rem 1rem !important;
        border-radius: 8px;
        color: #64748b !important;
        font-weight: 500;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .nav-link i {
        transition: transform 0.2s ease;
    }

    .nav-link:hover {
        color: #0d6efd !important;
        background-color: #f8fafc;
    }

    .nav-link:hover i {
        transform: translateY(-2px);
    }

    .nav-link.active {
        color: #0d6efd !important;
        background-color: #e7f1ff !important;
        font-weight: 700;
    }
</style>
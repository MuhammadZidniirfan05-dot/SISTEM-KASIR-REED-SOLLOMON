<x-app-layout>
    {{-- Page Title --}}
    <x-page-title>Products</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-4">
        <div class="row align-items-center">
            <div class="d-grid d-lg-block col-lg-5 col-xl-6 mb-4 mb-lg-0">
                <a href="{{ route('products.create') }}" class="btn btn-primary py-2 px-3 shadow-sm">
                    <i class="ti ti-plus me-2"></i> Add Product
                </a>
            </div>
            <div class="col-lg-7 col-xl-6">
                <form action="{{ route('products.index') }}" method="GET">
                    <div class="input-group shadow-sm">
                        <input type="text" name="search" class="form-control form-search py-2" value="{{ request('search') }}" placeholder="Search product name or barcode..." autocomplete="off">
                        <button class="btn btn-primary py-2 px-4" type="submit">
                            <i class="ti ti-search me-1"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2 shadow-sm pt-4 px-4 pb-3 mb-5">
        <div class="table-responsive mb-3">
            <table class="table table-hover align-middle mb-0" style="width:100%">
                <thead class="bg-light text-dark">
                    <tr>
                        <th class="text-center py-3">No.</th>
                        <th class="text-center py-3">Image</th>
                        <th class="py-3">Product Information</th>
                        <th class="text-center py-3">Barcode (RS Protected)</th>
                        <th class="text-center py-3">Stock</th>
                        <th class="text-center py-3">Price</th>
                        <th class="text-center py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td width="30" class="text-center">
                            <span class="text-muted">{{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}</span>
                        </td>
                        <td width="80" class="text-center">
                            @if($product->image)
                                <img src="{{ asset('/storage/products/'.$product->image) }}" class="rounded-3 shadow-sm border" width="65" height="65" style="object-fit: cover;" alt="Product">
                            @else
                                <div class="bg-light rounded-3 d-flex align-items-center justify-content-center border" style="width: 65px; height: 65px;">
                                    <i class="ti ti-photo-off text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td width="250">
                            <div class="fw-bold text-dark fs-5 mb-0">{{ $product->name }}</div>
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 mt-1" style="font-size: 0.75rem;">
                                <i class="ti ti-tag me-1"></i>{{ $product->category->name }}
                            </span>
                        </td>
                        
                        <td width="200" class="text-center">
                            <div class="bg-light p-2 rounded-2 border border-dashed">
                                <code class="text-primary fw-bold" style="font-size: 0.85rem; word-break: break-all;">
                                    {{ $product->barcode_encoded ?? '-' }}
                                </code>
                                <div class="small text-muted mt-1" style="font-size: 0.7rem;">
                                    Original: <span class="fw-semibold">{{ $product->barcode_original ?? '-' }}</span>
                                </div>
                            </div>
                        </td>

                        <td width="80" class="text-center">
                            @if($product->stock <= 5)
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2">
                                    {{ $product->stock }} <small class="fw-bold">LOW</small>
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                    {{ $product->stock }}
                                </span>
                            @endif
                        </td>

                        <td width="120" class="text-end fw-bold text-dark">
                            {{ 'Rp ' . number_format($product->price, 0, '', '.') }}
                        </td>

                        <td width="150" class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('products.show', $product->id) }}" class="btn btn-outline-warning btn-sm" title="Detail">
                                    <i class="ti ti-eye"></i>
                                </a>
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                    <i class="ti ti-edit"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalDelete{{ $product->id }}" title="Delete"> 
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Modal Delete --}}
                    <div class="modal fade" id="modalDelete{{ $product->id }}" data-bs-backdrop="static" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-body text-center py-4">
                                    <i class="ti ti-alert-triangle text-danger display-1 mb-3"></i>
                                    <h4 class="fw-bold">Delete Product?</h4>
                                    <p class="text-muted">Are you sure you want to delete <strong>{{ $product->name }}</strong>? This action cannot be undone.</p>
                                    
                                    <div class="d-flex justify-content-center gap-2 mt-4">
                                        <button type="button" class="btn btn-light px-4 border" data-bs-dismiss="modal">Cancel</button>
                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger px-4">Yes, Delete It</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="ti ti-package-off fs-1 d-block mb-2"></i>
                            No products found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-2">
            {{ $products->links() }}
        </div>
    </div>
</x-app-layout>
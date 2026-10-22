<x-app-layout>
    {{-- Page Title --}}
    <x-page-title>Add Product - Toko Dua Putra</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        
        {{-- form add data --}}
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" id="form-add-product">
            @csrf
            <div class="row">
                <div class="col-lg-7">
                    
                    {{-- INPUT BARCODE (Wajib) --}}
                    <div class="mb-3 pe-xl-3">
                        <label class="form-label fw-bold">Barcode <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-upc-scan text-primary"></i></span>
                            {{-- Ditambahkan 'autofocus' agar siap ditembak scanner saat halaman dimuat --}}
                            <input type="text" name="barcode" id="barcode" class="form-control form-control-lg @error('barcode') is-invalid @enderror" value="{{ old('barcode') }}" placeholder="Tembak barcode pakai alat scanner..." autofocus autocomplete="off">
                        </div>
                        <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle"></i> Arahkan kursor ke sini, lalu gunakan alat scanner fisik Anda.</small>
                        @error('barcode')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- CATEGORY (Wajib) --}}
                    <div class="mb-3 pe-xl-3">
                        <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                        <select name="category" id="category" class="form-select select2-single @error('category') is-invalid @enderror">
                            <option selected disabled value="">- Select category -</option>
                            @foreach ($categories as $category)
                                <option {{ old('category') == $category->id ? 'selected' : '' }} value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- NAME (Wajib) --}}
                    <div class="mb-3 pe-xl-3">
                        <label class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" autocomplete="off">
                        @error('name')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- STOCK (Wajib) --}}
                    <div class="mb-3 pe-xl-3">
                        <label class="form-label fw-bold">Stock <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                            <input type="number" name="stock" id="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', 0) }}" min="0">
                        </div>
                        @error('stock')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- DESCRIPTION (Opsional) --}}
                    <div class="mb-3 pe-xl-3">
                        <label class="form-label fw-bold">Description <span class="text-muted fw-normal">(Optional)</span></label>
                        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Keterangan tambahan barang...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- PRICE (Wajib) --}}
                    <div class="mb-3 pe-xl-3">
                        <label class="form-label fw-bold">Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="price" id="price" class="form-control mask-number @error('price') is-invalid @enderror" value="{{ old('price') }}" autocomplete="off">
                        </div>
                        @error('price')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-lg-5">
                    {{-- IMAGE (Opsional) --}}
                    <div class="mb-3 ps-xl-3 text-center text-lg-start">
                        <label class="form-label fw-bold">Product Image <span class="text-muted fw-normal">(Optional)</span></label>
                        <input type="file" accept=".jpg, .jpeg, .png" name="image" id="image" class="form-control @error('image') is-invalid @enderror">
                        <small class="text-muted">Format: JPG, PNG. Max: 2MB</small>
                        @error('image')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror

                        <div class="mt-4">
                            <img id="imagePreview" src="{{ asset('images/no-image.svg') }}" class="img-thumbnail rounded-4 shadow-sm" style="width: 200px; height: 200px; object-fit: cover;" alt="Preview">
                        </div>
                    </div>
                </div>
            </div>
    
            <div class="pt-4 pb-2 mt-5 border-top">
                <div class="d-grid gap-3 d-sm-flex justify-content-md-start pt-1">
                    <button type="submit" class="btn btn-primary py-2 px-5 fw-bold">Save Product</button>
                    <a href="{{ route('products.index') }}" class="btn btn-light py-2 px-4 border">Cancel</a>
                </div>
            </div>
        </form>
    </div>

    {{-- SCRIPTS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Logika Alat Scanner Fisik
            const barcodeInput = document.getElementById('barcode');
            
            barcodeInput.addEventListener('keypress', function(e) {
                // Alat scanner fisik otomatis menekan "Enter" setelah membaca kode.
                // Kita cegah Enter tersebut mensubmit form agar tidak error validasi.
                if (e.key === 'Enter') {
                    e.preventDefault(); 
                    // Pindahkan fokus kursor ke kolom kategori setelah scan
                    document.getElementById('category').focus();
                }
            });

            // 2. Preview Gambar
            document.getElementById('image').onchange = evt => {
                const [file] = document.getElementById('image').files;
                if (file) {
                    document.getElementById('imagePreview').src = URL.createObjectURL(file);
                }
            }
        });
    </script>
</x-app-layout>
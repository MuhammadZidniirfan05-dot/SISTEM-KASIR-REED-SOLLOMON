<x-app-layout>
    {{-- Page Title --}}
    <x-page-title>Edit Product</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        
        {{-- Area Scanner (Opsional jika ingin update barcode via scan) --}}
        <div id="scanner-section" class="mb-4 d-none">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-camera"></i> Rescan Barcode...</h5>
                    <button type="button" class="btn-close btn-close-white" onclick="stopScanner()"></button>
                </div>
                <div class="card-body text-center">
                    <div id="reader" style="width: 100%; max-width: 500px; margin: 0 auto;"></div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-outline-primary" onclick="startScanner()">
                <i class="bi bi-qr-code-scan"></i> Change Barcode via Scan
            </button>
        </div>

        {{-- form edit data --}}
        <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-lg-7">
                    
                    {{-- INPUT BARCODE --}}
                    <div class="mb-3 pe-xl-3">
                        <label class="form-label">Barcode <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                            <input type="text" name="barcode" id="barcode" class="form-control @error('barcode') is-invalid @enderror" value="{{ old('barcode', $product->barcode_original) }}" placeholder="Barcode value..." autocomplete="off">
                        </div>
                        @error('barcode')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 pe-xl-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select select2-single @error('category') is-invalid @enderror" autocomplete="off">
                            <option disabled value="">- Select category -</option>
                            @foreach ($categories as $category)
                                <option {{ old('category', $product->category_id) == $category->id ? 'selected' : '' }} value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 pe-xl-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" autocomplete="off">
                        @error('name')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- INPUT STOCK --}}
                    <div class="mb-3 pe-xl-3">
                        <label class="form-label">Stock <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                            <input type="number" name="stock" id="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', $product->stock) }}" placeholder="0" min="0">
                        </div>
                        @error('stock')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 pe-xl-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror" autocomplete="off">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 pe-xl-3">
                        <label class="form-label">Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="price" class="form-control mask-number @error('price') is-invalid @enderror" value="{{ old('price', number_format($product->price, 0, '', '.')) }}" autocomplete="off">
                        </div>
                        @error('price')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="mb-3 ps-xl-3">
                        <label class="form-label">Image (Leave blank if not changing)</label>
                        <input type="file" accept=".jpg, .jpeg, .png" name="image" id="image" class="form-control @error('image') is-invalid @enderror" autocomplete="off">
                        @error('image')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror

                        <div class="mt-4">
                            <label class="d-block mb-2 text-muted small">Current / Preview Image:</label>
                            <img id="imagePreview" src="{{ asset('/storage/products/'.$product->image) }}" class="img-thumbnail rounded-4 shadow-sm" width="50%" alt="Image">
                        </div>
                    </div>
                </div>
            </div>
    
            <div class="pt-4 pb-2 mt-5 border-top">
                <div class="d-grid gap-3 d-sm-flex justify-content-md-start pt-1">
                    <button type="submit" class="btn btn-primary py-2 px-4">Update Product</button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary py-2 px-3">Cancel</a>
                </div>
            </div>
        </form>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        // Logika Preview Image saat pilih file baru
        document.getElementById('image').onchange = evt => {
            const [file] = document.getElementById('image').files
            if (file) {
                document.getElementById('imagePreview').src = URL.createObjectURL(file)
            }
        }

        // Script Scanner (Sama dengan halaman Add)
        let html5QrCode;
        async function startScanner() {
            const scannerSection = document.getElementById('scanner-section');
            scannerSection.classList.remove('d-none');
            setTimeout(async () => {
                try {
                    if (html5QrCode) await html5QrCode.clear();
                    html5QrCode = new Html5Qrcode("reader");
                    await html5QrCode.start(
                        { facingMode: "environment" }, 
                        { fps: 20, qrbox: { width: 300, height: 150 } }, 
                        (decodedText) => {
                            document.getElementById('barcode').value = decodedText;
                            stopScanner();
                        }
                    );
                } catch (err) { alert("Scanner Error: " + err); }
            }, 300);
        }

        function stopScanner() {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    document.getElementById('scanner-section').classList.add('d-none');
                });
            }
        }
    </script>
</x-app-layout>
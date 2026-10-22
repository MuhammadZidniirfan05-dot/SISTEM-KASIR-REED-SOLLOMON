<x-app-layout>
    <x-page-title>New Transaction</x-page-title>

    <div class="row">
        {{-- Bagian Kiri: Input Scan Fisik & Info Customer --}}
        <div class="col-lg-4">
            
            {{-- AREA SCANNER FISIK & KAMERA --}}
            <div class="card border-primary shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-upc-scan me-2"></i> Scan Produk</h5>
                </div>
                <div class="card-body bg-light">
                    
                    {{-- ===== FITUR BARU: MONITOR KAMERA ===== --}}
                    <div id="camera-container" class="position-relative text-center bg-dark rounded overflow-hidden mb-3 shadow-inner" style="height: 240px; display: none;">
                        <video id="video" style="width: 100%; height: 100%; object-fit: cover;"></video>
                        {{-- Garis laser merah virtual --}}
                        <div class="position-absolute top-50 start-50 translate-middle w-75 border-top border-danger border-2 shadow" style="box-shadow: 0 0 10px red;"></div>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="button" id="btn-toggle-camera" class="btn btn-outline-primary fw-bold shadow-sm">
                            <i class="bi bi-camera-video me-1"></i> Buka Kamera Scanner
                        </button>
                    </div>
                    <hr class="my-3 border-secondary">
                    {{-- ====================================== --}}

                    <label class="form-label fw-bold text-dark">Input Scanner Fisik / Manual</label>
                    <input type="text" id="scanner-input" class="form-control form-control-lg border-primary shadow-sm text-center fw-bold text-primary" placeholder="Tembak barcode di sini..." autofocus autocomplete="off" style="font-size: 1.2rem; letter-spacing: 2px;">
                    <small class="text-muted mt-2 d-block text-center"><i class="bi bi-info-circle"></i> Sistem akan memproses otomatis setelah angka masuk.</small>
                    
                    <div id="scan-feedback" class="mt-3 text-center fw-bold d-none" style="font-size: 0.95rem;"></div>
                    
                    {{-- DEBUG AREA --}}
                    <div id="debug-scanner" class="mt-2 p-2 bg-dark text-white rounded" style="font-size: 0.8rem; font-family: monospace; display: none;">
                        <strong>Debug:</strong> 
                        <span id="debug-content">Menunggu scan...</span>
                        <br>
                        <small>Tekan Ctrl+Shift+D untuk toggle debug</small>
                    </div>
                </div>
            </div>

            {{-- AREA CUSTOMER & DATE --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <label class="form-label fw-bold">Customer & Date</label>
                    <input type="text" id="date" class="form-control datepicker mb-3" value="{{ date('Y-m-d') }}" readonly>
                    <select id="customer" class="form-select select2-single">
                        <option value="">- General Customer -</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Bagian Kanan: Daftar Keranjang --}}
        <div class="col-lg-8">
            <form action="{{ route('transactions.store') }}" method="POST" id="form-transaction">
                @csrf
                <input type="hidden" name="customer_id" id="hidden-customer">
                <input type="hidden" name="date" id="hidden-date">

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0" id="table-cart">
                            <thead class="bg-primary-subtle text-primary">
                                <tr>
                                    <th class="ps-4 py-3 border-0 rounded-start">Product Item</th>
                                    <th class="border-0">Price</th>
                                    <th width="130" class="border-0 text-center">Qty</th>
                                    <th class="border-0">Subtotal</th>
                                    <th class="text-center border-0 rounded-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="empty-cart">
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-cart-x fs-1 d-block mb-2" style="color: #dee2e6;"></i>
                                        Keranjang masih kosong
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white p-4 border-top-0 rounded-bottom shadow-sm">
                        <div class="row align-items-center">
                            <div class="col">
                                    <h5 class="text-muted fw-bold mb-0">TOTAL TAGIHAN</h5>
                            </div>
                            <div class="col text-end">
                                <h2 class="fw-bolder text-primary mb-0" id="grand-total-text">Rp 0</h2>
                                <input type="hidden" name="total" id="grand-total-val" value="0">
                            </div>
                        </div>
                        <hr class="my-4 border-dashed" style="border-top: 2px dashed #dee2e6;">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('transactions.index') }}" class="btn btn-light px-4 fw-bold border">Batal</a>
                            <button type="submit" class="btn btn-primary px-5 fw-bold" id="btn-save" disabled>
                                <i class="bi bi-cart-check me-1"></i> Proses Transaksi
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    {{-- LIBRARY ZXING UNTUK KAMERA BARCODE --}}
    <script src="https://unpkg.com/@zxing/library@latest"></script>
    
    <script>
        $(document).ready(function() {
            let cart = [];
            let isProcessing = false;
            let debugMode = false;
            
            const beep = new Audio("https://assets.mixkit.co/active_storage/sfx/2568/2568-preview.mp3");
            const errBeep = new Audio("https://assets.mixkit.co/active_storage/sfx/2572/2572-preview.mp3");

            // ============================================================
            // INISIALISASI KAMERA SCANNER (ZXING)
            // ============================================================
            let codeReader = new ZXing.BrowserMultiFormatReader();
            let isCameraOpen = false;

            $('#btn-toggle-camera').on('click', function() {
                let container = $('#camera-container');
                
                if (isCameraOpen) {
                    // Matikan Kamera
                    codeReader.reset();
                    container.slideUp();
                    $(this).html('<i class="bi bi-camera-video me-1"></i> Buka Kamera Scanner')
                           .removeClass('btn-danger').addClass('btn-outline-primary');
                    isCameraOpen = false;
                    $('#scanner-input').focus();
                } else {
                    // Nyalakan Kamera
                    container.slideDown();
                    $(this).html('<i class="bi bi-camera-video-off me-1"></i> Matikan Kamera')
                           .removeClass('btn-outline-primary').addClass('btn-danger');
                    isCameraOpen = true;
                    
                    // Decode dari kamera (pilih kamera belakang jika ada)
                    codeReader.decodeFromVideoDevice(null, 'video', (result, err) => {
                        if (result) {
                            let rawValue = result.text;
                            
                            // Mencegah kamera melakukan scan berulang kali dalam 1 detik
                            if (!isProcessing) {
                                console.log('📸 Terdeteksi oleh kamera:', rawValue);
                                $('#scanner-input').val(rawValue); // Tampilkan di kotak input
                                processBarcode(rawValue); // Kirim ke Reed-Solomon
                            }
                        }
                        // err diabaikan karena library ini terus melakukan looping pencarian
                    }).catch((err) => {
                        console.error("Kamera error:", err);
                        showFeedback('Gagal mengakses kamera. Pastikan izin kamera diberikan.', true);
                    });
                }
            });
            // ============================================================

            // Toggle debug dengan Ctrl+Shift+D
            $(document).on('keydown', function(e) {
                if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                    e.preventDefault();
                    debugMode = !debugMode;
                    $('#debug-scanner').toggle(debugMode);
                    if (debugMode) {
                        $('#debug-content').text('Debug mode ACTIVE');
                    }
                }
            });

            function showFeedback(message, isError = true) {
                let fb = $('#scan-feedback');
                fb.removeClass('d-none text-success text-danger');
                
                if(isError) {
                    fb.addClass('text-danger').html(`<i class="bi bi-x-circle-fill me-1"></i> ${message}`);
                    errBeep.play().catch(e => {}); // Catch error autoplay
                } else {
                    fb.addClass('text-success').html(`<i class="bi bi-check-circle-fill me-1"></i> ${message}`);
                }

                setTimeout(() => { fb.addClass('d-none'); }, 3000);
            }

            // FUNGSI UNTUK MEMBERSIHKAN BARCODE
            function cleanBarcode(input) {
                if (!input) return '';
                let cleaned = String(input);
                cleaned = cleaned.replace(/[\r\n\t\s]+/g, ''); 
                cleaned = cleaned.replace(/[^0-9]/g, ''); 
                return cleaned;
            }

            // FUNGSI UTAMA PROSES BARCODE
            function processBarcode(rawInput) {
                const cleanedBarcode = cleanBarcode(rawInput);
                
                console.log('=== PROCESS BARCODE ===');
                console.log('Raw input:', rawInput);
                console.log('Cleaned:', cleanedBarcode);
                
                if (debugMode) {
                    $('#debug-content').html(`
                        Raw: "${rawInput}"<br>
                        Cleaned: "${cleanedBarcode}"<br>
                        Length: ${cleanedBarcode.length}
                    `);
                }

                if (!cleanedBarcode || cleanedBarcode.length < 3) {
                    showFeedback('Barcode tidak valid (terlalu pendek)', true);
                    resetScanner();
                    return;
                }

                if (isProcessing) return;

                isProcessing = true;
                $('#scanner-input').prop('disabled', true).attr('placeholder', 'Memproses...');

                // PANGGIL API RS-DECODE
                $.ajax({
                    url: "/rs-decode",
                    type: "POST",
                    data: { 
                        barcode: cleanedBarcode, 
                        _token: '{{ csrf_token() }}' 
                    },
                    timeout: 10000,
                    success: function(rsResponse) {
                        console.log('RS Response:', rsResponse);
                        
                        if (debugMode) {
                            $('#debug-content').append(`<br>RS Status: ${rsResponse.status}`);
                        }
                        
                        if (rsResponse && rsResponse.status === 'success' && rsResponse.barcode) {
                            const verifiedBarcode = rsResponse.barcode;
                            const errorCorrected = rsResponse.error_corrected || 0;
                            
                            console.log(`✅ Decode success: ${verifiedBarcode}, Error: ${errorCorrected}`);
                            
                            if (debugMode) {
                                $('#debug-content').append(`<br>✅ Decoded: ${verifiedBarcode} (${errorCorrected} errors)`);
                            }
                            
                            if (errorCorrected > 0) {
                                showFeedback(`Barcode cacat diperbaiki: ${errorCorrected} error`, false);
                            }
                            
                            // LANJUTKAN PENCARIAN PRODUK
                            searchProduct(verifiedBarcode, errorCorrected);
                            
                        } else {
                            // FALLBACK
                            console.warn('⚠️ RS gagal, fallback ke database dengan:', cleanedBarcode);
                            if (debugMode) {
                                $('#debug-content').append(`<br>⚠️ Fallback to DB: ${cleanedBarcode}`);
                            }
                            searchProduct(cleanedBarcode, 0);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ RS Error:', status, error);
                        if (debugMode) {
                            $('#debug-content').append(`<br>❌ RS Error: ${status}`);
                        }
                        searchProduct(cleanedBarcode, 0);
                    }
                });
            }

            // FUNGSI PENCARIAN PRODUK
            function searchProduct(barcode, errorCorrected) {
                console.log('🔍 Searching product:', barcode);
                if (debugMode) {
                    $('#debug-content').append(`<br>🔍 Searching: ${barcode}`);
                }
                
                $.ajax({
                    url: "/products/search",
                    data: { search: barcode },
                    dataType: 'json',
                    timeout: 5000,
                    success: function(data) {
                        if (data && data.length > 0) {
                            beep.play().catch(e => {});
                            let p = data[0];
                            
                            if (p.stock <= 0) {
                                showFeedback(`Stok ${p.name} habis!`, true);
                                resetScanner();
                                return;
                            }
                            
                            addToCart(p.id, p.name, p.price, 1, p.stock);
                            
                            if (errorCorrected > 0) {
                                showFeedback(`${p.name} ditambahkan (${errorCorrected} error dikoreksi RS)`, false);
                            } else {
                                showFeedback(`${p.name} berhasil ditambahkan`, false);
                            }
                        } else {
                            errBeep.play().catch(e => {});
                            showFeedback(`Barcode "${barcode}" tidak ditemukan`, true);
                            
                            Swal.fire({
                                icon: 'warning',
                                title: 'Produk Tidak Ditemukan',
                                html: `
                                    <div class="text-center">
                                        <i class="bi bi-box-seam" style="font-size: 4rem; color: #ffc107;"></i>
                                        <p class="mt-3">Barcode <strong>${barcode}</strong> tidak terdaftar di database.</p>
                                        ${errorCorrected > 0 ? `<div class="alert alert-info mt-2"><i class="bi bi-check-circle"></i> ${errorCorrected} error berhasil diperbaiki</div>` : ''}
                                    </div>
                                `,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#0d6efd'
                            }).then(() => {
                                resetScanner();
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Search error:', xhr);
                        resetScanner();
                        showFeedback("Gagal mencari produk", true);
                    }
                });
            }

            // ============================================================
            // EVENT HANDLER UNTUK INPUT MANUAL (KEYBOARD/FISIK SCANNER)
            // ============================================================
            $('#scanner-input').on('keydown', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    let rawValue = $(this).val();
                    $(this).val('');
                    if (rawValue && rawValue.trim() !== '') {
                        processBarcode(rawValue);
                    } else {
                        showFeedback('Barcode kosong, silahkan scan ulang', true);
                        resetScanner();
                    }
                }
            });

            $('#scanner-input').on('paste', function(e) {
                setTimeout(() => {
                    let rawValue = $(this).val();
                    if (rawValue && rawValue.trim() !== '' && !isProcessing) {
                        processBarcode(rawValue);
                        $(this).val('');
                    }
                }, 100);
            });

            // ============================================================
            // FUNGSI RESET & LOGIKA KERANJANG
            // ============================================================
            function resetScanner() {
                isProcessing = false;
                $('#scanner-input').prop('disabled', false).attr('placeholder', 'Tembak barcode di sini...').focus();
            }

            function addToCart(id, name, price, qty, maxStock) {
                let existing = cart.find(item => item.id == id);
                if (existing) {
                    if (existing.qty + qty > maxStock) {
                        showFeedback(`Stok ${name} tidak mencukupi!`, true);
                        resetScanner();
                        return;
                    }
                    existing.qty += qty;
                } else {
                    cart.push({ id, name, price, qty, maxStock });
                }
                updateTable();
                resetScanner();
            }

            function updateTable() {
                let html = '';
                let total = 0;
                
                if (cart.length > 0) {
                    cart.forEach((item, index) => {
                        let subtotal = item.price * item.qty;
                        total += subtotal;
                        html += `<tr>
                            <td class="ps-4 border-bottom-0">
                                <b class="text-dark">${item.name}</b>
                                <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                            </td>
                            <td class="text-muted border-bottom-0">Rp ${new Intl.NumberFormat('id-ID').format(item.price)}</td>
                            <td class="border-bottom-0">
                                <input type="number" class="form-control form-control-sm text-center edit-qty shadow-sm border-primary-subtle mx-auto" data-index="${index}" name="items[${index}][qty]" value="${item.qty}" min="1" max="${item.maxStock}" style="max-width: 80px;">
                            </td>
                            <td class="fw-bold text-dark border-bottom-0">Rp ${new Intl.NumberFormat('id-ID').format(subtotal)}</td>
                            <td class="text-center border-bottom-0">
                                <button type="button" class="btn btn-light text-danger btn-sm rounded-circle" onclick="removeItem(${index})" title="Hapus">
                                    <i class="bi bi-trash fs-5"></i>
                                </button>
                            </td>
                        </tr>`;
                    });
                    $('#btn-save').prop('disabled', false);
                } else {
                    html = '<tr><td colspan="5" class="text-center py-5 text-muted border-0"><i class="bi bi-cart-x fs-1 d-block mb-2" style="color: #dee2e6;"></i>Keranjang masih kosong</td></tr>';
                    $('#btn-save').prop('disabled', true);
                }
                
                $('#table-cart tbody').html(html);
                $('#grand-total-text').text('Rp ' + new Intl.NumberFormat('id-ID').format(total));
                $('#grand-total-val').val(total);
            }

            $(document).on('change', '.edit-qty', function() {
                let i = $(this).data('index');
                let v = parseInt($(this).val());
                if (v > cart[i].maxStock) v = cart[i].maxStock;
                if (v < 1 || isNaN(v)) v = 1;
                cart[i].qty = v;
                updateTable();
                
                // Jangan paksa focus ke input jika sedang pakai kamera
                if (!isCameraOpen) $('#scanner-input').focus();
            });

            window.removeItem = function(i) { 
                cart.splice(i, 1); 
                updateTable(); 
                if (!isCameraOpen) $('#scanner-input').focus(); 
            }

            $('#form-transaction').submit(function() {
                $('#hidden-customer').val($('#customer').val());
                $('#hidden-date').val($('#date').val());
            });

            // Focus otomatis hanya jika kamera tidak aktif
            $(document).on('click', function(e) {
                if(!isCameraOpen && !$(e.target).closest('input, button, a, select, .swal2-container, #camera-container').length) {
                    $('#scanner-input').focus();
                }
            });

            console.log('✅ System ready. Memuat konfigurasi kamera...');
        });
    </script> 
</x-app-layout>
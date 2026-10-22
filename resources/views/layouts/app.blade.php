{{-- Aplikasi POS Apotek dengan Laravel 11
*******************************************
* Developer   : Indra Styawantoro
* Company     : Pustaka Koding
* Release     : Juni 2024
* Update      : -
* Website     : pustakakoding.com
* E-mail      : pustaka.koding@gmail.com
* WhatsApp    : +62-813-7778-3334 
--}}

<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    {{-- Required meta tags --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aplikasi POS Retail - Toko Dua Putra">
    <meta name="author" content="Indra Styawantoro">

    {{-- Title --}}
    <title>POS System - Toko Dua Putra</title>

    {{-- Favicon icon --}}
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    {{-- Tabler Icons CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    
    {{-- Bootstrap Icons CSS (Ditambahkan untuk mendukung ikon di halaman transaksi) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    {{-- Font --}}
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    {{-- Flatpickr CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    {{-- Template CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- CSS TAMBAHAN UNTUK MEMPERBAIKI JARAK (GAP) KOSONG --}}
    <style>
        body { padding-top: 0 !important; }
        .page-content { margin-top: 0 !important; padding-top: 0 !important; }
    </style>

    {{-- jQuery Core --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>

<body class="d-flex flex-column h-100 bg-light">
    {{-- Header --}}
    <header>
        {{-- Navbar --}}
        @include('layouts.navbar')
    </header>

    {{-- Main Content (Jarak atas dikurangi menjadi pt-3 agar lebih rapat ke navbar) --}}
    <main class="flex-shrink-0 pt-3 pb-4"> 
        <div class="container-fluid px-4"> 
            <div class="page-content">
                {{-- menampilkan konten sesuai halaman yang dipilih --}}
                {{ $slot }}
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="footer bg-white shadow-sm mt-auto py-3 border-top">
        <div class="container-fluid px-4">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
                {{-- copyright --}}
                <div class="copyright text-center mb-2 mb-md-0 text-muted" style="font-size: 0.85rem;">
                    &copy; {{ date('Y') }} - <span class="fw-bold text-dark">Toko Dua Putra</span>. All rights reserved.
                </div>
                {{-- link --}}
                <div class="link">
                    <a href="#" class="text-decoration-none text-muted" style="font-size: 0.85rem;">Terms & Conditions</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    {{-- Flatpickr JS --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/jquery-mask-plugin@1.14.16/dist/jquery.mask.min.js" integrity="sha256-Kg2zTcFO9LXOc7IwcBx1YeUBJmekycsnTsq2RuFHSZU=" crossorigin="anonymous"></script>
    
    {{-- Sweetalert2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    {{-- Bootstrap Notify --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-notify@3.1.3/bootstrap-notify.min.js"></script>

    {{-- Custom Scripts --}}
    <script src="{{ asset('js/plugins.js') }}"></script>
    <script src="{{ asset('js/image-preview.js') }}"></script>

    <script>
        // menampilkan pesan dengan sweetalert
        @if (session('success'))
            Swal.fire({
                icon: "success",
                title: "Success!",
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000
            });
        @elseif (session('error'))
            Swal.fire({
                icon: "error",
                title: "Failed!",
                text: "{{ session('error') }}",
                showConfirmButton: false,
                timer: 2500 
            });
        @endif
    </script>
</body>

</html>
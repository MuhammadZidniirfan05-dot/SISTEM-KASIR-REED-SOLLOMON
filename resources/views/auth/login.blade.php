<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - POS Toko Dua Putra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <style>
        body { background-color: #f8fafc; font-family: 'Nunito', sans-serif; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center h-100">

    <div class="card border-0 shadow-lg" style="width: 100%; max-width: 400px; border-radius: 1rem;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <div class="bg-primary text-white d-inline-flex p-3 rounded-4 shadow-sm mb-3">
                    <i class="ti ti-building-store fs-1"></i>
                </div>
                <h4 class="fw-bold text-dark mb-0">DUA PUTRA</h4>
                <p class="text-muted small"></p>
            </div>

            @if(session('success'))
                <div class="alert alert-success small p-2 text-center">{{ session('success') }}</div>
            @endif

            <form action="{{ route('login.process') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold text-secondary small">Email Address</label>
                    <input type="email" name="email" class="form-control py-2 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="admin@duaputra.com" autofocus required>
                    @error('email')
                        <div class="invalid-feedback small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold text-secondary small">Password</label>
                    <input type="password" name="password" class="form-control py-2" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3 shadow-sm">
                    <i class="ti ti-login me-2"></i> Sign In
                </button>
            </form>

            {{-- Akun Demo --}}
            <div class="mt-4 pt-3 border-top">
                <p class="text-center text-muted small fw-bold mb-1">Akun Demo</p>
                <p class="text-center text-muted mb-3" style="font-size: 0.75rem;">Khusus demo portofolio, klik untuk mengisi otomatis</p>

                <button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-2 text-start"
                        onclick="isiAkun('admin@duaputra.com', 'passwordbaru')">
                    <i class="ti ti-shield-lock me-1"></i> <strong>Admin</strong><br>
                    <span class="small text-muted">admin@duaputra.com / passwordbaru</span>
                </button>

                <button type="button" class="btn btn-outline-secondary btn-sm w-100 text-start"
                        onclick="isiAkun('kasir@duaputra.com', 'passwordkasir')">
                    <i class="ti ti-cash me-1"></i> <strong>Kasir</strong><br>
                    <span class="small text-muted">kasir@duaputra.com / passwordkasir</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function isiAkun(email, password) {
            document.querySelector('input[name=email]').value = email;
            document.querySelector('input[name=password]').value = password;
        }
    </script>

</body>
</html>
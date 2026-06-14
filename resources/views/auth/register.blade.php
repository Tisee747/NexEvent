<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Panitia - NexEvent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .register-card { max-width: 500px; margin: 40px auto; border-radius: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow-sm border-0 register-card p-4">
            <div class="text-center mb-4">
                <h4 class="fw-bold text-primary"><i class="fas fa-user-plus me-2"></i>Daftar Akun Panitia</h4>
                <p class="text-muted small">Buat akun untuk organisasi kamu agar bisa mempublikasikan acara.</p>
            </div>
            
            <form action="{{ route('register.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Perwakilan</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Santoso" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Asal Organisasi (HIMA/UKM)</label>
                    <input type="text" name="organization" class="form-control" placeholder="Contoh: BEM KEMA Tel-U" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Resmi Organisasi</label>
                    <input type="email" name="email" class="form-control" placeholder="organisasi@student.telkomuniversity.ac.id" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="regPassword" class="form-control" placeholder="Minimal 8 karakter" required>
                        <button class="btn btn-light border" type="button" onclick="togglePassword('regPassword', 'iconReg1')">
                            <i class="fas fa-eye text-muted" id="iconReg1"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Konfirmasi Password</label>
                    <div class="input-group">
                        <input type="password" name="password_confirmation" id="regConfirm" class="form-control" placeholder="Ketik ulang password" required>
                        <button class="btn btn-light border" type="button" onclick="togglePassword('regConfirm', 'iconReg2')">
                            <i class="fas fa-eye text-muted" id="iconReg2"></i>
                        </button>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger small p-2">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="alert alert-warning border-0 p-3 small mb-4">
                    <i class="fas fa-info-circle me-2"></i> Akun yang baru didaftarkan akan berstatus <strong>Pending</strong> dan harus menunggu persetujuan (Approval) dari pihak Kemahasiswaan.
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold mb-3">Daftar Sekarang</button>
            </form>

            <div class="text-center mt-2 small">
                Sudah punya akun? <a href="{{ route('login') }}" class="fw-bold text-decoration-none">Kembali ke Login</a>
            </div>
        </div>
    </div>
    <script>
        function togglePassword(inputId, iconId) {
            var input = document.getElementById(inputId);
            var icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
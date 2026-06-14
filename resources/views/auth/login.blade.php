<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NexEvent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .login-card { max-width: 400px; margin: 80px auto; border-radius: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow-sm border-0 login-card p-4">
            <div class="text-center mb-4">
                <h3 class="fw-bold text-primary"><i class="fas fa-ticket-alt me-2"></i>NexEvent</h3>
                <p class="text-muted small">Masuk ke Panel Admin & Superadmin</p>
            </div>
            
            @if(session('error'))
                <div class="alert alert-danger small p-2"><i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success small p-2"><i class="fas fa-check-circle me-1"></i> {{ session('success') }}</div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Pengguna</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="nama@student.telkomuniversity.ac.id" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" name="password" id="loginPassword" class="form-control" placeholder="••••••••" required>
                        <button class="btn btn-light border" type="button" onclick="togglePassword('loginPassword', 'iconLogin')">
                            <i class="fas fa-eye text-muted" id="iconLogin"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-4 small">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Ingat Saya</label>
                    </div>
                    <a href="#" class="text-decoration-none">Lupa Password?</a>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold mb-3">Masuk Sekarang</button>
            </form>

            <div class="text-center mt-2 small">
                Belum punya akun organisasi? <a href="{{ route('register') }}" class="fw-bold text-decoration-none">Daftar Panitia Baru</a>
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
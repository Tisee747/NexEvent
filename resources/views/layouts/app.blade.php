<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexEvent Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; overflow-x: hidden; }
        .sidebar { min-height: 100vh; background-color: #212529; color: white; width: 250px; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 15px; display: block; transition: 0.3s; }
        .sidebar a:hover { background-color: #343a40; color: #fff; border-radius: 5px; }
        .sidebar a.active { background-color: #0d6efd; color: #fff; border-radius: 5px; }
        .main-content { width: calc(100% - 250px); padding: 20px; }
    </style>
</head>
<body>
    <div class="d-flex">
        
        <div class="sidebar p-3 shadow-sm">
            <h4 class="text-center mb-4 mt-2 fw-bold text-white">
                <i class="fas fa-ticket-alt text-primary me-2"></i>NexEvent
            </h4>

            <div id="menuSuperadmin" class="d-none">
                <small class="text-warning text-uppercase fw-bold mb-2 d-block mt-3">Menu Kemahasiswaan</small>
                <a href="/superadmin/dashboard" class="{{ request()->is('superadmin/dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie me-2"></i> Dashboard Utama
                </a>
                <a href="/superadmin" class="{{ request()->is('superadmin') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt me-2"></i> Pusat Approval
                </a>
                <a href="/superadmin/all-events" class="{{ request()->is('superadmin/all-events') ? 'active' : '' }}">
                    <i class="fas fa-list-alt me-2"></i> Semua Acara
                </a>
                <a href="/superadmin/organizations" class="{{ request()->is('superadmin/organizations') ? 'active' : '' }}">
                    <i class="fas fa-sitemap me-2"></i> Manajemen Organisasi
                </a>
            </div>
            
            <div id="menuPanitia" class="d-none">
                <small class="text-info text-uppercase fw-bold mb-2 d-block mt-3">Menu Panitia</small>
                <a href="/" class="{{ request()->is('/') ? 'active' : '' }}">
                    <i class="fas fa-chart-line me-2"></i> Dashboard
                </a>
                <a href="/events" class="{{ request()->is('events*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt me-2"></i> Daftar Acara
                </a>
                <a href="/participants" class="{{ request()->is('participants*') ? 'active' : '' }}">
                    <i class="fas fa-users me-2"></i> Manajemen Peserta
                </a>
                <a href="/attendance" class="{{ request()->is('attendance*') ? 'active' : '' }}">
                    <i class="fas fa-qrcode me-2"></i> Verifikasi Kehadiran
                </a>
            </div>
            
            <hr class="border-secondary mt-4">
            <a href="#" onclick="handleLogout(event)"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>

        <div class="main-content">
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm rounded mb-4 p-3">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h4 fw-bold">@yield('title', 'Dashboard')</span>
                    <div class="d-flex align-items-center">
                        <div class="text-end me-3">
                            <span id="navUserName" class="d-block fw-semibold lh-1">Memuat...</span>
                            <small id="navUserOrg" class="text-muted" style="font-size: 0.75rem;">...</small>
                        </div>
                        <img id="navUserAvatar" src="https://ui-avatars.com/api/?name=User&background=0d6efd&color=fff" class="rounded-circle shadow-sm" width="40" alt="Profile">
                    </div>
                </div>
            </nav>

            @yield('content')

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('auth_token');
            const userData = localStorage.getItem('user_data');

            if (!token || !userData) {
                window.location.href = '/login';
                return;
            }

            const user = JSON.parse(userData);

            document.getElementById('navUserName').textContent = user.name;
            document.getElementById('navUserOrg').textContent = user.organization || (user.role === 'superadmin' ? 'Superadmin' : 'Panitia');
            document.getElementById('navUserAvatar').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=0d6efd&color=fff`;

            // Menampilkan menu samping sesuai peran
            if (user.role === 'superadmin') {
                document.getElementById('menuSuperadmin').classList.remove('d-none');
            } else {
                document.getElementById('menuPanitia').classList.remove('d-none');
            }
        });

        function handleLogout(e) {
            e.preventDefault();
            const token = localStorage.getItem('auth_token');
            
            fetch('/api/logout', {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            }).then(() => {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user_data');
                window.location.href = '/login';
            }).catch(() => {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user_data');
                window.location.href = '/login';
            });
        }
    </script>
</body>
</html>
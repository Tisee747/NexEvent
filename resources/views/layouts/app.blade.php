<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexEvent Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2563EB;
            --secondary-color: #38BDF8;
            --bg-color: #F8FAFC;
            --card-color: #FFFFFF;
            --sidebar-bg: #1E293B;
            --sidebar-hover: #334155;
            --border-light: #E2E8F0;
        }
        body { background-color: var(--bg-color); overflow-x: hidden; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background-color: var(--sidebar-bg); color: white; width: 250px; }
        .sidebar a { color: #cbd5e1; text-decoration: none; padding: 12px 15px; display: block; transition: 0.3s; margin-bottom: 4px; }
        .sidebar a:hover { background-color: var(--sidebar-hover); color: #fff; border-radius: 8px; }
        .sidebar a.active { background-color: var(--primary-color); color: #fff; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.4); }
        .main-content { width: calc(100% - 250px); padding: 24px; }
        .card { background-color: var(--card-color); border-color: var(--border-light); }
        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); }
        .btn-primary:hover { background-color: #1D4ED8; border-color: #1D4ED8; }
        .text-primary { color: var(--primary-color) !important; }
        .bg-primary { background-color: var(--primary-color) !important; }
    </style>
</head>
<body>
    <div class="d-flex">
        
        <div class="sidebar p-3 shadow">
            <h4 class="text-center mb-4 mt-2 fw-bold text-white">
                <i class="fas fa-ticket-alt text-primary me-2"></i>NexEvent
            </h4>

            <div id="menuSuperadmin" class="d-none">
                <small class="text-secondary text-uppercase fw-bold mb-2 d-block mt-4" style="font-size: 0.75rem; letter-spacing: 1px;">Menu Kemahasiswaan</small>
                <a href="/superadmin/dashboard" class="{{ request()->is('superadmin/dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie me-2 w-20px"></i> Dashboard Utama
                </a>
                <a href="/superadmin" class="{{ request()->is('superadmin') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt me-2 w-20px"></i> Pusat Approval
                </a>
                <a href="/superadmin/all-events" class="{{ request()->is('superadmin/all-events') ? 'active' : '' }}">
                    <i class="fas fa-list-alt me-2 w-20px"></i> Semua Acara
                </a>
                <a href="/superadmin/organizations" class="{{ request()->is('superadmin/organizations') ? 'active' : '' }}">
                    <i class="fas fa-sitemap me-2 w-20px"></i> Manajemen Organisasi
                </a>
            </div>
            
            <div id="menuPanitia" class="d-none">
                <small class="text-secondary text-uppercase fw-bold mb-2 d-block mt-4" style="font-size: 0.75rem; letter-spacing: 1px;">Menu Panitia</small>
                <a href="/" class="{{ request()->is('/') ? 'active' : '' }}">
                    <i class="fas fa-chart-line me-2 w-20px"></i> Dashboard
                </a>
                <a href="/events" class="{{ request()->is('events*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt me-2 w-20px"></i> Daftar Acara
                </a>
                <a href="/participants" class="{{ request()->is('participants*') ? 'active' : '' }}">
                    <i class="fas fa-users me-2 w-20px"></i> Manajemen Peserta
                </a>
                <a href="/attendance" class="{{ request()->is('attendance*') ? 'active' : '' }}">
                    <i class="fas fa-qrcode me-2 w-20px"></i> Verifikasi Kehadiran
                </a>
                <a href="/admin/members" class="{{ request()->is('admin/members*') ? 'active' : '' }}">
                    <i class="fas fa-user-plus me-2 w-20px"></i> Manajemen Panitia
                </a>
            </div>
            
            <hr class="border-secondary mt-4 opacity-25">
            <a href="#" onclick="handleLogout(event)" class="text-danger mt-2"><i class="fas fa-sign-out-alt me-2 w-20px"></i> Logout Akun</a>
        </div>

        <div class="main-content">
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm rounded-3 mb-4 p-3 border-0">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h5 fw-bold text-dark">@yield('title', 'Dashboard')</span>
                    <div class="d-flex align-items-center">
                        <div class="text-end me-3">
                            <span id="navUserName" class="d-block fw-bold lh-1 text-dark">Memuat...</span>
                            <small id="navUserOrg" class="text-primary fw-semibold" style="font-size: 0.75rem;">...</small>
                        </div>
                        <img id="navUserAvatar" src="https://ui-avatars.com/api/?name=User&background=2563EB&color=fff" class="rounded-circle shadow-sm" width="45" height="45" alt="Profile">
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
            window.currentUser = user; 

            document.getElementById('navUserName').textContent = user.name;
            document.getElementById('navUserOrg').textContent = user.organization || (user.role === 'superadmin' ? 'Pihak Kampus' : 'Panitia Acara');
            document.getElementById('navUserAvatar').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=2563EB&color=fff&rounded=true&bold=true`;

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
                headers: { 
                    'Authorization': `Bearer ${token}`, 
                    'Accept': 'application/json' 
                },
                credentials: 'omit'
            }).then(() => {
                localStorage.clear();
                window.location.href = '/login';
            }).catch(() => {
                localStorage.clear();
                window.location.href = '/login';
            });
        }
    </script>
</body>
</html>
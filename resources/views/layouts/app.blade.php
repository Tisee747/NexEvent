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

            @if(Auth::user()->role === 'superadmin')
                <small class="text-warning text-uppercase fw-bold mb-2 d-block mt-3">Menu Kemahasiswaan</small>
                
                <a href="{{ route('superadmin.dashboard') }}" class="{{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie me-2"></i> Dashboard Utama
                </a>
                
                <a href="{{ route('superadmin.index') }}" class="{{ request()->routeIs('superadmin.index') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt me-2"></i> Pusat Approval
                </a>
                
                <a href="{{ route('superadmin.allEvents') }}" class="{{ request()->routeIs('superadmin.allEvents') ? 'active' : '' }}">
                    <i class="fas fa-list-alt me-2"></i> Semua Acara
                </a>
                
                <a href="{{ route('superadmin.organizations') }}" class="{{ request()->routeIs('superadmin.organizations') ? 'active' : '' }}">
                    <i class="fas fa-sitemap me-2"></i> Manajemen Organisasi
                </a>
                
            @else
                
                <small class="text-info text-uppercase fw-bold mb-2 d-block mt-3">Menu Panitia</small>
                <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">
                    <i class="fas fa-chart-line me-2"></i> Dashboard
                </a>
                <a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.index') || request()->routeIs('events.create') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt me-2"></i> Daftar Acara
                <a href="{{ route('participants.index') }}" class="{{ request()->routeIs('participants.index') ? 'active' : '' }}">
                    <i class="fas fa-users me-2"></i> Manajemen Peserta
                </a>
                <a href="{{ route('attendance.index') }}" class="{{ request()->routeIs('attendance.index') ? 'active' : '' }}">
                    <i class="fas fa-qrcode me-2"></i> Verifikasi Kehadiran
                </a>
                
            @endif
            
            <hr class="border-secondary mt-4">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-link text-danger text-decoration-none p-0 ps-3 w-100 text-start">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </button>
            </form>
        </div>

        <div class="main-content">
            
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm rounded mb-4 p-3">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h4 fw-bold">@yield('title', 'Dashboard')</span>
                    <div class="d-flex align-items-center">
                        <div class="text-end me-3">
                            <span class="d-block fw-semibold lh-1">{{ Auth::user()->name }}</span>
                            <small class="text-muted" style="font-size: 0.75rem;">{{ Auth::user()->organization ?? 'Superadmin' }}</small>
                        </div>
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0d6efd&color=fff" class="rounded-circle shadow-sm" width="40" alt="Profile">
                    </div>
                </div>
            </nav>

            @yield('content')

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
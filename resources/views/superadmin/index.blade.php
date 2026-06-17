@extends('layouts.app')

@section('title', 'Pusat Approval')

@section('content')
<div class="container-fluid p-0">
    <div id="alertBox" class="alert d-none small p-2 mb-3"></div>

    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-user-clock me-2"></i>Menunggu Persetujuan Akun Panitia</h6>
                </div>
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Nama Perwakilan</th>
                                <th>Organisasi</th>
                                <th>Email</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="pendingUsersTable">
                            <tr><td colspan="4" class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-check me-2"></i>Menunggu Persetujuan Acara</h6>
                </div>
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Judul Acara</th>
                                <th>Penyelenggara</th>
                                <th>Tanggal Pelaksanaan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="pendingEventsTable">
                            <tr><td colspan="4" class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const token = localStorage.getItem('auth_token');

    document.addEventListener('DOMContentLoaded', () => {
        loadPendingData();
    });

    async function loadPendingData() {
        try {
            let res = await fetch('/api/superadmin/pending-events', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            let result = await res.json();

            if (res.ok) {
                renderUsers(result.data.pendingUsers);
                renderEvents(result.data.pendingEvents);
            }
        } catch (error) {
            showAlert('Gagal terhubung ke server API.', 'danger');
        }
    }

    function renderUsers(users) {
        let tbody = document.getElementById('pendingUsersTable');
        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Tidak ada akun yang menunggu persetujuan.</td></tr>';
            return;
        }
        
        tbody.innerHTML = '';
        users.forEach(user => {
            tbody.innerHTML += `
                <tr>
                    <td>${user.name}</td>
                    <td><span class="badge bg-secondary">${user.organization}</span></td>
                    <td>${user.email}</td>
                    <td>
                        <button onclick="approveUser(${user.id})" class="btn btn-sm btn-success">
                            <i class="fas fa-check me-1"></i> Aktifkan Akun
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    function renderEvents(events) {
        let tbody = document.getElementById('pendingEventsTable');
        if (events.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Tidak ada proposal acara baru.</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        events.forEach(event => {
            let dateStr = new Date(event.event_date).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});
            let orgName = event.panitia ? event.panitia.organization : '-';
            
            tbody.innerHTML += `
                <tr>
                    <td class="fw-bold">${event.title}</td>
                    <td>${orgName}</td>
                    <td>${dateStr}</td>
                    <td>
                        <a href="/superadmin/event/${event.id}" class="btn btn-sm btn-info text-white fw-bold">
                            <i class="fas fa-search me-1"></i> Review Detail
                        </a>
                    </td>
                </tr>
            `;
        });
    }

    async function approveUser(id) {
        if(!confirm('Aktifkan akun organisasi ini?')) return;
        
        try {
            let res = await fetch(`/api/superadmin/user/${id}/approve`, {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}` }
            });
            let result = await res.json();
            
            if (res.ok) {
                showAlert(result.message, 'success');
                loadPendingData(); // Perbarui tabel tanpa refresh web
            } else {
                showAlert('Gagal mengaktifkan akun.', 'danger');
            }
        } catch(e) {
            showAlert('Kesalahan jaringan.', 'danger');
        }
    }

    function showAlert(msg, type) {
        let box = document.getElementById('alertBox');
        box.className = `alert alert-${type} small p-2 mb-3`;
        box.innerHTML = msg;
        box.classList.remove('d-none');
        setTimeout(() => box.classList.add('d-none'), 3000);
    }
</script>
@endsection
@extends('layouts.app')

@section('title', 'Manajemen Acara')

@section('content')
<div class="container-fluid p-0">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-gray-800">Daftar Acara & Proposal Anggota</h5>
            <a href="/events/create" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Ajukan Acara
            </a>
        </div>
        <div class="card-body">
            
            <div id="alertBox" class="alert d-none small p-2 mb-3"></div>

            <div class="row mb-4">
                <div class="col-12">
                    <form id="searchForm">
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control bg-light" placeholder="Ketik judul acara...">
                            <button type="submit" class="btn btn-primary px-4 fw-bold" id="btnSearch"><i class="fas fa-search me-1"></i> Cari</button>
                            <button type="button" class="btn btn-danger px-4 d-none" id="btnReset" onclick="resetSearch()"><i class="fas fa-times me-1"></i> Reset</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="25%">Judul Acara</th>
                            <th width="15%">Tanggal Pelaksanaan</th>
                            <th width="10%">Kapasitas</th>
                            <th width="15%">Status Proposal</th>
                            <th width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="eventTableBody">
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-spinner fa-spin me-2"></i> Memuat data acara...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</div>

<script>
    const token = localStorage.getItem('auth_token');
    const user = JSON.parse(localStorage.getItem('user_data'));

    if (!token || !user) {
        window.location.href = '/login';
    }

    const tbody = document.getElementById('eventTableBody');
    const searchInput = document.getElementById('searchInput');
    const btnReset = document.getElementById('btnReset');

    document.addEventListener('DOMContentLoaded', () => {
        fetchEvents();
    });

    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        fetchEvents(searchInput.value);
    });

    window.resetSearch = function() {
        searchInput.value = '';
        fetchEvents();
    }

    async function fetchEvents(keyword = '') {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i> Memuat data...</td></tr>';
        
        let url = `/api/events?admin_id=${user.id}`;
        if (keyword) {
            url += `&search=${encodeURIComponent(keyword)}`;
            btnReset.classList.remove('d-none');
        } else {
            btnReset.classList.add('d-none');
        }

        try {
            let response = await fetch(url, {
                method: 'GET',
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });

            let result = await response.json();

            if (response.ok) {
                renderTable(result.data);
            } else {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Gagal memuat data.</td></tr>`;
            }
        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4"><i class="fas fa-wifi me-2"></i>Koneksi ke API terputus.</td></tr>`;
        }
    }

    function renderTable(events) {
        if (events.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada acara yang diajukan.</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        events.forEach((event, index) => {
            const dateObj = new Date(event.event_date);
            const dateStr = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            const timeStr = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';

            const locationIcon = event.is_online ? 'fa-video' : 'fa-map-marker-alt';
            const locationText = event.is_online ? 'Online / Virtual' : 'Offline / Titik Maps';

            let statusBadge = '';
            let actionBtns = '';

            if (event.status === 'approved') {
                statusBadge = '<span class="badge bg-success"><i class="fas fa-check-circle"></i> Approved (Kampus)</span>';
                actionBtns = `
                    <a href="/events/${event.id}/edit" class="btn btn-sm btn-outline-info" title="Lihat/Edit"><i class="fas fa-edit"></i></a>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteEvent(${event.id})" title="Hapus"><i class="fas fa-trash"></i></button>`;
            
            } else if (event.status === 'pending_admin') { // STATUS DARI ANGGOTA
                statusBadge = '<span class="badge bg-warning text-dark"><i class="fas fa-user-shield"></i> Review Internal (HIMA)</span>';
                actionBtns = `
                    <button class="btn btn-sm btn-success fw-bold mb-1" onclick="approveInternal(${event.id}, 'pending_superadmin')" title="Teruskan ke Kampus"><i class="fas fa-check"></i> ACC</button>
                    <button class="btn btn-sm btn-danger fw-bold mb-1" onclick="approveInternal(${event.id}, 'rejected')" title="Kembalikan ke Anggota"><i class="fas fa-times"></i> Tolak</button>`;
            
            } else if (event.status === 'pending' || event.status === 'pending_superadmin') {
                statusBadge = '<span class="badge bg-primary"><i class="fas fa-clock"></i> Review Kampus</span>';
                actionBtns = `
                    <a href="/events/${event.id}/edit" class="btn btn-sm btn-outline-info" title="Lihat/Edit"><i class="fas fa-edit"></i></a>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteEvent(${event.id})" title="Hapus"><i class="fas fa-trash"></i></button>`;
            
            } else {
                statusBadge = '<span class="badge bg-danger"><i class="fas fa-times-circle"></i> Rejected</span>';
                actionBtns = `
                    <a href="/events/${event.id}/edit" class="btn btn-sm btn-outline-info" title="Revisi"><i class="fas fa-edit"></i></a>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteEvent(${event.id})" title="Hapus"><i class="fas fa-trash"></i></button>`;
            }

            tbody.innerHTML += `
                <tr>
                    <td>${index + 1}</td>
                    <td>
                        <span class="fw-bold">${event.title}</span><br>
                        <small class="text-muted"><i class="fas ${locationIcon}"></i> ${locationText}</small>
                    </td>
                    <td>${dateStr}<br><small class="text-muted">${timeStr}</small></td>
                    <td><span class="badge bg-info text-dark">${event.registrations_count || 0} / ${event.capacity} Peserta</span></td>
                    <td>${statusBadge}</td>
                    <td>${actionBtns}</td>
                </tr>
            `;
        });
    }

    window.approveInternal = async function(id, action) {
        let reason = '';
        if (action === 'rejected') {
            reason = prompt('Masukkan alasan penolakan/revisi ke anggota agar mereka bisa memperbaiki proposalnya:');
            if (reason === null) return; 
        } else {
            if (!confirm('Setujui dan teruskan proposal ini ke Kampus (Superadmin)?')) return;
        }

        try {
            let response = await fetch(`/api/events/${id}/internal-status`, {
                method: 'POST',
                headers: { 
                    'Authorization': `Bearer ${token}`, 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json' 
                },
                body: JSON.stringify({ status: action, reject_reason: reason })
            });

            let result = await response.json();

            if (response.ok) {
                let alertBox = document.getElementById('alertBox');
                alertBox.className = 'alert alert-success d-block small p-2';
                alertBox.innerHTML = '<i class="fas fa-check-circle me-1"></i> ' + result.message;
                fetchEvents(searchInput.value); 
                setTimeout(() => alertBox.classList.add('d-none'), 3000);
            } else {
                alert(result.message || 'Gagal memproses proposal');
            }
        } catch (error) {
            alert('Kesalahan jaringan saat memproses proposal.');
        }
    }

    window.deleteEvent = async function(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus acara ini?')) return;
        try {
            let response = await fetch(`/api/events/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (response.ok) fetchEvents(searchInput.value); 
        } catch (error) {
            alert('Kesalahan jaringan.');
        }
    }
</script>
@endsection
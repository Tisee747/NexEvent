@extends('layouts.app')

@section('title', 'Manajemen Peserta')

@section('content')
<div class="container-fluid p-0">
    <div id="alertBox" class="alert d-none small p-2 mb-3"></div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="mb-4">
                <h5 class="fw-bold mb-1">Daftar Pendaftar</h5>
                <p class="text-muted small mb-0">Pantau kuota dan sistem antrean otomatis (Waitlist)</p>
            </div>

            <form id="filterForm">
                <div class="row gy-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold text-primary small"><i class="fas fa-filter me-1"></i> Pilih Acara yang Dikelola</label>
                        <select id="eventSelect" class="form-select bg-light" onchange="fetchParticipants()">
                            <option value="">Memuat acara...</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-primary small"><i class="fas fa-search me-1"></i> Cari Peserta di Acara Ini</label>
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control bg-light" placeholder="Ketik nama atau email peserta...">
                            <button type="button" class="btn btn-primary px-4 fw-bold" onclick="fetchParticipants()"><i class="fas fa-search me-1"></i> Cari</button>
                            <button type="button" class="btn btn-danger px-3 d-none" id="btnReset" onclick="resetSearch()"><i class="fas fa-times"></i> Reset</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 mb-2 mb-md-0">
            <div class="alert alert-info border-0 shadow-sm mb-0 d-flex align-items-center">
                <i class="fas fa-info-circle fa-2x me-3 opacity-50"></i>
                <div>
                    <span class="d-block small">Kapasitas Acara:</span>
                    <strong id="statCapacity">0 Peserta</strong>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-warning border-0 shadow-sm mb-0 d-flex align-items-center">
                <i class="fas fa-users fa-2x me-3 opacity-50"></i>
                <div>
                    <span class="d-block small">Total Pendaftar:</span>
                    <strong id="statTotal">0 Orang</strong> <small id="statWaitlist">(0 Masuk Waitlist)</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3">No</th>
                            <th class="py-3">Info Mahasiswa</th>
                            <th class="py-3">Waktu Mendaftar</th>
                            <th class="py-3">Status Antrean</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="participantTableBody">
                        <tr><td colspan="5" class="text-center py-4 text-muted">Pilih acara terlebih dahulu.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const token = localStorage.getItem('auth_token');
    
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            let response = await fetch('/api/participants', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            let result = await response.json();
            
            let select = document.getElementById('eventSelect');
            select.innerHTML = '<option value="">-- Pilih Acara --</option>';
            
            if(result.data.events.length > 0) {
                result.data.events.forEach(ev => {
                    select.innerHTML += `<option value="${ev.id}">${ev.title}</option>`;
                });
            } else {
                select.innerHTML = '<option value="">Belum ada acara yang dibuat</option>';
            }
        } catch (error) {
            console.error("Gagal memuat acara", error);
        }
    });

    async function fetchParticipants() {
        let eventId = document.getElementById('eventSelect').value;
        let search = document.getElementById('searchInput').value;
        let tbody = document.getElementById('participantTableBody');
        let btnReset = document.getElementById('btnReset');

        if (!eventId) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Pilih acara terlebih dahulu.</td></tr>';
            return;
        }

        if(search) btnReset.classList.remove('d-none');
        else btnReset.classList.add('d-none');

        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i> Memuat peserta...</td></tr>';

        try {
            let response = await fetch(`/api/participants?event_id=${eventId}&search=${search}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            let result = await response.json();

            document.getElementById('statCapacity').textContent = (result.data.selectedEvent?.capacity || 0) + ' Peserta';
            document.getElementById('statTotal').textContent = result.data.registrations.length + ' Orang';
            document.getElementById('statWaitlist').textContent = `(${result.data.totalWaitlist} Masuk Waitlist)`;

            if (result.data.registrations.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada pendaftar yang ditemukan.</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            result.data.registrations.forEach((reg, index) => {
                let dateStr = new Date(reg.created_at).toLocaleString('id-ID', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'});
                
                let statusBadge = reg.status === 'utama' 
                    ? '<span class="badge bg-success rounded-pill px-3 py-2"><i class="fas fa-check-circle me-1"></i> Peserta Utama</span>'
                    : '<span class="badge bg-warning text-dark rounded-pill px-3 py-2"><i class="fas fa-hourglass-half me-1"></i> Waitlist</span>';

                tbody.innerHTML += `
                    <tr>
                        <td class="px-4">${index + 1}</td>
                        <td>
                            <span class="badge bg-secondary mb-1">${reg.reg_code || 'REG'}</span><br>
                            <div class="fw-bold text-dark">${reg.user.name}</div>
                            <small class="text-muted">${reg.user.email}</small>
                        </td>
                        <td>${dateStr} WIB</td>
                        <td>${statusBadge}</td>
                        <td class="px-4 text-center">
                            <button onclick="removeParticipant(${reg.id})" class="btn btn-sm btn-outline-danger" title="Diskualifikasi / Hapus">
                                <i class="fas fa-user-times"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        } catch (error) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Gagal menarik data dari server.</td></tr>';
        }
    }

    function resetSearch() {
        document.getElementById('searchInput').value = '';
        fetchParticipants();
    }

    async function removeParticipant(regId) {
        if(!confirm('Yakin ingin mendiskualifikasi peserta ini?')) return;
        
        try {
            let response = await fetch(`/api/events/${regId}/cancel`, {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}` }
            });
            let result = await response.json();
            
            if(response.ok) {
                fetchParticipants(); // Refresh tabel
            } else {
                alert(result.message || 'Gagal menghapus peserta');
            }
        } catch (error) {
            alert('Terjadi kesalahan jaringan.');
        }
    }
</script>
@endsection
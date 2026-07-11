@extends('layouts.app')

@section('title', 'Pusat Validasi Kehadiran')

@section('content')
<div class="container-fluid p-0">
    <div id="alertBox" class="alert d-none alert-dismissible fade show small p-3 mb-4 rounded-3 fw-bold"></div>

    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body p-4 bg-white rounded-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="mb-3 mb-md-0">
                <h5 class="fw-bold text-dark mb-1"><i class="fas fa-qrcode text-primary me-2"></i>Modul Manajemen Kehadiran</h5>
                <small class="text-muted">Sistem otomatis menyaring dan memunculkan acara dengan status disetujui saja.</small>
            </div>
            <div class="d-flex w-100 w-md-50 ms-md-auto">
                <select id="eventSelect" class="form-select bg-light py-2 shadow-sm border-light" onchange="fetchAttendance()">
                    <option value="">Sistem sedang menarik data acara aktif...</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle border">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th>Identitas Peserta</th>
                            <th>Alamat Email</th>
                            <th>Tanda Waktu Registrasi</th>
                            <th class="text-center">Indikator Kehadiran</th>
                            <th class="text-center">Aksi Petugas</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTableBody">
                        <tr><td colspan="6" class="text-center py-5 text-muted fw-semibold">Silakan pilih acara target melalui menu dropdown di atas.</td></tr>
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

            if (response.status === 401) {
                localStorage.clear();
                window.location.href = '/login';
                return;
            }

            let result = await response.json();
            
            let select = document.getElementById('eventSelect');
            select.innerHTML = '<option value="">-- Pilih target acara spesifik --</option>';
            
            if(result.data.events.length > 0) {
                result.data.events.forEach(ev => {
                    select.innerHTML += `<option value="${ev.id}">${ev.title}</option>`;
                });
            }
        } catch (error) {
            console.error("Sistem gagal menarik data acara dari server utama.");
        }
    });

    async function fetchAttendance() {
        let eventId = document.getElementById('eventSelect').value;
        let tbody = document.getElementById('attendanceTableBody');

        if (!eventId) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted fw-semibold">Silakan pilih acara target melalui menu dropdown di atas.</td></tr>';
            return;
        }

        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x mb-2 text-primary"></i><br>Sistem sedang merekapitulasi presensi kehadiran...</td></tr>';

        try {
            let response = await fetch(`/api/participants?event_id=${eventId}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });

            if (response.status === 401) {
                localStorage.clear();
                window.location.href = '/login';
                return;
            }

            let result = await response.json();
            let pesertaUtama = result.data.registrations.filter(r => r.status === 'utama');

            if (pesertaUtama.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted fw-semibold">Sistem tidak menemukan pendaftar utama untuk acara target ini.</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            pesertaUtama.forEach((reg, index) => {
                let dateStr = new Date(reg.created_at).toLocaleString('id-ID', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'});
                
                let statusBadge = reg.attendance_status === 'hadir'
                    ? '<span class="badge bg-success py-2 px-3"><i class="fas fa-check-double me-1"></i> Peserta Hadir</span>'
                    : '<span class="badge bg-secondary py-2 px-3"><i class="fas fa-minus me-1"></i> Absen Kosong</span>';

                let actionBtn = reg.attendance_status === 'hadir'
                    ? `<button onclick="updateAttendance(${reg.id}, 'belum_hadir')" class="btn btn-sm btn-outline-danger px-3" title="Anulir Kehadiran Peserta"><i class="fas fa-undo me-1"></i> Tarik Validasi</button>`
                    : `<button onclick="updateAttendance(${reg.id}, 'hadir')" class="btn btn-sm btn-primary fw-bold px-3"><i class="fas fa-user-check me-1"></i> Validasi Kedatangan</button>`;

                tbody.innerHTML += `
                    <tr>
                        <td class="text-center fw-bold">${index + 1}</td>
                        <td class="fw-bold text-dark fs-6">${reg.user.name}</td>
                        <td class="text-muted">${reg.user.email}</td>
                        <td class="text-muted">${dateStr} WIB</td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-center">${actionBtn}</td>
                    </tr>
                `;
            });
        } catch (error) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-danger fw-bold">Sistem gagal menarik data presensi dari server.</td></tr>';
        }
    }

    async function updateAttendance(regId, newStatus) {
        try {
            let response = await fetch(`/api/attendance/${regId}`, {
                method: 'POST',
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ attendance_status: newStatus })
            });

            if (response.status === 401) {
                localStorage.clear();
                window.location.href = '/login';
                return;
            }

            if(response.ok) {
                fetchAttendance(); 
            } else {
                alert('Sistem server gagal merekam validasi status presensi terbaru.');
            }
        } catch (error) {
            alert('Kesalahan jaringan. Mohon periksa koneksi internet Anda.');
        }
    }
</script>
@endsection
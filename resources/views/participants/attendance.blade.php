@extends('layouts.app')

@section('title', 'Verifikasi Kehadiran')

@section('content')
<div class="container-fluid p-0">
    <div id="alertBox" class="alert d-none alert-dismissible fade show small p-2 mb-3"></div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4 bg-light rounded d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-1"><i class="fas fa-qrcode text-primary me-2"></i>Pilih Acara untuk Absensi</h6>
                <small class="text-muted">Hanya menampilkan acara yang sudah disetujui kampus.</small>
            </div>
            <div class="d-flex gap-2 w-50">
                <select id="eventSelect" class="form-select" onchange="fetchAttendance()">
                    <option value="">Memuat acara...</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Peserta</th>
                            <th>Email</th>
                            <th>Waktu Mendaftar</th>
                            <th>Status Kehadiran</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTableBody">
                        <tr><td colspan="6" class="text-center py-4 text-muted">Pilih acara terlebih dahulu.</td></tr>
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
            }
        } catch (error) {
            console.error("Gagal memuat acara");
        }
    });

    async function fetchAttendance() {
        let eventId = document.getElementById('eventSelect').value;
        let tbody = document.getElementById('attendanceTableBody');

        if (!eventId) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Pilih acara terlebih dahulu.</td></tr>';
            return;
        }

        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i> Memuat data kehadiran...</td></tr>';

        try {
            let response = await fetch(`/api/participants?event_id=${eventId}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            let result = await response.json();

            // Filter hanya peserta utama (Bukan Waitlist)
            let pesertaUtama = result.data.registrations.filter(r => r.status === 'utama');

            if (pesertaUtama.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada peserta berstatus UTAMA di acara ini.</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            pesertaUtama.forEach((reg, index) => {
                let dateStr = new Date(reg.created_at).toLocaleString('id-ID', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'});
                
                let statusBadge = reg.attendance_status === 'hadir'
                    ? '<span class="badge bg-success"><i class="fas fa-check-double"></i> Hadir</span>'
                    : '<span class="badge bg-secondary"><i class="fas fa-minus"></i> Belum Hadir</span>';

                let actionBtn = reg.attendance_status === 'hadir'
                    ? `<button onclick="updateAttendance(${reg.id}, 'belum_hadir')" class="btn btn-sm btn-outline-danger" title="Batalkan Kehadiran"><i class="fas fa-undo me-1"></i> Batal</button>`
                    : `<button onclick="updateAttendance(${reg.id}, 'hadir')" class="btn btn-sm btn-success fw-bold"><i class="fas fa-check me-1"></i> Hadir</button>`;

                tbody.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td class="fw-bold">${reg.user.name}</td>
                        <td>${reg.user.email}</td>
                        <td>${dateStr} WIB</td>
                        <td>${statusBadge}</td>
                        <td class="text-center">${actionBtn}</td>
                    </tr>
                `;
            });
        } catch (error) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Gagal menarik data server.</td></tr>';
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

            if(response.ok) {
                fetchAttendance(); 
            } else {
                alert('Gagal memperbarui status absensi');
            }
        } catch (error) {
            alert('Kesalahan jaringan');
        }
    }
</script>
@endsection
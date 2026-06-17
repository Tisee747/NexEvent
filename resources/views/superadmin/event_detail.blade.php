@extends('layouts.app')

@section('title', 'Review Detail Acara')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 fw-bold text-gray-800">Detail Pengajuan Acara</h5>
        <a href="/superadmin" class="btn btn-light border shadow-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    </div>
    
    <div id="alertBox" class="alert d-none small p-2 mb-3"></div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4" id="eventDetailBox">
                    <div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-3"></i><br>Memuat data proposal...</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4" id="documentBox">
                    <h6 class="fw-bold mb-3"><i class="fas fa-file-alt text-primary me-2"></i>Berkas Pendukung</h6>
                    <div class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin"></i></div>
                </div>
            </div>

            <div id="actionBox">
                </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Catatan Penolakan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Masukan alasan penolakan/catatan revisi: <span class="text-danger">*</span></label>
                    <textarea id="reject_reason" class="form-control bg-light" rows="4" placeholder="Contoh: Proposal kurang rincian anggaran..." required></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" onclick="submitReview('rejected')" class="btn btn-danger fw-bold"><i class="fas fa-paper-plane me-1"></i> Submit Penolakan</button>
            </div>
        </div>
    </div>
</div>

<script>
    const token = localStorage.getItem('auth_token');
    const eventId = window.location.pathname.split('/').pop();

    document.addEventListener('DOMContentLoaded', () => {
        loadEventDetail();
    });

    async function loadEventDetail() {
        try {
            let res = await fetch(`/api/events/${eventId}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            let result = await res.json();

            if (res.ok) {
                renderDetail(result.data);
            } else {
                document.getElementById('eventDetailBox').innerHTML = '<div class="text-center py-5 text-danger">Gagal memuat data.</div>';
            }
        } catch (error) {
            document.getElementById('eventDetailBox').innerHTML = '<div class="text-center py-5 text-danger">Kesalahan jaringan.</div>';
        }
    }

    function renderDetail(event) {
        let dateStr = new Date(event.event_date).toLocaleString('id-ID', {day:'2-digit', month:'long', year:'numeric', hour:'2-digit', minute:'2-digit'});
        let orgName = event.panitia ? event.panitia.organization : '-';
        let repName = event.panitia ? event.panitia.name : '-';
        
        let locationHtml = event.is_online 
            ? `<span class="badge bg-info text-dark px-3 py-2 rounded-pill"><i class="fas fa-video me-1"></i> Online / Virtual</span>`
            : `<span class="badge bg-secondary px-3 py-2 rounded-pill"><i class="fas fa-map-marker-alt me-1"></i> Offline / Di Tempat</span><br><small class="text-muted d-block mt-2"><i class="fas fa-location-arrow me-1"></i> Koordinat Maps: ${event.latitude || '-'}, ${event.longitude || '-'}</small>`;

        document.getElementById('eventDetailBox').innerHTML = `
            <h4 class="fw-bold text-primary mb-4">${event.title}</h4>
            <table class="table table-borderless mb-0">
                <tr><th width="30%" class="text-muted pb-3">Penyelenggara</th><td class="fw-bold pb-3">${orgName} <br><small class="text-muted fw-normal">Perwakilan: ${repName}</small></td></tr>
                <tr><th class="text-muted pb-3">Tanggal & Waktu</th><td class="pb-3">${dateStr} WIB</td></tr>
                <tr><th class="text-muted pb-3">Lokasi / Media</th><td class="pb-3">${locationHtml}</td></tr>
                <tr><th class="text-muted pb-3">Kapasitas</th><td class="pb-3">${event.capacity} Peserta</td></tr>
                <tr><th class="text-muted pb-3">Deskripsi Acara</th><td class="pb-3">${event.description}</td></tr>
            </table>
        `;

        let docHtml = '<h6 class="fw-bold mb-3"><i class="fas fa-file-alt text-primary me-2"></i>Berkas Pendukung</h6>';
        
        if (event.proposal_path) {
            docHtml += `<a href="/storage/${event.proposal_path}" target="_blank" class="btn btn-outline-danger w-100 mb-2 text-start"><i class="fas fa-file-pdf me-2"></i> Lihat Proposal PDF</a>`;
        } else {
            docHtml += `<button class="btn btn-light w-100 mb-2 text-start disabled"><i class="fas fa-times me-2"></i> Proposal Belum Tersedia</button>`;
        }

        if (event.poster_path) {
            docHtml += `<a href="/storage/${event.poster_path}" target="_blank" class="btn btn-outline-primary w-100 text-start"><i class="fas fa-image me-2"></i> Lihat Poster Acara</a>`;
        } else {
            docHtml += `<button class="btn btn-light w-100 text-start disabled"><i class="fas fa-times me-2"></i> Poster Belum Tersedia</button>`;
        }
        document.getElementById('documentBox').innerHTML = docHtml;

        let actionHtml = '';
        if (event.status === 'pending' || event.status === 'pending_superadmin') {
            actionHtml = `
                <div class="card shadow-sm border-0 bg-light border-start border-4 border-warning">
                    <div class="card-body p-4 text-center">
                        <h6 class="fw-bold mb-3 text-dark">Keputusan Approval</h6>
                        <button onclick="submitReview('approved')" class="btn btn-success w-100 fw-bold py-2 mb-2"><i class="fas fa-check-circle me-1"></i> Setujui Acara</button>
                        <button type="button" class="btn btn-danger w-100 fw-bold py-2" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="fas fa-times-circle me-1"></i> Tolak / Revisi</button>
                    </div>
                </div>
            `;
        } else {
            let isApproved = event.status === 'approved';
            let badgeStr = isApproved 
                ? '<span class="badge bg-success fs-6 px-4 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i> Approved</span><small class="d-block mt-3 text-muted">Acara ini sudah disetujui.</small>' 
                : `<span class="badge bg-danger fs-6 px-4 py-2 rounded-pill"><i class="fas fa-times-circle me-1"></i> Rejected</span><div class="alert alert-danger mt-3 mb-0 small text-start"><strong>Catatan Penolakan:</strong><br>${event.reject_reason || '-'}</div>`;
            
            actionHtml = `
                <div class="card shadow-sm border-0 bg-light border-start border-4 ${isApproved ? 'border-success' : 'border-danger'}">
                    <div class="card-body p-4 text-center">
                        <h6 class="fw-bold mb-3 text-dark">Status Saat Ini</h6>
                        ${badgeStr}
                    </div>
                </div>
            `;
        }
        document.getElementById('actionBox').innerHTML = actionHtml;
    }

    async function submitReview(status) {
        let reason = document.getElementById('reject_reason').value;
        if (status === 'rejected' && !reason) {
            alert('Alasan penolakan wajib diisi!');
            return;
        }

        try {
            let res = await fetch(`/api/superadmin/events/${eventId}/status`, {
                method: 'POST',
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: status, reject_reason: reason })
            });

            if (res.ok) {
                let modalEl = document.getElementById('rejectModal');
                let modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                
                showAlert('Status acara berhasil diperbarui.', 'success');
                loadEventDetail();
            } else {
                showAlert('Gagal memperbarui status.', 'danger');
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
        window.scrollTo(0,0);
    }
</script>
@endsection
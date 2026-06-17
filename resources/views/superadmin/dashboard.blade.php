@extends('layouts.app')

@section('title', 'Dashboard Superadmin')

@section('content')
<div class="container-fluid p-0">
    
    <div class="card shadow-sm border-0 mb-4 rounded-3" style="background-color: #3b82f6;">
        <div class="card-body p-4 d-flex align-items-center text-white">
            <i class="fas fa-university fa-3x me-4 opacity-75"></i>
            <div>
                <h4 class="fw-bold mb-1">Selamat Datang, <span id="dashUserName">Memuat...</span>!</h4>
                <p class="mb-0 opacity-75">Pantau seluruh aktivitas HIMA/UKM, perizinan acara, dan pendaftar di platform NexEvent.</p>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 border-start border-4 border-warning h-100 rounded-3">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <p class="text-xs fw-bold text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Antrean Review</p>
                        <h3 class="fw-bold text-dark mb-0"><span id="statPending"><i class="fas fa-spinner fa-spin"></i></span> <span class="fs-6 fw-normal text-muted">Proposal</span></h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                        <i class="fas fa-file-signature text-warning fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 border-start border-4 border-primary h-100 rounded-3">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <p class="text-xs fw-bold text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Organisasi Terdaftar</p>
                        <h3 class="fw-bold text-dark mb-0"><span id="statOrg"><i class="fas fa-spinner fa-spin"></i></span> <span class="fs-6 fw-normal text-muted">HIMA/UKM</span></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                        <i class="fas fa-building text-primary fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 border-start border-4 border-success h-100 rounded-3">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <p class="text-xs fw-bold text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Total Acara Kampus</p>
                        <h3 class="fw-bold text-dark mb-0"><span id="statAcara"><i class="fas fa-spinner fa-spin"></i></span> <span class="fs-6 fw-normal text-muted">Acara</span></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                        <i class="fas fa-calendar-check text-success fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0">
                <i class="fas fa-bell text-warning me-2"></i> Butuh Tindakan Cepat (Pending)
            </h6>
            <a href="/superadmin" class="btn btn-outline-primary btn-sm px-3">Lihat Semua Antrean</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0">
                    <thead class="table-light text-dark small" style="border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9;">
                        <tr>
                            <th class="ps-4 py-3 fw-bold">Penyelenggara</th>
                            <th class="py-3 fw-bold">Judul Acara</th>
                            <th class="py-3 fw-bold">Tanggal Diajukan</th>
                            <th class="pe-4 py-3 text-end fw-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="quickPendingTable">
                        <tr><td colspan="4" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i> Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', async function() {
        const token = localStorage.getItem('auth_token');
        const user = JSON.parse(localStorage.getItem('user_data'));

        document.getElementById('dashUserName').textContent = user.name;

        try {
            let response = await fetch('/api/dashboard/superadmin', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            let result = await response.json();

            if (response.ok) {
                document.getElementById('statPending').textContent = result.pendingReviews;
                document.getElementById('statOrg').textContent = result.totalOrganizations;
                document.getElementById('statAcara').textContent = result.totalAcaraKampus;

                let tbody = document.getElementById('quickPendingTable');
                if (result.pendingEvents.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex justify-content-center align-items-center mb-3" style="width: 50px; height: 50px;">
                                    <i class="fas fa-check text-success fa-lg"></i>
                                </div>
                                <p class="text-muted small mb-0">Tidak ada request approval.</p>
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbody.innerHTML = '';
                result.pendingEvents.forEach(event => {
                    let dateStr = new Date(event.created_at).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});
                    let orgName = event.panitia ? event.panitia.organization : 'Organisasi';
                    
                    tbody.innerHTML += `
                        <tr style="border-bottom: 1px solid #f8f9fa;">
                            <td class="ps-4 fw-medium text-dark">${orgName}</td>
                            <td class="fw-bold text-dark">${event.title}</td>
                            <td class="text-muted">${dateStr}</td>
                            <td class="pe-4 text-end">
                                <a href="/superadmin/event/${event.id}" class="btn btn-sm btn-primary px-3">Review</a>
                            </td>
                        </tr>
                    `;
                });
            }
        } catch (error) {
            console.error("Gagal memuat data dasbor", error);
        }
    });
</script>
@endsection
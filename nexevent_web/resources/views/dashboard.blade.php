@extends('layouts.app')

@section('title', 'Dashboard Organization')

@section('content')
<div class="container-fluid p-0">
    
    <div class="mb-4">
        <h4 class="fw-bold text-dark mb-1">Selamat Datang, <span id="userName">Panitia</span>!</h4>
        <p class="text-muted small">Kelola dan pantau semua acara <span id="userOrg">Organisasi</span> di sini.</p>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 border-start border-4 border-primary h-100 rounded-3">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <p class="text-xs fw-bold text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Total Acara Dibuat</p>
                        <h3 class="fw-bold text-dark mb-0"><span id="statTotalAcara"><i class="fas fa-spinner fa-spin"></i></span> <span class="fs-6 fw-normal text-muted">Acara</span></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                        <i class="fas fa-calendar-alt text-primary fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 border-start border-4 border-success h-100 rounded-3">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <p class="text-xs fw-bold text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Total Pendaftar</p>
                        <h3 class="fw-bold text-dark mb-0"><span id="statTotalPendaftar"><i class="fas fa-spinner fa-spin"></i></span> <span class="fs-6 fw-normal text-muted">Mahasiswa</span></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                        <i class="fas fa-user-friends text-success fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 border-start border-4 border-warning h-100 rounded-3">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <p class="text-xs fw-bold text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Aktivitas Terakhir</p>
                        <div id="statAktivitas">
                            <h5 class="fw-bold text-secondary mb-0 mt-2"><i class="fas fa-spinner fa-spin"></i></h5>
                        </div>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                        <i class="fas fa-exclamation text-warning fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mb-4">
            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-rocket text-primary me-2"></i> Akses Cepat Panitia</h6>
            <div class="row">
                <div class="col-sm-6 mb-3">
                    <a href="/events/create" class="text-decoration-none">
                        <div class="card shadow-sm border text-start p-3 h-100 rounded-3 btn-outline-light transition-all">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 45px; height: 45px;">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Ajukan Acara</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.8rem;">Ajukan acara baru yang ingin diadakan</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                
                <div class="col-sm-6 mb-3">
                    <a href="/participants" class="text-decoration-none">
                        <div class="card shadow-sm border text-start p-3 h-100 rounded-3 btn-outline-light transition-all">
                            <div class="d-flex align-items-center">
                                <div class="text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 45px; height: 45px; background-color: #5b9bd5;">
                                    <i class="fas fa-users-cog"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Kelola Peserta</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.8rem;">Cek antrean & kuota</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 p-4 text-center d-flex flex-column justify-content-center rounded-3" style="background-color: #468bcb;">
                <div>
                    <i class="fas fa-lightbulb fa-3x mb-3 text-white opacity-75"></i>
                    <h5 class="fw-bold text-white mb-2">Tips NexEvent</h5>
                    <p class="small mb-0 text-white opacity-75" style="line-height: 1.5;">Pastikan proposal PDF dan poster yang diunggah beresolusi tinggi agar cepat di ACC oleh pihak Kampus.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async function() {
        const token = localStorage.getItem('auth_token');
        const userData = localStorage.getItem('user_data');

        if (!token || !userData) {
            window.location.href = '/login';
            return;
        }

        const user = JSON.parse(userData);
        
        document.getElementById('userName').textContent = user.name;
        document.getElementById('userOrg').textContent = user.organization || 'Organisasi';

        try {
            let response = await fetch('/api/dashboard/organization', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                let result = await response.json();
                
                document.getElementById('statTotalAcara').textContent = result.totalAcara;
                document.getElementById('statTotalPendaftar').textContent = result.totalPendaftarBulanIni;
                
                let aktivitasBox = document.getElementById('statAktivitas');
                if (result.rejectedEvents && result.rejectedEvents.length > 0) {
                    aktivitasBox.innerHTML = '<h5 class="fw-bold text-danger mb-0 mt-2">Ada Acara Ditolak</h5>';
                } else {
                    aktivitasBox.innerHTML = '<h5 class="fw-bold text-success mb-0 mt-2">Semua Aman</h5>';
                }
            }
        } catch (error) {
            console.error("Gagal mengambil data statistik", error);
        }
    });
</script>
@endsection
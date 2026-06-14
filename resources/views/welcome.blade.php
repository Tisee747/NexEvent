@extends('layouts.app')

@section('title', 'Dashboard Utama')

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-primary text-white mb-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Total Event Aktif</h6>
                        <h2 class="mb-0 fw-bold">5</h2>
                    </div>
                    <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-success text-white mb-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Total Pendaftar</h6>
                        <h2 class="mb-0 fw-bold">128</h2>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-warning text-dark mb-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Menunggu Approval</h6>
                        <h2 class="mb-0 fw-bold">2</h2>
                    </div>
                    <i class="fas fa-clock fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-5 text-center">
            <img src="https://illustrations.popsy.co/amber/student-going-to-school.svg" alt="Welcome" width="200" class="mb-3">
            <h3>Selamat Datang di NexEvent Dashboard</h3>
            <p class="text-muted">Pilih menu di sebelah kiri untuk mulai mengelola acara dan peserta.</p>
        </div>
    </div>
</div>
@endsection
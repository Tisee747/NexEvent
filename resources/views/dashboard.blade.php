@extends('layouts.app')

@section('title', 'Dashboard Organisasi')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-gray-800 mb-0">Selamat Datang, {{ Auth::user()->name }}! 👋</h4>
            <p class="text-muted small">Kelola dan pantau semua acara {{ Auth::user()->organization }} di sini.</p>
        </div>
    </div>

    @if($rejectedEvents->count() > 0)
        @foreach($rejectedEvents as $reject)
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-start mb-4" role="alert">
            <i class="fas fa-exclamation-circle fa-2x me-3 mt-1"></i>
            <div>
                <h6 class="fw-bold mb-1">Acara "{{ $reject->title }}" Ditolak Kampus!</h6>
                <p class="mb-0 small"><strong>Catatan Superadmin:</strong> {{ $reject->reject_reason }}</p>
                <a href="{{ route('events.index') }}" class="btn btn-sm btn-outline-danger mt-2 fw-bold">Revisi Sekarang</a>
            </div>
        </div>
        @endforeach
    @endif

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 border-start border-4 border-primary h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Acara Dibuat</div>
                            <div class="h3 mb-0 fw-bold text-gray-800">{{ $totalAcara }} <small class="fs-6 fw-normal text-muted">Acara</small></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 border-start border-4 border-success h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Pendaftar Bulan Ini</div>
                            <div class="h3 mb-0 fw-bold text-gray-800">{{ $totalPendaftarBulanIni }} <small class="fs-6 fw-normal text-muted">Mahasiswa</small></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
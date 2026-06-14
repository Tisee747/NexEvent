@extends('layouts.app')

@section('title', 'Dashboard Superadmin')

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 bg-primary text-white h-100">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <h6 class="text-uppercase fw-bold mb-2">Total Event Aktif</h6>
                        <h2 class="mb-0 fw-bolder">{{ $totalEvent }}</h2>
                    </div>
                    <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 bg-success text-white h-100">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <h6 class="text-uppercase fw-bold mb-2">Total Mhs Mendaftar</h6>
                        <h2 class="mb-0 fw-bolder">{{ $totalMhs }}</h2>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 bg-warning text-dark h-100">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <h6 class="text-uppercase fw-bold mb-2">Total Organisasi</h6>
                        <h2 class="mb-0 fw-bolder">{{ $totalOrg }}</h2>
                    </div>
                    <i class="fas fa-sitemap fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Dashboard Superadmin')

@section('content')
<div class="container-fluid p-0">
    
    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle me-1"></i> {{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-user-clock me-2"></i>Menunggu Persetujuan Akun Panitia</h6>
                </div>
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Nama Perwakilan</th>
                                <th>Organisasi</th>
                                <th>Email</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingUsers as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td><span class="badge bg-secondary">{{ $user->organization }}</span></td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <form action="{{ route('superadmin.approveUser', $user->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i> Aktifkan Akun</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted">Tidak ada akun yang menunggu persetujuan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-check me-2"></i>Menunggu Persetujuan Acara</h6>
                </div>
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Judul Acara</th>
                                <th>Penyelenggara</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingEvents as $event)
                            <tr>
                                <td class="fw-bold">{{ $event->title }}</td>
                                <td>{{ $event->panitia->organization }}</td>
                                <td>{{ \Carbon\Carbon::parse($event->event_date)->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('superadmin.showEvent', $event->id) }}" class="btn btn-sm btn-info text-white fw-bold">
                                        <i class="fas fa-search me-1"></i> Review Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted">Tidak ada acara yang menunggu persetujuan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
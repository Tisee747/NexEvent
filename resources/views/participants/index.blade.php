@extends('layouts.app')

@section('title', 'Manajemen Peserta')

@section('content')
<div class="container-fluid p-0">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="mb-4">
                <h5 class="fw-bold mb-1">Daftar Pendaftar</h5>
                <p class="text-muted small mb-0">Pantau kuota dan sistem antrean otomatis (Waitlist)</p>
            </div>

            <form action="{{ route('participants.index') }}" method="GET">
                <div class="row gy-3">
                    
                    <div class="col-12">
                        <label class="form-label fw-semibold text-primary small"><i class="fas fa-filter me-1"></i> Pilih Acara yang Dikelola</label>
                        <select name="event_id" class="form-select bg-light" onchange="this.form.submit()">
                            @forelse($events as $event)
                                <option value="{{ $event->id }}" {{ $selectedEventId == $event->id ? 'selected' : '' }}>
                                    {{ $event->title }}
                                </option>
                            @empty
                                <option value="">Belum ada acara yang dibuat</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-primary small"><i class="fas fa-search me-1"></i> Cari Peserta di Acara Ini</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control bg-light" placeholder="Ketik nama atau email peserta..." value="{{ $searchKeyword ?? '' }}">
                            <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fas fa-search me-1"></i> Cari</button>
                            
                            @if(!empty($searchKeyword))
                                <a href="{{ route('participants.index', ['event_id' => $selectedEventId]) }}" class="btn btn-danger px-3" title="Hapus Pencarian">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            @endif
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
                    <strong>{{ $selectedEvent ? $selectedEvent->capacity : 0 }} Peserta</strong>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-warning border-0 shadow-sm mb-0 d-flex align-items-center">
                <i class="fas fa-users fa-2x me-3 opacity-50"></i>
                <div>
                    <span class="d-block small">Total Pendaftar:</span>
                    <strong>{{ $registrations->count() }} Orang</strong> <small>({{ $totalWaitlist }} Masuk Waitlist)</small>
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
                            <th class="py-3">NIM</th>
                            <th class="py-3">Waktu Mendaftar</th>
                            <th class="py-3">Status Antrean</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registrations as $index => $reg)
                        <tr>
                            <td class="px-4">{{ $index + 1 }}</td>
                            <td>
                                <span class="badge bg-secondary mb-1">{{ $reg->reg_code }}</span><br>
                                <div class="fw-bold text-dark">{{ $reg->user->name }}</div>
                                <small class="text-muted">{{ $reg->user->email }}</small>
                            </td>
                            <td>{{ $reg->created_at->format('d M Y, H:i') }} WIB</td>
                            <td>
                                @if($reg->status == 'utama')
                                    <span class="badge bg-success rounded-pill px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i> Peserta Utama
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                        <i class="fas fa-hourglass-half me-1"></i> Waitlist
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 text-center">
                                <button class="btn btn-sm btn-outline-danger" title="Diskualifikasi / Hapus">
                                    <i class="fas fa-user-times"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Verifikasi Kehadiran')

@section('content')
<div class="container-fluid p-0">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-1"></i> {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4 bg-light rounded d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-1"><i class="fas fa-qrcode text-primary me-2"></i>Pilih Acara untuk Absensi</h6>
                <small class="text-muted">Hanya menampilkan acara yang sudah disetujui kampus.</small>
            </div>
            <form action="{{ route('attendance.index') }}" method="GET" class="d-flex gap-2 w-50">
                <select name="event_id" class="form-select" onchange="this.form.submit()">
                    @forelse($events as $event)
                        <option value="{{ $event->id }}" {{ $selectedEventId == $event->id ? 'selected' : '' }}>{{ $event->title }}</option>
                    @empty
                        <option value="">Belum ada acara yang disetujui</option>
                    @endforelse
                </select>
            </form>
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
                    <tbody>
                        @forelse($registrations as $index => $reg)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="fw-bold">{{ $reg->user->name }}</td>
                            <td>{{ $reg->user->email }}</td>
                            <td>{{ \Carbon\Carbon::parse($reg->created_at)->format('d M Y, H:i') }} WIB</td>
                            <td>
                                @if($reg->attendance_status == 'hadir')
                                    <span class="badge bg-success"><i class="fas fa-check-double"></i> Hadir</span>
                                @else
                                    <span class="badge bg-secondary"><i class="fas fa-minus"></i> Belum Hadir</span>
                                @endif
                            </td>
                            
                            <td class="text-center">
                                <form action="{{ route('attendance.mark', $reg->id) }}" method="POST" class="d-flex justify-content-center">
                                    @csrf
                                    @if($reg->attendance_status == 'hadir')
                                        <button type="submit" name="attendance_status" value="belum_hadir" class="btn btn-sm btn-outline-danger" title="Batalkan Kehadiran">
                                            <i class="fas fa-undo me-1"></i> Batal
                                        </button>
                                    @else
                                        <button type="submit" name="attendance_status" value="hadir" class="btn btn-sm btn-success fw-bold">
                                            <i class="fas fa-check me-1"></i> Hadir
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted">Tidak ada peserta berstatus UTAMA di acara ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
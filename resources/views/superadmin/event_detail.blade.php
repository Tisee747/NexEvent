@extends('layouts.app')

@section('title', 'Review Detail Acara')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 fw-bold text-gray-800">Detail Pengajuan Acara</h5>
        <a href="{{ url()->previous() }}" class="btn btn-light border shadow-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    </div>
    
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <h4 class="fw-bold text-primary mb-4">{{ $event->title }}</h4>
                    <table class="table table-borderless mb-0">
                        <tr><th width="30%" class="text-muted pb-3">Penyelenggara</th><td class="fw-bold pb-3">{{ $event->panitia->organization }} <br><small class="text-muted fw-normal">Perwakilan: {{ $event->panitia->name }}</small></td></tr>
                        <tr><th class="text-muted pb-3">Tanggal & Waktu</th><td class="pb-3">{{ \Carbon\Carbon::parse($event->event_date)->format('d F Y, H:i') }} WIB</td></tr>
                        <tr>
                            <th class="text-muted pb-3">Lokasi / Media</th>
                            <td class="pb-3">
                                @if($event->is_online)
                                    <span class="badge bg-info text-dark px-3 py-2 rounded-pill"><i class="fas fa-video me-1"></i> Online / Virtual</span>
                                @else
                                    <span class="badge bg-secondary px-3 py-2 rounded-pill"><i class="fas fa-map-marker-alt me-1"></i> Offline / Di Tempat</span>
                                    <br><small class="text-muted d-block mt-2"><i class="fas fa-location-arrow me-1"></i> Koordinat Maps: {{ $event->latitude ?? '-' }}, {{ $event->longitude ?? '-' }}</small>
                                @endif
                            </td>
                        </tr>
                        <tr><th class="text-muted pb-3">Kapasitas</th><td class="pb-3">{{ $event->capacity }} Peserta</td></tr>
                        <tr><th class="text-muted pb-3">Deskripsi Acara</th><td class="pb-3">{{ $event->description }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-file-alt text-primary me-2"></i>Berkas Pendukung</h6>
                    
                    @if($event->proposal)
                        <a href="{{ asset('storage/' . $event->proposal) }}" target="_blank" class="btn btn-outline-danger w-100 mb-2 text-start">
                            <i class="fas fa-file-pdf me-2"></i> Lihat Proposal PDF
                        </a>
                    @else
                        <button class="btn btn-light w-100 mb-2 text-start disabled"><i class="fas fa-times me-2"></i> Proposal Belum Tersedia</button>
                    @endif

                    @if($event->poster)
                        <a href="{{ asset('storage/' . $event->poster) }}" target="_blank" class="btn btn-outline-primary w-100 text-start">
                            <i class="fas fa-image me-2"></i> Lihat Poster Acara
                        </a>
                    @else
                        <button class="btn btn-light w-100 text-start disabled"><i class="fas fa-times me-2"></i> Poster Belum Tersedia</button>
                    @endif
                </div>
            </div>

            @if($event->status === 'pending')
                <div class="card shadow-sm border-0 bg-light border-start border-4 border-warning">
                    <div class="card-body p-4 text-center">
                        <h6 class="fw-bold mb-3 text-dark">Keputusan Approval</h6>
                        <form action="{{ route('superadmin.updateEvent', $event->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" name="action" value="approved" class="btn btn-success w-100 fw-bold py-2"><i class="fas fa-check-circle me-1"></i> Setujui Acara</button>
                        </form>
                        <button type="button" class="btn btn-danger w-100 fw-bold py-2" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="fas fa-times-circle me-1"></i> Tolak / Revisi</button>
                    </div>
                </div>
            @else
                <div class="card shadow-sm border-0 bg-light border-start border-4 {{ $event->status == 'approved' ? 'border-success' : 'border-danger' }}">
                    <div class="card-body p-4 text-center">
                        <h6 class="fw-bold mb-3 text-dark">Status Saat Ini</h6>
                        @if($event->status == 'approved')
                            <span class="badge bg-success fs-6 px-4 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i> Approved</span>
                            <small class="d-block mt-3 text-muted">Acara ini sudah disetujui dan tayang di aplikasi mahasiswa.</small>
                        @else
                            <span class="badge bg-danger fs-6 px-4 py-2 rounded-pill"><i class="fas fa-times-circle me-1"></i> Rejected</span>
                            <div class="alert alert-danger mt-3 mb-0 small text-start">
                                <strong>Catatan Penolakan:</strong><br>
                                {{ $event->reject_reason }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
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
            <form action="{{ route('superadmin.updateEvent', $event->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="rejected">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Masukan alasan penolakan/catatan revisi: <span class="text-danger">*</span></label>
                        <textarea name="reject_reason" class="form-control bg-light" rows="4" placeholder="Contoh: Proposal kurang rincian anggaran, harap diperbaiki dan ajukan ulang..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger fw-bold"><i class="fas fa-paper-plane me-1"></i> Submit Penolakan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
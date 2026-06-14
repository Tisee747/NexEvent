@extends('layouts.app')

@section('title', 'Tambah Acara Baru')

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-gray-800">Form Pengajuan Acara</h5>
                </div>
                <div class="card-body p-4">
                    <form action="#" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Judul Acara <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" placeholder="Contoh: Seminar Nasional Teknologi" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi Lengkap <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="description" rows="4" placeholder="Jelaskan detail acara..." required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tanggal & Waktu <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="event_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Batas Kapasitas Peserta <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="capacity" placeholder="Contoh: 150" required>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold mb-3"><i class="fas fa-map-marker-alt text-danger me-2"></i>Detail Lokasi / Media Acara</h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold d-block">Tipe Lokasi</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="is_online" id="tipeOffline" value="0" checked onclick="toggleLokasi()">
                                <label class="form-check-label" for="tipeOffline">Offline (Onsite Meeting)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="is_online" id="tipeOnline" value="1" onclick="toggleLokasi()">
                                <label class="form-check-label" for="tipeOnline">Online (Virtual Meeting)</label>
                            </div>
                        </div>

                        <div id="formOffline">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" class="form-control" name="latitude" placeholder="Contoh: -6.917464">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" class="form-control" name="longitude" placeholder="Contoh: 107.619123">
                                </div>
                            </div>
                            <small class="text-muted d-block mb-4">Titik koordinat ini akan memunculkan Google Maps di mobile peserta.</small>
                        </div>

                        <div id="formOnline" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Tautan Virtual Meeting (Microsoft Teams/ Zoom / G-Meet)</label>
                                <input type="url" class="form-control" name="meeting_link" placeholder="https://zoom.us/j/123456789">
                            </div>
                            <small class="text-muted d-block mb-4">Link ini hanya akan muncul bagi mahasiswa yang tiketnya berstatus Utama.</small>
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold mb-3"><i class="fas fa-upload text-primary me-2"></i>Unggah Berkas Pendukung</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Poster Acara (.jpg, .png)</label>
                                <input type="file" class="form-control" name="poster" accept="image/*">
                                @if(isset($event) && $event->poster)
                                    <small class="text-success"><i class="fas fa-check"></i> Poster sudah terupload</small>
                                @endif
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Dokumen Proposal (.pdf)</label>
                                <input type="file" class="form-control" name="proposal" accept="application/pdf">
                                @if(isset($event) && $event->proposal)
                                    <small class="text-success"><i class="fas fa-check"></i> Proposal sudah terupload</small>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('events.index') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Ajukan Acara</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h6 class="fw-bold"><i class="fas fa-info-circle text-info me-2"></i>Informasi Persetujuan</h6>
                    <p class="small text-muted mt-2">
                        Setiap acara yang diajukan akan berstatus <strong>Pending</strong> terlebih dahulu. Acara baru akan muncul di aplikasi mobile mahasiswa setelah proposal disetujui (Approve) oleh pihak Kemahasiswaan Kampus (Superadmin).
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleLokasi() {
        var isOnline = document.getElementById('tipeOnline').checked;
        var formOffline = document.getElementById('formOffline');
        var formOnline = document.getElementById('formOnline');

        if (isOnline) {
            formOffline.style.display = 'none';
            formOnline.style.display = 'block';
        } else {
            formOffline.style.display = 'block';
            formOnline.style.display = 'none';
        }
    }
</script>
@endsection
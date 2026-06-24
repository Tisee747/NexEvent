@extends('layouts.app')

@section('title', 'Ajukan Acara Baru')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 320px; width: 100%; border-radius: 12px; z-index: 1; }
</style>

<div class="container-fluid p-0">
    <div class="mb-4">
        <h4 class="fw-bold text-dark mb-1">Ajukan Acara Baru</h4>
        <p class="text-muted small">Lengkapi formulir di bawah ini dengan detail informasi dan dokumen proposal.</p>
    </div>

    <div id="alertBox" class="alert d-none small p-3 rounded-3 fw-bold"></div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">
            <form id="createEventForm">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-bold text-dark">Judul Acara</label>
                        <input type="text" name="title" class="form-control bg-light py-2" placeholder="Contoh Seminar Teknologi Nasional 2026" required>
                    </div>
                    <div class="col-md-3 mb-4">
                        <label class="form-label fw-bold text-dark">Tanggal & Waktu</label>
                        <input type="datetime-local" name="event_date" class="form-control bg-light py-2" required>
                    </div>
                    <div class="col-md-3 mb-4">
                        <label class="form-label fw-bold text-dark">Kapasitas Maksimal</label>
                        <input type="number" name="capacity" class="form-control bg-light py-2" placeholder="Tentukan kuota peserta" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-dark">Deskripsi Acara</label>
                    <textarea name="description" class="form-control bg-light py-2" rows="4" placeholder="Jelaskan secara komprehensif mengenai latar belakang dan tujuan acara ini" required></textarea>
                </div>

                <hr class="my-5 opacity-25">

                <h5 class="fw-bold text-dark mb-4"><i class="fas fa-map-marker-alt text-danger me-2"></i> Pengaturan Lokasi</h5>
                
                <div class="row mb-4">
                    <div class="col-12">
                        <label class="form-label fw-bold text-dark">Format Acara</label>
                        <select name="is_online" id="is_online" class="form-select bg-light py-2" onchange="toggleLocationMode()">
                            <option value="0">Kegiatan Offline (Bertemu Tatap Muka)</option>
                            <option value="1">Kegiatan Online (Melalui Platform Virtual)</option>
                        </select>
                    </div>
                </div>

                <div id="offline-section" class="mb-4">
                    <label class="form-label fw-bold text-dark">Nama Gedung / Ruangan</label>
                    <input type="text" name="location_name" class="form-control bg-light py-2 mb-3" placeholder="Ketik nama ruangan atau gedung penyelenggaraan acara">

                    <label class="form-label fw-bold text-dark">Titik Lokasi Peta</label>
                    <p class="small text-muted mb-3">Geser pin merah muda ini ke lokasi akurat penyelenggaraan acara Anda.</p>
                    <div id="map" class="mb-3 shadow-sm border border-light"></div>
                    <div class="row">
                        <div class="col-6">
                            <input type="text" id="latitude" name="latitude" class="form-control form-control-sm bg-light py-2 text-center fw-bold text-muted" placeholder="Garis Lintang" readonly>
                        </div>
                        <div class="col-6">
                            <input type="text" id="longitude" name="longitude" class="form-control form-control-sm bg-light py-2 text-center fw-bold text-muted" placeholder="Garis Bujur" readonly>
                        </div>
                    </div>
                </div>

                <div id="online-section" class="mb-4" style="display: none;">
                    <label class="form-label fw-bold text-dark">Tautan Platform Pertemuan</label>
                    <input type="url" name="meeting_link" class="form-control bg-light py-2" placeholder="Masukkan link platform meeting Anda">
                </div>

                <hr class="my-5 opacity-25">

                <h5 class="fw-bold text-dark mb-4"><i class="fas fa-file-alt text-primary me-2"></i> Kelengkapan Berkas</h5>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-bold text-dark">Desain Poster Acara</label>
                        <input type="file" name="poster_path" class="form-control bg-light py-2" accept="image/*">
                        <small class="text-muted d-block mt-1">Sifatnya opsional. Gunakan format JPG atau PNG.</small>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-bold text-dark">Dokumen Proposal Resmi</label>
                        <input type="file" name="proposal_path" class="form-control bg-light py-2" accept="application/pdf" required>
                        <small class="text-danger d-block mt-1 fw-semibold">Sifatnya wajib. Gunakan format PDF khusus.</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="/events" class="btn btn-light border py-2 px-4 me-3 fw-bold">Batalkan Proses</a>
                    <button type="submit" id="btnSubmit" class="btn btn-primary py-2 px-5 fw-bold shadow-sm">Kirim ke Sistem</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map = L.map('map').setView([-6.97426, 107.6337], 15);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    let marker = L.marker([-6.97426, 107.6337], {draggable: true}).addTo(map);

    document.getElementById('latitude').value = marker.getLatLng().lat;
    document.getElementById('longitude').value = marker.getLatLng().lng;

    marker.on('dragend', function (e) {
        document.getElementById('latitude').value = marker.getLatLng().lat;
        document.getElementById('longitude').value = marker.getLatLng().lng;
    });

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        document.getElementById('latitude').value = e.latlng.lat;
        document.getElementById('longitude').value = e.latlng.lng;
    });

    window.toggleLocationMode = function() {
        let isOnline = document.getElementById('is_online').value;
        document.getElementById('offline-section').style.display = isOnline === "1" ? "none" : "block";
        document.getElementById('online-section').style.display = isOnline === "1" ? "block" : "none";
        setTimeout(() => map.invalidateSize(), 100);
    };

    document.getElementById('createEventForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const token = localStorage.getItem('auth_token');
        const user = JSON.parse(localStorage.getItem('user_data'));
        
        let btn = document.getElementById('btnSubmit');
        let alertBox = document.getElementById('alertBox');

        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sedang Memproses Data...';
        btn.disabled = true;
        alertBox.classList.add('d-none');

        let formData = new FormData(this);
        formData.append('admin_id', user.id); 

        try {
            let response = await fetch('/api/events', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                body: formData
            });

            if (response.status === 401) {
                localStorage.clear();
                window.location.href = '/login';
                return;
            }

            let result = await response.json();

            if (response.ok) {
                alertBox.className = 'alert alert-success small p-3 rounded-3 fw-bold';
                alertBox.innerHTML = '<i class="fas fa-check-circle me-2"></i> Acara baru berhasil didaftarkan. Anda akan segera dialihkan.';
                alertBox.classList.remove('d-none');
                setTimeout(() => window.location.href = '/events', 1500);
            } else {
                alertBox.className = 'alert alert-danger small p-3 rounded-3 fw-bold';
                alertBox.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i> ' + (result.message || 'Sistem menolak pengajuan formulir ini.');
                alertBox.classList.remove('d-none');
                btn.innerHTML = 'Kirim ke Sistem';
                btn.disabled = false;
            }
        } catch (error) {
            alertBox.className = 'alert alert-danger small p-3 rounded-3 fw-bold';
            alertBox.innerHTML = '<i class="fas fa-wifi me-2"></i> Gagal berkomunikasi dengan server utama.';
            alertBox.classList.remove('d-none');
            btn.innerHTML = 'Kirim ke Sistem';
            btn.disabled = false;
        }
    });
</script>
@endsection
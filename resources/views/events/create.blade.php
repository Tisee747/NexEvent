@extends('layouts.app')

@section('title', 'Ajukan Acara Baru')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 300px; width: 100%; border-radius: 8px; z-index: 1; }
</style>

<div class="container-fluid p-0">
    <div class="mb-4">
        <h4 class="fw-bold text-dark mb-1">Ajukan Acara Baru</h4>
        <p class="text-muted small">Lengkapi detail acara dan proposal untuk proses review Kampus.</p>
    </div>

    <div id="alertBox" class="alert d-none small p-2"></div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-4">
            <form id="createEventForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Judul Acara</label>
                        <input type="text" name="title" class="form-control" placeholder="Contoh: Seminar Teknologi 2026" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Tanggal & Waktu</label>
                        <input type="datetime-local" name="event_date" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Kapasitas Peserta</label>
                        <input type="number" name="capacity" class="form-control" placeholder="Contoh: 150" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Deskripsi Acara</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Jelaskan secara singkat mengenai acara ini..." required></textarea>
                </div>

                <hr class="my-4">

                <h6 class="fw-bold text-dark mb-3">Detail Lokasi</h6>
                
                <div class="row mb-3">
                    <div class="col-12">
                        <label class="form-label fw-bold">Format Acara</label>
                        <select name="is_online" id="is_online" class="form-select" onchange="toggleLocationMode()">
                            <option value="0">Offline (Onsite)</option>
                            <option value="1">Online (Virtual)</option>
                        </select>
                    </div>
                </div>

                <div id="offline-section" class="mb-4">
                    <label class="form-label fw-bold">Pilih Titik Lokasi Peta</label>
                    <p class="small text-muted mb-2">Geser pin ke lokasi spesifik acara Anda.</p>
                    <div id="map" class="mb-2 shadow-sm border"></div>
                    <div class="row">
                        <div class="col-6">
                            <input type="text" id="latitude" name="latitude" class="form-control form-control-sm bg-light" placeholder="Latitude" readonly>
                        </div>
                        <div class="col-6">
                            <input type="text" id="longitude" name="longitude" class="form-control form-control-sm bg-light" placeholder="Longitude" readonly>
                        </div>
                    </div>
                </div>

                <div id="online-section" class="mb-4" style="display: none;">
                    <label class="form-label fw-bold">Tautan Pertemuan (Zoom/Meet)</label>
                    <input type="url" name="meeting_link" class="form-control" placeholder="https://zoom.us/j/123456789">
                </div>

                <hr class="my-4">

                <h6 class="fw-bold text-dark mb-3">Dokumen Pendukung</h6>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Poster Acara (Opsional, .jpg, .png)</label>
                        <input type="file" name="poster_path" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Proposal PDF (Wajib)</label>
                        <input type="file" name="proposal_path" class="form-control" accept="application/pdf" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="/events" class="btn btn-light border me-2 fw-bold">Batal</a>
                    <button type="submit" id="btnSubmit" class="btn btn-primary fw-bold px-4">Kirim Proposal</button>
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

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
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

            let result = await response.json();

            if (response.ok) {
                alertBox.className = 'alert alert-success small p-2';
                alertBox.innerHTML = '<i class="fas fa-check-circle me-1"></i> Acara berhasil diajukan! Mengalihkan...';
                alertBox.classList.remove('d-none');
                setTimeout(() => window.location.href = '/events', 1500);
            } else {
                alertBox.className = 'alert alert-danger small p-2';
                alertBox.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> ' + (result.message || 'Terjadi kesalahan.');
                alertBox.classList.remove('d-none');
                btn.innerHTML = 'Kirim Proposal';
                btn.disabled = false;
            }
        } catch (error) {
            alertBox.className = 'alert alert-danger small p-2';
            alertBox.innerHTML = '<i class="fas fa-wifi me-1"></i> Gagal terhubung ke server.';
            alertBox.classList.remove('d-none');
            btn.innerHTML = 'Kirim Proposal';
            btn.disabled = false;
        }
    });
</script>
@endsection
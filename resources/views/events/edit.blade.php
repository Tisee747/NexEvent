@extends('layouts.app')

@section('title', 'Edit Acara')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 300px; width: 100%; border-radius: 8px; z-index: 1; }
</style>

<div class="container-fluid p-0">
    <div class="mb-4">
        <h4 class="fw-bold text-dark mb-1">Edit Acara</h4>
        <p class="text-muted small">Ubah detail proposal atau dokumen pendukung acara Anda.</p>
    </div>

    <div id="alertBox" class="alert d-none small p-2"></div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-4">
            <form id="editEventForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Judul Acara</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Tanggal & Waktu</label>
                        <input type="datetime-local" id="event_date" name="event_date" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Kapasitas Peserta</label>
                        <input type="number" id="capacity" name="capacity" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Deskripsi Lengkap</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
                </div>

                <hr class="my-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-map-marker-alt text-danger me-2"></i>Detail Lokasi / Media Acara</h6>
                
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
                    <div id="map" class="mb-2 shadow-sm border"></div>
                    <div class="row">
                        <div class="col-6">
                            <input type="text" id="latitude" name="latitude" class="form-control form-control-sm bg-light" readonly>
                        </div>
                        <div class="col-6">
                            <input type="text" id="longitude" name="longitude" class="form-control form-control-sm bg-light" readonly>
                        </div>
                    </div>
                </div>

                <div id="online-section" class="mb-4" style="display: none;">
                    <label class="form-label fw-bold">Tautan Virtual Meeting (Zoom / G-Meet)</label>
                    <input type="url" id="meeting_link" name="meeting_link" class="form-control">
                </div>

                <hr class="my-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-upload text-primary me-2"></i>Unggah Berkas Baru (Opsional)</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Poster Acara (.jpg, .png)</label>
                        <input type="file" class="form-control" name="poster_path" accept="image/*">
                        <small class="text-muted">Biarkan kosong jika tidak ingin mengubah poster.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Dokumen Proposal (.pdf)</label>
                        <input type="file" class="form-control" name="proposal_path" accept="application/pdf">
                        <small class="text-muted">Biarkan kosong jika tidak ingin mengubah proposal.</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="/events" class="btn btn-secondary">Batal</a>
                    <button type="submit" id="btnSubmit" class="btn btn-primary fw-bold"><i class="fas fa-save me-1"></i> Simpan & Ajukan Ulang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const token = localStorage.getItem('auth_token');
    const eventId = window.location.pathname.split('/')[2]; 

    let map, marker;

    document.addEventListener('DOMContentLoaded', async function() {
        try {
            let response = await fetch(`/api/events/${eventId}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            let result = await response.json();

            if (response.ok) {
                let event = result.data;
                document.getElementById('title').value = event.title;
                document.getElementById('capacity').value = event.capacity;
                document.getElementById('description').value = event.description;
                document.getElementById('is_online').value = event.is_online;
                
                let dt = new Date(event.event_date);
                dt.setMinutes(dt.getMinutes() - dt.getTimezoneOffset());
                document.getElementById('event_date').value = dt.toISOString().slice(0,16);

                document.getElementById('meeting_link').value = event.meeting_link || '';
                document.getElementById('latitude').value = event.latitude || '-6.97426';
                document.getElementById('longitude').value = event.longitude || '107.6337';

                let lat = event.latitude || -6.97426;
                let lng = event.longitude || 107.6337;

                map = L.map('map').setView([lat, lng], 15);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
                marker = L.marker([lat, lng], {draggable: true}).addTo(map);

                marker.on('dragend', function (e) {
                    document.getElementById('latitude').value = marker.getLatLng().lat;
                    document.getElementById('longitude').value = marker.getLatLng().lng;
                });
                map.on('click', function(e) {
                    marker.setLatLng(e.latlng);
                    document.getElementById('latitude').value = e.latlng.lat;
                    document.getElementById('longitude').value = e.latlng.lng;
                });

                toggleLocationMode();
            }
        } catch (error) {
            console.error("Gagal mengambil data acara");
        }
    });

    window.toggleLocationMode = function() {
        let isOnline = document.getElementById('is_online').value;
        document.getElementById('offline-section').style.display = isOnline === "1" ? "none" : "block";
        document.getElementById('online-section').style.display = isOnline === "1" ? "block" : "none";
        if(map) setTimeout(() => map.invalidateSize(), 100);
    };

    document.getElementById('editEventForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        let btn = document.getElementById('btnSubmit');
        let alertBox = document.getElementById('alertBox');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        btn.disabled = true;
        alertBox.classList.add('d-none');

        let formData = new FormData(this);

        try {
            let response = await fetch(`/api/events/${eventId}`, {
                method: 'POST', 
                headers: { 'Authorization': `Bearer ${token}` },
                body: formData
            });

            let result = await response.json();

            if (response.ok) {
                alertBox.className = 'alert alert-success small p-2';
                alertBox.innerHTML = '<i class="fas fa-check-circle me-1"></i> Perubahan disimpan! Mengalihkan...';
                alertBox.classList.remove('d-none');
                setTimeout(() => window.location.href = '/events', 1500);
            } else {
                alertBox.className = 'alert alert-danger small p-2';
                alertBox.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> ' + (result.message || 'Gagal menyimpan.');
                alertBox.classList.remove('d-none');
                btn.innerHTML = '<i class="fas fa-save me-1"></i> Simpan & Ajukan Ulang';
                btn.disabled = false;
            }
        } catch (error) {
            alertBox.className = 'alert alert-danger small p-2';
            alertBox.innerHTML = '<i class="fas fa-wifi me-1"></i> Gagal terhubung ke server.';
            alertBox.classList.remove('d-none');
            btn.innerHTML = '<i class="fas fa-save me-1"></i> Simpan & Ajukan Ulang';
            btn.disabled = false;
        }
    });
</script>
@endsection
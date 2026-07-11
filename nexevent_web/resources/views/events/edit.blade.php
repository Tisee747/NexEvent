@extends('layouts.app')

@section('title', 'Modifikasi Acara')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 320px; width: 100%; border-radius: 12px; z-index: 1; }
</style>

<div class="container-fluid p-0">
    <div class="mb-4">
        <h4 class="fw-bold text-dark mb-1">Modifikasi Data Acara</h4>
        <p class="text-muted small">Perbaiki informasi proposal yang mendapatkan catatan revisi dari pihak kampus.</p>
    </div>

    <div id="alertBox" class="alert d-none small p-3 rounded-3 fw-bold"></div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">
            <form id="editEventForm">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-bold text-dark">Judul Acara</label>
                        <input type="text" id="title" name="title" class="form-control bg-light py-2" required>
                    </div>
                    <div class="col-md-3 mb-4">
                        <label class="form-label fw-bold text-dark">Tanggal Waktu</label>
                        <input type="datetime-local" id="event_date" name="event_date" class="form-control bg-light py-2" required>
                    </div>
                    <div class="col-md-3 mb-4">
                        <label class="form-label fw-bold text-dark">Kapasitas Maksimal</label>
                        <input type="number" id="capacity" name="capacity" class="form-control bg-light py-2" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-dark">Deskripsi Lengkap</label>
                    <textarea id="description" name="description" class="form-control bg-light py-2" rows="5" required></textarea>
                </div>

                <hr class="my-5 opacity-25">
                
                <h5 class="fw-bold text-dark mb-4"><i class="fas fa-map-marker-alt text-danger me-2"></i> Pengaturan Lokasi Ulang</h5>
                
                <div class="row mb-4">
                    <div class="col-12">
                        <label class="form-label fw-bold text-dark">Format Acara</label>
                        <select name="is_online" id="is_online" class="form-select bg-light py-2" onchange="toggleLocationMode()">
                            <option value="0">Kegiatan Offline</option>
                            <option value="1">Kegiatan Online</option>
                        </select>
                    </div>
                </div>

                <div id="offline-section" class="mb-4">
                    <label class="form-label fw-bold text-dark">Nama Gedung Ruangan</label>
                    <input type="text" id="location_name" name="location_name" class="form-control bg-light py-2 mb-3" placeholder="Ketik nama lokasi spesifik">
                    <label class="form-label fw-bold text-dark">Titik Lokasi Peta</label>
                    <div id="map" class="mb-3 shadow-sm border border-light"></div>
                    <div class="row">
                        <div class="col-6">
                            <input type="text" id="latitude" name="latitude" class="form-control form-control-sm bg-light py-2 text-center fw-bold text-muted" readonly>
                        </div>
                        <div class="col-6">
                            <input type="text" id="longitude" name="longitude" class="form-control form-control-sm bg-light py-2 text-center fw-bold text-muted" readonly>
                        </div>
                    </div>
                </div>

                <div id="online-section" class="mb-4" style="display: none;">
                    <label class="form-label fw-bold text-dark">Tautan Platform Pertemuan Terpadu</label>
                    <input type="url" id="meeting_link" name="meeting_link" class="form-control bg-light py-2">
                </div>

                <hr class="my-5 opacity-25">
                
                <h5 class="fw-bold text-dark mb-4"><i class="fas fa-upload text-primary me-2"></i> Penyesuaian Dokumen Revisi</h5>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <a id="btnViewPoster" href="#" target="_blank" class="btn btn-outline-primary w-100 d-none fw-bold"><i class="fas fa-image me-2"></i>Lihat Poster Saat Ini</a>
                    </div>
                    <div class="col-md-6">
                        <a id="btnViewProposal" href="#" target="_blank" class="btn btn-outline-danger w-100 d-none fw-bold"><i class="fas fa-file-pdf me-2"></i>Lihat Proposal Saat Ini</a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-bold text-dark">Unggah Poster Baru</label>
                        <input type="file" class="form-control bg-light py-2" name="poster_path" accept="image/*">
                        <small class="text-muted d-block mt-1">Kosongkan kolom ini jika desain poster tidak direvisi.</small>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-bold text-dark">Unggah Proposal PDF Baru</label>
                        <input type="file" class="form-control bg-light py-2" name="proposal_path" accept="application/pdf">
                        <small class="text-muted d-block mt-1">Kosongkan kolom ini jika dokumen proposal tidak direvisi.</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="/events" class="btn btn-light border py-2 px-4 me-3 fw-bold">Batalkan Proses</a>
                    <button type="submit" id="btnSubmit" class="btn btn-primary py-2 px-5 fw-bold shadow-sm"><i class="fas fa-save me-2"></i> Simpan Modifikasi Data</button>
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
            
            if (response.status === 401) {
                localStorage.clear();
                window.location.href = '/login';
                return;
            }

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
                document.getElementById('location_name').value = event.location_name || '';
                document.getElementById('latitude').value = event.latitude || '-6.97426';
                document.getElementById('longitude').value = event.longitude || '107.6337';

                if (event.poster_path) {
                    let posterBtn = document.getElementById('btnViewPoster');
                    posterBtn.href = `/view-document?path=${encodeURIComponent(event.poster_path)}`;
                    posterBtn.classList.remove('d-none');
                }

                if (event.proposal_path) {
                    let proposalBtn = document.getElementById('btnViewProposal');
                    proposalBtn.href = `/view-document?path=${encodeURIComponent(event.proposal_path)}`;
                    proposalBtn.classList.remove('d-none');
                }

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
            console.error("Gagal menarik data");
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
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Mengunggah Modifikasi...';
        btn.disabled = true;
        alertBox.classList.add('d-none');

        let formData = new FormData(this);

        try {
            let response = await fetch(`/api/events/${eventId}`, {
                method: 'POST', 
                headers: { 'Authorization': `Bearer ${token}` },
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
                alertBox.innerHTML = '<i class="fas fa-check-circle me-2"></i> Perubahan berhasil disalin ke server inti.';
                alertBox.classList.remove('d-none');
                setTimeout(() => window.location.href = '/events', 1500);
            } else {
                alertBox.className = 'alert alert-danger small p-3 rounded-3 fw-bold';
                alertBox.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i> Gagal memperbarui data';
                alertBox.classList.remove('d-none');
                btn.innerHTML = '<i class="fas fa-save me-2"></i> Simpan Modifikasi Data';
                btn.disabled = false;
            }
        } catch (error) {
            alertBox.className = 'alert alert-danger small p-3 rounded-3 fw-bold';
            alertBox.innerHTML = '<i class="fas fa-wifi me-2"></i> Koneksi terputus';
            alertBox.classList.remove('d-none');
            btn.innerHTML = '<i class="fas fa-save me-2"></i> Simpan Modifikasi Data';
            btn.disabled = false;
        }
    });
</script>
@endsection
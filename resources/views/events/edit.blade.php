@extends('layouts.app')

@section('title', 'Edit Acara')

@section('content')
<div class="container-fluid p-0">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-gray-800">Edit Acara</h5>
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-light border"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </div>
        <div class="card-body p-4">
            
            @if($event->status == 'rejected')
                <div class="alert alert-danger mb-4">
                    <strong><i class="fas fa-exclamation-triangle me-1"></i> Catatan Penolakan Kampus:</strong><br>
                    {{ $event->reject_reason }}
                </div>
            @endif

            <form action="{{ route('events.update', $event->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Judul Acara</label>
                    <input type="text" class="form-control" name="title" value="{{ $event->title }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Deskripsi Lengkap</label>
                    <textarea class="form-control" name="description" rows="4" required>{{ $event->description }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal & Waktu</label>
                        <input type="datetime-local" class="form-control" name="event_date" value="{{ \Carbon\Carbon::parse($event->event_date)->format('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Batas Kapasitas Peserta</label>
                        <input type="number" class="form-control" name="capacity" value="{{ $event->capacity }}" required>
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-map-marker-alt text-danger me-2"></i>Detail Lokasi / Media Acara</h6>
                
                <div class="mb-3">
                    <label class="form-label fw-bold d-block">Tipe Lokasi</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_online" id="tipeOffline" value="0" {{ $event->is_online == 0 ? 'checked' : '' }} onclick="toggleLokasi()">
                        <label class="form-check-label" for="tipeOffline">Offline (Tempat Fisik)</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_online" id="tipeOnline" value="1" {{ $event->is_online == 1 ? 'checked' : '' }} onclick="toggleLokasi()">
                        <label class="form-check-label" for="tipeOnline">Online (Virtual Meeting)</label>
                    </div>
                </div>

                <div id="formOffline" style="display: {{ $event->is_online == 0 ? 'block' : 'none' }};">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="text" class="form-control" name="latitude" value="{{ $event->latitude }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="text" class="form-control" name="longitude" value="{{ $event->longitude }}">
                        </div>
                    </div>
                </div>

                <div id="formOnline" style="display: {{ $event->is_online == 1 ? 'block' : 'none' }};">
                    <div class="mb-3">
                        <label class="form-label">Tautan Virtual Meeting (Zoom / G-Meet)</label>
                        <input type="url" class="form-control" name="meeting_link" value="{{ $event->meeting_link }}">
                    </div>
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

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-save me-1"></i> Simpan & Ajukan Ulang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleLokasi() {
        var isOnline = document.getElementById('tipeOnline').checked;
        document.getElementById('formOffline').style.display = isOnline ? 'none' : 'block';
        document.getElementById('formOnline').style.display = isOnline ? 'block' : 'none';
    }
</script>
@endsection
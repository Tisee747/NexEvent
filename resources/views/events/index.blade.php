@extends('layouts.app')

@section('title', 'Manajemen Acara')

@section('content')
<div class="container-fluid p-0">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-gray-800">Daftar Acara Saya</h5>
            <a href="{{ route('events.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Tambah Acara
            </a>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-12">
                    <form action="{{ route('events.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control bg-light" placeholder="Ketik judul acara yang ingin dicari..." value="{{ $searchKeyword ?? '' }}">
                            <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fas fa-search me-1"></i> Cari</button>
                            
                            @if(!empty($searchKeyword))
                                <a href="{{ route('events.index') }}" class="btn btn-danger px-4" title="Hapus Pencarian">
                                    <i class="fas fa-times me-1"></i> Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="25%">Judul Acara</th>
                            <th width="15%">Tanggal Pelaksanaan</th>
                            <th width="10%">Kapasitas</th>
                            <th width="15%">Status Proposal</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($events as $index => $event)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <span class="fw-bold">{{ $event->title }}</span><br>
                                <small class="text-muted">
                                    <i class="fas {{ $event->is_online ? 'fa-video' : 'fa-map-marker-alt' }}"></i> 
                                    {{ $event->is_online ? 'Online / Virtual' : 'Offline / Titik Maps' }}
                                </small>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($event->event_date)->format('d M Y') }}<br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($event->event_date)->format('H:i') }} WIB</small>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">{{ $event->registrations_count }} / {{ $event->capacity }} Peserta</span>
                            </td>
                            <td>
                                @if($event->status == 'approved')
                                    <span class="badge bg-success"><i class="fas fa-check-circle"></i> Approved</span>
                                @elseif($event->status == 'pending')
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Pending</span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Rejected</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('events.edit', $event->id) }}" class="btn btn-sm btn-outline-info" title="Lihat/Edit"><i class="fas fa-edit"></i></a>
                                <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="fas fa-trash"></i></button>
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
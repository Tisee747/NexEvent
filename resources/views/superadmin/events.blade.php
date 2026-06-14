@extends('layouts.app')

@section('title', 'Semua Acara Kampus')

@section('content')
<div class="container-fluid p-0">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold text-gray-800"><i class="fas fa-list-alt text-primary me-2"></i>Master Data Semua Acara</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Judul Acara</th>
                            <th>Penyelenggara</th>
                            <th>Tanggal Acara</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($events as $index => $event)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="fw-bold">{{ $event->title }}</td>
                            <td>{{ $event->panitia->organization ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($event->event_date)->format('d M Y') }}</td>
                            <td>
                                @if($event->status == 'approved')
                                    <span class="badge bg-success"><i class="fas fa-check"></i> Approved</span>
                                @elseif($event->status == 'pending')
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Pending</span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-times"></i> Rejected</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('superadmin.showEvent', $event->id) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail"><i class="fas fa-eye"></i></a>
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
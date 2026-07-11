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
                    <tbody id="allEventsTable">
                        <tr><td colspan="6" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i> Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const token = localStorage.getItem('auth_token');

    document.addEventListener('DOMContentLoaded', () => {
        fetchAllEvents();
    });

    async function fetchAllEvents() {
        try {
            let res = await fetch('/api/superadmin/all-events', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            let result = await res.json();

            if (res.ok) {
                renderTable(result.data);
            }
        } catch (error) {
            document.getElementById('allEventsTable').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Gagal memuat data.</td></tr>';
        }
    }

    function renderTable(events) {
        let tbody = document.getElementById('allEventsTable');
        if (events.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Belum ada acara yang terdaftar.</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        events.forEach((event, index) => {
            let dateStr = new Date(event.event_date).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});
            let orgName = event.panitia ? event.panitia.organization : '-';
            
            let statusBadge = '';
            if(event.status === 'approved') statusBadge = '<span class="badge bg-success"><i class="fas fa-check"></i> Approved</span>';
            else if(event.status === 'pending' || event.status === 'pending_superadmin') statusBadge = '<span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Pending</span>';
            else statusBadge = '<span class="badge bg-danger"><i class="fas fa-times"></i> Rejected</span>';

            tbody.innerHTML += `
                <tr>
                    <td>${index + 1}</td>
                    <td class="fw-bold">${event.title}</td>
                    <td>${orgName}</td>
                    <td>${dateStr}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <a href="/superadmin/event/${event.id}" class="btn btn-sm btn-outline-info" title="Lihat Detail"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
            `;
        });
    }
</script>
@endsection
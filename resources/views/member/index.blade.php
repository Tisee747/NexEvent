@extends('layouts.app') 

@section('title', 'Manajemen Panitia')

@section('content')
<div class="container-fluid p-0">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show small p-3 mb-3" role="alert">
        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @error('email')
    <div class="alert alert-danger alert-dismissible fade show small p-3 mb-3" role="alert">
        <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @enderror

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-gray-800">Daftar Anggota Panitia</h5>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                <i class="fas fa-plus me-1"></i> Tambah Panitia
            </button>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="35%">Nama Mahasiswa</th>
                            <th width="25%">Email Akses</th>
                            <th width="15%">Posisi</th>
                            <th width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $index => $member)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <span class="fw-bold">{{ $member->user->name }}</span><br>
                                <small class="text-muted"><i class="fas fa-id-card me-1"></i> {{ $member->user->nim ?? '-' }}</small>
                            </td>
                            <td>{{ $member->user->email }}</td>
                            <td><span class="badge bg-info text-dark">{{ $member->position }}</span></td>
                            <td>
                                <form action="{{ route('admin.members.destroy', $member->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akses panitia ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Akses">
                                        <i class="fas fa-trash me-1"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-users-slash me-2"></i> Belum ada panitia yang terdaftar.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="addMemberModalLabel"><i class="fas fa-envelope me-2 text-primary"></i>Undang Panitia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.members.store') }}" method="POST">
                @csrf
                <input type="hidden" name="admin_id" id="hidden_admin_id">
                <div class="modal-body">
                    <p class="small text-muted mb-3">Masukkan alamat email mahasiswa yang valid. Mahasiswa tersebut harus sudah pernah login ke dalam aplikasi setidaknya satu kali.</p>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email Telkom University</label>
                        <input type="email" class="form-control bg-light" id="email" name="email" placeholder="nama@student.telkomuniversity.ac.id" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Tambahkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userData = localStorage.getItem('user_data');
        if(userData) {
            const user = JSON.parse(userData);
            const adminInput = document.getElementById('hidden_admin_id');
            if(adminInput) {
                adminInput.value = user.id;
            }
        }
    });
</script>
@endsection
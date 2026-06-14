@extends('layouts.app')

@section('title', 'Manajemen Organisasi')

@section('content')
<div class="container-fluid p-0">
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-bold text-gray-800"><i class="fas fa-sitemap me-2"></i>Daftar Organisasi Kampus</h6>
            <button class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Tambah Organisasi</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Organisasi</th>
                            <th>Nama Ketua / Perwakilan</th>
                            <th>Email Resmi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($organizations as $index => $org)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="fw-bold text-primary">{{ $org->organization }}</td>
                            <td>{{ $org->name }}</td>
                            <td>{{ $org->email }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#editOrg{{ $org->id }}" title="Edit"><i class="fas fa-edit"></i></button>
                                    
                                    <form action="{{ route('superadmin.deleteOrg', $org->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus organisasi ini secara permanen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>

                                <div class="modal fade text-start" id="editOrg{{ $org->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-light">
                                                <h6 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Organisasi</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('superadmin.updateOrg', $org->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Nama Organisasi</label>
                                                        <input type="text" name="organization" class="form-control" value="{{ $org->organization }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Nama Perwakilan / Ketua</label>
                                                        <input type="text" name="name" class="form-control" value="{{ $org->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Email Resmi</label>
                                                        <input type="email" name="email" class="form-control" value="{{ $org->email }}" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Belum ada organisasi yang terdaftar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
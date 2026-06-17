@extends('layouts.app')

@section('title', 'Manajemen Organisasi')

@section('content')
<div class="container-fluid p-0">
    <div id="alertBox" class="alert d-none alert-dismissible fade show shadow-sm border-0 small p-2 mb-3"></div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold text-gray-800"><i class="fas fa-sitemap me-2"></i>Daftar Organisasi Kampus</h6>
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
                    <tbody id="orgTableBody">
                        <tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i> Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editOrgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h6 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Organisasi</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editOrgForm">
                <div class="modal-body p-4">
                    <input type="hidden" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Organisasi</label>
                        <input type="text" id="edit_organization" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Perwakilan / Ketua</label>
                        <input type="text" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Resmi</label>
                        <input type="email" id="edit_email" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSaveOrg" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const token = localStorage.getItem('auth_token');

    document.addEventListener('DOMContentLoaded', () => {
        fetchOrganizations();
    });

    async function fetchOrganizations() {
        try {
            let res = await fetch('/api/superadmin/organizations', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            let result = await res.json();

            if (res.ok) {
                renderOrgTable(result.data);
            }
        } catch (error) {
            showAlert('Gagal memuat data organisasi.', 'danger');
        }
    }

    function renderOrgTable(orgs) {
        let tbody = document.getElementById('orgTableBody');
        if (orgs.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada organisasi yang terdaftar.</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        orgs.forEach((org, index) => {
            let orgData = encodeURIComponent(JSON.stringify(org));
            
            tbody.innerHTML += `
                <tr>
                    <td>${index + 1}</td>
                    <td class="fw-bold text-primary">${org.organization || '-'}</td>
                    <td>${org.name}</td>
                    <td>${org.email}</td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <button class="btn btn-sm btn-outline-info" onclick="openEditModal('${orgData}')" title="Edit"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteOrg(${org.id})" title="Hapus"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        });
    }

    function openEditModal(encodedData) {
        let org = JSON.parse(decodeURIComponent(encodedData));
        document.getElementById('edit_id').value = org.id;
        document.getElementById('edit_organization').value = org.organization;
        document.getElementById('edit_name').value = org.name;
        document.getElementById('edit_email').value = org.email;
        
        let modal = new bootstrap.Modal(document.getElementById('editOrgModal'));
        modal.show();
    }

    document.getElementById('editOrgForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        let id = document.getElementById('edit_id').value;
        let btn = document.getElementById('btnSaveOrg');
        
        btn.innerHTML = 'Menyimpan...';
        btn.disabled = true;

        let payload = {
            organization: document.getElementById('edit_organization').value,
            name: document.getElementById('edit_name').value,
            email: document.getElementById('edit_email').value
        };

        try {
            let res = await fetch(`/api/superadmin/organizations/${id}`, {
                method: 'PUT',
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                let modalEl = document.getElementById('editOrgModal');
                let modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                
                showAlert('Data organisasi berhasil diperbarui.', 'success');
                fetchOrganizations();
            } else {
                showAlert('Gagal memperbarui data.', 'danger');
            }
        } catch (error) {
            showAlert('Kesalahan jaringan.', 'danger');
        }

        btn.innerHTML = 'Simpan Perubahan';
        btn.disabled = false;
    });

    async function deleteOrg(id) {
        if(!confirm('Yakin ingin menghapus organisasi ini secara permanen?')) return;
        
        try {
            let res = await fetch(`/api/superadmin/organizations/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': `Bearer ${token}` }
            });
            
            if (res.ok) {
                showAlert('Organisasi berhasil dihapus.', 'success');
                fetchOrganizations();
            } else {
                showAlert('Gagal menghapus organisasi.', 'danger');
            }
        } catch(e) {
            showAlert('Kesalahan jaringan.', 'danger');
        }
    }

    function showAlert(msg, type) {
        let box = document.getElementById('alertBox');
        box.className = `alert alert-${type} alert-dismissible fade show shadow-sm border-0 small p-2 mb-3`;
        box.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-1"></i> ${msg} <button type="button" class="btn-close" style="padding: 0.8rem" data-bs-dismiss="alert"></button>`;
        box.classList.remove('d-none');
        window.scrollTo(0,0);
    }
</script>
@endsection
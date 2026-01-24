@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-0">Form Adendum Kontrak</h3>
                <span class="text-muted small">Nomor Kontrak: <span class="fw-bold text-primary">001/SPK/JKRL/2026</span></span>
            </div>
            <div>
                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> Mode Edit: Adendum</span>
            </div>
        </div>

        <form action="" method="POST">
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-light fw-bold py-3">
                    <i class="bi bi-info-circle-fill me-2 text-primary"></i> Detail Kontrak (Saat Ini)
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small text-uppercase">Perusahaan / Instansi</label>
                            <input type="text" class="form-control bg-light" value="PT. Maju Mundur Sejahtera" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small text-uppercase">Jenis Layanan</label>
                            <input type="text" class="form-control bg-light" value="TLD Perorangan (Whole Body)" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-muted small text-uppercase">Awal Kontrak</label>
                            <input type="text" class="form-control bg-light" value="01 Januari 2025" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small text-uppercase">Akhir Kontrak</label>
                            <input type="text" class="form-control bg-light" value="31 Desember 2025" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small text-uppercase">Total Unit Eksisting</label>
                            <input type="text" class="form-control bg-light fw-bold" value="5 Personil" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4 border-primary border-top-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                    <span class="fw-bold"><i class="bi bi-people-fill me-2"></i> Daftar Personil TLD</span>
                    <button type="button" class="btn btn-sm btn-light text-primary fw-bold shadow-sm" onclick="addRow()">
                        <i class="bi bi-person-plus-fill"></i> Tambah Personil Baru
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tableTLD">
                            <thead class="table-light text-uppercase small">
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="35%">Nama Personil</th>
                                    <th width="20%">Divisi</th>
                                    <th width="25%">Status Adendum</th>
                                    <th width="15%">Pengganti (Jika Ganti)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center">1</td>
                                    <td>
                                        <span class="fw-bold text-dark">Budi Santoso</span>
                                        <div class="small text-muted">ID: 1001</div>
                                    </td>
                                    <td>Radiologi</td>
                                    <td>
                                        <select class="form-select form-select-sm border-success text-success fw-bold" onchange="checkStatus(this)">
                                            <option value="tetap" selected>Tetap (Lanjut)</option>
                                            <option value="ganti" class="text-dark">Ganti Personil</option>
                                            <option value="stop" class="text-danger">Berhenti (Hapus)</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm bg-light" placeholder="-" disabled>
                                    </td>
                                </tr>

                                <tr class="table-warning bg-opacity-10">
                                    <td class="text-center">2</td>
                                    <td>
                                        <span class="fw-bold text-dark">Siti Aminah</span>
                                        <div class="small text-muted">ID: 1002</div>
                                    </td>
                                    <td>Laboratorium</td>
                                    <td>
                                        <select class="form-select form-select-sm border-warning text-warning fw-bold" onchange="checkStatus(this)">
                                            <option value="tetap">Tetap (Lanjut)</option>
                                            <option value="ganti" selected>Ganti Personil</option>
                                            <option value="stop" class="text-danger">Berhenti (Hapus)</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm">
                                            <option selected>Pilih Pegawai Baru...</option>
                                            <option>Ahmad Dani (Baru)</option>
                                            <option>Rina Nose (Baru)</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr class="table-info bg-opacity-10">
                                    <td class="text-center"><i class="bi bi-plus-circle text-primary"></i></td>
                                    <td colspan="2">
                                        <select class="form-select form-select-sm">
                                            <option selected>-- Pilih Personil Penambahan Baru --</option>
                                            <option>Joko Anwar</option>
                                            <option>Reza Rahadian</option>
                                        </select>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">Penambahan Baru</span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">Batal</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light small text-muted">
                    <i class="bi bi-info-circle"></i> Pilih "Ganti Personil" pada kolom status untuk menukar TLD. Klik tombol "Tambah Personil Baru" di pojok kanan atas untuk menambah kuota.
                </div>
            </div>

            <div class="card shadow-sm mb-5 border-0">
                <div class="card-header bg-light fw-bold py-3">
                    <i class="bi bi-calendar-week-fill me-2 text-primary"></i> Periode Kontrak
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5 border-end">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3">Riwayat Periode (Locked)</h6>

                            <div class="d-flex justify-content-between align-items-center p-2 mb-2 bg-secondary bg-opacity-10 rounded border border-secondary border-opacity-25">
                                <div>
                                    <span class="badge bg-secondary mb-1">Periode 1</span>
                                    <div class="small fw-bold text-dark">01 Jan 2025 - 31 Des 2025</div>
                                </div>
                                <i class="bi bi-lock-fill text-secondary fs-5" title="Tidak dapat dihapus"></i>
                            </div>
                        </div>

                        <div class="col-md-7 ps-md-4">
                            <h6 class="text-uppercase text-primary small fw-bold mb-3">
                                <i class="bi bi-plus-square"></i> Tambah Periode Baru
                            </h6>
                            <div class="p-3 bg-primary bg-opacity-10 rounded border border-primary border-opacity-25">
                                <div class="row g-2">
                                    <div class="col-12 mb-2">
                                        <span class="badge bg-primary">Periode 2 (Next)</span>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Tanggal Mulai Baru</label>
                                        <input type="date" class="form-control" name="new_start_date">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Tanggal Selesai Baru</label>
                                        <input type="date" class="form-control" name="new_end_date">
                                    </div>
                                    <div class="col-12 mt-2 text-muted small fst-italic">
                                        * Periode baru akan otomatis ditambahkan setelah periode terakhir.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fixed-bottom bg-white border-top p-3 shadow-lg" style="z-index: 1000;">
                <div class="container d-flex justify-content-end gap-2">
                    <a href="#" class="btn btn-secondary px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-5 fw-bold">
                        <i class="bi bi-save me-2"></i> Simpan Perubahan Adendum
                    </button>
                </div>
            </div>
            <div style="height: 80px;"></div>

        </form>
    </div>
</div>
@endsection
@push('scripts')
    <script>
    function checkStatus(selectElement) {
        // Logika untuk enable/disable input pengganti
        const row = selectElement.closest('tr');
        const inputPengganti = row.querySelector('td:last-child select, td:last-child input');

        if (selectElement.value === 'ganti') {
            row.classList.add('table-warning', 'bg-opacity-10');
            // Ganti input text jadi select dummy
            row.cells[4].innerHTML = `
                <select class="form-select form-select-sm">
                    <option selected>Pilih Pegawai Baru...</option>
                    <option>Karyawan A</option>
                    <option>Karyawan B</option>
                </select>`;
        } else if (selectElement.value === 'stop') {
            row.classList.remove('table-warning', 'bg-opacity-10');
            row.classList.add('table-danger', 'bg-opacity-10');
            row.cells[4].innerHTML = `<input type="text" class="form-control form-control-sm bg-light" value="-" disabled>`;
        } else {
            row.classList.remove('table-warning', 'bg-opacity-10', 'table-danger');
            row.cells[4].innerHTML = `<input type="text" class="form-control form-control-sm bg-light" value="-" disabled>`;
        }
    }

    function addRow() {
        const tableBody = document.querySelector('#tableTLD tbody');
        const newRow = `
            <tr class="table-info bg-opacity-10">
                <td class="text-center"><i class="bi bi-plus-circle text-primary"></i></td>
                <td colspan="2">
                    <select class="form-select form-select-sm">
                        <option selected>-- Pilih Personil Penambahan Baru --</option>
                        <option>Pegawai Baru 1</option>
                        <option>Pegawai Baru 2</option>
                    </select>
                </td>
                <td><span class="badge bg-primary">Penambahan Baru</span></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">Batal</button></td>
            </tr>
        `;
        tableBody.insertAdjacentHTML('beforeend', newRow);
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
    }
</script>
@endpush

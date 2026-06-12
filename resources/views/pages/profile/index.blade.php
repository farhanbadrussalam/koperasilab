@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content col-md-12">
            <div class="container py-4">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 text-center p-4 position-sticky" style="top: 7rem;">
                            <div class="position-relative d-inline-block mx-auto mb-3">
                                <div id="profile-avatar-container">
                                    {!! renderUserAvatar($profile, false, '120px', 'border border-4 border-white shadow fs-1') !!}
                                </div>
                                <button class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 border border-2 border-white shadow-sm"
                                        title="Ganti Foto" style="width: 35px; height: 35px;" onclick="$('#profile-avatar-input').click()">
                                    <i class="bi bi-camera-fill"></i>
                                </button>
                                @if($profile->profile && $profile->profile->avatar)
                                <button class="btn btn-danger btn-sm rounded-circle position-absolute bottom-0 start-0 border border-2 border-white shadow-sm"
                                        title="Hapus Foto" id="btn-delete-avatar" style="width: 35px; height: 35px;">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                                @endif
                                <input type="file" id="profile-avatar-input" class="d-none" accept="image/png, image/jpeg, image/jpg">
                            </div>
                            <h5 class="fw-bold text-dark mb-1">{{ $profile->name }}</h5>
                            <p class="text-muted small mb-3">{{ $profile->jabatan ?? 'Pelanggan' }}</p>

                            <div class="d-flex justify-content-center gap-2 mb-4">
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                                    <i class="bi bi-check-circle-fill me-1"></i> Akun Aktif
                                </span>
                            </div>

                            <div class="list-group list-group-flush text-start custom-menu">
                                <div id="detail-tab" class="list-group-item list-group-item-action active border-0 rounded-3 mb-1 cursor-pointer" data-bs-toggle="tab" data-bs-target="#detail-tab-pane" role="tab" aria-controls="detail-tab-pane" aria-selected="true">
                                    <i class="bi bi-person me-2"></i> Biodata Diri
                                </div>
                                @if(Auth::user()->hasRole('Pelanggan'))
                                <div id="instansi-tab" class="list-group-item list-group-item-action border-0 rounded-3 mb-1 cursor-pointer" data-bs-toggle="tab" data-bs-target="#instansi-tab-pane" role="tab" aria-controls="instansi-tab-pane" aria-selected="true">
                                    <i class="bi bi-building me-2"></i> Data Instansi
                                </div>
                                @endif
                                <div id="ttd-tab" class="list-group-item list-group-item-action border-0 rounded-3 mb-1 cursor-pointer" data-bs-toggle="tab" data-bs-target="#ttd-tab-pane" role="tab" aria-controls="ttd-tab-pane" aria-selected="true">
                                    <i class="bi bi-file-earmark-text me-2"></i> Tanda Tangan
                                </div>
                                <div id="changepassword-tab" class="list-group-item list-group-item-action border-0 rounded-3 mb-1 text-danger cursor-pointer" data-bs-toggle="tab" data-bs-target="#changepassword-tab-pane" role="tab" aria-controls="changepassword-tab-pane" aria-selected="true">
                                    <i class="bi bi-key me-2"></i> Ganti Password
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="detail-tab-pane" role="tabpanel" aria-labelledby="detail-tab" tabindex="0">
                                <div class="card border-0 shadow-sm rounded-4 mb-0">
                                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                                        <h6 class="m-0 fw-bold text-dark">Informasi Biodata</h6>
                                        <button class="btn btn-outline-primary btn-sm rounded-pill px-3" id="btnEnableEdit">
                                            <i class="bi bi-pencil-square me-1"></i> Edit Profil
                                        </button>
                                    </div>

                                    <div class="card-body p-4">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label small text-muted fw-bold text-uppercase">Email Akun</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                                    <input type="text" class="form-control bg-light" id="email_pic" readonly>
                                                </div>
                                            </div>

                                            <form id="form-biodata" class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted fw-bold">NIK <span class="fw-bold fs-14 text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="nik_pic" id="nik_pic"
                                                        data-parsley-required-message="NIK harus diisi"
                                                        data-parsley-errors-container="#message-nik-biodata"
                                                        disabled required>
                                                    <div id="message-nik-biodata" class="invalid-feedback d-block"></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted fw-bold">Nama Lengkap PIC</label>
                                                    <input type="text" class="form-control" name="nama_pic" id="nama_pic" disabled>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted fw-bold">Jabatan</label>
                                                    <input type="text" class="form-control" name="jabatan_pic" id="jabatan_pic" disabled>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted fw-bold">Jenis Kelamin</label>
                                                    <select name="jenis_kelamin" id="jenis_kelamin" class="form-select me-2" disabled>
                                                        <option value="laki-laki">Laki-laki</option>
                                                        <option value="perempuan">Perempuan</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-12">
                                                    <label class="form-label small text-muted fw-bold">No. Telepon / WA <span class="fw-bold fs-14 text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="telepon" id="telepon"
                                                        data-parsley-errors-container="#message-telepon-biodata"
                                                        data-parsley-required-message="Telepon harus diisi" required disabled>
                                                    <div id="message-telepon-biodata" class="invalid-feedback d-block"></div>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label small text-muted fw-bold">Alamat Domisili</label>
                                                    <textarea class="form-control" name="alamat_pic" id="alamat_pic" rows="3" disabled>-</textarea>
                                                </div>
                                            </form>

                                            <div class="d-none justify-content-end gap-2 mt-4 pt-3 border-top" id="actionButtons">
                                                <button type="button" class="btn btn-light" id="btnCancelEdit">Batal</button>
                                                <button type="submit" class="btn btn-primary px-4" onclick="simpanPerubahanBiodata(this)">Simpan Perubahan</button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="tab-pane fade show" id="instansi-tab-pane" role="tabpanel" aria-labelledby="instansi-tab" tabindex="0">
                                <div class="row">
                                    @if(Auth::user()->hasRole('Pelanggan'))
                                    <div class="col-12 mb-2">
                                        <div class="p-3 bg-light rounded-3 border border-dashed">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label small fw-bold mb-0">Dokumen Surat Kuasa</label>
                                            </div>

                                            <div id="uploadSuratKuasa" class="bg-white p-2 rounded"></div>
                                        </div>
                                    </div>
                                    @endif

                                    <div class="col-12" id="card-instansi-nonaktif" style="display: none;">
                                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                                            <div class="card-body p-5 text-center">
                                                <div class="mb-4" id="icon-status-instansi">
                                                    <i class="bi bi-hourglass-split text-warning" style="font-size: 4rem;"></i>
                                                </div>
                                                <h4 class="fw-bold text-dark mb-2" id="title-status-instansi">Proses Verifikasi</h4>
                                                <p class="text-muted mb-4" id="desc-status-instansi">
                                                    Data instansi Anda sedang dalam proses verifikasi oleh tim kami. Silakan tunggu beberapa saat.
                                                </p>
                                                <div id="action-status-instansi"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12" id="card-form-instansi" style="display: none;">
                                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                                            <div class="card-header bg-white py-3 border-bottom rounded-top-4 d-flex justify-content-between">
                                                <h6 class="m-0 fw-bold text-dark">
                                                    <i class="bi bi-building-add me-2 text-primary"></i>Pengajuan Instansi Baru
                                                </h6>
                                                <button class="btn btn-sm btn-light text-danger border-0" onclick="batalPengajuan()">Batal</button>
                                            </div>
                                            <div class="card-body p-4">
                                                <form id="form-pengajuan-instansi" novalidate>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <div class="form-check">
                                                                <input type="radio" class="form-check-input" id="p_baru_profile" name="pelanggan_tipe" value="baru" required data-parsley-errors-container="#pelanggan_tipe_error_profile" data-parsley-required-message="Silakan pilih tipe pelanggan terlebih dahulu">
                                                                <label class="form-check-label" for="p_baru_profile">Pelanggan Baru</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check">
                                                                <input type="radio" class="form-check-input" id="p_lama_profile" name="pelanggan_tipe" value="lama">
                                                                <label class="form-check-label" for="p_lama_profile">Pelanggan Lama</label>
                                                            </div>
                                                        </div>
                                                        <div id="pelanggan_tipe_error_profile" class="col-12 mt-1"></div>
                                                        
                                                        <div class="col-12" id="section-instansi-profile" style="display: none;">
                                                            <label for="nama_instansi_baru_profile" class="form-label small fw-bold text-uppercase text-muted">Nama Instansi <span class="text-danger">*</span></label>
                                                            
                                                            <!-- Input Teks untuk Pelanggan Baru -->
                                                            <input type="text" class="form-control" id="nama_instansi_baru_profile" name="nama_instansi" required data-parsley-required-message="Nama instansi harus diisi">

                                                            <!-- Select2 untuk Pelanggan Lama -->
                                                            <select class="form-select" id="nama_instansi_lama_profile" style="display: none;" data-parsley-required-message="Pilih instansi Anda" name="nama_instansi_lama"></select>
                                                        </div>
                                                        
                                                        <div id="form-instansi-detail-profile" class="col-12 m-0 p-0" style="display: none;">
                                                            <div class="row g-3 mt-0">
                                                                <div class="col-md-6">
                                                                    <label for="email_instansi_profile" class="form-label small fw-bold text-uppercase text-muted">Email Instansi <span class="text-danger">*</span></label>
                                                                    <input type="email" class="form-control maskEmail instansi-detail-input-profile" id="email_instansi_profile" name="email_instansi" data-parsley-errors-container="#email_instansi_error_profile">
                                                                    <div id="email_instansi_error_profile" class="invalid-feedback d-block"></div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="npwp_profile" class="form-label small fw-bold text-uppercase text-muted">NPWP <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control maskNPWP instansi-detail-input-profile" id="npwp_profile" name="npwp">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="kota_profile" class="form-label small fw-bold text-uppercase text-muted">Kota <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control instansi-detail-input-profile" id="kota_profile" name="kota">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="kode_pos_profile" class="form-label small fw-bold text-uppercase text-muted">Kode Pos <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control maskNumber instansi-detail-input-profile" id="kode_pos_profile" name="kode_pos">
                                                                </div>
                                                                <div class="col-12">
                                                                    <label for="alamat_instansi_profile" class="form-label small fw-bold text-uppercase text-muted">Alamat Instansi <span class="text-danger">*</span></label>
                                                                    <textarea name="alamat_instansi" id="alamat_instansi_profile" rows="2" class="form-control instansi-detail-input-profile"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-12 mt-4 text-end" id="action-pengajuan" style="display: none;">
                                                            <button type="button" class="btn btn-light me-2" onclick="batalPengajuan()">Batal</button>
                                                            <button type="button" class="btn btn-primary px-4" onclick="ajukanInstansi(this)">Ajukan Instansi</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="card-instansi-aktif">
                                        <div class="col-12">
                                            <div class="card border-0 shadow-sm rounded-4">
                                                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                                                    <h6 class="m-0 fw-bold text-dark">
                                                        <i class="bi bi-building me-2 text-primary"></i>Identitas Perusahaan
                                                    </h6>
                                                    @if (Auth::user()->status == 1)
                                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btnEditInstansi">
                                                        <i class="bi bi-pencil-square me-1"></i> Edit Data
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3" id="btnBackInstansi" style="display: none;">
                                                        <i class="bi bi-x-circle me-1"></i> Batal
                                                    </button>
                                                    @endif
                                                </div>

                                                <div class="card-body p-4">
                                                    <div class="d-flex align-items-center mb-4 p-3 bg-primary-subtle rounded-3 border border-primary-subtle">
                                                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                                                            <i class="bi bi-qr-code"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <small class="text-primary fw-bold text-uppercase d-block">Kode Instansi</small>
                                                            <span class="fs-5 fw-bold text-dark" id="kode_instansi"></span>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-light text-primary" onclick="copyKode('kode_instansi')" title="Salin Kode">
                                                            <i class="bi bi-clipboard"></i>
                                                        </button>
                                                    </div>

                                                    <form id="form-instansi" novalidate>

                                                        <div class="row g-3">
                                                            <div class="col-md-6 mt-1">
                                                                <label class="form-label small fw-bold text-muted">Nama Instansi <span class="fw-bold fs-14 text-danger">*</span></label>
                                                                <input type="text" class="form-control" id="nama_perusahaan" name="nama_perusahaan" placeholder=""
                                                                            data-parsley-required-message="Nama instansi harus diisi"
                                                                            data-parsley-errors-container="#message-nama_perusahaan"
                                                                            required disabled>
                                                                <div id="message-nama_perusahaan" class="invalid-feedback d-block"></div>
                                                            </div>

                                                            <div class="col-md-6 mt-1">
                                                                <label class="form-label small fw-bold text-muted">NPWP <span class="fw-bold fs-14 text-danger">*</span></label>
                                                                <input type="text" class="form-control me-2 maskNPWP" id="npwp" name="npwp_perusahaan"
                                                                    data-parsley-errors-container="#message-npwp"
                                                                    data-parsley-required-message="NPWP harus diisi"
                                                                    required disabled autocomplete="true">
                                                                <div id="message-npwp" class="invalid-feedback d-block"></div>
                                                            </div>

                                                            <div class="col-12 mt-1">
                                                                <label class="form-label small fw-bold text-muted">Email Resmi <span class="fw-bold fs-14 text-danger">*</span></label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                                                                    <input type="email" class="form-control me-2" id="email" name="email" placeholder=""
                                                                        data-parsley-errors-container="#message-email"
                                                                        data-parsley-required-message="Email harus diisi"
                                                                        required
                                                                        disabled>
                                                                </div>
                                                                <div id="message-email" class="invalid-feedback d-block"></div>
                                                            </div>
                                                        </div>

                                                    </form>

                                                    <div class="mt-4 pt-3 border-top d-flex gap-2">
                                                        <button id="btnSimpanInstansi" class="btn btn-primary w-100" type="button" onclick="simpanPerubahanInstansi()" disabled>Simpan Perubahan Data</button>
                                                        @if (Auth::user()->status == 1)
                                                        <button type="button" class="btn btn-light text-primary border-primary-subtle w-100" onclick="openModalPic()">
                                                            <i class="bi bi-person-gear me-1"></i> Ganti PIC
                                                        </button>
                                                        <button type="button" class="btn btn-light text-secondary border-secondary-subtle w-100" onclick="openModalHistoryPic()">
                                                            <i class="bi bi-clock-history me-1"></i> History PIC
                                                        </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12" id="card-upload-stempel">
                                            <div class="card border-0 shadow-sm rounded-4">
                                                <div class="card-header bg-white py-3 border-bottom rounded-top-4">
                                                    <h6 class="m-0 fw-bold text-dark">
                                                        <i class="bi bi-upload me-2 text-primary"></i>Upload Stempel
                                                    </h6>
                                                </div>
                                                <div class="card-body p-2 px-4">
                                                    <div class="col-md-12">
                                                        <div id="upload-stempel"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- div untuk KOP Surat instansi --}}
                                        <div class="col-12" id="card-kop-surat">
                                            <div class="card border-0 shadow-sm rounded-4">
                                                <div class="card-header bg-white py-3 border-bottom rounded-top-4 d-flex justify-content-between align-items-center">
                                                    <h6 class="m-0 fw-bold text-dark">
                                                        <i class="bi bi-envelope-fill me-2 text-danger"></i>KOP Surat
                                                    </h6>
                                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btnTambahKopSurat">
                                                        <i class="bi bi-plus-circle me-1"></i> Tambah
                                                    </button>
                                                </div>

                                                <div class="card-body p-3">
                                                    <div class="col-md-12">
                                                        <table class="table table-borderless" id="table-kop-surat">
                                                            <tbody id="tbody-kop-surat">
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <div class="col-md-12">
                                                        {{-- Pagination --}}
                                                        <div class="d-flex justify-content-end">
                                                            <div id="pagination-kop-surat"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12" id="card-detail-lokasi">
                                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                                <div class="card-header bg-white py-3 border-bottom rounded-top-4">
                                                    <h6 class="m-0 fw-bold text-dark">
                                                        <i class="bi bi-geo-alt-fill me-2 text-danger"></i>Detail Lokasi
                                                    </h6>
                                                </div>

                                                <div class="card-body p-4">
                                                    <div class="col-md-12" id="list-alamat"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade show" id="ttd-tab-pane" role="tabpanel" aria-labelledby="ttd-tab" tabindex="0">
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                                        <h6 class="m-0 fw-bold text-dark">
                                            <i class="bi bi-pen-fill me-2 text-primary"></i>Update Tanda Tangan
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-outline-danger d-none" id="btn-hapus-ttd">
                                            <i class="bi bi-eraser me-1"></i> Bersihkan
                                        </button>
                                    </div>

                                    <div class="card-body p-4">
                                        <div class="row g-4">

                                            <div class="col-md-8">
                                                <label class="form-label small fw-bold text-muted mb-2">Area Tanda Tangan</label>

                                                <div class="signature-wrapper position-relative border rounded-3 bg-light overflow-hidden"
                                                    style="height: 300px; cursor: crosshair; width: 444px;">

                                                    <div id="show-ttd" class="w-100 h-100"></div>

                                                    <div class="position-absolute top-50 start-0 w-100 border-top border-secondary opacity-25" style="border-style: dashed !important; pointer-events: none;"></div>

                                                    <div class="position-absolute bottom-0 start-50 translate-middle-x mb-2 text-muted opacity-50 small user-select-none" style="pointer-events: none;">
                                                        Tanda tangan di sini
                                                    </div>
                                                </div>

                                                <div class="form-text small mt-2">
                                                    <i class="bi bi-info-circle me-1"></i> Gunakan mouse atau jari (layar sentuh) untuk tanda tangan.
                                                </div>
                                            </div>

                                            <div class="col-md-4 d-flex flex-column">
                                                <label class="form-label small fw-bold text-muted mb-2">Tanda Tangan Saat Ini</label>

                                                <div class="border rounded-3 p-3 bg-white d-flex align-items-center justify-content-center flex-grow-1 text-center position-relative mb-3"
                                                    style="min-height: 200px; background-image: radial-gradient(#dee2e6 1px, transparent 1px); background-size: 20px 20px;">

                                                    <div id="show-ttd-preview d-none" >
                                                        <div id="ttd-preview"></div>
                                                        <span class="badge bg-success position-absolute top-0 end-0 m-2 shadow-sm">Aktif</span>
                                                    </div>

                                                    <div class="text-muted opacity-50" id="empty-ttd-preview">
                                                        <i class="bi bi-x-circle fs-1 d-block mb-2"></i>
                                                        <small>Belum ada data</small>
                                                    </div>
                                                </div>

                                                <input type="hidden" name="signature_data" id="signature-data">

                                                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" id="btn-upload-ttd">
                                                    Simpan
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade show" id="changepassword-tab-pane" role="tabpanel" aria-labelledby="changepassword-tab" tabindex="0">
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-header bg-white py-3 border-bottom rounded-top-4">
                                        <h6 class="m-0 fw-bold text-dark">
                                            <i class="bi bi-shield-lock me-2 text-danger"></i>Keamanan Akun
                                        </h6>
                                    </div>

                                    <div class="card-body p-4">
                                        <div class="alert alert-light border-start border-4 border-info shadow-sm mb-4">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-info-circle-fill text-info fs-4 me-3"></i>
                                                <div>
                                                    <small class="text-muted d-block fw-bold">Amankan akun Anda</small>
                                                    <small class="text-muted" style="font-size: 0.8rem;">Gunakan kombinasi huruf besar, kecil, dan angka agar password sulit ditebak.</small>
                                                </div>
                                            </div>
                                        </div>

                                        <form id="form-change-password" novalidate>
                                            <div class="mb-4 bg-light p-3 rounded-3 border">
                                                <label class="form-label small fw-bold text-muted">Password Saat Ini <span class="fw-bold fs-14 text-danger">*</span></label>

                                                <div class="input-group mb-2 mt-1">
                                                    <input
                                                        class="form-control form-control input-login"
                                                        id="old_password"
                                                        type="password"
                                                        name="old_password"
                                                        required
                                                        data-parsley-required-message="Password Lama harus diisi"
                                                        data-parsley-errors-container="#error-old-password"
                                                        placeholder="Enter your old password" />
                                                    <button class="input-group-text btn border-start-0 border" onclick="showPassword(this)">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="invalid-feedback d-block" id="error-old-password"></div>
                                            </div>

                                            <hr class="my-4 opacity-10">

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted">Password Baru <span class="fw-bold fs-14 text-danger">*</span></label>

                                                    <div class="input-group">
                                                        <input
                                                            class="form-control form-control input-login"
                                                            id="new_password"
                                                            type="password"
                                                            name="new_password"
                                                            required
                                                            data-parsley-trigger="input"
                                                            data-parsley-minlength="8"
                                                            data-parsley-lowercase="true"
                                                            data-parsley-uppercase="true"
                                                            data-parsley-errors-container="#error-new-password"
                                                            placeholder="Enter your new password" />
                                                        <button class="input-group-text btn border-start-0 border" onclick="showPassword(this)">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </div>
                                                    <div class="progress mt-2" style="height: 4px;">
                                                        <div class="progress-bar bg-danger" id="strengthBar" role="progressbar" style="width: 0%"></div>
                                                    </div>
                                                    <ul id="password-rules" class="p-3 bg-light rounded-3 mt-2">
                                                    </ul>
                                                    <div class="invalid-feedback d-none" id="error-new-password"></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted">Konfirmasi Password <span class="fw-bold fs-14 text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input
                                                            class="form-control form-control input-login"
                                                            id="confirm_password"
                                                            type="password"
                                                            name="confirm_password"
                                                            required
                                                            data-parsley-trigger="input"
                                                            data-parsley-equalto="#new_password"
                                                            data-parsley-equalto-message="Konfirmasi password tidak sama."
                                                            data-parsley-required-message="Konfirmasi password wajib diisi."
                                                            data-parsley-errors-container="#error-confirm-password"
                                                            placeholder="Enter your confirm password" />
                                                        <button class="input-group-text btn border-start-0 border" onclick="showPassword(this)">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </div>
                                                    <div class="invalid-feedback d-block" id="error-confirm-password"></div>
                                                </div>
                                            </div>
                                        </form>
                                        <div class="d-flex justify-content-end mt-4">
                                            <button type="submit" class="btn btn-primary px-4" onclick="gantiPassword(this)">
                                                Update Password
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="modal-kop-surat" tabindex="-1" aria-labelledby="kopModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-white border-bottom px-4 py-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10" style="width:36px;height:36px;">
                            <i class="bi bi-file-earmark-richtext text-primary fs-5"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold text-dark mb-0" id="kopModalLabel">Kop Surat</h6>
                            <small class="text-muted">Atur tampilan kop surat dokumen</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <input type="hidden" id="kop_surat_id">

                    <div class="row g-3">
                        {{-- Nama --}}
                        <div class="col-12">
                            <label for="nama_kop_surat" class="form-label small fw-bold text-muted text-uppercase">
                                <i class="bi bi-tag me-1"></i> Nama Kop Surat
                            </label>
                            <input type="text" name="nama_kop_surat" id="nama_kop_surat"
                                class="form-control rounded-3"
                                placeholder="Masukkan nama kop surat..." required>
                        </div>

                        {{-- Status Aktif --}}
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">
                                <i class="bi bi-toggle-on me-1"></i> Status
                            </label>
                            <div class="d-flex gap-3">
                                <label for="active_kop_surat_1"
                                    class="d-flex align-items-center gap-2 px-4 py-2 rounded-3 border"
                                    style="cursor:pointer;">
                                    <input class="form-check-input m-0" type="radio" name="active_kop_surat"
                                        id="active_kop_surat_1" value="1" checked>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i> Aktif
                                    </span>
                                </label>
                                <label for="active_kop_surat_0"
                                    class="d-flex align-items-center gap-2 px-4 py-2 rounded-3 border"
                                    style="cursor:pointer;">
                                    <input class="form-check-input m-0" type="radio" name="active_kop_surat"
                                        id="active_kop_surat_0" value="0">
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                        <i class="bi bi-dash-circle me-1"></i> Tidak Aktif
                                    </span>
                                </label>
                            </div>
                        </div>

                        {{-- Content / Editor --}}
                        <div class="col-12">
                            <label for="content_kop_surat" class="form-label small fw-bold text-muted text-uppercase">
                                <i class="bi bi-pencil-square me-1"></i> Konten Kop Surat
                            </label>
                            <div class="alert alert-info border-0 rounded-3 py-2 px-3 d-flex align-items-center gap-2 mb-2" style="font-size:0.82rem;">
                                <i class="bi bi-info-circle-fill text-info flex-shrink-0"></i>
                                <span>Disarankan menggunakan <strong>gambar dengan resolusi 761 &times; 134 px</strong> untuk tampilan terbaik.</span>
                            </div>
                            <div class="rounded-3 overflow-hidden border">
                                <textarea name="content_kop_surat" id="content_kop_surat" class="form-control border-0 rounded-0" rows="6" required></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top px-4 py-3 d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-shield-check text-success me-1"></i> Data akan disimpan secara aman
                    </small>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                            <i class="bi bi-x me-1"></i> Batal
                        </button>
                        <button class="btn btn-primary rounded-pill px-4" id="btnSimpanKopSurat" onclick="simpanKopSurat(this)">
                            <i class="bi bi-save2 me-1"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('pages.profile.component.modal_pic')
@endsection
@push('scripts')
    <script>
        @if ($errors->any())
            editProfile($('#btnEditProfile'));
        @endif

        let profile = @json($profile);
        const isPassword = {{ $isPassword ? 1 : 0 }};
        function editProfile(obj) {
            const name = $('#inputName');
            const nik = $('#inputNik');
            const email = $('#inputEmail');
            const nomer = $('#inputNomer');
            const jenisKelamin = $('#inputJenisKelamin');
            const alamat = $('#inputAlamat');

            name.removeAttr('readonly');
            nik.removeAttr('readonly');
            email.removeAttr('readonly');
            nomer.removeAttr('readonly');
            jenisKelamin.removeAttr('disabled');
            alamat.removeAttr('readonly');

            name.focus();

            $(obj).addClass('d-none');
            $('#actionBtnProfile').removeClass('d-none');
        }
    </script>
    <script src="{{ asset('js/profile.js') }}"></script>
    <script src="{{ asset('js/kop_surat.js') }}"></script>
@endpush

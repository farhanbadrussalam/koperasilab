@extends('layouts.main')

@section('content')
    <div class="content-wrapper row">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item px-3">
                <a href="{{ $_SERVER['HTTP_REFERER'] }}" class="icon-link text-danger"><i
                        class="bi bi-chevron-left fs-3 fw-bolder h-100"></i> Kembali</a>
            </li>
        </ul>
        <div class="col-md-9 p-2">
            <div class="card p-0 m-0 shadow border-0">
                <div class="card-body">
                    <div class="row">
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama_header" class="form-control" id="nama_header"
                                value="{{ $data->name ?? '' }}" required {{ $type == 'body' ? 'readonly' : '' }}>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Variables (huruf BESAR, pisahkan koma)</label>
                            @php $vars = $data->variables ?? []; @endphp
                            <textarea id="varsInput" class="form-control">{{ implode(',', $vars) }}</textarea>
                            <input type="hidden" name="variables[]" id="varsHidden">
                            <small class="text-muted">Contoh: NAMA,NO_VA,TANGGAL,ALAMAT</small>
                        </div>
                        {{-- Header --}}
                        <div class="mb-3 {{ $type != 'body' ? 'd-none' : '' }}" id="headerDiv">
                            <label for="header" class="form-label">Header</label>
                            <select name="header" id="header" class="form-select">
                                <option value="">No Header</option>
                                @foreach ($headers as $h)
                                    <option value="{{ $h->doc_hash }}"
                                        {{ isset($data) && $data->header_hash == $h->doc_hash ? 'selected' : '' }}>
                                        {{ $h->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content {{ $type }}</label>
                            <div class="form-floating">
                                <textarea id="header_html" name="header_html" class="form-control" rows="6">{!! $data->content ?? '' !!}</textarea>
                            </div>
                        </div>
                        <div class="mb-3 {{ $type != 'body' ? 'd-none' : '' }}">
                            <label class="form-label">Nomor Formulir</label>
                            <input type="text" name="no_formulir" class="form-control" id="no_formulir"
                                value="{{ $data->no_formulir ?? '' }}" placeholder="F-7.1.01.08-24/NL, Rev.1, Feb.2022">
                        </div>
                        {{-- Footer --}}
                        <div class="mb-3 {{ $type != 'body' ? 'd-none' : '' }}" id="footerDiv">
                            <label for="footer" class="form-label">Footer</label>
                            <select name="footer" id="footer" class="form-select">
                                <option value="">No Footer</option>
                                @foreach ($footers as $f)
                                    <option value="{{ $f->doc_hash }}"
                                        {{ isset($data) && $data->footer_hash == $f->doc_hash ? 'selected' : '' }}>
                                        {{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" onclick="simpanHeader(this)">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const type = `{{ $type }}`;
        const _data = @json($data ?? []);
        let vars = @json($vars ?? []);
    </script>
    <script src="{{ asset_versioned('js/management/document_tambah.js') }}"></script>
@endpush

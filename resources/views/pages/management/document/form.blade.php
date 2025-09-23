@extends('layouts.app')

@section('content')
<div class="container">
  <h1>{{ $doc->exists ? 'Edit' : 'Buat' }} Master Document</h1>

  <form method="post" action="{{ $doc->exists ? route('master-documents.update', $doc) : route('master-documents.store') }}">
    @csrf
    @if($doc->exists) @method('PUT') @endif

    <div class="mb-3">
      <label class="form-label">Nama Template</label>
      <input name="name" class="form-control" value="{{ old('name', $doc->name) }}" required>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Paper size</label>
        <select name="paper_size" class="form-select">
          @foreach(['A4','Letter','Legal'] as $ps)
            <option value="{{ $ps }}" @selected(old('paper_size',$doc->paper_size)===$ps)>{{ $ps }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Orientation</label>
        <select name="orientation" class="form-select">
          @foreach(['portrait','landscape'] as $o)
            <option value="{{ $o }}" @selected(old('orientation',$doc->orientation)===$o)>{{ ucfirst($o) }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Variables (huruf BESAR, pisahkan koma)</label>
      @php $vars = old('variables', $doc->variables ?? []); @endphp
      <input id="varsInput" class="form-control" value="{{ implode(',', $vars) }}">
      <input type="hidden" name="variables[]" id="varsHidden">
      <small class="text-muted">Contoh: NAMA,NO_VA,TANGGAL,ALAMAT</small>
    </div>

    <div class="mb-3">
      <label class="form-label">Header (opsional)</label>
      <textarea id="header_html" name="header_html" class="form-control" rows="6">{{ old('header_html', $doc->header_html) }}</textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Body</label>
      <textarea id="body_html" name="body_html" class="form-control" rows="12" required>{{ old('body_html', $doc->body_html) }}</textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Footer (opsional)</label>
      <textarea id="footer_html" name="footer_html" class="form-control" rows="6">{{ old('footer_html', $doc->footer_html) }}</textarea>
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-primary">{{ $doc->exists ? 'Simpan' : 'Buat' }}</button>
      @if($doc->exists)
        <a class="btn btn-outline-secondary"
           href="{{ route('master-documents.preview', $doc) }}?NAMA=Farhan&NO_VA=8902&TANGGAL={{ now()->format('d/m/Y') }}&ALAMAT=Bandung"
           target="_blank">Preview PDF</a>
      @endif
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
  // CKEditor init with mentions as "merge fields"
  const vars = (@json($vars)).filter(Boolean);

  function mkEditor(selector){
    return ClassicEditor.create(document.querySelector(selector), {
      toolbar: ['heading','bold','italic','link','bulletedList','numberedList','insertTable','undo','redo'],
      mention: {
        // gunakan marker '{{' agar saat ketik '{{' akan muncul daftar variable
        // (CKEditor 5 mendukung custom marker & limit dropdown)
        feeds: [{
          marker: '{{',
          feed: (queryText) => {
            const q = (queryText || '').toLowerCase();
            return vars
              .filter(v => v.toLowerCase().startsWith(q))
              .map(v => `{{${v}}}`);
          },
          minimumCharacters: 0,
          dropdownLimit: 50
        }]
      }
    });
  }

  Promise.all([mkEditor('#header_html'), mkEditor('#body_html'), mkEditor('#footer_html')]);

  // simpan variables[] dari input text
  const varsInput = document.getElementById('varsInput');
  const varsHidden = document.getElementById('varsHidden');
  function syncVars(){
    const items = (varsInput.value || '').split(',').map(s => s.trim()).filter(Boolean);
    varsHidden.setAttribute('name','variables'); // kirim sebagai array di Laravel
    varsHidden.value = JSON.stringify(items);
  }
  varsInput.addEventListener('input', syncVars);
  syncVars();
</script>
@endpush

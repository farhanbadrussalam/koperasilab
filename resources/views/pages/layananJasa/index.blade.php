@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Layanan Jasa</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content col-xl-5 col-md-12">
        <div class="container">
            <div class="card card-default color-palette-box shadow">
                <div class="card-header d-flex ">
                    <h3 class="card-title flex-grow-1">
                      Layanan jasa
                    </h3>
                    @can('Layananjasa.create')
                    <a href="{{ route('layananJasa.create') }}" class="btn btn-primary btn-sm">Add Layanan</a>
                    @endcan
                </div>
                <div class="card-body">
                    <table class="table table-hover w-100" id="layanan-table">
                        <thead>
                            <th width="5%">No</th>
                            <th>Nama Layanan</th>
                            <th width="20%">Action</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modalJenisLayanan" tabindex="-1" aria-labelledby="modalJenisLayanan" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-body">
            <div class="row fw-bolder">
                <h4 class="col-6">Jenis Layanan</h4>
                <h4 class="col-6">Tarif</h4>
            </div>
          <div id="isi-jenislayanan" class="px-2">

          </div>
          <div class="mt-2 text-center w-100">
              <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
</div>
@endsection
@push('scripts')
    <script>
        let datatable_layanan = false;
        $(function () {
            datatable_layanan = $('#layanan-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('layananJasa.getData') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
                    { data: 'nama_layanan', name: 'nama_layanan' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });
        });

        function btnDelete(id) {
            deleteGlobal(() => {
                $.ajax({
                    url: "{{ url('/api/deletePegawai') }}?id="+id,
                    method: 'DELETE',
                    dataType: 'json',
                    processData: true,
                    headers: {
                        'Authorization': `Bearer {{ $token }}`,
                        'Content-Type': 'application/json'
                    }
                }).done((result) => {
                    if(result.message){
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: result.message
                        });
                        datatable_layanan?.ajax.reload();
                    }
                }).fail(function(message) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: message.responseJSON.message
                    });
                });
            });
        }

        function showJenis(obj) {
            const arrJenis = $(obj).data('jenis');
            let html = '';
            for (const data of arrJenis) {
                let tarif = formatRupiah(data.tarif)
                html += `
                    <div class="row mb-1">
                        <span class="col-6">${data.jenis}</span>
                        <span class="col-6">${tarif}</span>
                    </div>
                `;
            }
            $('#isi-jenislayanan').html(html);
            $('#modalJenisLayanan').modal('show');
        }
    </script>
@endpush

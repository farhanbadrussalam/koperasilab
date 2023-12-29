@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('jadwal.index') }}">Jadwal</a></li>
                        <li class="breadcrumb-item active">List Permohonan</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content col-md-12">
        <div class="container">
            <div class="card card-default color-palette-box shadow">
                <div class="card-body">
                    <div class="container" id="attribute">
                        <div class="row align-items-start">
                            <div class="col-md-4">
                                <label for="">Nama layanan</label>
                                <p>{{ $jadwal->layananjasa->nama_layanan }}</p>
                            </div>
                            <div class="col-md-4">
                                <label for="">Jenis layanan</label>
                                <p>{{ $jadwal->jenislayanan }}</p>
                            </div>
                            <div class="col-md-4">
                                <label for="">Tarif</label>
                                <p>{{ formatCurrency($jadwal->tarif) }}</p>
                            </div>
                            <div class="col-md-4">
                                <label for="">Kuota</label>
                                <p>{{ $jadwal->kuota }}</p>
                            </div>
                            <div class="col-md-4">
                                <label for="">Tanggal mulai</label>
                                <p>{{ convert_date($jadwal->date_mulai) }}</p>
                            </div>
                            <div class="col-md-4">
                                <label for="">Tanggal selesai</label>
                                <p>{{ convert_date($jadwal->date_selesai) }}</p>
                            </div>
                            <div class="col-md-12">
                                <label for="">Penanggung jawab</label>
                                <p>{{ $jadwal->layananjasa->user->name }}</p>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-header d-flex border-0">
                    <h3 class="card-title flex-grow-1">
                        List Permohonan
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table w-100" id="permohonan-table">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="40%">Customer</th>
                                <th width="15%">No Bapeten</th>
                                <th width="15%">Antrian</th>
                                <th width="15%">Jumlah</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>
            </div>
        </div>
    </section>
</div>
@include('pages.permohonan.confirm')
@include('pages.keuangan.modalinvoice')
@include('pages.jadwal.info')
@include('pages.jadwal.addPetugas')
@include('pages.jadwal.changePetugas')
@endsection
@push('scripts')
    <script>
        let dt_permohonan = false;
        let d_jadwal = @json($jadwal);
        const pegawai = @json($pegawai);

        $(function(){
            dt_permohonan = $('#permohonan-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ordering: false,
                lengthChange: false,
                infoCallback: function( settings, start, end, max, total, pre ) {
                    var api = this.api();
                    var pageInfo = api.page.info();

                    return 'Page '+ (pageInfo.page+1) +' of '+ pageInfo.pages;
                },
                ajax: {
                    url: "{{ route('penugasan.dataPermohonan') }}",
                    data: function(d) {
                        d.idJadwal = d_jadwal.jadwal_hash
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, className: 'align-middle text-center' },
                    { data: 'customer', name: 'customer', className: 'align-middle'},
                    { data: 'no_bapeten', name: 'no_bapeten', className: 'align-middle text-center'},
                    { data: 'nomor_antrian', name: 'nomor_antrian', className: 'align-middle text-center'},
                    { data: 'jumlah', name: 'jumlah', className: 'align-middle text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'align-middle text-center' }
                ]
            })
        })

        function addPetugas() {
            $.ajax({
                method: 'GET',
                url: "{{ url('api/petugas/getJadwalPetugas/'.$jadwal->jadwal_hash) }}",
                dataType: "JSON",
                processData: true,
                headers: {
                    'Authorization': `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                }
            }).done(function(result){
                let arrResult = [];
                console.log(result.data);
                for (const data of pegawai) {
                    let find = result.data.find(f => f.petugas.user_hash == data.petugas.user_hash);

                    if(!find){
                        arrResult.push({
                            id: data.petugas.user_hash,
                            text: data.petugas.name,
                            title: stringSplit(data.otorisasi[0].name, 'Otorisasi-')
                        });
                    }
                }
                $('#selectPetugas').select2({
                    theme: "bootstrap-5",
                    placeholder: "Select petugas",
                    templateResult: formatSelect2Staff,
                    dropdownParent: $('#addPetugas'),
                    data: arrResult
                });

                $('#addPetugas').modal('show');
            })
        }

    </script>
@endpush

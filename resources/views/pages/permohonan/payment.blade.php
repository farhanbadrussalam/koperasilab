@extends('layouts.main')

@section('content')
<div class="content-wrapper mt-4 mt-sm-2 mt-md-1 mt-xl-0">
    <section class="content col-md-12">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.index') }}">Pemohonan</a></li>
                        <li class="breadcrumb-item active">Payment</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content col-md-12">
        <div class="row d-flex">
            <div class="col-md-8 col-sm-12 pe-md-1 p-sm-0">
                <div class="card card-default shadow bg-white">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <h3 class="card-title fw-bolder mb-3">Detail permohonan</h3>
                            </div>
                            <div class="col-6">
                                <div class="fw-bolder">Nama Layanan</div>
                                <div><span id="txtNamaLayananModal">{{ $permohonan->layananjasa->nama_layanan }}</span></div>
                            </div>
                            <br>
                            <div class="col-6">
                                <div class="fw-bolder">Customer</div>
                                <div><span id="txtNamaPelangganModal">{{ $permohonan->user->name }}</span></div>
                            </div>
                            <div class="col-6">
                                <div class="fw-bolder">Email</div>
                                <div><span id="txtAlamatModal">{{ $permohonan->user->email }}</span></div>
                            </div>
                        </div>
                        <hr>
                        <h3>Rincian : </h3>
                        <table class="table table-borderless" id="rincian-table-kip">
                            <tbody>
                                <tr>
                                    <td>{{ $permohonan->jenis_layanan }} x {{ $permohonan->jumlah }}</td>
                                    <td>{{ formatCurrency($permohonan->tbl_kip->harga) }}</td>
                                </tr>
                                <tr>
                                    <td>PPN 11%</td>
                                    <td>{{ formatCurrency($permohonan->tbl_kip->pajak) }}</td>
                                </tr>
                                <tr>
                                    <th class="w-75">Jumlah</th>
                                    <th>{{ formatCurrency($permohonan->tbl_kip->harga + $permohonan->tbl_kip->pajak) }}</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-12 ps-md-1 p-sm-0">
                <div class="card card-default shadow bg-white">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <h3 class="card-title fw-bolder">Payment Method</h3>
                            </div>
                            <div class="col-12 mb-2">
                                <input type="radio" class="btn-check" name="options-base" onclick="payMethod('other')" id="option-other" autocomplete="off" checked>
                                <label class="btn" for="option-other">Other</label>

                                <input type="radio" class="btn-check" name="options-base" onclick="payMethod('cc')" id="option-cc" autocomplete="off">
                                <label class="btn" for="option-cc">Credit Card</label>
                            </div>
                            <div id="payOther" class="col-12">
                                <p class="border rounded p-3">
                                    BRI : 393494847548537 <br>
                                    BNI : 234828934923842
                                </p>
                            </div>
                            <div id="payCC" class="col-12" style="display: none;">
                                <p class="border rounded p-3 text-center">
                                    <b>Is coming soon</b>
                                </p>
                            </div>
                            <div class="col-12 text-center">
                                <button class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#paymentModal">Confirm Payment</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
{{-- modal upload bukti pembayaran --}}
<div class="modal fade" id="paymentModal" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title text-center w-100" id="txtInfoConfirm">Upload bukti pembayaran</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    {{-- Upload Surat --}}
                    <div class="mb-2">
                        <div class="card mb-0">
                            <input type="file" name="uploadBuktiPembayaran" id="uploadBuktiPembayaran" class="form-control dropify">
                        </div>
                        <span class="mb-3 text-muted" style="font-size: 12px;">Allowed file types: png, jpg, jpeg.
                            Recommend size under 5MB.</span>
                    </div>
                    {{-- Status --}}
                    <input type="hidden" name="statusVerif" id="statusVerif">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" role="button" data-bs-dismiss="modal" aria-label="Close">Batal</button>
                <button class="btn btn-primary" role="button" onclick="submitBukti()">Submit</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script>
        setDropify('init', '#uploadBuktiPembayaran', {
            allowedFileExtentions: ['jpg', 'png', 'jpeg'],
            maxFileSize: '5M'
        });
        
        const permohonan = @json($permohonan);
        function payMethod(type){
            $('#payOther').hide();
            $('#payCC').hide();
            switch (type) {
                case 'cc':
                    $('#payCC').show();
                    break;
                default:
                    $('#payOther').show();
                    break;
            }
        }

        function submitBukti(){
            // get image
            const file = $('#uploadBuktiPembayaran')[0].files[0];

            if(file){
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('file', file);
                formData.append('idKip', permohonan.tbl_kip.kip_hash);

                ajaxPost(`api/kip/sendPayment`, formData, result => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: result.message
                    });

                    $('#paymentModal').modal('hide');
                    window.location.href = "{{ url('permohonan') }}";
                });
            }else{
                Swal.fire({
                    icon: "error",
                    text: "Silahkan upload bukti pembayaran !"
                });
            }

        }

    </script>
@endpush

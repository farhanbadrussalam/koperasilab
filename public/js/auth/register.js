$(function () {
    $('#nama_instansi').select2({
        theme: "bootstrap-5",
        tags: true,
        createTag: function(params) {
            return {
                id: params.term,   // Nilai yang akan disimpan
                text: params.term, // Text yang ditampilkan
                newTag: true       // Penanda bahwa ini adalah input baru
            }
        },
        minimumInputLength: 2,
        placeholder: "Search Instansi",
        allowClear: true,
        ajax: {
            url: `${window.location.origin}/api/v1/profile/list/perusahaan`,
            dataType: 'json',
            type: 'GET',
            delay: 250,
            data: function(params) {
                let queryParams = {
                    search: params.term
                }
                return queryParams;
            },
            processResults: function (result) {
                let items = [];
                
                for (const value of result.data) {
                    items.push({
                        'id': value.perusahaan_hash,
                        'text': value.nama_perusahaan,
                        'email': value.email
                    });
                }
                
                return {
                    results: items
                };
            },
            cache: true

        }
    }).on('select2:select', function(e) {
        $('#type_instansi').val(e.params.data.newTag ? 'new' : 'old');
        $('#email_instansi').val(e.params.data.email ? e.params.data.email : '');
    })
})
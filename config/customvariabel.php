<?php
    return [
        'arr_evaluasi' => ['EvaluasiDenganKontrak','KontrakEvaluasi'],
        'arr_sewa' => ['KontrakSewa'],
        'arr_putus' => ['EvaluasiTanpaKontrak','ZeroCekTanpaKontrak'],

        'catatan_invoice' => ['EvaluasiTanpaKontrak'],

        'urlStempel' => "data:image/png;base64," . base64_encode(file_get_contents(public_path('icons/Stempel-Lab.png'))),
        'urlTtdDefault' => "data:image/png;base64," . base64_encode(file_get_contents(public_path('icons/default/white.png')))
    ]
?>

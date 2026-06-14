<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$penyelias = \App\Models\Penyelia::whereHas('permohonan', function($q) {
    $q->where('is_have_tld', 1)->whereNotNull('id_kontrak');
})->with('permohonan', 'periodenow')->limit(5)->get();

foreach($penyelias as $p) {
    echo "Penyelia ID: " . $p->id_penyelia . " | Kontrak ID: " . $p->id_kontrak . "\n";
    if ($p->permohonan) {
        $jml = ($p->permohonan->jumlah_pengguna ?? 0) + ($p->permohonan->jumlah_kontrol ?? 0);
        echo "TLD from permohonan: " . $jml . "\n";
    }
    
    // Check if it's a kontrak periode
    $kp = $p->periodenow;
    if ($kp) {
        $tlds = \App\Models\Kontrak_tld::where('id_kontrak', $kp->id_kontrak)->where('count_tld', $kp->count_tld)->count();
        echo "TLD from kontrak_periode: " . $tlds . "\n";
    } else {
        $tlds = \App\Models\Permohonan_tld::where('id_permohonan', $p->id_permohonan)->sum('count');
        echo "TLD from permohonan_tld count: " . $tlds . "\n";
    }
    echo "------------------\n";
}

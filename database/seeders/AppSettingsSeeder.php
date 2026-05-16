<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // KOSONGKAN TABEL SEBELUM MENGISI DATA BARU
        DB::table('app_settings')->truncate();

        $now = Carbon::now();

        $settings = [
            // --- Group: Identity (Identitas Koperasi/Lab) ---
            [
                'key' => 'lab_name',
                'value' => 'NuklindoLab',
                'group' => 'identity',
                'description' => 'Nama instansi / koperasi',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'lab_address',
                'value' => 'Plaza Ciputat Mas Blok B Kav P-Q, Jl. Ir. H. Juanda No. 5A, Ciputat Timur - Tangerang Selatan',
                'group' => 'identity',
                'description' => 'Alamat lengkap instansi',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'lab_phone',
                'value' => '021 - 2950 0440',
                'group' => 'identity',
                'description' => 'Nomor telepon resmi',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'lab_email_cs',
                'value' => 'cs@kop-jkrl.co.id',
                'group' => 'identity',
                'description' => 'Email Customer Service',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'lab_email_tld',
                'value' => 'tld@kop-jkrl.co.id',
                'group' => 'identity',
                'description' => 'Email khusus layanan TLD',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'lab_website',
                'value' => 'www.kop-jkrl.co.id',
                'group' => 'identity',
                'description' => 'Website resmi instansi',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'lab_lokasi',
                'value' => 'Tangerang Selatan',
                'group' => 'identity',
                'description' => 'Lokasi instansi',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'lab_logo',
                'value' => null,
                'group' => 'identity',
                'description' => 'Logo instansi',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'lab_stamp',
                'value' => null,
                'group' => 'identity',
                'description' => 'Stamp instansi',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // --- Group: Finance (Keuangan) ---
            [
                'key' => 'ppn_rate',
                'value' => '11',
                'group' => 'finance',
                'description' => 'Persentase Pajak Pertambahan Nilai (PPN) saat ini',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'pph_rate',
                'value' => '3',
                'group' => 'finance',
                'description' => 'Persentase Pajak Penghasilan Harian (PPH) saat ini',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // --- Group: Technical / System ---
            [
                'key' => 'session_lifetime',
                'value' => '120',
                'group' => 'technical',
                'description' => 'Lama waktu session aktif (dalam menit)',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Masukkan data ke database
        DB::table('app_settings')->insert($settings);

        // php artisan db:seed --class=AppSettingsSeeder 
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Satuan_kerja;

class SatuanKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Satuan_kerja::create(['name' => 'Keselamatan & UK']);
        Satuan_kerja::create(['name' => 'AUR & ALKES']);
        Satuan_kerja::create(['name' => 'Pengujian Dosimetri']);
    }
}

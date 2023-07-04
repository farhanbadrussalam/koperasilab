<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserRolePermissionSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = Role::create(['name' => 'Super Admin']);
        $pelanggan = Role::create(['name' => 'Pelanggan']);
        $frontdesk = Role::create(['name' => 'Frontdesk']);
        $manager = Role::create(['name' => 'Manager']);
        $pelaksana_kontrak = Role::create(['name' => 'Pelaksana Kontrak']);
        $penyedia_lab = Role::create(['name' => 'Penyedia Lab']);
        $pelaksana_lab = Role::create(['name' => 'Pelaksana Lab']);
        $keuangan = Role::create(['name' => 'Keuangan']);
        $admin = Role::create(['name' => 'Administrasi']);
        $manager_2 = Role::create(['name' => 'Manager 2']);
        $lab = Role::create(['name' => 'LAB']);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('password')
        ])->assignRole($superadmin);
    }
}

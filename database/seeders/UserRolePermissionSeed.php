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
        $gmanager = Role::create(['name' => 'General Manager']);
        $keuangan = Role::create(['name' => 'Manager Keuangan']);
        $admin = Role::create(['name' => 'Staff Admin']);
        $manager = Role::create(['name' => 'manager']);
        $staff = Role::create(['name' => 'staff']);
        // $frontdesk = Role::create(['name' => 'Frontdesk']);
        // $manager = Role::create(['name' => 'Manager']);
        // $pelaksana_kontrak = Role::create(['name' => 'Pelaksana Kontrak']);
        // $penyedia_lab = Role::create(['name' => 'Penyedia Lab']);
        // $pelaksana_lab = Role::create(['name' => 'Pelaksana Lab']);
        // $keuangan = Role::create(['name' => 'Keuangan']);
        // $admin = Role::create(['name' => 'Administrasi']);
        // $manager_2 = Role::create(['name' => 'Manager 2']);
        // $lab = Role::create(['name' => 'LAB']);

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('password')
        ])->assignRole($superadmin);
        
        User::factory()->create([
            'name' => 'Manager',
            'email' => 'manager@gmail.com',
            'password' => Hash::make('password')
        ])->assignRole($gmanager);
        
        User::factory()->create([
            'name' => 'Keuangan',
            'email' => 'keuangan@gmail.com',
            'password' => Hash::make('password')
        ])->assignRole($keuangan);

        User::factory()->create([
            'name' => 'Manager UKUK',
            'email' => 'managerukuk@gmail.com',
            'password' => Hash::make('password')
        ])->assignRole($manager);

        User::factory()->create([
            'name' => 'Manager UKAURAK',
            'email' => 'managerukaurak@gmail.com',
            'password' => Hash::make('password')
        ])->assignRole($manager);
                
        User::factory()->create([
            'name' => 'Manager UPD',
            'email' => 'managerupd@gmail.com',
            'password' => Hash::make('password')
        ])->assignRole($manager);

        User::factory()->create([
            'name' => 'Staff UKUK',
            'email' => 'staffukuk@gmail.com',
            'password' => Hash::make('password')
        ])->assignRole($staff);

        User::factory()->create([
            'name' => 'Staff UKAURAK',
            'email' => 'staffukaurak@gmail.com',
            'password' => Hash::make('password')
        ])->assignRole($staff);

        User::factory()->create([
            'name' => 'Staff UPD',
            'email' => 'staffupd@gmail.com',
            'password' => Hash::make('password')
        ])->assignRole($staff);

        // User::factory()->create([
        //     'name' => 'Pelaksana Kontrak',
        //     'email' => 'pelaksanaKontrak@gmail.com',
        //     'password' => Hash::make('password')
        // ])->assignRole($pelaksana_kontrak);

        // User::factory()->create([
        //     'name' => 'Penyedia Lab',
        //     'email' => 'penyedialab@gmail.com',
        //     'password' => Hash::make('password')
        // ])->assignRole($penyedia_lab);


        // User::factory()->create([
        //     'name' => 'Manager 2',
        //     'email' => 'manager2@gmail.com',
        //     'password' => Hash::make('password')
        // ])->assignRole($manager_2);

        // User::factory()->create([
        //     'name' => 'LAB',
        //     'email' => 'lab@gmail.com',
        //     'password' => Hash::make('password')
        // ])->assignRole($lab);
    }
}

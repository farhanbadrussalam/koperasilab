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
        $permissions = [
            ['name' => 'Biodata.pribadi'],
            ['name' => 'Biodata.perusahaan'],
            ['name' => 'Management.layanan.jasa'],
            ['name' => 'Home'],
            ['name' => 'User.management']
        ];

        foreach ($permissions as $permissionData) {
            Permission::create($permissionData);
        }

        $superadmin = Role::create(['name' => 'Super Admin']);
        $pelanggan = Role::create(['name' => 'Pelanggan']);
        $gmanager = Role::create(['name' => 'General Manager']);
        $keuangan = Role::create(['name' => 'Manager Keuangan']);
        $admin = Role::create(['name' => 'Staff Admin']);
        $manager = Role::create(['name' => 'manager']);
        $staff = Role::create(['name' => 'staff']);

        $superadmin->givePermissionTo('Home','User.management', 'Management.layanan.jasa');
        $pelanggan->givePermissionTo('Biodata.pribadi', 'Biodata.perusahaan');
        $gmanager->givePermissionTo('Home');
        $keuangan->givePermissionTo('Home');
        $admin->givePermissionTo('Home');
        $manager->givePermissionTo('Home', 'Management.layanan.jasa');
        $staff->givePermissionTo('Home', 'Management.layanan.jasa');

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
            'satuankerja_id' => 1,
            'password' => Hash::make('password')
        ])->assignRole($manager);

        User::factory()->create([
            'name' => 'Manager UKAURAK',
            'email' => 'managerukaurak@gmail.com',
            'satuankerja_id' => 2,
            'password' => Hash::make('password')
        ])->assignRole($manager);

        User::factory()->create([
            'name' => 'Manager UPD',
            'email' => 'managerupd@gmail.com',
            'satuankerja_id' => 3,
            'password' => Hash::make('password')
        ])->assignRole($manager);

        User::factory()->create([
            'name' => 'Staff UKUK',
            'email' => 'staffukuk@gmail.com',
            'satuankerja_id' => 1,
            'password' => Hash::make('password')
        ])->assignRole($staff);

        User::factory()->create([
            'name' => 'Staff UKAURAK',
            'email' => 'staffukaurak@gmail.com',
            'satuankerja_id' => 2,
            'password' => Hash::make('password')
        ])->assignRole($staff);

        User::factory()->create([
            'name' => 'Staff UPD',
            'email' => 'staffupd@gmail.com',
            'satuankerja_id' => 3,
            'password' => Hash::make('password')
        ])->assignRole($staff);
    }
}

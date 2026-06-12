<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Permohonan;
use App\Models\Permohonan_detail;
use Spatie\Permission\Models\Role;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DestroyAdendumTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Pastikan role Super Admin ada di DB jika belum di-seed
        if (!Role::where('name', 'Super Admin')->exists()) {
            Role::create(['name' => 'Super Admin']);
        }
        
        if (!Role::where('name', 'Developer')->exists()) {
            Role::create(['name' => 'Developer']);
        }

        if (!Role::where('name', 'Pelanggan')->exists()) {
            Role::create(['name' => 'Pelanggan']);
        }
    }

    /**
     * Menguji bahwa request tanpa login akan ditolak (401).
     */
    public function test_destroy_adendum_guest_unauthorized()
    {
        $response = $this->deleteJson('/api/v1/permohonan/destroyAdendum/any-hash');
        $response->assertStatus(401);
    }

    /**
     * Menguji bahwa user dengan role non-Super Admin/Developer ditolak (403).
     */
    public function test_destroy_adendum_regular_user_forbidden()
    {
        $user = User::factory()->create([
            'password' => 'password'
        ]);
        $user->assignRole('Pelanggan');

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/permohonan/destroyAdendum/any-hash');
        $response->assertStatus(403)
                 ->assertJsonFragment(['msg' => 'Akses ditolak. Hanya Developer atau Super Admin yang diperbolehkan.']);
    }

    /**
     * Menguji penghapusan adendum oleh Super Admin dengan ID yang bukan adendum (400).
     */
    public function test_destroy_adendum_non_adendum_permohonan()
    {
        $user = User::factory()->create([
            'password' => 'password'
        ]);
        $user->assignRole('Super Admin');

        Sanctum::actingAs($user);

        // Buat permohonan biasa (bukan adendum)
        $permohonan = Permohonan::create([
            'tipe_kontrak' => 'kontrak baru',
            'status' => 1,
            'created_by' => $user->id
        ]);

        $hash = encryptor($permohonan->id_permohonan);

        $response = $this->deleteJson('/api/v1/permohonan/destroyAdendum/' . $hash);
        $response->assertStatus(400)
                 ->assertJsonFragment(['msg' => 'Data yang ingin dihapus bukan merupakan adendum']);
    }

    /**
     * Menguji sukses hapus adendum oleh Super Admin/Developer.
     */
    public function test_destroy_adendum_success()
    {
        $user = User::factory()->create([
            'password' => 'password'
        ]);
        $user->assignRole('Super Admin');

        Sanctum::actingAs($user);

        // Buat permohonan adendum
        $permohonan = Permohonan::create([
            'tipe_kontrak' => 'adendum',
            'status' => 1,
            'created_by' => $user->id
        ]);

        $hash = encryptor($permohonan->id_permohonan);

        $response = $this->deleteJson('/api/v1/permohonan/destroyAdendum/' . $hash);
        $response->assertStatus(200)
                 ->assertJsonFragment(['msg' => 'Adendum berhasil dihapus!']);

        // Pastikan permohonan sudah terhapus
        $this->assertDatabaseMissing('permohonan', [
            'id_permohonan' => $permohonan->id_permohonan
        ]);
    }

    /**
     * Menguji sukses hapus adendum oleh Developer.
     */
    public function test_destroy_adendum_developer_success()
    {
        $user = User::factory()->create([
            'password' => 'password'
        ]);
        $user->assignRole('Developer');

        Sanctum::actingAs($user);

        // Buat permohonan adendum
        $permohonan = Permohonan::create([
            'tipe_kontrak' => 'adendum',
            'status' => 1,
            'created_by' => $user->id
        ]);

        $hash = encryptor($permohonan->id_permohonan);

        $response = $this->deleteJson('/api/v1/permohonan/destroyAdendum/' . $hash);
        $response->assertStatus(200)
                 ->assertJsonFragment(['msg' => 'Adendum berhasil dihapus!']);

        // Pastikan permohonan sudah terhapus
        $this->assertDatabaseMissing('permohonan', [
            'id_permohonan' => $permohonan->id_permohonan
        ]);
    }

    /**
     * Menguji sukses hapus adendum beserta data anak (penyelia, keuangan, pengiriman) yang terikat.
     */
    public function test_destroy_adendum_with_related_data_success()
    {
        $user = User::factory()->create([
            'password' => 'password'
        ]);
        $user->assignRole('Super Admin');

        Sanctum::actingAs($user);

        // 1. Buat Permohonan adendum
        $permohonan = Permohonan::create([
            'tipe_kontrak' => 'adendum',
            'status' => 1,
            'created_by' => $user->id
        ]);

        // 2. Buat Penyelia terikat
        $penyelia = \App\Models\Penyelia::create([
            'id_permohonan' => $permohonan->id_permohonan,
            'status' => 1
        ]);

        // 3. Buat Keuangan terikat
        $keuangan = \App\Models\Keuangan::create([
            'id_permohonan' => $permohonan->id_permohonan,
            'status' => 1
        ]);

        // 4. Buat Pengiriman terikat
        $pengiriman = \App\Models\Pengiriman::create([
            'id_pengiriman' => 'SHIP-' . uniqid(),
            'id_permohonan' => $permohonan->id_permohonan,
            'status' => 1
        ]);

        $hash = encryptor($permohonan->id_permohonan);

        $response = $this->deleteJson('/api/v1/permohonan/destroyAdendum/' . $hash);
        $response->assertStatus(200)
                 ->assertJsonFragment(['msg' => 'Adendum berhasil dihapus!']);

        // Pastikan permohonan sudah terhapus
        $this->assertDatabaseMissing('permohonan', [
            'id_permohonan' => $permohonan->id_permohonan
        ]);

        // Pastikan data relasi anak-anaknya juga terhapus
        $this->assertDatabaseMissing('penyelia', [
            'id_penyelia' => $penyelia->id_penyelia
        ]);
        $this->assertDatabaseMissing('keuangan', [
            'id_keuangan' => $keuangan->id_keuangan
        ]);
        $this->assertDatabaseMissing('pengiriman', [
            'id_pengiriman' => $pengiriman->id_pengiriman
        ]);
    }
}

<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSettings;
use DB;

class AppSettingsController extends Controller
{
    public function index()
    {
        // Ensure max_upload_size exists
        AppSettings::firstOrCreate(
            ['key' => 'max_upload_size'],
            [
                'value' => '2048',
                'group' => 'technical',
                'description' => 'Maksimal ukuran file upload (dalam KB)'
            ]
        );

        $settings = AppSettings::orderBy('group')->get()->groupBy('group');
        
        $data = [
            'title' => 'Management',
            'module' => 'app_settings',
            'settings' => $settings
        ];
        
        return view('pages.management.app-settings.index', $data);
    }

    public function store(Request $request)
    {
        $inputs = $request->except(['_token']);
        
        DB::beginTransaction();
        try {
            foreach ($inputs as $key => $value) {
                // we should only update existing keys or handle appropriately
                $setting = AppSettings::where('key', $key)->first();
                if ($setting) {
                    $setting->value = $value;
                    $setting->save();
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'Pengaturan aplikasi berhasil disimpan.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan pengaturan: ' . $th->getMessage());
        }
    }
}

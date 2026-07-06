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

        if ($request->has('max_upload_size') && $request->has('max_upload_size_unit')) {
            $size = (int)$request->input('max_upload_size');
            $unit = $request->input('max_upload_size_unit');
            $inputs['max_upload_size'] = ($unit === 'MB') ? ($size * 1024) : $size;
        }

        unset($inputs['max_upload_size_unit']);

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
            session()->forget('app_settings');
            return redirect()->back()->with('success', 'Pengaturan aplikasi berhasil disimpan.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan pengaturan: ' . $th->getMessage());
        }
    }
}

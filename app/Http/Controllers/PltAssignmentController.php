<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PltAssignment;
use App\Models\User;

class PltAssignmentController extends Controller
{
    /**
     * Menampilkan halaman daftar penugasan PLT (khusus untuk role Manager Keuangan)
     */
    public function index()
    {
        $data = [
            'title' => 'Manajemen PLT',
            'module' => 'manager-plt',
            'assignments' => PltAssignment::with(['pltUser', 'originalUser'])
                                ->where('role_name', 'Manager Keuangan')
                                ->orderBy('id', 'desc')
                                ->get()
        ];
        return view('pages.manager.plt.index', $data);
    }

    /**
     * Pencarian pengguna menggunakan select2 Ajax
     */
    public function searchUser(Request $request)
    {
        $search = $request->get('q');
        
        $users = User::where('status', 1)
                    ->where(function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->limit(20)
                    ->get();
        
        $result = [];
        foreach ($users as $user) {
            $result[] = [
                'id' => $user->id,
                'text' => $user->name . ' (' . ($user->jabatan ?? 'Pegawai') . ')'
            ];
        }

        return response()->json($result);
    }

    /**
     * Menyimpan data penunjukan PLT baru
     */
    public function store(Request $request)
    {
        // Validasi
        $request->validate([
            'plt_user_id' => 'required|exists:users,id',
            'original_user_id' => 'nullable|exists:users,id',
            'role_name' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'surat_tugas' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        // Simpan File Fisik Surat Tugas
        $path = null;
        if ($request->hasFile('surat_tugas')) {
            $path = $request->file('surat_tugas')->store('surat_tugas_plt', 'public');
        }

        // Insert ke DB
        $plt = PltAssignment::create([
            'plt_user_id' => $request->plt_user_id,
            'original_user_id' => $request->original_user_id,
            'role_name' => $request->role_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'surat_tugas_path' => $path,
            'status' => 1
        ]);

        // Kirim Notifikasi ke PLT yang ditunjuk
        $pesan = "Anda telah ditunjuk sebagai PLT {$request->role_name} dari tanggal {$request->start_date} hingga {$request->end_date}.";
        $url = $path ? asset('storage/' . $path) : '#';
        $pltUser = User::find($request->plt_user_id);
        if ($pltUser) {
            $pltUser->notify(new \App\Notifications\NotifikasiBaru(
                $pesan, 
                $url, 
                $plt->id, 
                'PltAssign', 
                auth()->id() // Yang menugaskan (manager/admin)
            ));
        }
        
        return redirect()->back()->with('success', 'Pelaksana Tugas (PLT) berhasil ditunjuk dan diaktifkan.');
    }

    /**
     * Menampilkan penugasan PLT milik user yang sedang login
     */
    public function myAssignments()
    {
        $assignments = PltAssignment::with(['originalUser'])
            ->where('plt_user_id', auth()->id())
            ->where('status', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('id', 'desc')
            ->get();

        return view('pages.plt.my_assignments', [
            'title' => 'Penugasan PLT Saya',
            'module' => 'plt-my',
            'assignments' => $assignments,
        ]);
    }

    public function revoke($id)
    {
        $plt = PltAssignment::findOrFail($id);
        $plt->update(['status' => 0]);
        
        return redirect()->back()->with('success', 'Penunjukan Pelaksana Tugas (PLT) berhasil dicabut.');
    }
}

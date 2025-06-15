<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BahanBaku;

class LoginController extends Controller
{
    //
    public function index(){
        return view('auth.login');
    }

    // public function login_proses(Request $request){
    //     // dd($request->all());
    //     $request->validate([
    //         'email'     => 'required|email',
    //         'password'  => 'required',
    //     ]);

    //     $data= [
    //         'email'     => $request->email,
    //         'password'  => $request->password
    //     ];
    //     if(Auth::attempt($data)){
    //         return redirect()->intended(route('owner.dashboard'));
            
    //     } else{
    //         return redirect()->route('login')->with('failed','Email atau Password Salah');
    //     }
    // }


    // app/Http/Controllers/LoginController.php

    public function login_proses(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        
        // Cek apakah checkbox "remember" dicentang
        $remember = $request->has('remember');

        // Masukkan variabel $remember sebagai parameter kedua
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();


            // ===============================================
            // ===== AWAL LOGIKA NOTIFIKASI SETELAH LOGIN =====
            // ===============================================

            // 1. Cek bahan baku yang hampir habis atau sudah habis
            $lowStockItems = BahanBaku::whereRaw('stok <= batas_minimum')->get();

            // 2. Jika ada item yang stoknya rendah/habis
            if ($lowStockItems->isNotEmpty()) {
                // Buat pesan notifikasi dengan me-list nama-nama item
                $itemNames = $lowStockItems->pluck('nama_bahan')->implode(', ');
                $fullMessage = 'Segera lakukan pemesanan ulang untuk: ' . $itemNames;

                // 3. Kirim pesan ini ke sesi untuk ditampilkan di halaman dashboard
                $request->session()->flash('low_stock_alert', $fullMessage);
            }
            
            // ===============================================
            // ===== AKHIR LOGIKA NOTIFIKASI =====
            // ===============================================

            $user = Auth::user();

            if ($user->role == 'owner') {
                return redirect()->intended(route('owner.dashboard'));
            } elseif ($user->role == 'karyawan') { 
                return redirect()->intended(route('owner.dashboard'));
            }
        }

        return back()->withErrors([
            'email' => 'Email atau Password yang Anda masukan salah.',
        ])->onlyInput('email');
    }

    public function logout(){
        // dd('oke');
        Auth::logout();
        return redirect()->route('login')->with('succes','Kamu Berhasil Logout');
    }
}

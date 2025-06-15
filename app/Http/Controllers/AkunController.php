<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AkunController extends Controller
{
    // Controller Akun Pengguna
    public function akun(){
        $data = User::get();
        return view('Akun.akun',compact('data'));
    }

        public function create(){
        return view('Akun.create');
    }

    public function store(Request $request){
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'nama'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'phone_number'  => 'nullable|string|max:15',
            'password'      => 'required|min:3',
            'role'          => 'required|in:owner,karyawan',
        ]);

        if($validator->fails()) return redirect()->back()->withInput()->withErrors($validator);

        $data['name'] = $request->nama;
        $data['email'] = $request->email;
        $data['phone_number'] = $request->phone_number ?? null;
        $data['password'] = Hash::make($request->password);
        $data['role'] =$request->role;

        User::create($data);
        return redirect()->route('owner.akun');
    }

    public function edit(Request $request,$id){
        $data = User::find($id);
        // dd($data);
        return view('Akun.edit', compact ('data'));
    }

    public function update(Request $request,$id){
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'nama'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $id,
            'phone_number'  => 'nullable|string|max:15', 
            'password'      => 'nullable|min:3',
            'role'          => 'required|in:owner,karyawan',
        ]);


        if($validator->fails()) return redirect()->back()->withInput()->withErrors($validator);

        $data['name'] = $request->nama;
        $data['email'] = $request->email;
        $data['phone_number'] = $request->phone_number;

        if($request->password){
            $data['password']   = Hash::make($request->password);
        }
        $data['role'] = $request->role;

        User::whereId($id)->update($data);
        return redirect()->route('owner.akun');
    }

    public function delete(Request $request,$id) {
        $user = User::findOrFail($id);

        // Pengaman agar tidak bisa menghapus diri sendiri tetap penting
        if (Auth::id() == $user->id) {
            return redirect()->route('owner.akun')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri secara permanen.');
        }

        // Langsung hapus user. Database akan menangani sisanya.
        $user->delete();

        return redirect()->route('owner.akun')->with('success', 'User berhasil dihapus secara permanen. Semua riwayatnya kini bersifat anonim.');
    }

    // End Controller Akun Pengguna
}

@extends('layout.main')
@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Tambah Bahan Baku Masuk</h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <form action="{{ route('owner.masuk.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Form Bahan Masuk</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="bahan_baku_id">Nama Bahan</label>
                                        <select name="bahan_baku_id" id="bahan_baku_id"
                                            class="form-control @error('bahan_baku_id') is-invalid @enderror">
                                            <option value="">-- Pilih Bahan Baku --</option>
                                            @foreach ($bahan_baku as $bahan)
                                                <option value="{{ $bahan->id }}" {{ old('bahan_baku_id') == $bahan->id ? 'selected' : '' }}>
                                                    {{ $bahan->nama_bahan }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('bahan_baku_id') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="jumlah_awal">Jumlah Masuk</label>
                                        <input type="number" step="0.01" name="jumlah_awal" id="jumlah_awal"
                                            class="form-control @error('jumlah_awal') is-invalid @enderror"
                                            placeholder="Masukan Jumlah" value="{{ old('jumlah_awal') }}">
                                        @error('jumlah_awal') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="tanggal_kadaluarsa">Tanggal Kadaluarsa (Opsional)</label>
                                        <input type="date" name="tanggal_kadaluarsa" id="tanggal_kadaluarsa"
                                            class="form-control" value="{{ old('tanggal_kadaluarsa') }}">
                                        @error('tanggal_kadaluarsa') <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection
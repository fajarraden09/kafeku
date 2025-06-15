@extends('layout.main')
@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Catat Bahan Baku Keluar</h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <form action="{{ route('owner.keluar.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Form Bahan Keluar</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="bahan_baku_id">Nama Bahan</label>
                                        <select name="bahan_baku_id" id="bahan_baku_id"
                                            class="form-control @error('bahan_baku_id') is-invalid @enderror">
                                            <option value="">-- Pilih Bahan Baku --</option>
                                            @foreach ($bahan_baku as $bahan)
                                                <option value="{{ $bahan->id }}" {{ old('bahan_baku_id') == $bahan->id ? 'selected' : '' }}>
                                                    {{ $bahan->nama_bahan }} (Stok: {{ $bahan->stok }} {{ $bahan->satuan }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('bahan_baku_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="jumlah_keluar">Jumlah Keluar</label>
                                        <input type="number" step="0.01" name="jumlah_keluar" id="jumlah_keluar"
                                            class="form-control @error('jumlah_keluar') is-invalid @enderror"
                                            placeholder="Masukan Jumlah" value="{{ old('jumlah_keluar') }}">
                                        @error('jumlah_keluar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="keterangan">Keterangan</label>
                                        <textarea name="keterangan" id="keterangan"
                                            class="form-control @error('keterangan') is-invalid @enderror" rows="3"
                                            placeholder="Contoh: Rusak, Terbuang, Dipakai untuk event, dll.">{{ old('keterangan') }}</textarea>
                                        @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
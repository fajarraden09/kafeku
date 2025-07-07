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
                                        {{-- Tambahkan kelas 'select2' untuk mengaktifkan fitur pencarian --}}
                                        <select name="bahan_baku_id" id="bahan_baku_id"
                                            class="form-control select2 @error('bahan_baku_id') is-invalid @enderror"
                                            style="width: 100%;"> {{-- Penting untuk Select2 --}}
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

@push('scripts')
    <!-- Pastikan jQuery sudah dimuat sebelum Select2 -->
    <!-- Jika Anda menggunakan AdminLTE, jQuery biasanya sudah ada -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Opsional: Jika Anda ingin tema Bootstrap 4 untuk Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css"
        rel="stylesheet" />

    <script>
        $(document).ready(function () {
            // Inisialisasi Select2 pada elemen dengan ID 'bahan_baku_id'
            $('#bahan_baku_id').select2({
                placeholder: '-- Pilih Bahan Baku --', // Placeholder untuk Select2
                allowClear: true, // Memungkinkan penghapusan pilihan
                theme: 'bootstrap4' // Menggunakan tema Bootstrap 4 jika Anda memuat CSS-nya
            });
        });
    </script>
@endpush
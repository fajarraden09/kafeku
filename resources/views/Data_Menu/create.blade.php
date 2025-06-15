@extends('layout.main')
@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Tambah Menu Baru</h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                <form action="{{ route('owner.menu.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Form Tambah Menu & Resep</h3>
                                </div>
                                <div class="card-body">
                                    {{-- FORM MENU DASAR --}}
                                    <div class="form-group">
                                        <label for="nama_menu">Nama Menu</label>
                                        <input type="text" name="nama_menu"
                                            class="form-control @error('nama_menu') is-invalid @enderror" id="nama_menu"
                                            placeholder="Masukan Nama Menu" value="{{ old('nama_menu') }}">
                                        @error('nama_menu')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="harga">Harga</label>
                                        <input type="number" name="harga"
                                            class="form-control @error('harga') is-invalid @enderror" id="harga"
                                            placeholder="Masukan Harga" value="{{ old('harga') }}">
                                        @error('harga')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="kategori_id">Kategori Menu</label>
                                        <select name="kategori_id" id="kategori_id"
                                            class="form-control @error('kategori_id') is-invalid @enderror" required>
                                            <option value="">-- Pilih Kategori --</option>
                                            @foreach ($kategori as $kat)
                                                <option value="{{ $kat->id }}" {{ old('kategori_id') == $kat->id ? 'selected' : '' }}>
                                                    {{ $kat->nama_kategori }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('kategori_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="image">Gambar Menu</label>
                                        <input type="file" name="image" class="form-control-file" id="image">
                                        @error('image') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="ketersediaan">Ketersediaan (Nilai Awal)</label>
                                        <select name="ketersediaan" class="form-control" id="ketersediaan">
                                            <option value="1" {{ old('ketersediaan', 1) == 1 ? 'selected' : '' }}>Tersedia
                                            </option>
                                            <option value="0" {{ old('ketersediaan') == 0 ? 'selected' : '' }}>Habis</option>
                                        </select>
                                        <small class="form-text text-muted">Status ini akan diperbarui secara otomatis jika
                                            stok bahan baku berubah.</small>
                                    </div>

                                    {{-- FORM RESEP DINAMIS --}}
                                    <hr>
                                    <h4>Resep / Bahan Baku</h4>
                                    <div id="resep-container">
                                        {{-- Bagian ini untuk menampilkan kembali input jika ada error validasi --}}
                                        @if(old('resep'))
                                            @foreach(old('resep') as $index => $item)
                                                <div class="row resep-row mb-2">
                                                    <div class="col-md-6">
                                                        <select name="resep[{{ $index }}][bahan_baku_id]" class="form-control">
                                                            @foreach ($bahan_baku as $bahan)
                                                                <option value="{{ $bahan->id }}" {{ $item['bahan_baku_id'] == $bahan->id ? 'selected' : '' }}>
                                                                    {{ $bahan->nama_bahan }} ({{ $bahan->satuan }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="number" step="0.01"
                                                            name="resep[{{ $index }}][jumlah_dibutuhkan]" class="form-control"
                                                            placeholder="Jumlah" value="{{ $item['jumlah_dibutuhkan'] }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-danger btn-remove-resep">Hapus</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" id="btn-add-resep" class="btn btn-success mt-2">Tambah Bahan
                                        Baku</button>
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

    {{-- SCRIPT JAVASCRIPT --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Tentukan index awal berdasarkan jumlah baris resep yang sudah ada (jika ada error validasi)
                let resepIndex = document.querySelectorAll('.resep-row').length;

                document.getElementById('btn-add-resep').addEventListener('click', function () {
                    const container = document.getElementById('resep-container');
                    const newRow = document.createElement('div');
                    newRow.classList.add('row', 'resep-row', 'mb-2');

                    // Perhatikan penggunaan `resep[${resepIndex}]` untuk nama input array
                    newRow.innerHTML = `
                                                    <div class="col-md-6">
                                                        <select name="resep[${resepIndex}][bahan_baku_id]" class="form-control">
                                                            <option value="">-- Pilih Bahan --</option>
                                                            @foreach ($bahan_baku as $bahan)
                                                                <option value="{{ $bahan->id }}">{{ $bahan->nama_bahan }} ({{ $bahan->satuan }})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="number" step="0.01" name="resep[${resepIndex}][jumlah_dibutuhkan]" class="form-control" placeholder="Jumlah" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-danger btn-remove-resep">Hapus</button>
                                                    </div>
                                                `;

                    container.appendChild(newRow);
                    resepIndex++; // Naikkan index untuk baris berikutnya
                });

                // Gunakan event delegation untuk menghandle tombol hapus pada baris yang baru dibuat
                document.getElementById('resep-container').addEventListener('click', function (e) {
                    if (e.target && e.target.classList.contains('btn-remove-resep')) {
                        // Hapus elemen .resep-row terdekat dari tombol yang diklik
                        e.target.closest('.resep-row').remove();
                    }
                });
            });
        </script>
    @endpush

@endsection
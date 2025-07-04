@extends('layout.main')
@section('content')
    <div class="content-wrapper">
         <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Edit Menu</h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                {{-- Ganti action dan tambahkan @method('PUT') --}}
                <form action="{{ route('owner.menu.update', ['id' => $menu->id]) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Form Edit Menu & Resep</h3>
                                </div>
                                <div class="card-body">
                                    {{-- FORM MENU DASAR --}}
                                    <div class="form-group">
                                        <label>Nama Menu</label>
                                        <input type="text" name="nama_menu" class="form-control"
                                            value="{{ $menu->nama_menu }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Harga</label>
                                        <input type="number" name="harga" class="form-control" value="{{ $menu->harga }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="kategori_id">Kategori Menu</label>
                                        <select name="kategori_id" id="kategori_id" class="form-control" required>
                                            <option value="">-- Pilih Kategori --</option>
                                            @foreach ($kategori as $kat)

                                                {{-- AWAL PERBAIKAN LOGIKA --}}
                                                <option value="{{ $kat->id }}" {{ $menu->kategori_id == $kat->id ? 'selected' : '' }}>
                                                    {{ $kat->nama_kategori }}
                                                </option>
                                                {{-- AKHIR PERBAIKAN LOGIKA --}}

                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="image">Gambar Menu</label>
                                        <input type="file" name="image" class="form-control-file" id="image">
                                        @error('image') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    {{-- Khusus di edit.blade.php, tampilkan gambar saat ini --}}
                                    @if($menu->image)
                                        <div class="form-group">
                                            <label>Gambar Saat Ini:</label>
                                            <img src="{{ asset('uploads/' . $menu->image) }}" alt="{{ $menu->nama_menu }}"
                                                width="150">
                                        </div>
                                    @endif
                                    <div class="form-group">
                                        <label>Ketersediaan</label>
                                        <select name="ketersediaan" class="form-control">
                                            <option value="1" {{ $menu->ketersediaan == 1 ? 'selected' : '' }}>Tersedia
                                            </option>
                                            <option value="0" {{ $menu->ketersediaan == 0 ? 'selected' : '' }}>Habis</option>
                                        </select>
                                    </div>

                                    {{-- FORM RESEP DINAMIS --}}
                                    <hr>
                                    <h4>Resep / Bahan Baku</h4>
                                    <div id="resep-container">
                                        {{-- Loop resep yang sudah ada --}}
                                        @foreach ($menu->resep as $index => $item)
                                            <div class="row resep-row mb-2">
                                                <div class="col-md-6">
                                                    <select name="resep[{{ $index }}][bahan_baku_id]" class="form-control">
                                                        @foreach ($bahan_baku as $bahan)
                                                            <option value="{{ $bahan->id }}" {{ $item->bahan_baku_id == $bahan->id ? 'selected' : '' }}>
                                                                {{ $bahan->nama_bahan }} ({{ $bahan->satuan }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="number" step="0.01"
                                                        name="resep[{{ $index }}][jumlah_dibutuhkan]" class="form-control"
                                                        placeholder="Jumlah" value="{{ $item->jumlah_dibutuhkan }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-danger btn-remove-resep">Hapus</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" id="btn-add-resep" class="btn btn-success mt-2">Tambah Bahan
                                        Baku</button>
                                </div>

                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Update</button>
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
                let resepIndex = {{ $menu->resep->count() }}; // Mulai dari jumlah resep yang ada

                document.getElementById('btn-add-resep').addEventListener('click', function () {
                    const container = document.getElementById('resep-container');
                    const newRow = document.createElement('div');
                    newRow.classList.add('row', 'resep-row', 'mb-2');

                    // Perhatikan penggunaan `resep[${resepIndex}]` untuk nama input
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
                                                                <input type="number" step="0.01" name="resep[${resepIndex}][jumlah_dibutuhkan]" class="form-control" placeholder="Jumlah">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <button type="button" class="btn btn-danger btn-remove-resep">Hapus</button>
                                                            </div>
                                                        `;

                    container.appendChild(newRow);
                    resepIndex++;
                });

                // Event delegation untuk tombol hapus
                document.getElementById('resep-container').addEventListener('click', function (e) {
                    if (e.target && e.target.classList.contains('btn-remove-resep')) {
                        e.target.closest('.resep-row').remove();
                    }
                });
            });
        </script>
    @endpush

@endsection
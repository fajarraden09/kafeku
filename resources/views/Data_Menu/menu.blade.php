@extends('layout.main')
@section('content')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Data Menu</h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Main row -->
                <div class="row">
                    <div class="col-12">
                        <a href="{{ route('owner.menu.create') }}" class="btn btn-primary mb-3">Tambah Menu Baru</a>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Data Menu Kafeku</h3>
                                {{-- AWAL BLOK SEARCH --}}
                                <div class="card-tools">
                                    <div class="input-group input-group-sm" style="width: 250px;">
                                        <input type="text" id="menuSearch" name="table_search"
                                            class="form-control float-right" placeholder="Cari Nama Menu...">
                                    </div>
                                </div>
                                {{-- AKHIR BLOK SEARCH --}}
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body table-responsive p-0">
                                <table class="table table-bordered table-hover text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Gambar</th>
                                            <th>Nama Menu</th>
                                            <th>Kategori</th>
                                            <th>Harga</th>
                                            <th>Ketersediaan</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="menuTableBody">
                                        @foreach ($data as $d)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    {{-- BAGIAN GAMBAR --}}
                                                    @if ($d->image)
                                                        <img src="{{ asset('uploads/' . $d->image) }}" alt="{{ $d->nama_menu }}"
                                                            style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">
                                                    @else
                                                        <div
                                                            style="width: 80px; height: 80px; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #666; font-size: 12px; border-radius: 5px;">
                                                            <span>No Image</span>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>{{ $d->nama_menu }}</td>
                                                <td>
                                                    <span
                                                        class="badge badge-info">{{ $d->kategori->nama_kategori ?? 'Tanpa Kategori' }}</span>
                                                </td>
                                                <td>Rp {{ number_format($d->harga, 0, ',', '.') }}</td>
                                                {{-- BLOK LOGIKA KETERSEDIAAN OTOMATIS --}}
                                                <td>
                                                    @if ($d->ketersediaan)
                                                        {{-- Jika $d->ketersediaan bernilai true --}}
                                                        <span class="badge badge-success">Tersedia</span>
                                                    @else
                                                        {{-- Jika $d->ketersediaan bernilai false --}}
                                                        <span class="badge badge-danger">Tidak Tersedia</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('owner.menu.show', ['id' => $d->id]) }}"
                                                        class="btn btn-info "><i class="fas fa-eye"></i> Detail</a>
                                                    <a href="{{ route('owner.menu.edit', ['id' => $d->id]) }}"
                                                        class="btn btn-primary"><i class="fas fa-pen"></i> Edit</a>
                                                    <a data-toggle="modal" data-target="#modal-hapus{{ $d->id }}" href="#"
                                                        class="btn btn-danger"><i class="fas fa-trash-alt"></i> Hapus</a>
                                                </td>
                                            </tr>
                                            <div class="modal fade" id="modal-hapus{{ $d->id }}">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Konfirmasi Hapus Data</h4>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Apakah Anda yakin ingin menghapus menu
                                                                <b>{{ $d->nama_menu }}</b>?
                                                            </p>
                                                        </div>
                                                        <div class="modal-footer justify-content-between">
                                                            <form action="{{ route('owner.menu.delete', ['id' => $d->id]) }}"
                                                                method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button" class="btn btn-default"
                                                                    data-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary">Ya, Hapus</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                </div>
                <!-- /.row (main row) -->
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Event listener saat pengguna mengetik di kotak pencarian
            $("#menuSearch").on("keyup", function () {
                var value = $(this).val().toLowerCase();
                // Loop melalui setiap baris di tabel body
                $("#menuTableBody tr").filter(function () {
                    // Toggle (sembunyikan/tampilkan) baris berdasarkan cocok atau tidaknya teks
                    // .toggle(true) akan menampilkan, .toggle(false) akan menyembunyikan
                    // Kita menargetkan kolom kedua (index 1) yaitu "Nama Menu"
                    $(this).toggle($(this).children('td:eq(2)').text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
@endpush
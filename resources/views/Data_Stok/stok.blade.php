@extends('layout.main')
@section('content')

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Stok Bahan Baku</h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <a href="{{ route('owner.stok.create') }}" class="btn btn-primary mb-3">Tambah Stok Bahan Baku
                            Baru</a>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Data Stok Bahan Baku</h3>
                                {{-- AWAL BLOK SEARCH --}}
                                <div class="card-tools">
                                    <div class="input-group input-group-sm" style="width: 250px;">
                                        <input type="text" id="stokSearch" name="table_search"
                                            class="form-control float-right" placeholder="Cari Nama Bahan...">
                                    </div>
                                </div>
                                {{-- AKHIR BLOK SEARCH --}}
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-bordered table-hover text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Nama Bahan</th>
                                            <th>Sisa Stok</th>
                                            <th>Satuan</th>
                                            <th>Status Stok</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="stokTableBody">
                                        @foreach ($data as $d)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $d->nama_bahan }}</td>
                                                <td>{{ $d->stok }}</td>
                                                <td>{{ $d->satuan }}</td>
                                                <td>
                                                    @if ($d->stok == 0)
                                                        {{-- Kondisi 1: Jika stok tepat 0, maka statusnya 'Habis' --}}
                                                        <span class="badge badge-dark">Habis</span>

                                                    @elseif ($d->stok <= $d->batas_minimum)
                                                        {{-- Kondisi 2: Jika stok di bawah atau sama dengan batas minimum (tapi
                                                        tidak 0), maka 'Hampir Habis' --}}
                                                        <span class="badge badge-danger">Hampir Habis</span>

                                                    @else
                                                        {{-- Kondisi 3: Jika tidak keduanya, maka stok 'Aman' --}}
                                                        <span class="badge badge-success">Aman</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('owner.stok.edit', ['id' => $d->id]) }}"
                                                        class="btn btn-primary"><i class="fas fa-pen"></i> Edit</a>
                                                    <a data-toggle="modal" data-target="#modal-hapus{{ $d->id }}"
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
                                                            <p>Apakah Anda Yakin Menghapus Data Bahan Baku
                                                                <b>{{ $d->nama_bahan }}</b>
                                                            </p>
                                                        </div>
                                                        <div class="modal-footer justify-content-between">
                                                            <form action="{{ route('owner.stok.delete', ['id' => $d->id]) }}"
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
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Event listener untuk pencarian stok
            $("#stokSearch").on("keyup", function () {
                var value = $(this).val().toLowerCase();
                // Loop melalui setiap baris di tabel body
                $("#stokTableBody tr").filter(function () {
                    // Kita menargetkan kolom kedua (index 1) yaitu "Nama Bahan"
                    $(this).toggle($(this).children('td:eq(1)').text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
@endpush
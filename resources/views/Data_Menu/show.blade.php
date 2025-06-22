@extends('layout.main')
@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                {{-- ... header halaman ... --}}
                <h1 class="m-0">Detail Menu: {{ $menu->nama_menu }}</h1>
                <a href="{{ route('owner.menu') }}" class="btn btn-secondary mt-3">Kembali ke Daftar Menu</a>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Informasi Menu</h3>
                            </div>
                            <div class="card-body">
                                <p><strong>Nama Menu:</strong> {{ $menu->nama_menu }}</p>
                                <p><strong>Harga:</strong> Rp {{ number_format($menu->harga, 0, ',', '.') }}</p>
                                <p><strong>Ketersediaan:</strong> {{ $menu->ketersediaan ? 'Tersedia' : 'Habis' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">Resep / Bahan Baku</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Bahan Baku</th>
                                            <th>Jumlah Dibutuhkan</th>
                                            <th>Satuan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($menu->resep as $item)
                                            <tr>
                                                <td>{{ $item->bahanBaku->nama_bahan }}</td>
                                                <td>{{ $item->jumlah_dibutuhkan }}</td>
                                                <td>{{ $item->bahanBaku->satuan }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center">Resep belum ditambahkan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('owner.menu.edit', ['id' => $menu->id]) }}" class="btn btn-primary">Edit
                                    Menu & Resep</a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>
@endsection
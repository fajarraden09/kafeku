@extends('layout.main')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Riwayat Bahan Keluar</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <a href="{{ route('owner.keluar.create') }}" class="btn btn-primary mb-3">Tambah Riwayat Bahan
                            Keluar</a>

                        {{-- STRUKTUR CARD YANG DIRAPIKAN --}}
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Data Riwayat Bahan Keluar</h3>
                            </div>
                            <div class="card-body">
                                <table id="keluarTable" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Nama Bahan</th>
                                            <th>Jumlah Keluar</th>
                                            <th>Keterangan</th>
                                            <th>Dicatat oleh</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($dataKeluar as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y H:i') }}</td>
                                                <td>{{ $item->bahanBaku->nama_bahan ?? 'N/A' }}</td>
                                                <td>{{ $item->jumlah_keluar }} {{ $item->bahanBaku->satuan ?? '' }}</td>
                                                <td>{{ $item->keterangan }}</td>
                                                <td>{{ $item->user->name ?? 'N/A' }}</td>
                                                <td class="text-center">
                                                    <a data-toggle="modal" data-target="#modal-hapus{{ $item->id }}" href="#"
                                                        class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Hapus</a>
                                                </td>
                                            </tr>
                                            <div class="modal fade" id="modal-hapus{{ $item->id }}">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Konfirmasi Hapus Riwayat</h4>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Apakah Anda yakin ingin menghapus riwayat ini?</p>
                                                            <p><b>{{ $item->bahanBaku->nama_bahan ?? '' }}</b> sejumlah
                                                                <b>{{ $item->jumlah_keluar }}</b></p>
                                                            <p class="text-danger">Aksi ini tidak dapat dibatalkan dan tidak
                                                                akan mengembalikan stok.</p>
                                                        </div>
                                                        <div class="modal-footer justify-content-between">
                                                            <form
                                                                action="{{ route('owner.keluar.delete', ['id' => $item->id]) }}"
                                                                method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button" class="btn btn-default"
                                                                    data-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary">Ya, Hapus
                                                                    Riwayat</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">Belum ada riwayat bahan baku keluar.</td>
                                            </tr>
                                        @endforelse
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
            $('#keluarTable').DataTable({
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ entri",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Berikutnya",
                        "previous": "Sebelumnya"
                    },
                    "emptyTable": "Tidak ada data yang tersedia di dalam tabel",
                    "zeroRecords": "Tidak ditemukan data yang cocok"
                }
            });
        });
    </script>
@endpush
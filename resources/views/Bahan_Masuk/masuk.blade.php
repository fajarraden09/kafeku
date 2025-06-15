@extends('layout.main')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Bahan Masuk</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <a href="{{ route('owner.masuk.create') }}" class="btn btn-primary mb-3">Tambah Riwayat Bahan
                            Masuk</a>

                        {{-- STRUKTUR CARD YANG DIRAPIKAN --}}
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Data Riwayat Bahan Masuk</h3>
                            </div>
                            <div class="card-body">
                                <table id="masukTable" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Bahan</th>
                                            <th>Jumlah Masuk</th>
                                            <th>Tanggal Masuk</th>
                                            <th>Dicatat oleh</th>
                                            <th>Tanggal Kadaluarsa</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($batches as $batch)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $batch->bahanBaku->nama_bahan ?? 'N/A' }}</td>
                                                <td>{{ $batch->jumlah_awal }} {{ $batch->bahanBaku->satuan ?? '' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($batch->created_at)->format('d-m-Y H:i') }}</td>
                                                <td>{{ $batch->user->name ?? 'N/A' }}</td>
                                                <td>{{ $batch->tanggal_kadaluarsa ? \Carbon\Carbon::parse($batch->tanggal_kadaluarsa)->format('d-m-Y') : '-' }}
                                                </td>
                                                <td class="text-center">
                                                    <a data-toggle="modal" data-target="#modal-hapus{{ $batch->id }}" href="#"
                                                        class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Hapus</a>
                                                </td>
                                            </tr>
                                            <div class="modal fade" id="modal-hapus{{ $batch->id }}">
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
                                                            <p>Apakah Anda yakin ingin menghapus riwayat masuk untuk
                                                                <b>{{ $batch->bahanBaku->nama_bahan ?? '' }}</b> sejumlah
                                                                <b>{{ $batch->jumlah_awal }}</b>?
                                                            </p>
                                                            <p class="text-muted">Aksi ini hanya akan menghapus catatan riwayat
                                                                dan tidak akan mempengaruhi jumlah stok saat ini.</p>
                                                        </div>
                                                        <div class="modal-footer justify-content-between">
                                                            <form
                                                                action="{{ route('owner.masuk.delete', ['id' => $batch->id]) }}"
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
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">Belum ada riwayat bahan baku masuk.</td>
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
            $('#masukTable').DataTable({
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
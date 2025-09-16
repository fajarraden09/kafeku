@extends('layout.main')

@section('content')
    <div class="content-wrapper">
        <section class="content mt-3">
            <div class="container-fluid">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <div class="row">
                    <div class="col-md-7">
                        <div class="card">

                            <div class="card-header d-flex p-0">
                                <ul class="nav nav-pills p-2">
                                    <li class="nav-item"><a class="nav-link active btn-filter" href="#"
                                            data-filter="all">Semua</a></li>
                                    @if(isset($kategori))
                                        @foreach ($kategori as $kat)
                                            <li class="nav-item"><a class="nav-link btn-filter" href="#"
                                                    data-filter="{{ $kat->id }}">{{ $kat->nama_kategori }}</a></li>
                                        @endforeach
                                    @endif
                                </ul>
                            </div>

                            <div class="card-body row" id="menu-container">
                                @forelse ($menus as $menu)
                                    <div class="col-md-4 mb-3 menu-card" data-kategori-id="{{ $menu->kategori_id }}">
                                        <div class="card h-100">
                                            <div style="width: 100%; padding-top: 100%; position: relative; overflow: hidden;">
                                                @if ($menu->image)
                                                    <img src="{{ asset('uploads/' . $menu->image) }}" class="card-img-top"
                                                        alt="{{ $menu->nama_menu }}"
                                                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
                                                @else
                                                    <div
                                                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: #f0f0f0; display: flex; justify-content: center; align-items: center; color: #6c757d;">
                                                        <span>No Image</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="card-body text-center d-flex flex-column">
                                                <h5 class="card-title flex-grow-1">{{ $menu->nama_menu }}</h5>
                                                <div class="mb-2">
                                                    @php
                                                        $sisaPorsi = $menu->sisa_porsi;
                                                        $badgeClass = 'bg-success';
                                                        $text = 'Porsi Tersedia: ' . $sisaPorsi;

                                                        if ($sisaPorsi === INF) {
                                                            $badgeClass = 'bg-secondary';
                                                            $text = 'Tanpa Stok';
                                                        } elseif ($sisaPorsi <= 0) {
                                                            $badgeClass = 'bg-danger';
                                                            $text = 'Stok Habis';
                                                        } elseif ($sisaPorsi <= 5) {
                                                            $badgeClass = 'bg-warning text-dark';
                                                        }
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}"
                                                        id="sisa-porsi-{{ $menu->id }}">{{ $text }}</span>
                                                </div>
                                                <p class="card-text">Rp {{ number_format($menu->harga, 0, ',', '.') }}</p>
                                                <button class="btn btn-primary btn-sm btn-add-to-cart mt-auto"
                                                    id="btn-tambah-{{ $menu->id }}" data-id="{{ $menu->id }}"
                                                    data-name="{{ $menu->nama_menu }}" data-price="{{ $menu->harga }}"
                                                    data-sisa-porsi="{{ is_numeric($sisaPorsi) ? $sisaPorsi : -1 }}" {{-- -1
                                                    untuk tak terhingga --}} {{ $sisaPorsi <= 0 && is_numeric($sisaPorsi) ? 'disabled' : '' }}>
                                                    Tambah
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <p class="text-center">Tidak ada menu yang tersedia saat ini.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Pesanan dengan ID untuk CSS --}}
                    <div class="col-md-5">
                        <div class="card" id="kartu-pesanan">
                            <div class="card-body">
                                <form action="{{ route('owner.kasir.store') }}" method="POST" id="order-form">
                                    @csrf
                                    <div class="form-group mb-2">
                                        <label for="nama_pelanggan">Nama Pelanggan <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="nama_pelanggan" id="nama_pelanggan" class="form-control"
                                            placeholder="Masukkan nama pelanggan..." required>
                                    </div>
                                    <hr class="my-1">
                                    <div class="cart-scroll-container">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 100%;">Menu</th>
                                                    <th style="width: 80px; text-align: center;">Jumlah</th>
                                                    <th class="kolom-subtotal">Subtotal</th>
                                                    <th style="width: 60px; text-align: center;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="cart-items">
                                            </tbody>
                                        </table>
                                    </div>
                                    <hr>
                                    <h4>Total: <span id="total-price">Rp 0</span></h4>
                                    <div class="form-group mt-3">
                                        <label for="uang_dibayar">Uang Dibayar</label>
                                        <input type="text" id="uang_dibayar" class="form-control form-control-lg"
                                            placeholder="Masukkan jumlah uang...">
                                    </div>
                                    <h4 class="mt-2">Kembalian: <span id="uang_kembalian">Rp 0</span></h4>

                                    <div class="mt-3">
                                        <strong>Metode Pembayaran:</strong>
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="metode_pembayaran"
                                                    id="metodeTunai" value="Tunai" checked>
                                                <label class="form-check-label" for="metodeTunai">Tunai</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="metode_pembayaran"
                                                    id="metodeQR" value="QR-code">
                                                <label class="form-check-label" for="metodeQR">QR-code</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <strong>Waktu Pembayaran:</strong>
                                        <div class="form-check"><input class="form-check-input" type="radio"
                                                name="waktu_pembayaran" id="bayarLangsung" value="Langsung" checked><label
                                                class="form-check-label" for="bayarLangsung">Bayar Langsung (Lunas)</label>
                                        </div>
                                        <div class="form-check"><input class="form-check-input" type="radio"
                                                name="waktu_pembayaran" id="bayarNanti" value="Nanti"><label
                                                class="form-check-label" for="bayarNanti">Bayar di Akhir (Belum
                                                Dibayar)</label></div>
                                    </div>

                                    <div id="hidden-inputs"></div>
                                    <button type="submit" class="btn btn-success btn-block mt-3">Buat Pesanan</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

<style>
    /* 1. TATA LETAK INTERNAL KARTU (BERLAKU SEJAK AWAL) */

    /* Atur kartu pesanan agar menggunakan flexbox dan tidak melebihi tinggi layar */
    #kartu-pesanan {
        display: flex;
        flex-direction: column;
        max-height: 90vh;
        /* Maksimal 90% dari tinggi layar */
    }

    /* Atur agar .card-body dan #order-form mengisi sisa ruang di dalam kartu */
    #kartu-pesanan .card-body,
    #kartu-pesanan #order-form {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        overflow: hidden;
    }

    /* Atur agar area daftar item bisa di-scroll jika isinya panjang */
    .cart-scroll-container {
        flex-grow: 1;
        overflow-y: auto;
    }

    /* 2. STYLE UNTUK EFEK STICKY (HANYA AKTIF SAAT SCROLL) */

    /* Kelas .is-sticky ini akan ditambahkan oleh JavaScript saat scroll */
    #kartu-pesanan.is-sticky {
        position: fixed;
        top: 20px;
    }

    .kolom-subtotal {
        white-space: nowrap;
        /* Mencegah teks turun ke baris baru */
    }
</style>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            let cart = {};
            let currentTotalPrice = 0; // Variabel untuk menyimpan total harga numerik
            const sisaPorsiState = {};

            // ================= FUNGSI BARU UNTUK MENGHITUNG KEMBALIAN =================
            function calculateAndDisplayChange() {
                const bayarEl = document.getElementById('uang_dibayar');
                const kembalianEl = document.getElementById('uang_kembalian');

                // Hilangkan karakter non-numerik (seperti 'Rp' atau '.') lalu ubah ke angka
                const uangDibayar = parseFloat(bayarEl.value.replace(/[^0-9]/g, '')) || 0;

                let kembalian = 0;
                if (uangDibayar > 0 && uangDibayar >= currentTotalPrice) {
                    kembalian = uangDibayar - currentTotalPrice;
                }

                kembalianEl.textContent = `Rp ${kembalian.toLocaleString('id-ID')}`;
            }
            // ==========================================================================

            function updatePorsiDisplay(menuId, sisa) {
                // ... (Fungsi ini tidak berubah)
            }

            document.querySelectorAll('.btn-add-to-cart').forEach(button => {
                // ... (Bagian ini tidak berubah)
            });

            function renderCart() {
                const cartItemsContainer = document.getElementById('cart-items');
                const totalPriceEl = document.getElementById('total-price');
                const hiddenInputsContainer = document.getElementById('hidden-inputs');

                cartItemsContainer.innerHTML = '';
                hiddenInputsContainer.innerHTML = '';
                let totalPrice = 0;
                let index = 0;

                const sisaPorsiSaatIni = { ...sisaPorsiState };

                for (const id in cart) {
                    if (sisaPorsiSaatIni[id] !== -1) {
                        sisaPorsiSaatIni[id] -= cart[id].jumlah;
                    }
                }

                for (const menuId in sisaPorsiState) {
                    updatePorsiDisplay(menuId, sisaPorsiSaatIni[menuId]);
                }

                for (const id in cart) {
                    const item = cart[id];
                    const subtotal = item.price * item.jumlah;
                    totalPrice += subtotal;

                    const row = `<tr>...</tr>`; // (Isi row tidak berubah)
                    cartItemsContainer.insertAdjacentHTML('beforeend', row);

                    const hiddenId = `<input type="hidden" name="items[${index}][menu_id]" value="${id}">`;
                    const hiddenJumlah = `<input type="hidden" name="items[${index}][jumlah]" value="${item.jumlah}">`;
                    hiddenInputsContainer.insertAdjacentHTML('beforeend', hiddenId);
                    hiddenInputsContainer.insertAdjacentHTML('beforeend', hiddenJumlah);
                    index++;
                }

                totalPriceEl.textContent = `Rp ${totalPrice.toLocaleString('id-ID')}`;

                // ================= MODIFIKASI PADA renderCart() ======================
                // 1. Simpan nilai total harga ke variabel global
                currentTotalPrice = totalPrice;
                // 2. Panggil fungsi kalkulator setiap kali keranjang diperbarui
                calculateAndDisplayChange();
                // =====================================================================
            }

            // ... (Fungsi event listener lainnya tidak berubah) ...

            // ================= EVENT LISTENER BARU UNTUK INPUT UANG DIBAYAR ==========
            document.getElementById('uang_dibayar').addEventListener('input', function () {
                // Format input agar ada titik ribuan saat diketik
                let value = this.value.replace(/[^0-9]/g, '');
                if (value) {
                    this.value = parseInt(value, 10).toLocaleString('id-ID');
                } else {
                    this.value = '';
                }
                // Panggil fungsi kalkulator setiap kali user mengetik
                calculateAndDisplayChange();
            });
            // ==========================================================================

        });
    </script>
@endpush

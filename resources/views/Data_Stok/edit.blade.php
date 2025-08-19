@extends('layout.main')
@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Edit Bahan Baku</h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <form action="{{ route('owner.stok.update', ['id' => $data->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Form Edit Bahan Baku</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="nama_bahan">Nama Bahan</label>
                                        <input type="text" name="nama_bahan" class="form-control"
                                            value="{{ $data->nama_bahan }}">
                                        @error('nama_bahan') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    {{-- <div class="form-group">
                                        <label for="stok">Stok</label>
                                        <input type="number" name="stok" class="form-control" value="{{ $data->stok }}">
                                        @error('stok') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div> --}}
                                    <div class="form-group">
                                        <label for="satuan">Satuan</label>
                                        <input type="text" name="satuan" class="form-control" value="{{ $data->satuan }}">
                                        @error('satuan') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="batas_minimum">Batas Minimum Stok</label>
                                        <input type="number" name="batas_minimum" class="form-control"
                                            value="{{ $data->batas_minimum }}">
                                        @error('batas_minimum') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection
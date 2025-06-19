<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <form class="d-flex">
                    <button type="button" class="btn btn-primary ms-2" onclick="openModal()">
                        <i class="ri-add-circle-line"></i>
                        Tambahkan Pemetaan Kelompok Praktik
                    </button>
                </form>
            </div>
            <h4 class="page-title">Kelompok Praktik</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="basic-datatable" class="table table-striped dt-responsive w-100">
                    <thead>
                        <tr>
                            <th>Nama Kelompok</th>
                            <th>Mata Kuliah</th>
                            <th style="text-wrap: wrap;">Dosen</th>
                            <th style="text-wrap: wrap;">Mahasiswa</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>

            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div> <!-- end row-->

<div class="modal fade" id="main-modal" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-label"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-submit" action="<?= url('kelompok-praktik/submit') ?>" method="POST">
                @csrf
                <input type="hidden" name="id_kelompok_head" id="id_kelompok_head">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-2">
                                <label for="nama_kelompok" class="form-label">Nama Kelompok <small class="text-danger">*</small></label>
                                <input type="text" name="nama_kelompok" id="nama_kelompok" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-2">
                                <label for="id_detsem" class="form-label">Mata Kuliah Praktik <small class="text-danger">*</small></label>
                                <select name="id_detsem" class="form-select" id="id_detsem" required>
                                    <option></option>
                                    @foreach($matkul_praktik as $item)
                                    <option value="<?= $item->ID_DETSEM ?>"><?= $item->NAMA_MATKUL ?></option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="kode_dosen" class="form-label">Dosen <small class="text-danger">*</small></label>
                                <select name="kode_dosen[]" multiple class="form-select" id="kode_dosen" required>
                                    @foreach($dosen as $item)
                                    <option value="<?= $item->KODE_DOSEN ?>"><?= $item->NAMA_DOSEN ?></option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="kode_mahasiswa" class="form-label">Mahasiswa <small class="text-danger">*</small></label>
                                <select name="kode_mahasiswa[]" multiple class="form-select kode_mahasiswa" id="kode_mahasiswa" disabled required></select>
                                <span id="loading-container"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="submitForm()">Simpan</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
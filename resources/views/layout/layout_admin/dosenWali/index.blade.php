<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <form class="d-flex">
                    <button type="button" class="btn btn-primary ms-2" onclick="openModal()">
                        <i class="ri-add-circle-line"></i>
                        Tambahkan Pemetaan Dosen Wali
                    </button>
                </form>
            </div>
            <h4 class="page-title">Dosen Wali</h4>
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
                            <th style="width: 30%;">Dosen</th>
                            <th style="width: 50%; text-wrap: wrap;">Mahasiswa</th>
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
            <form id="form-submit" action="<?= url('dosen-wali/submit') ?>" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <input type="hidden" name="id_wali_dosen" id="id_wali_dosen">
                                <label for="kode_dosen" class="form-label">Dosen <small class="text-danger">*</small></label>
                                <select name="kode_dosen"  class="form-select" id="kode_dosen" required>
                                    @foreach($dosen as $item)
                                    <option value="<?= $item->KODE_DOSEN ?>"><?= $item->NAMA_DOSEN ?></option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="nama_dosen" id="nama_dosen">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="kode_mahasiswa" class="form-label">Mahasiswa <small class="text-danger">*</small></label>
                                <select name="kode_mahasiswa[]" multiple class="form-select kode_mahasiswa" id="kode_mahasiswa" required>
                                    @foreach($mhs as $item)
                                    <option value="<?= $item->KODE_MAHASISWA ?>"><?= $item->NIM ?> - <?= $item->NAMA_MAHASISWA ?></option>
                                    @endforeach
                                </select>
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

<div class="modal fade" id="update-modal" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-label"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-update" action="<?= url('dosen-wali/update') ?>" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md">
                            <div class="mb-2">
                                <input type="hidden" name="up_id_wali_dosen" id="up_id_wali_dosen">
                                <label for="kode_dosen" class="form-label">Dosen <small class="text-danger">*</small></label>
                                <select name="kode_dosen"  class="form-select" id="kode_dosen" required disabled>
                                    @foreach($dosen as $item)
                                    <option value="<?= $item->KODE_DOSEN ?>"><?= $item->NAMA_DOSEN ?></option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div id="div_mahasiswa">
                        </div>
                        <div class="delete">

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="submitUpdateForm()">Simpan</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
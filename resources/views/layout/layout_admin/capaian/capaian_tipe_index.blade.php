<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <form class="d-flex">
                    <button type="button" class="btn btn-primary ms-2" onclick="openModalTipeCapaian()">
                        <i class="ri-add-circle-line"></i>
                        Tambah Tipe Capaian
                    </button>
                </form>
            </div>
            <h4 class="page-title">Tipe Capaian</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="basic-datatable-tipe-capaian" class="table table-striped dt-responsive nowrap w-100">
                    <thead>
                        <tr>
                            <th style="width: 70%;">Tipe Penilaian</th>
                            <th>Status</th>
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
            <form id="form-submit" action="<?= url('tipe-capaian/submit') ?>" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4" hidden>
                            <div class="mb-3">
                                <label for="id_tipe_capaian" class="form-label">Kode Capaian</label>
                                <input type="text" id="id_tipe_capaian" name="id_tipe_capaian" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="tipe_penilaian" class="form-label">Tipe Penilaian <small class="text-danger">*</small></label>
                                <input type="text" id="tipe_penilaian" name="tipe_penilaian" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="ket_tipe_capaian" class="form-label">Keterangan</label>
                                <textarea class="form-control" name="ket_tipe_capaian" rows="5" cols="150" id="ket_tipe_capaian"></textarea>
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
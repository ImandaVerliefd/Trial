<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <form class="d-flex">
                    <button type="button" class="btn btn-primary ms-2" onclick="openModal()">
                        <i class="ri-add-circle-line"></i>
                        Tambah Ruangan
                    </button>
                </form>
            </div>
            <h4 class="page-title">Ruangan</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="basic-datatable" class="table table-striped dt-responsive nowrap w-100">
                    <thead>
                        <tr>
                            <th style="width: 60%;">Nama Ruangan</th>
                            <th>Tipe</th>
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
            <form id="form-submit" action="<?= url('ruangan/submit') ?>" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" hidden>
                            <div class="mb-3">
                                <label for="id_ruangan" class="form-label">ID Ruangan</label>
                                <input type="text" id="id_ruangan" name="id_ruangan" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-2">
                                <label for="nama_ruangan" class="form-label">Nama Ruangan <small class="text-danger">*</small></label>
                                <input type="text" id="nama_ruangan" name="nama_ruangan" class="form-control" placeholder="Masukkan nama ruangan" required>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="mb-3 position-relative">
                                <label class="form-label" for="tipe">Tipe <small class="text-danger">*</small></label>
                                <input type="text" class="form-control" placeholder="Masukkan tipe ruangan" id="tipe" name="tipe">
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
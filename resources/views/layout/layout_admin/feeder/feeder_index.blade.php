<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <form class="d-flex">
                    <button type="button" class="btn btn-primary ms-2" onclick="openModal()">
                        <i class="ri-add-circle-line"></i>
                        Tambah Feeder
                    </button>
                </form>
            </div>
            <h4 class="page-title">Feeder</h4>
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
                            <th style="width: 90%;">Feeder</th>
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
            <form id="form-submit" action="<?= url('feeder/submit') ?>" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" hidden>
                            <div class="mb-2">
                                <label for="id_feeder" class="form-label">ID Feeder <small class="text-danger">*</small></label>
                                <input type="text" id="id_feeder" name="id_feeder" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-2">
                                <label for="feeder" class="form-label">Feeder <small class="text-danger">*</small></label>
                                <input type="text" id="feeder" name="feeder" class="form-control" required>
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
<style>
    td:nth-child(2) {
        word-wrap: break-word;
        overflow-wrap: break-word;
        white-space: normal !important;
    }

    [data-dtr-index="1"] .dtr-data {
        overflow-wrap: break-word;
        white-space: normal !important;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <form class="d-flex">
                    <button type="button" class="btn btn-primary ms-2" onclick="openModal()">
                        <i class="ri-add-circle-line"></i>
                        Tambah Capaian
                    </button>
                </form>
            </div>
            <h4 class="page-title">Capaian</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    @foreach($prodi as $key => $item)
                    <li class="nav-item" role="presentation">
                        <a href="#" data-bs-toggle="tab" aria-expanded="true" class="nav-link" aria-selected="true" role="tab" data-prodi="<?= $item->ID_PRODI ?>" onclick="reRenderTable()">
                            <?= $item->JENJANG ?> <?= $item->PRODI ?>
                        </a>
                    </li>
                    @endforeach
                </ul>

                <ul class="nav nav-pills bg-nav-pills nav-justified mb-3 w-50" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a href="#" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0" aria-selected="true" role="tab" data-tipe="CPL" onclick="reRenderTable()">
                            Capaian Pembelajaran (CPL)
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="#" data-bs-toggle="tab" aria-expanded="true" class="nav-link rounded-0" aria-selected="false" role="tab" tabindex="-1" data-tipe="CPMK" onclick="reRenderTable()">
                            Capaian Mata Kuliah (CPMK)
                        </a>
                    </li>
                </ul>
                <table id="basic-datatable" class="table table-striped dt-responsive nowrap w-100">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th style="width: 50%;">Capaian</th>
                            <th>Jenis</th>
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
            <form id="form-submit" action="<?= url('capaian/submit') ?>" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" hidden>
                            <div class="mb-3">
                                <label for="kode_capaian" class="form-label">Kode Capaian</label>
                                <input type="text" id="kode_capaian" name="kode_capaian" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-2">
                                <label for="capaian" class="form-label">Capaian <small class="text-danger">*</small></label>
                                <input type="text" id="capaian" name="capaian" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label for="kurikulum" class="form-label">Kurikulum <small class="text-danger">*</small></label>
                                <select class="form-select" id="kurikulum" required>
                                    <option value="">-- Pilih Kurikulum --</option>
                                    @foreach($kurikulum as $item)
                                    <option value="<?= $item->ID_KURIKULUM ?>"><?= $item->KURIKULUM ?></option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="kurikulum_capaian">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label for="prodi" class="form-label">Program Studi <small class="text-danger">*</small></label>
                                <select class="form-select" id="prodi" required>
                                    <option value="">-- Pilih Program Studi --</option>
                                    @foreach($prodi as $item)
                                    <option value="<?= $item->ID_PRODI ?>"><?= (!empty($item->JENJANG) ? $item->JENJANG . ' ' : '') ?><?= $item->PRODI ?></option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="prodi_capaian">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label for="jenis" class="form-label">Jenis Capaian <small class="text-danger">*</small></label>
                                <select class="form-select" id="jenis" required>
                                    <option value="">-- Pilih Jenis Capaian --</option>
                                    <option value="CPMK">CPMK</option>
                                    <option value="CPL">CPL</option>
                                </select>
                                <input type="hidden" name="jenis_capaian">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-2">
                                <span class="text-danger" id="msg-jenis-capaian"></span>
                            </div>
                        </div>
                        <div id="container-cpl">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="select2-capaian-cpl" class="form-label">Capaian Pembelajaran (CPL) <small class="text-danger">*</small></label>
                                    <select class="form-select" name="capaian_parent[]" id="select2-capaian-cpl" required multiple>
                                    </select>
                                </div>
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
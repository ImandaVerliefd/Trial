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
                        Tambah Pemetaan CPMK
                    </button>
                </form>
            </div>
            <h4 class="page-title">Pemetaan CPMK</h4>
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

                <table id="basic-datatable" class="table table-striped table-hover dt-responsive nowrap w-100">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Mata Kuliah</th>
                            <th>CPMK</th>
                            <th>Kurikulum</th>
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
            <form id="form-submit" action="<?= url('capaian-matkul/submit') ?>" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="kode_matkul" class="form-label">Mata Kuliah <small class="text-danger">*</small></label>
                                <select name="kode_matkul" class="form-select" id="kode_matkul" required>
                                    @foreach($mataKuliah as $item)
                                    <option value="<?= $item->KODE_MATKUL ?>"><?= $item->JENJANG ?? 'Pendidikan Profesi' ?> - <?= $item->NAMA_MATKUL ?></option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="id_kurikulum" class="form-label">Kurikulum <small class="text-danger">*</small></label>
                                <select name="id_kurikulum" class="form-select" id="id_kurikulum" required>
                                    @foreach($kurikulum as $item)
                                    <option value="<?= $item->ID_KURIKULUM ?>" data-tahun="<?= $item->TAHUN ?>"><?= $item->KURIKULUM ?></option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="tahun_kurikulum">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-2">
                                <label for="id_capaian" class="form-label">Capaian Mata Kuliah<small class="text-danger">*</small></label>
                                <select name="id_capaian[]" class="form-select" id="id_capaian" required multiple>
                                    <!-- @foreach($capaian as $item)
                                    <option value="{{ $item->KODE_CAPAIAN }}">{{ $item->CAPAIAN }}</option>
                                    @endforeach -->
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
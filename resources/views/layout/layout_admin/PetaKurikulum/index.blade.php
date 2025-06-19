<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <form class="d-flex">
                    <button type="button" class="btn btn-primary ms-2" onclick="openModal()">
                        <i class="ri-add-circle-line"></i>
                        Tambah Peta Kurikulum
                    </button>
                </form>
            </div>
            <h4 class="page-title">Peta Kurikulum</h4>
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
                <table id="basic-datatable" class="table table-striped dt-responsive nowrap w-100">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th style="width: 60%;">Mata Kuliah</th>
                            <th>Semester</th>
                            <th>Kurikulum</th>
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
            <form id="form-submit" action="<?= url('peta-kurikulum/submit') ?>" method="POST">
                @csrf
                <input type="hidden" name="idDetSem" id="id_detSem">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="mataKuliah" class="form-label">Mata Kuliah <small class="text-danger">*</small></label>
                                <select name="kode_matkul" class="form-select" id="mataKuliah" required>
                                    <option value="">-- Pilih Mata Kuliah --</option>
                                    @foreach($matkul as $item)
                                    <option value="<?= $item->KODE_MATKUL ?>"><?= $item->JENJANG ?? 'Pendidikan Profesi' ?> - <?= $item->NAMA_MATKUL ?></option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="kurikulum" class="form-label">Kurikulum <small class="text-danger">*</small></label>
                                <select name="id_kurikulum" class="form-select" id="kurikulum" required disabled>
                                    <option value="">-- Pilih Kurikulum --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="id_semester" class="form-label">Semester <small class="text-danger">*</small></label>
                                <select name="id_semester" class="form-select" id="id_semester" required>
                                    <option value="">-- Pilih Semester --</option>
                                    @foreach($semester as $item)
                                    <option value="<?= $item->ID_SEMESTER ?>"><?= $item->SEMESTER ?></option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="used_semester" class="form-label">Semester Digunakan<small class="text-danger">*</small></label>
                                <select name="used_semester" class="form-select" id="used_semester" required>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label" for="bahan-kajian">Rumpun Matkul</label>
                                <input type="text" name="rumpun_matkul" id="" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label" for="bahan-kajian">Kategori Matkul</label>
                                <input type="text" name="kate_matkul" id="" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-2">
                                <label class="form-label" for="bahan-kajian">Deskripsi Matkul</label>
                                <textarea cols="30" rows="6" name="desc_matkul" id="" class="form-control" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label" for="bahan-kajian">Apakah anda yakin ingin membuat mata kuliah ini menjadi umum?</label>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input" name="is_umum" id="is_umum" value="1" style="cursor: pointer;">
                                        Ya
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label" for="bahan-kajian">Apakah anda yakin ingin membuat mata kuliah ini menjadi lintas prodi?</label>
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="is_lintas_prodi" id="is_lintas_prodi" value="1" style="cursor: pointer;">
                                            Ya
                                        </label>
                                    </div>
                                    <select name="id_linprod[]" class="form-select" id="lintas_prodi" multiple>
                                        @foreach($prodi as $item)
                                        <option value="<?= $item->ID_PRODI ?>"><?= $item->JENJANG ?> <?= $item->PRODI ?></option>
                                        @endforeach
                                    </select>
                                    <div id="container-linprod"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-2">
                                <label class="form-label" for="bahan-kajian">Apakah anda yakin ingin membuat mata kuliah ini menjadi praktek lapangan?</label>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input" name="is_lapangan" id="is_lapangan" value="1" style="cursor: pointer;">
                                        Ya
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="start_date" class="form-label">Start Date <small class="text-danger">*</small></label>
                                <input type="date" name="start_date" id="start_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="end_date" class="form-label">End Date <small class="text-danger">*</small></label>
                                <input type="date" name="end_date" id="end_date" class="form-control">
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

<style>
    .select2-container {
        width: 87% !important;
        z-index: 1055 !important;
    }

    .select2-container .select2-search--inline .select2-search__field {
        margin-top: 0px !important;
    }
</style>
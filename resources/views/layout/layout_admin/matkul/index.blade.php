<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <form class="d-flex">
                    <button type="button" class="btn btn-primary ms-2" onclick="openModal()">
                        <i class="ri-add-circle-line"></i>
                        Tambah Mata Kuliah
                    </button>
                </form>
            </div>
            <h4 class="page-title">Mata Kuliah</h4>
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
                            <th>Kode</th>
                            <th style="width: 60%;">Nama Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Pertemuan</th>
                            <th>Prodi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Kode</th>
                            <th style="width: 60%;">Nama Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Pertemuan</th>
                            <th>Prodi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </tfoot>
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
            <form id="form-submit" action="<?= url('mata-kuliah/submit') ?>" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="kode_matkul" class="form-label">Kode Mata Kuliah <small class="text-danger">*</small></label>
                                <input type="text" id="kode_matkul" name="kode_matkul" class="form-control" placeholder="Masukkan nama mata kuliah" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="nama_matkul" class="form-label">Nama Mata Kuliah <small class="text-danger">*</small></label>
                                <input type="text" id="nama_matkul" name="nama_matkul" class="form-control" placeholder="Masukkan nama mata kuliah" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="prodi" class="form-label">Program Studi <small class="text-danger">*</small></label>
                                <select class="form-select" id="prodi" required>
                                    <option value="">-- Pilih Program Studi --</option>
                                    @foreach($prodi as $item)
                                    <option value="<?= $item->ID_PRODI ?>"><?= $item->JENJANG ?> <?= $item->PRODI ?></option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="prodi">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="sks" class="form-label">SKS <small class="text-danger">*</small></label>
                                <input type="number" id="sks" name="sks" class="form-control" placeholder="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="jumlah_pertemuan" class="form-label">Jumlah Pertemuan <small class="text-danger">*</small></label>
                                <input type="number" id="jumlah_pertemuan" name="jumlah_pertemuan" class="form-control" placeholder="0" required>
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
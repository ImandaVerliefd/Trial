<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title"><?= $title ?></h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form id="form-submit" action="<?= url('paket-mata-kuliah/submit') ?>" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-2">
                                <label for="prodi" class="form-label">Program Studi <small class="text-danger">*</small></label>
                                <select class="form-select" id="prodi" required>
                                    <option value="">-- Pilih Program Studi --</option>
                                    @foreach($prodi as $item)
                                    <option value="<?= $item->ID_PRODI ?>"><?= $item->JENJANG . ' ' . $item->PRODI ?></option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="prodi">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="semester" class="form-label">Tahun Ajaran <small class="text-danger">*</small></label>
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <select class="form-select" id="tahunajar" required>
                                            <option value="">-- Pilih Tahun Ajaran --</option>
                                            @foreach($tahunajar as $item)
                                            <option value="<?= $item->ID_TAHUN_AJAR ?>"><?= $item->TAHUN_AJAR ?></option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="tahunajar">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="semester" class="form-label">Semester <small class="text-danger">*</small></label>
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <select name="used_semester" class="form-select" id="used_semester" required>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-2">
                                <label for="paket-matkul" class="form-label">Mata Kuliah<small class="text-danger">*</small></label>
                                <select class="form-select" id="paket-matkul" name="paket-matkul[]" multiple required>
                                </select>
                                <div class="container-kode-matkul" hidden></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div> <!-- end card body-->
            <div class="card-footer">
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="submitForm()">Simpan</button>
                </div>
            </div>
        </div> <!-- end card -->
    </div><!-- end col-->
</div> <!-- end row-->
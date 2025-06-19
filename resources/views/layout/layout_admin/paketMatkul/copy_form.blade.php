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
                <form id="form-copy-paket" action="<?= url('paket-mata-kuliah/copy') ?>" method="POST">
                    @csrf
                    <h5 class="mb-3">Paket source</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="prodi_source" class="form-label">Program Studi <small class="text-danger">*</small></label>
                                <select class="form-select" id="prodi_source" name="prodi_source" required>
                                    <option value="">-- Pilih Program Studi --</option>
                                    @foreach($prodi as $item)
                                    <option value="<?= $item->ID_PRODI ?>"><?= $item->JENJANG . ' ' . $item->PRODI ?></option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="tahunajar_source" class="form-label">Tahun Ajaran <small class="text-danger">*</small></label>
                                <select class="form-select" id="tahunajar_source" name="tahunajar_source" required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>
                                    @foreach($tahunajar as $item)
                                    <option value="<?= $item->ID_TAHUN_AJAR ?>"><?= $item->TAHUN_AJAR ?></option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="semester_source" class="form-label">Semester <small class="text-danger">*</small></label>
                                <select name="semester_source" class="form-select" id="semester_source" required>
                                    <option value="">-- Pilih Semester --</option>
                                    @foreach($semester as $item)
                                    <option value="<?= $item->KODE_SEMESTER ?>"><?= $item->SEMESTER_DESKRIPSI ?></option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-3 mt-4">Mata Kuliah yang akan disalin</h5>
                    <div class="mb-2">
                        <label for="matkul_to_copy" class="form-label">Mata Kuliah <small class="text-danger">*</small></label>
                        <select class="form-select" id="matkul_to_copy" name="matkul_to_copy[]" multiple required>
                            <!-- Options will be loaded via AJAX -->
                        </select>
                        <small class="text-muted">Silahkan pilih Matkul yang akan di salin. Atau Biarkan kosong jika ingin menyalin semua Matkul.</small>
                    </div>

                    <h5 class="mb-3 mt-4">Paket Target</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label for="prodi_target" class="form-label">Program Studi Target <small class="text-danger">*</small></label>
                                <select class="form-select" id="prodi_target" name="prodi_target" required>
                                    <option value="">-- Pilih Program Studi Target --</option>
                                    @foreach($prodi as $item)
                                    <option value="<?= $item->ID_PRODI ?>"><?= $item->JENJANG . ' ' . $item->PRODI ?></option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label for="tahunajar_target" class="form-label">Tahun Ajaran Target <small class="text-danger">*</small></label>
                                <select class="form-select" id="tahunajar_target" name="tahunajar_target" required>
                                    <option value="">-- Pilih Tahun Ajaran Target --</option>
                                    @foreach($tahunajar as $item)
                                    <option value="<?= $item->ID_TAHUN_AJAR ?>"><?= $item->TAHUN_AJAR ?></option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label for="semester_target" class="form-label">Target Semester <small class="text-danger">*</small></label>
                                <select name="semester_target" class="form-select" id="semester_target" required>
                                    <option value="">-- Pilih Target Semester --</option>
                                    @foreach($semester as $item)
                                    <option value="<?= $item->KODE_SEMESTER ?>"><?= $item->SEMESTER_DESKRIPSI ?></option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div> <!-- end card body-->
            <div class="card-footer">
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light" onclick="window.history.back()">Kembali</button>
                    <button type="button" class="btn btn-primary" onclick="submitCopyForm()">Salin Paket</button>
                </div>
            </div>
        </div> <!-- end card -->
    </div><!-- end col-->
</div> <!-- end row-->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <form class="d-flex">
                    <button type="button" class="btn btn-primary ms-2" onclick="openModal()">
                        <i class="ri-add-circle-line"></i>
                        Tambah Semester
                    </button>
                </form>
            </div>
            <h4 class="page-title">Semester</h4>
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
                            <th>Semester</th>
                            <th>Tahun</th>
                            <th>Kurikulum</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>

            </div> </div> </div></div> <div class="modal fade" id="main-modal" data-bs-backdrop="static" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-label"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-submit" action="<?= url('semester/submit') ?>" method="POST">
                @csrf
                <input type="hidden" name="id_semester">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-1">
                                <label for="semester" class="form-label">Semester <small class="text-danger">*</small></label>
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <select name="semester" id="semester" class="form-control" required>
                                        <option value="">-- Pilih Semester --</option>
                                        <option value="Ganjil">Ganjil</option>
                                        <option value="Genap">Genap</option>
                                    </select>
                                    <input class="form-control yearpicker" type="text" name="sem_year_start" id="year_start" required placeholder="Select Start Year">
                                    <span> / </span>
                                    <input class="form-control yearpicker" type="text" name="sem_year_end" id="year_end" required placeholder="Select End Year">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-1">
                                <label for="id_kurikulum" class="form-label">Kurikulum <small class="text-danger">*</small></label>
                                <select name="id_kurikulum" class="form-select" id="id_kurikulum" required>
                                    <option value="">-- Pilih Kurikulum --</option>
                                    @foreach($kurikulum as $item)
                                    <option value="<?= $item->ID_KURIKULUM ?>" data-tahun="<?= $item->TAHUN ?>"><?= $item->KURIKULUM ?></option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="tahun_kurikulum">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="start_date" class="form-label">Start Semester <small class="text-danger">*</small></label>
                                <input type="datetime-local" name="start_date" id="start_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="end_date" class="form-label">End Semester <small class="text-danger">*</small></label>
                                <input type="datetime-local" name="end_date" id="end_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="start_perwalian" class="form-label">Start Perwalian <small class="text-danger">*</small></label>
                                <input type="datetime-local" name="start_perwalian" id="start_perwalian" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="status_perwalian" class="form-label">Status Perwalian <small class="text-danger">*</small></label>
                                <select name="status_perwalian" id="status_perwalian" class="form-select" required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="0">BELUM DIMULAI</option>
                                    <option value="1">DIMULAI</option>
                                    <option value="2">DITUTUP</option>
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
        </div></div></div>
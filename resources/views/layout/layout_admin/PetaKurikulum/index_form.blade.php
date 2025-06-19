<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Peta Kurikulum</h4>
        </div>
    </div>
</div>

<form id="form-submit" action="<?= url('peta-kurikulum/submit') ?>" method="POST">
    @csrf
    <div class="card">
        <div class="card-body">
            <div class="d-flex gap-2 align-items-end w-100">
                <div class="mb-2 w-100">
                    <label class="form-label" for="progStudi">Program Studi</label>
                    <select name="prodi" class="form-select" id="progStudi" required>
                        <option value=""></option>
                    </select>
                </div>
                <div class="mb-2 w-100">
                    <label class="form-label" for="tahunAjaran">Tahun Ajaran</label>
                    <select name="tahun_ajar" class="form-select" id="tahunAjaran" required>
                        <option value=""></option>
                    </select>
                </div>
                <div class="mb-2 w-100">
                    <label class="form-label" for="semester">Semester</label>
                    <select name="semester" class="form-select" id="semester" required>
                        <option value=""></option>
                    </select>
                </div>
                <div class="mb-2">
                    <button type="button" class="btn btn-success" style="width: max-content;" onclick="renderPaperForm(this)">Tampilkan Paket</button>
                </div>
            </div>
        </div>
    </div>

    <div id="main-container">
        <div class="card">
            <div class="card-body">
                <div class="alert alert-info text-center mb-0" role="alert">
                    <div class="avatar-sm mb-2 mx-auto">
                        <span class="avatar-title bg-info rounded-circle">
                            <i class="ri-information-line align-middle fs-22"></i>
                        </span>
                    </div>
                    <h4 class="alert-heading">Informasi Penting!</h4>
                    <p>Harap pilih <strong>Program Studi</strong>, <strong>Tahun Ajar</strong>, dan <strong>Semester</strong> 
                    terlebih dahulu sebelum melanjutkan proses. Pilihan ini diperlukan agar paket mata kuliah yang ditampilkan 
                    sesuai dengan kebutuhan Anda dan proses berjalan dengan benar.</p>
                    <hr class="border-info border-opacity-25">
                    <p class="mb-0">Jika Anda belum mengisi salah satu dari ketiga pilihan tersebut, silakan lengkapi terlebih dahulu untuk melanjutkan.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer mb-2">
        <button type="button" class="btn btn-primary" onclick="submitForm()">Simpan</button>
    </div>
</form>
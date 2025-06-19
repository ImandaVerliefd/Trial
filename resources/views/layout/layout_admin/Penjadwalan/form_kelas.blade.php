<div class="sub-container-kelas">
</div>

<div class="row">
    <div class="col-12">
        <button type="button" class="w-100 btn btn-primary btn-tambah-kelas" onclick="addKelas()">
            Tambahkan Kelas
        </button>
        <div class="d-flex justify-content-end mt-3 gap-2">
            <button type="button" class="btn btn-danger" onclick="location.href=`<?= url('penjadwalan') ?>`">Kembali</button>
            <button type="button" class="btn btn-primary" onclick="submitFormKelas()">Simpan</button>
        </div>
    </div>
</div>
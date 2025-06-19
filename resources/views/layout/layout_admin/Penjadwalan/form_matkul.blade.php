<div class="sub-container-matkul">
</div>

<button type="button" class="w-100 btn btn-primary btn-tambah-jadwal" onclick="addJadwal()">
    Tambahkan Jadwal
</button>


<div class="d-flex justify-content-end mt-3 gap-2">
    <button type="button" class="btn btn-primary" onclick="submitFormMatkul()">Simpan</button>
</div>

<script>
    var index = 0
    var rawDetMatkul = <?= json_encode($detail_matkul) ?> || [];
    if (rawDetMatkul.KODE) {        
        addExistJadwal(rawDetMatkul);
    } else {
        addJadwal();
    }

    function addJadwal() {
        $('.btn-tambah-jadwal').show()

        $('.sub-container-matkul').append(`
            <div id="sub-container-${index}">
                <input type="hidden" name="kode_jadwal[]">
                <div class="row justify-content-sm-between">
                    <div class="w-100">
                        <div class="w-100 d-flex justify-content-between align-items-end mb-1">
                            <label class="form-check-label" for="task1">
                                Masukkan Hari
                            </label>
                            <button type="button" class="btn btn-danger btn-delete" onclick="hapusJadwal(${index})">
                                Hapus Jadwal
                            </button>
                        </div>
                        <select name="hari_matkul[]" id="hari_matkul_${index}" class="form-control" required>
                            <?php $itemHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] ?>
                            <option value="">-- Pilih Hari --</option>
                            <?php foreach ($itemHari as $mainHari) { ?>
                                <option value="<?= $mainHari ?>"><?= $mainHari ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="row justify-content-sm-between mt-2">
                    <div class="col-5">
                        <div class="w-100">
                            <label class="form-check-label" for="jam_mulai_0">
                                Masukkan Jam Mulai
                            </label>
                            <input type="time" name="jam_mulai[]" id="jam_mulai_${index}" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="w-100">
                            <label class="form-check-label" for="jam_selesai_0">
                                Masukkan Jam Selesai
                            </label>
                            <input type="time" name="jam_selesai[]" id="jam_selesai_${index}" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="w-100">
                            <label class="form-check-label" for="jam_selesai_0">
                                Masukkan SKS
                            </label>
                            <input type="number" name="sks_digunakan[]" id="sks_digunakan_${index}" class="form-control" required>
                        </div>
                    </div>
                    <div id="alert-container_${index}"></div>
                </div>
                <div class="row justify-content-sm-between mt-2">
                    <div class="w-100" id="alert_container_${index}">
                    </div>
                </div>
                <div class="row justify-content-sm-between mt-2">
                    <div class="w-50">
                        <label class="form-check-label" for="task1">
                            Masukkan Ruangan
                        </label>
                        <select name="id_ruangan[]" id="task1" class="form-control" required>
                            <option value="">-- Pilih Ruangan --</option>
                            <?php foreach ($data_ruangan as $itemRuangan) { ?>
                                <option value="<?= $itemRuangan->ID_RUANGAN ?>"><?= $itemRuangan->NAMA_RUANGAN ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="w-25">
                        <label class="form-check-label" for="metodeAjar">
                            Metode Ajar
                        </label>
                        <select name="metode_ajar[]" id="metodeAjar" class="form-control" required>
                            <option value="">-- Pilih Metode Ajar --</option>
                            <?php foreach ($metode_ajar as $itemAjar) { ?>
                                <option value="<?= $itemAjar->ID_METODE ?>"><?= $itemAjar->METODE ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="w-25">
                        <label class="form-check-label" for="kapasitas">
                            Kapasitas Ruangan
                        </label>
                        <input type="number" name="kapasitas[]" id="kapasitas" class="form-control" required>
                    </div>
                </div>
                <hr>
            </div>
        `)

        index++
    }

    function addExistJadwal(rawDetMatkul) {        
        let kodeJadwal = rawDetMatkul.KODE ? rawDetMatkul.KODE.split(';') : [];

        let hari = rawDetMatkul.HARI ? rawDetMatkul.HARI.split(';') : [];
        let jamMulai = rawDetMatkul.JAM_MULAI ? rawDetMatkul.JAM_MULAI.split(';') : [];
        let jamSelesai = rawDetMatkul.JAM_SELESAI ? rawDetMatkul.JAM_SELESAI.split(';') : [];
        let sks = rawDetMatkul.SKS_MATKUL ? rawDetMatkul.SKS_MATKUL.split(';') : [];
        let idRuangan = rawDetMatkul.ID_RUANGAN ? rawDetMatkul.ID_RUANGAN.split(';') : [];
        let idMetode = rawDetMatkul.ID_METODE ? rawDetMatkul.ID_METODE.split(';') : [];
        let KapasitasRuangan = rawDetMatkul.KAPASITAS_RUANGAN ? rawDetMatkul.KAPASITAS_RUANGAN.split(';') : [];

        $.each(kodeJadwal, function(indexEach, jadwalCode) {
            addJadwal()

            $('.btn-delete').each(function() {
                $(this).remove();
            });

            $('.btn-tambah-jadwal').hide()
            
            $(`input[name="kode_jadwal[]"]`).eq(indexEach).val(kodeJadwal[indexEach])
            $(`select[name="hari_matkul[]"]`).eq(indexEach).val(hari[indexEach]).trigger('change')
            $(`input[name="jam_mulai[]"]`).eq(indexEach).val(jamMulai[indexEach])
            $(`input[name="jam_selesai[]"]`).eq(indexEach).val(jamSelesai[indexEach])
            $(`input[name="sks_digunakan[]"]`).eq(indexEach).val(sks[indexEach])
            $(`select[name="id_ruangan[]"]`).eq(indexEach).val(idRuangan[indexEach]).trigger('change')
            $(`select[name="metode_ajar[]"]`).eq(indexEach).val(idMetode[indexEach]).trigger('change')
            $(`input[name="kapasitas[]"]`).eq(indexEach).val(KapasitasRuangan[indexEach])
        })
    }

    function hapusJadwal(index) {
        $(`#sub-container-${index}`).remove()
    }
</script>
<!-- <?php if (empty($detail_matkul->HARI)) { ?>
    <div class="alert alert-danger mt-3" role="alert">
        <i class="ri-close-circle-line me-1 align-middle fs-16"></i>
        Lengkapi penjadwalan mata kuliah terlebih dahulu!
    </div>
<?php } ?> -->
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Pertemuan</th>
            <th>Nama Pembelajaran</th>
            <th>Bobot</th>
            <th>Dosen Pengampu</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $prevID = null;
        $rowspanCount = [];

        foreach ($detail_subCPMK as $item) {
            if (!isset($rowspanCount[$item->ID_CAPAIAN_DETAIL])) {
                $rowspanCount[$item->ID_CAPAIAN_DETAIL] = 0;
            }
            $rowspanCount[$item->ID_CAPAIAN_DETAIL]++;
        }

        foreach ($detail_subCPMK as $index => $item) {
        ?>
            <tr>
                <td>
                    Pertemuan <?= $item->PERTEMUAN ?? '' ?>
                    <input type="hidden" name="id_capaian_detail[]" value="<?= $item->ID_CAPAIAN_DETAIL ?>">
                    <input type="hidden" name="id_mapping[]" value="<?= $item->ID_MAPPING ?>">
                </td>
                <?php if ($item->ID_CAPAIAN_DETAIL !== $prevID) { ?>
                    <td rowspan="<?= $rowspanCount[$item->ID_CAPAIAN_DETAIL] ?>" class="container-nama-pembelajaran">
                        <?= $item->NAMA_PEMBELAJARAN ?>
                    </td>
                    <td rowspan="<?= $rowspanCount[$item->ID_CAPAIAN_DETAIL] ?>" class="container-bobot">
                        <?= $item->TOTAL_BOBOT ?? 0 ?> Point
                    </td>
                <?php } ?>
                <td>
                    <select name="kode_dosen[]" class="form-control select2-dosen" required>
                        <option value="">-- Pilih Dosen Pengampu --</option>
                        <?php foreach ($data_dosen as $itemDosen) { ?>
                            <option value="<?= $itemDosen->KODE_DOSEN ?>" <?= ($item->KODE_DOSEN == $itemDosen->KODE_DOSEN) ? 'selected' : '' ?>>
                                <?= $itemDosen->NAMA_DOSEN ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
        <?php
            $prevID = $item->ID_CAPAIAN_DETAIL;
        }
        ?>
    </tbody>
</table>
<div class="col-12">
    <!-- <?php if (!empty($detail_matkul->HARI)) { ?>
        
    <?php } ?> -->
    <div class="d-flex justify-content-end mt-3">
        <button type="button" class="btn btn-primary" onclick="submitForm()">Simpan</button>
    </div>
</div>
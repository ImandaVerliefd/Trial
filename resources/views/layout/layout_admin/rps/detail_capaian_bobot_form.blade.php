<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Bobot Penilaian</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <form id="form-bobot-rps" action="<?= url('rps/bobot/submit') ?>" method="post">
                @csrf
                <input type="hidden" name="id-kurikulum" value="{{ $idKurikulum }}">
                <input type="hidden" name="kode-matkul" value="{{ $kodeMatkul }}">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered border-secondary mb-0">
                            <thead>
                                <tr>
                                    <th>Sub CPMK</th>
                                    <th>Deskripsi Sub-CPMK</th>
                                    <?php foreach ($dataSiakad as $mainItem) : ?>
                                        <th><?= $mainItem->SIAKAD ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subCPMK as $key => $item) : ?>
                                    <tr>
                                        <th scope="row">
                                            Sub-CPMK {{ ($key + 1) }}
                                            <input type="hidden" name="id_capaian_detail[<?= $key ?>]" value="{{ $item->ID_CAPAIAN_DETAIL }}">
                                        </th>
                                        <td>{{ $item->NAMA_PEMBELAJARAN }}</td>
                                        <?php foreach ($dataSiakad as $siakadKey => $siakadItem) : ?>
                                            <td>
                                                <?php
                                                $idSiakad = explode(';', $item->ID_SIAKAD)[$siakadKey] ?? null;
                                                $bobot = 0;
                                                if (!empty($idSiakad) && $idSiakad == $siakadItem->ID_SIAKAD) {
                                                    $bobot = explode(';', $item->BOBOT)[$siakadKey] ?? '';
                                                }
                                                ?>
                                                <input type="hidden" name="id_siakad[<?= $key ?>][]" id="" step="0.01" min="0" value="{{ $siakadItem->ID_SIAKAD }}" required>
                                                <input type="number" class="form-control" name="bobot[<?= $key ?>][]" id="" step="0.01" min="0" value="{{ $bobot }}" required>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div> <!-- end card body-->
                <div class="card-footer">
                    <div class="d-flex justify-content-end ">
                        <button class="btn btn-info show-form-button" type="button" onclick="submitFormBobotPenilaian()">Simpan bobot penilaian</button>
                    </div>
                </div>
            </form>
        </div> <!-- end card -->
    </div><!-- end col-->
</div> <!-- end row-->
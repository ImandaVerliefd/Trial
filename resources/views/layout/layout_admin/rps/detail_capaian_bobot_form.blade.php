<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Bobot Penilaian</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <form id="form-bobot-rps" action="<?= url('rps/bobot/submit') ?>" method="post">
            @csrf
            <input type="hidden" name="id-kurikulum" value="{{ $idKurikulum }}">
            <input type="hidden" name="kode-matkul" value="{{ $kodeMatkul }}">

            <div class="card">
                <div class="card-body">
                    <h4 class="header-title">Bobot Penilaian Siakad</h4>
                    <p class="text-muted fs-14">
                        Total bobot penilaian untuk komponen Siakad (tugas, UTS, UAS, dll.) harus 100.
                    </p>
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
                                                <input type="hidden" name="id_siakad[<?= $key ?>][]" step="0.01" min="0" value="{{ $siakadItem->ID_SIAKAD }}" required>
                                                <input type="number" class="form-control" name="bobot[<?= $key ?>][]" step="0.01" min="0" value="{{ $bobot }}" required>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="header-title">Bobot Sub-CPMK</h4>
                    <p class="text-muted fs-14">
                        Pastikan total bobot dari keseluruhan Sub-CPMK adalah 100%.
                    </p>
                    <div class="table-responsive">
                        <table class="table table-bordered border-secondary mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Sub CPMK</th>
                                    <th>Deskripsi Sub-CPMK</th>
                                    <th style="width: 20%;">Bobot (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subCPMK as $key => $item) : ?>
                                    <tr>
                                        <th scope="row">Sub-CPMK {{ ($key + 1) }}</th>
                                        <td>{{ $item->NAMA_PEMBELAJARAN }}</td>
                                        <td>
                                            <input type="number" class="form-control bobot-subcpmk-input" name="bobot_subcpmk[<?= $key ?>]" step="0.01" min="0" value="{{ $item->BOBOT_SUBCPMK ?? 0 }}" required>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-end">Total Bobot:</th>
                                    <th id="total-bobot-subcpmk" style="color: red;">0%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-info show-form-button" type="button" onclick="submitFormBobotPenilaian()">Simpan bobot penilaian</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
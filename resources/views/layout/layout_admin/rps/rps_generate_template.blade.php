<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RPS OBE MANAJEMEN</title>
</head>

<body>
    <div id="rps-container">
        <style>
            body {
                font-family: 'Times New Roman', Times, serif;
            }

            .header {
                text-align: center;
                margin-bottom: 20px;
            }

            .header h2,
            .header h3 {
                margin: 5px 0;
            }

            .info-table,
            .rps-table,
            .verif-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            .info-table th,
            .info-table td,
            .rps-table th,
            .rps-table td,
            .verif-table td,
            .verif-table th {
                border: 1px solid black;
                padding: 8px;
                text-align: center;
            }

            .info-table th {
                background-color: #fff;
            }

            .text-custom {
                align-content: start;
                text-align: justify !important;
            }

            .w-100 {
                width: 100%;
            }

            .d-flex {
                display: flex;
                flex-wrap: wrap;
            }

            @media print {
                .page-break {
                    page-break-before: always;
                    /* Older browsers */
                }
            }
        </style>

        <table class="info-table">
            <tr>
                <th><img src="{{ asset('assets') }}/images/favicon.ico" alt="" style="width: 150px;"></th>
                <td colspan="5" style="line-height: 25px;">
                    <b>SEKOLAH TINGGI ILMU KESEHATAN <br>
                        STIKES PEMKAB JOMBANG</b>
                    <br>
                    Jalan Raya Pandanwangi Telp/Fax (0321) 870214 - JOMBANG <br>
                    Program Studi <?= $prodi ?> <br>
                </td>
                <th>Kode Dokumen</th>
            </tr>
            <tr>
                <th colspan="7">
                    <p>RENCANA PEMBELAJARAN SEMESTER</p>
                </th>
            </tr>
            <tr>
                <th style="width: 20%;">MATA KULIAH (MK)</th>
                <th>KODE MK</th>
                <th>RUMPUN MK</th>
                <th colspan="2">BOBOT (SKS)<br><?= $sks ?></th>
                <th>SEMESTER</th>
                <th>TGL PENGESAHAN</th>
            </tr>
            <tr>
                <td><?= $matkul ?></td>
                <td><?= $kodeMK ?></td>
                <td><?= $rumpun ?></td>
                <td>Kuliah<br><?= $sksKuliah ?></td>
                <td>Praktek<br><?= $sksPraktek ?></td>
                <td><?= $semester ?></td>
                <td><?= $tglPengesahan ?></td>
            </tr>
            <tr>
                <th class="text-custom">Kategori Mata Kuliah</th>
                <td class="text-custom" colspan="6"><?= $katMatkul ?></td>
            </tr>
            <tr>
                <th class="text-custom">Deskripsi Mata Kuliah</th>
                <td class="text-custom" colspan="6"><?= $descMatkul ?></td>
            </tr>
            <tr>
                <th class="text-custom" rowspan="<?= (count($cpl) + 1) ?>">Capaian Pembelajaran Lulusan (CPL) Prodi</th>
                <th class="text-custom" colspan="6">CPL Prodi Yang dibebankan pada MK</th>
            </tr>
            <?php foreach ($cpl as $itemCPL) : ?>
                <tr>
                    <td class="text-custom" colspan="6"><?= $itemCPL ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td class="text-custom" rowspan="<?= (count($cpmk) + 1) ?>"><b>Capaian Pembelajaran Mata Kuliah</b> <br><i>(Learning Outcome)</i></td>
                <th class="text-custom" colspan="6">Capaian Pembelajaran MK <i>(CPMK)</i></th>
            </tr>
            <?php foreach ($cpmk as $itemCPMK) : ?>
                <tr>
                    <td class="text-custom" colspan="6"><?= $itemCPMK['TXT'] ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td class="text-custom" rowspan="<?= (count($subCpmk) + 1) ?>"></td>
            </tr>
            <?php foreach ($subCpmk as $itemSubCpmk) : ?>
                <tr>
                    <td class="text-custom" colspan="6"><?= $itemSubCpmk['TXT'] ?></td>
                </tr>
            <?php endforeach; ?>
            <tr id="relevansi-cpl-cpmk">
                <th style="width: 280px;" class="text-custom">Relevansi CPL dengan CPMK</th>
                <td class="text-custom" colspan="6">
                    <table style="border-collapse: collapse !important; width: 100%;">
                        <thead>
                            <tr>
                                <th></th>
                                <?php $noCPL = 1 ?>
                                <?php foreach ($cpl as $key => $itemCPL) : ?>
                                    <th>CPL <?= $noCPL ?></th>
                                    <?php $noCPL++ ?>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $noCPMK = 1 ?>
                            <?php foreach ($cpmk as $itemCPMK) : ?>
                                <tr>
                                    <td>CPMK <?= $noCPMK ?></td>
                                    <?php foreach ($cpl as $keyCPL => $itemCPL) : ?>
                                        <td><?= (in_array($keyCPL, explode(';', $itemCPMK['PARENT'])) ? '√' : '') ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php $noCPMK++ ?>
                            <?php endforeach; ?>
                            <!-- Tambahkan baris sesuai kebutuhan -->
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr id="relevansi-cpmk-subCPMK">
                <th style="width: 280px;" class="text-custom">Relevansi CPMK dengan Sub-CPMK</th>
                <td class="text-custom" colspan="6">
                    <table style="border-collapse: collapse !important; width: 100%;">
                        <thead>
                            <tr>
                                <th></th>
                                <?php $noCPMK = 1 ?>
                                <?php foreach ($cpmk as $itemCpmk) : ?>
                                    <th>CPMK <?= $noCPMK ?></th>
                                    <?php $noCPMK++ ?>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $noSubCPMK = 1 ?>
                            <?php foreach ($subCpmk as $itemSubCpmk) : ?>
                                <tr>
                                    <th>Sub-CPMK <?= $noSubCPMK ?></th>
                                    <?php foreach ($cpmk as $itemCpmk) : ?>
                                        <th><?= (in_array($itemCpmk["ID"], explode(';', $itemSubCpmk['PARENT'])) ? '√' : '') ?></th>
                                    <?php endforeach; ?>
                                    <?php $noSubCPMK++ ?>
                                </tr>
                            <?php endforeach; ?>
                            <!-- Tambahkan baris sesuai kebutuhan -->
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr id="metode-penilaian">
                <th style="width: 280px;" class="text-custom">Metode Penilaian dan Kaitan dengan CPMK</th>
                <td class="text-custom" colspan="6">
                    <table style="border-collapse: collapse !important; width: 100%;">
                        <thead>
                            <tr>
                                <th>Komponen Penilaian</th>
                                <?php $noSubCPMK = 1 ?>
                                <?php foreach ($subCpmk as $itemSubCpmk) : ?>
                                    <th>Sub-CPMK <?= $noSubCPMK ?></th>
                                    <?php $noSubCPMK++ ?>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dataFeed as $itemFeed) : ?>
                                <tr>
                                    <td><?= $itemFeed->FEEDER ?></td>
                                    <?php foreach ($subCpmkFeeder as $feedSubCPMK) : ?>
                                        <td><?= $feedSubCPMK[$itemFeed->FEEDER] ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td>TOTAL</td>
                                <?php $total = 0; ?>
                                <?php foreach ($subCpmkFeeder as $feedSubCPMK) : ?>
                                    <td><?= array_sum($feedSubCPMK) ?></td>
                                <?php endforeach; ?>
                            </tr>

                            <!-- Tambahkan baris sesuai kebutuhan -->
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <th class="text-custom">Daftar Referensi</th>
                <td class="text-custom" colspan="6">
                    <ul style="list-style: none; padding-inline-start: 2px !important;">
                        <?php if (!empty($referensi)) : ?>
                            <?php foreach ($referensi as $key => $itemRef) : ?>
                                <li style="margin-bottom: 20px;">
                                    <?= ($key + 1) . '. ' . $itemRef ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <span>-</span>
                        <?php endif; ?>
                    </ul>
                </td>
            </tr>
            <tr>
                <th class="text-custom">Dosen Pengampu</th>
                <td class="text-custom" colspan="6">
                    <ul style="list-style: none; padding-inline-start: 2px !important;">
                        <?php foreach ($dosen as $key => $itemDosen) : ?>
                            <li style="margin-bottom: 20px;">
                                <?= ($key + 1) . '. ' . $itemDosen ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </td>
            </tr>
        </table>

        <div class="w-100 d-flex" style="align-content: center; justify-content: center;">
            <h3>RENCANA PEMBELAJARAN SEMESTER</h3>
        </div>

        <table class="rps-table">
            <thead>
                <tr>
                    <th style="width: 4%;">Minggu ke</th>
                    <th style="width: 16%;">Kompetensi Dasar/Kemampuan Akhir (Sub-CPMK)</th>
                    <th style="width: 10%;">Bahan Kajian</th>
                    <th style="width: 10%;">Bentuk Pembelajaran dan Metode Pembelajaran</th>
                    <th style="width: 10%;">Estimasi Waktu (Menit)</th>
                    <th style="width: 10%;">Pengalaman Belajar</th>
                    <th style="width: 10%;">Indikator</th>
                    <th style="width: 10%;">Kriteria Penilaian</th>
                    <th style="width: 10%;">Bobot Penilaian (%)</th>
                    <th style="width: 10%;">Dosen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allSubCPMK as $itemSub) : ?>
                    <tr>
                        <td>
                            <?php
                            $data = explode(';', $itemSub['PERTEMUAN']);
                            if (count($data) > 1) {
                                $mingguKe = join(", ", $data);
                                $mingguKe = substr_replace($mingguKe, " dan", strrpos($mingguKe, ","), 1);
                            } else {
                                $mingguKe = $data[0]; // Directly use the single element
                            } ?>
                            <?= $mingguKe ?>
                        </td>
                        <td><?= $itemSub['NAMA_PEMBELAJARAN'] ?? "-" ?></td>
                        <td><?= $itemSub['KAJIAN'] ?? "-" ?></td>
                        <td><?= $itemSub['BENTUK_PEMBELAJARAN'] ?? "-" ?></td>
                        <td><?= $itemSub['ESTIMASI_WAKTU'] ?? "-" ?></td>
                        <td><?= $itemSub['PENGALAMAN'] ?? "-" ?></td>
                        <td><?= $itemSub['INDIKATOR'] ?? "-" ?></td>
                        <td><?= $itemSub['KRITERIA'] ?? "-" ?></td>
                        <td><?= !empty($itemSub['BOBOT']) ? array_sum(explode(';', $itemSub['BOBOT'])) : 0 ?></td>
                        <td>
                            <?php
                            $dataDosen = [];
                            foreach (explode(';', $itemSub['NAMA_DOSEN']) as $namaDose) {
                                if (!in_array($namaDose, $dataDosen)) {
                                    $dataDosen[] = $namaDose;
                                }
                            }
                            ?>
                            <?= !empty($itemSub['NAMA_DOSEN']) ? implode(',', $dataDosen) : "-" ?>
                        </td>
                    </tr>
                <?php endforeach ?>
                <!-- Tambahkan baris sesuai kebutuhan -->
            </tbody>
        </table>

        <?php $monthID = [
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember"
        ]; ?>

        <div style="margin-top: 120px;">
            <div class="w-100 d-flex" style="align-content: center; justify-content: right;">
                <span>Jombang, <?= date('d') ?> <?= $monthID[(date('m') - 1)] ?> <?= date('Y') ?></span>
            </div>
        </div>
        
        <table class="verif-table" style="margin-top: 50px;">
            <tbody>
                <tr>
                    <td style="width: 50%; border-color: #fff;">Mengetahui, <br>STIKES PEMKAB JOMBANG <br>Ketua Prodi <?= $prodi ?></td>
                    <td style="width: 50%; border-color: #fff;">Dosen Pengajar</td>
                </tr>
                <tr>
                    <td style="width: 50%; border-color: #fff; height: 30vh;">__________________________________</td>
                    <td style="width: 50%; border-color: #fff; height: 30vh;">__________________________________</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
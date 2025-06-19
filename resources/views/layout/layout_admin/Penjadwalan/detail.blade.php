<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Penjadwalan</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <a href="#kelas" data-bs-toggle="tab" aria-expanded="true" class="nav-link active" aria-selected="true" role="tab">
                        Kelas
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="#dosen" data-bs-toggle="tab" aria-expanded="false" class="nav-link" aria-selected="false" role="tab" tabindex="-1">
                        Dosen
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="#matkul" data-bs-toggle="tab" aria-expanded="false" class="nav-link" aria-selected="false" role="tab" tabindex="-1">
                        Jadwal Mata Kuliah
                    </a>
                </li>
            </ul>

            <?php $viewDetMatkul = '
                <div class="col-12 col-lg-3">
                    <div class="card mb-2">
                        <div class="card-body">
                            <div>
                                <h5 class="mb-2">Mata Kuliah <br>' . $detail_matkul->NAMA_MATKUL . '</h5>
                                <hr>
                                <div class="mb-1">' . $detail_matkul->JUMLAH_PERTEMUAN . ' Pertemuan | ' . $detail_matkul->SKS . ' SKS</div>
                                <div class="mb-1">Program Studi ' . $detail_matkul->PRODI . ' ' . $detail_matkul->KURIKULUM . '</div>
                                <div class="mb-1">Semester ' . $detail_matkul->SEMESTER . '</div>
                                <div class="mb-1">Bobot Penilaian : ' . ($detail_matkul->TOTAL_BOBOT ?? 0) . ' Point</div>
                            </div>
                        </div>
                    </div>
                    <div class="info_detail_kelas"></div>
                </div>
            '; ?>

            <div class="tab-content">
                <div class="tab-pane active show" id="kelas" role="tabpanel">
                    <form action="<?= url('penjadwalan/submit-detail-kelas') ?>" method="POST" id="form-submit-kelas">
                        @csrf
                        <input type="hidden" name="id_detsem" value="<?= $detail_matkul->ID_DETSEM ?>">
                        <input type="hidden" name="kode_matkul" value="<?= $detail_matkul->KODE_MATKUL ?>">
                        <input type="hidden" name="id_semester" value="<?= $detail_matkul->ID_SEMESTER ?>">
                        <input type="hidden" name="id_detsem" value="<?= $detail_matkul->ID_DETSEM ?>">
                        <input type="hidden" name="kode_semester" value="<?= $detail_matkul->KODE_SEMESTER ?>">
                        
                        <div class="row justify-content-sm-between mt-2">
                            <div class="w-100" id="alert_container_main">
                            </div>
                        </div>
                        <div class="row">
                            <?= $viewDetMatkul ?>
                            <div class="col-12 col-lg-9">
                                <div class="card mb-0 mt-2">
                                    <div class="card-body">
                                        @include($content_form)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form> <!-- end card-body-->
                </div>
                <div class="tab-pane" id="matkul" role="tabpanel">
                    <form action="<?= url('penjadwalan/submit-detail-matkul') ?>" method="POST" id="form-submit-matkul">
                        @csrf
                        <input type="hidden" name="id_detsem" value="<?= $detail_matkul->ID_DETSEM ?>">
                        <input type="hidden" name="kode_matkul" value="<?= $detail_matkul->KODE_MATKUL ?>">
                        <input type="hidden" name="id_semester" value="<?= $detail_matkul->ID_SEMESTER ?>">
                        <input type="hidden" name="kode_semester" value="<?= $detail_matkul->KODE_SEMESTER ?>">
                        <div class="row justify-content-sm-between mt-2">
                            <div class="w-100" id="alert_container_main">
                            </div>
                        </div>
                        <div class="row">
                            <?= $viewDetMatkul ?>
                            <div class="col-12 col-lg-9">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <select name="kode_kelas" id="kode_kelas" class="form-control">
                                        <option value="">-- Pilih Kelas --</option>
                                        <?php foreach ($list_kelas as $itemKelas) : ?>
                                            <option value="<?= $itemKelas->KODE_KELAS ?>"><?= $itemKelas->NAMA_KELAS ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-primary btn-show-form" onclick="showFormMatkul()">Tampilkan</button>
                                </div>

                                <div class="card mb-0 mt-2">
                                    <div class="card-body">
                                        <div id="matkul-jadwal-container">
                                            <div class="w-100 d-flex justify-content-center">
                                                <img src="{{ asset('assets/images/empty-state.jpeg') }}" alt="" style="width: 44%;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form> <!-- end card-body-->
                </div>
                <div class="tab-pane" id="dosen" role="tabpanel">
                    <form action="<?= url('penjadwalan/submit') ?>" method="POST" id="form-submit">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="id_detsem" value="<?= $detail_matkul->ID_DETSEM ?>">
                            <input type="hidden" name="kode_matkul" value="<?= $detail_matkul->KODE_MATKUL ?>">
                            <input type="hidden" name="id_semester" value="<?= $detail_matkul->ID_SEMESTER ?>">
                            <input type="hidden" name="kode_semester" value="<?= $detail_matkul->KODE_SEMESTER ?>">
                            
                            <?= $viewDetMatkul ?>
                            <div class="col-12 col-lg-9">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <select name="kode_kelas" id="kode_kelas_dosen" class="form-control">
                                        <option value="">-- Pilih Kelas --</option>
                                        <?php foreach ($list_kelas as $itemKelas) : ?>
                                            <option value="<?= $itemKelas->KODE_KELAS ?>"><?= $itemKelas->NAMA_KELAS ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-primary btn-show-form" onclick="showFormDosen()">Tampilkan</button>
                                </div>

                                <div class="card mb-0">
                                    <div class="card-body">
                                        <div id="dosen-jadwal-container">
                                            <div class="w-100 d-flex justify-content-center">
                                                <img src="{{ asset('assets/images/empty-state.jpeg') }}" alt="" style="width: 44%;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form> <!-- end card-body-->
                </div>
            </div>
        </div> <!-- end card-body -->
    </div>
</div>
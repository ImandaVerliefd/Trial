<div class="card">
    <div class="card-body">
        <ul class="nav nav-tabs mb-3" role="tablist">
            @foreach($matkulByPaket as $key => $item)
            <li class="nav-item" role="presentation">
                <a href="#container-form-{{ $key }}" id="tab_head_{{ $key }}" data-bs-toggle="tab" class="nav-link <?= ($item->IS_DIADAKAN === 1) ? 'text-success' : 'text-danger' ?> <?= ($key == 0) ? 'active' : '' ?>" role="tab">
                    {{ $item->KODE_MATKUL ?? '' }}
                </a>
            </li>
            @endforeach
        </ul>
        <div class="tab-content">
            @foreach($matkulByPaket as $key => $item)
            <div class="tab-pane <?= ($key == 0) ? 'active show' : '' ?>" id="container-form-{{ $key }}" role="tabpanel">
                <input type="hidden" name="kode_paket[]" value="{{ $item->KODE_PAKET }}">
                <input type="hidden" name="idDetSem[]" id="id_detSem" value="{{ $item->ID_DETSEM }}">
                <div class="row">
                    <div class="col-md-12 mb-2">
                        <div class="d-flex justify-content-between gap-2">
                            <div id="container-alert"></div>
                            <button type="button" class="btn btn-secondary" onclick="openModal('#matkul-modal-<?= $key ?>')">
                                Lihat Matkul Sebelumnya
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label for="" class="form-label">Mata Kuliah <small class="text-danger">*</small></label>
                            <input type="text" class="form-control" name="nama_matkul[]" id="" value="{{ $item->NAMA_MATKUL ?? '' }}" readonly>
                            <input type="text" class="form-control" name="kode_matkul[]" id="" value="{{ $item->KODE_MATKUL ?? '' }}" hidden>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="kurikulum_{{ $key }}" class="form-label">Kurikulum <small class="text-danger">*</small></label>
                                <select name="id_kurikulum[]" class="form-select" id="kurikulum_{{ $key }}" required disabled>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="tahun_ajar_txt_{{ $key }}" class="form-label">Tahun Ajaran <small class="text-danger">*</small></label>
                                <input type="text" class="form-control" id="tahun_ajar_txt_{{ $key }}" readonly>
                                <input type="hidden" name="tahun_ajar[]" id="tahun_ajar_{{ $key }}" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="id_semester_{{ $key }}" class="form-label">Semester <small class="text-danger">*</small></label>
                                <select name="id_semester[]" class="form-select" id="id_semester_{{ $key }}" data-index="{{ $key }}" required>
                                    @foreach($semester as $itemSemester)
                                    <option value="<?= $itemSemester->ID_SEMESTER ?>"><?= $itemSemester->SEMESTER ?></option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="used_semester_txt_{{ $key }}" class="form-label">Semester Digunakan<small class="text-danger">*</small></label>
                                <input type="text" class="form-control" id="used_semester_txt_{{ $key }}" readonly>
                                <input type="hidden" name="used_semester[]" id="used_semester_{{ $key }}" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="rumpun_matkul_{{ $key }}">Rumpun Matkul</label>
                                <input type="text" name="rumpun_matkul[]" id="rumpun_matkul_{{ $key }}" class="form-control">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="kat_matkul_{{ $key }}">Kategori Matkul</label>
                                <input type="text" name="kate_matkul[]" id="kat_matkul_{{ $key }}" class="form-control">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Status Mata Kuliah</label>
                            <div class="form-check">
                                <input style="cursor: pointer !important;" type="checkbox" class="form-check-input status_mk" data-index="{{ $key }}" id="status_ck_{{ $key }}" onchange="changeStatusMK(this)">
                                <input type="hidden" id="status_mk_{{ $key }}" name="status_matkul[]" value="0">
                                <label style="cursor: pointer !important;" class="form-check-label" for="status_ck_{{ $key }}">Mata kuliah diadakan</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label" for="desc_matkul_{{ $key }}">Deskripsi Matkul</label>
                            <textarea name="desc_matkul[]" id="desc_matkul_{{ $key }}" class="form-control" rows="16"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div id="matkul-modal-<?= $key ?>" class="modal fade" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-labelledby="multiple-oneModalLabel">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="multiple-oneModalLabel">List Mata Kuliah Lampau</h4>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive-lg">
                                <table class="table table-bordered table-hover table-centered mb-0">
                                    <thead>
                                        <th style="width: 5%;"></th>
                                        <th>Nama Matkul</th>
                                        <th>Program Studi</th>
                                        <th>Tahun Ajaran</th>
                                        <th>Semester</th>
                                    </thead>
                                    <tbody>
                                        @foreach($matkulTerkait as $indexMM => $itemMM)
                                        @if($itemMM->KODE_MATKUL == $item->KODE_MATKUL && ($itemMM->ID_TAHUN_AJAR != $item->ID_TAHUN_AJAR || $itemMM->ID_PRODI != $item->ID_PRODI || $itemMM->KODE_SEMESTER != $item->KODE_SEMESTER))
                                        <tr style="cursor: pointer;">
                                            <td><input class="form-check-input me-1" type="radio" name="selectMatkul" id="radio-{{ $indexMM }}" data-used-id="{{ $indexMM }}"></td>
                                            <td>{{ $itemMM->NAMA_MATKUL }}</td>
                                            <td>{{ $itemMM->PRODI }}</td>
                                            <td>{{ $itemMM->TAHUN_AJAR }}</td>
                                            <td>{{ $itemMM->KODE_SEMESTER }}</td>
                                        </tr>
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" onclick="closeModal('#matkul-modal-<?= $key ?>')">Cancel</button>
                            <button type="button" class="btn btn-primary" onclick="openNextModal('#matkul-modal-<?= $key ?>', '#matkul-modal-detail', '<?= $key ?>')">Pilih Mata Kuliah</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="matkul-modal-detail" class="modal fade" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-labelledby="multiple-twoModalLabel">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="multiple-twoModalLabel">Kepemimpinan dan Manajemen Keperawatan</h4>
                        </div>
                        <div class="modal-body" id="detail-matkul">
                        </div>
                        <div class="modal-footer">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h4>
                                    <strong>
                                        Apakah anda yakin ingin menggunakan detail mata kuliah ini ?
                                    </strong>
                                </h4>
                                <div>
                                    <input type="hidden" id="parent_index">
                                    <input type="hidden" id="used_index">
                                    <button type="button" class="btn btn-danger" onclick="openNextModal('#matkul-modal-detail', '#matkul-modal-<?= $key ?>', '<?= $key ?>')">Tidak</button>
                                    <button type="button" class="btn btn-success" onclick="CopyDetailMatkul('#matkul-modal-detail')">Ya</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<style>
    .modal-dialog {
        box-shadow: none !important;
    }
</style>

@include($script_page)
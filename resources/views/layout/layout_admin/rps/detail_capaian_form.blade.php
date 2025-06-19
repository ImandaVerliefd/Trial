<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            @if(!empty($kodeCapaian))
            <h4 class="page-title">Ubah RPS</h4>
            @else
            <h4 class="page-title">Tambah RPS</h4>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-2">
                        <label class="form-label" for="select2-matkul">Mata Kuliah</label>
                        <select class="form-control select2" id="select2-matkul" data-toggle="select2">
                            <option value="">-- Pilih Mata Kuliah --</option>
                        </select>
                    </div>
                </div>
                @if(empty($kodeKurikulum) && empty($kodeMatkul))
                <div class="d-flex justify-content-end ">
                    <button class="btn btn-info show-form-button" type="button" onclick="showForm()">Tampilkan Form Presentase</button>
                </div>
                @endif
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div> <!-- end row-->

<form id="form-rps" action="<?= url('rps/submit') ?>" method="post">
    @csrf
    <div class="row">
        <div class="col-12">
            <div class="card main-form-presentase" style="display: none;">
                <ul class="nav nav-tabs" id="tab-btn" role="tablist">
                    <li class="nav-item px-1" role="presentation">
                        <button type="button" onclick="addTab()" class="btn btn-success waves-effect waves-light py-1">
                            &#x2B;
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="tab-content">
                </div>
                <div class="container-capaian" hidden>
                    <input type="hidden" name="kode-capaian">
                </div>
                <hr />
                <div class="card-footer">
                    <div class="d-flex justify-content-end gap-2">
                        <button class="btn btn-danger" type="button" onclick="location.href='<?= url('rps') ?>'">Kembali</button>
                        <button class="btn btn-success" type="button" onclick="submitFormDetailCapaian()">Simpan</button>
                    </div>
                </div>
            </div>
            <div class="card sub-form-presentase" style="display: none;">
            </div>
        </div>
    </div>
</form>
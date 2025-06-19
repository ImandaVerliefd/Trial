<style>
    td:nth-child(2) {
        word-wrap: break-word;
        overflow-wrap: break-word;
        white-space: normal !important;
    }

    [data-dtr-index="1"] .dtr-data {
        overflow-wrap: break-word;
        white-space: normal !important;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <button type="button" class="btn btn-primary ms-2" onclick="location.href='<?= url('rps/form/index') ?>'">
                    <i class="ri-add-circle-line"></i>
                    Tambah RPS
                </button>
            </div>
            <h4 class="page-title">Detail RPS</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    @foreach($prodi as $key => $item)
                    <li class="nav-item" role="presentation">
                        <a href="#" data-bs-toggle="tab" aria-expanded="true" class="nav-link" aria-selected="true" role="tab" data-prodi="<?= $item->ID_PRODI ?>" onclick="reRenderTable()">
                            <?= $item->JENJANG ?> <?= $item->PRODI ?>
                        </a>
                    </li>
                    @endforeach
                </ul>


                <table id="basic-datatable-rps" class="table table-striped dt-responsive nowrap w-100">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Mata Kuliah</th>
                            <th>Capaian</th>
                            <th>Kurikulum</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div> <!-- end row-->

<div id="print-container" hidden></div>
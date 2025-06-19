<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Penugasan Dosen</h4>
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
                <table id="basic-datatable" class="table table-striped dt-responsive nowrap w-100">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Nama Mata Kuliah</th>
                            <th>Kurikulum</th>
                            <th>Tahun Ajar</th>
                            <th style="width: 20%; text-wrap: wrap;">Dosen Pengampu</th>
                            <th>Semester</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>

            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div> <!-- end row-->
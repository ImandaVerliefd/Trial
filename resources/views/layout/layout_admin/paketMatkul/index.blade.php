<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <form class="d-flex">
                    <a type="button" class="btn btn-primary ms-2" href="<?= url('paket-mata-kuliah/form') ?>">
                        <i class="ri-add-circle-line"></i>
                        Tambah <?= $title ?>
                    </a>
                    <a href="<?= url('paket-mata-kuliah/copy-form') ?>" class="btn btn-info ms-2">
                        <i class="ri-file-copy-line"></i>
                        Copy Paket Mata Kuliah
                    </a>
                </form>
            </div>
            <h4 class="page-title"><?= $title ?></h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="basic-datatable" class="table table-striped dt-responsive nowrap w-100">
                    <thead>
                        <tr>
                            <th>Jenjang</th>
                            <th style="width: 60%;">Prodi</th>
                            <th>Total SKS</th>
                            <th>Semester</th>
                            <th>Tahun</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div> <!-- end row-->
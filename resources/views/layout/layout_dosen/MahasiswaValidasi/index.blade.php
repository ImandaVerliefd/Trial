<style>
    tfoot input {
        width: 100%;
        padding: 3px;
        box-sizing: border-box;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title"><?= $title ?></h4>
        </div>
    </div>
</div>

{{-- Allert notification --}}

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table class="table table-responsive" id="customTable">
                    <tbody>
                        <tr>
                            <th style="width: 200px;">NRP</th>
                            <td>{{ $data_mahasiswa['NIM'] }}</td>
                        </tr>
                        <tr>
                            <th>Nama</th>
                            <td>{{ $data_mahasiswa['NAMA_MAHASISWA'] }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $data_mahasiswa['EMAIL_MAHASISWA'] }}</td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-responsive">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Mata Kuliah</th>
                            <th>Nama Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Kelas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1 ?>
                        <?php foreach ($data_validasi as $item) { ?>
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $item->KODE_MATKUL }}</td>
                                <td>{{ $item->NAMA_MATKUL }}</td>
                                <td>{{ $item->SKS }}</td>
                                <td>{{ $item->NAMA_RUANGAN }}</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="text-center mt-3">
                    <form action="{{ url('mahasiswa-wali/validasi-krs-decline') }}" id="form-decline" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="KODE_MAHASISWA" value="{{ $data_mahasiswa['KODE_MAHASISWA'] }}">
                        <button class="btn btn-danger" onclick="declineForm()">Tolak KRS</button>
                    </form>
                    <form action="{{ url('mahasiswa-wali/validasi-krs-accept') }}" id="form-accept" method="post">
                        @csrf
                        <input type="hidden" name="KODE_MAHASISWA" value="{{ $data_mahasiswa['KODE_MAHASISWA'] }}">
                        <button class="btn btn-success" onclick="acceptForm()">Setujui KRS</button>
                    </form>
                </div>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div> <!-- end row-->

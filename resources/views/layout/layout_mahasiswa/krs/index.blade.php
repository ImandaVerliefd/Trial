<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Pengambilan KRS</h4>
        </div>
    </div>
</div>

<?php if($error) { ?>
<div class="alert alert-info text-center mb-0" role="alert">
    <div class="avatar-sm mb-2 mx-auto">
        <span class="avatar-title bg-info rounded-circle">
            <i class="ri-check-line align-middle fs-22"></i>
        </span>
    </div>
    <h4 class="alert-heading">Oh Snap !</h4>
    <div class="alert alert-danger">
        <?= $resp_msg ?>
    </div>
</div>
<?php } else { ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="basic-datatable" class="table table-striped dt-responsive nowrap w-100">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th style="width: 20%;">Nama Mata Kuliah</th>
                            <th style="width: 20%;">Dosen</th>
                            <th>Kelas</th>
                            <th style="width: 15%;">Jadwal Perkuliahan</th>
                            <th>Ruang</th>
                            <th style="width: 10%;">Ketersediaan Kelas</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($NewData_all as $matkulKey => $matkul)
                            @php
                                $totalRowCount = array_sum(array_map(fn($kelas) => $kelas->rowCount, $matkul));
                                $firstRow = true;
                            @endphp

                            @foreach ($matkul as $kelasKey => $kelasItem)
                                <tr>
                                    @if ($firstRow)
                                        <td rowspan="{{ $totalRowCount }}" style="vertical-align: middle;">{{ $matkulKey }}</td>
                                        <td rowspan="{{ $totalRowCount }}" style="vertical-align: middle;">{{ $kelasItem->NAMA_MATKUL }}</td>
                                        {{-- <td rowspan="{{ $totalRowCount }}" style="vertical-align: middle;">{{ $kelasItem->NAMA_DOSEN }}</td> --}}
                                        @php
                                            $firstRow = false;
                                        @endphp
                                    @endif  
                                    <td rowspan="{{ $kelasItem->rowCount }}" style="vertical-align: middle;">{{ $kelasItem->NAMA_DOSEN }}</td>
                                    <td rowspan="{{ $kelasItem->rowCount }}" style="vertical-align: middle;">{{ $kelasItem->NAMA_KELAS }}</td>
                                    <td>{{ $kelasItem->HARI[0] }}</td>
                                    <td>{{ $kelasItem->NAMA_RUANGAN[0] }}</td>
                                    <td rowspan="{{ $kelasItem->rowCount }}" style="vertical-align: middle;"><span class="remaining-slot" id="<?= $kelasItem->ID_DETSEM . "_" . $kelasItem->KODE_KELAS ?>">{{ $kelasItem->TERDAFTAR }}</span> / {{ $kelasItem->KAPASITAS_RUANGAN[0] }}</td>
                                    <td rowspan="{{ $kelasItem->rowCount }}" style="vertical-align: middle;">
                                        @if ($kelasItem->TERDAFTAR == $kelasItem->KAPASITAS_RUANGAN[0])
                                            <span class="text-danger">Kelas Penuh</span>
                                        @else
                                            <input type="radio" onclick="submitKelas(this)" name="<?= $kelasItem->ID_DETSEM ?>" id="<?= $kelasItem->ID_DETSEM . "_" . $kelasItem->KODE_KELAS ?>" value="<?= $kelasItem->KODE_KELAS ?>" @checked($kelasItem->ID_DETSEM . "_" . $kelasItem->KODE_KELAS == $kelasItem->ID_DETSEM . $kelasItem->SELECTED)>
                                        @endif
                                    </td>
                                    @if ($kelasItem->rowCount == 2)
                                        <tr>
                                            <td>{{ $kelasItem->HARI[1] }}</td>
                                            <td>{{ $kelasItem->NAMA_RUANGAN[1] }}</td>
                                        </tr>
                                    @endif
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
                <div class="text-center mt-3">
                    <button id="submitButton" class="btn btn-primary" onclick="submitForm()">Selesaikan</button>
                </div>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div> <!-- end row-->
<?php } ?>

<style>
    .center-wrap {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 20px;
        text-align: center;
    }

    #qrcode {
        width: 160px;
        height: 160px;
        margin-bottom: 20px;
    }

    #countdown {
        font-size: 6vw;
        color: #333;
        margin-top: 20px;
    }

    #refresh-btn {
        margin-top: 10px;
        padding: 10px 20px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    #qrModal {
        display: none;
        flex-direction: column;
        align-items: center;
    }

    .modal-visible {
        display: flex;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
            </div>
            <h4 class="page-title">Kehadiran Kelas</h4>
        </div>
    </div>
</div>

<div class="row" style="min-height: 10vh;">
    <div class="card w-100">
        <div class="card-body d-flex">
            <div class="col-9 d-flex align-content-center">
                <h2 class="mb-0">
                    <span style="color: white; background-color: #999999; border-radius: 6px; font-size: 40px; padding: 10px; margin: 0 10px 0 0;">#<?= $tot_pertemuan ?></span>
                    <b><?= '[' . $detail_kelas->KODE_MATKUL . '] ' . $detail_kelas->NAMA_MATKUL . ' - ' .
                    $detail_kelas->NAMA_KELAS . ' '
                    ?></b><small><?= $detail_kelas->SKS . ' SKS - ' . $detail_kelas->SEMESTER ?></small>
                </h2>
            </div>

            <div class="col-3 text-end">
                <button id="btnMulai" class="btn btn-primary"
                    style="<?= $DATA_CHECKING == '1' ? 'display: none;' : '' ?>"><i class="bi-door-open"></i> Mulai
                    Kelas</button>
                {{-- <button id="openModalBtn" onclick="openModal()">QR Scan</button> --}}
                <button id="btnQR" class="btn btn-warning" onclick="QrModal()"
                    style="<?= $DATA_CHECKING == '0' ? 'display: none;' : '' ?>">
                    <i class="bi-qr-code-scan"></i> QR Scan
                </button>
                <button id="btnAkhiri" class="btn btn-primary"
                    style="<?= $DATA_CHECKING == '0' ? 'display: none;' : '' ?>"><i class="ri-notification-3-fill"></i>
                    Akhiri Kelas</button>
            </div>
        </div> <!-- end card-body -->
    </div> <!-- end card -->
</div> <!-- end row -->

<div class="row" id="list_mahasiswa">
    <div class="col-4">
        <div class="card">
            <div class="card-body">
                <table class="table table-striped dt-responsive nowrap w-100">
                    <thead>
                        <tr>
                            <th><i class="ri-user-3-fill"></i> Pengajar</th>
                        </tr>
                        @foreach ($detail_kelas->NAMA_DOSEN as $item)
                            <tr>
                                <td>{{ $item }}</td>
                            </tr>
                        @endforeach

                        <tr>
                            <th><i class="ri-calendar-event-line"></i> Jadwal Pertemuan</th>
                        </tr>
                        @foreach ($detail_kelas->HARI as $index => $hari)
                            <tr>
                                <td>
                                    {{ $hari }} {{ $detail_kelas->JAM_MULAI[$index] }} -
                                    {{ $detail_kelas->JAM_SELESAI[$index] }}
                                    | Kelas {{ $detail_kelas->NAMA_KELAS }}
                                </td>
                            </tr>
                        @endforeach

                        <tr>
                            <th><i class="ri-calendar-event-line"></i> Pelaksanaan Pertemuan</th>
                        </tr>
                        <tr>
                            <td id="date_pertemuan"><?= $detail_kelas->DATE_PERTEMUAN ?></td>
                        </tr>
                    </thead>
                </table>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
    <div class="col-8">
        <div class="card">
            <div class="card-body">
                <h4>Kehadiran Peserta Kelas</h4>
                <table id="list-mahasiswa-datatable" class="table table-striped dt-responsive nowrap w-100">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th class="action_bar" style="display: none">Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $no = 1;
                        @endphp
                        @foreach ($mahasiswa as $list)
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $list->NIM ?></td>
                                <td><?= $list->NAMA_MAHASISWA ?></td>
                                <td class="action_bar" value="<?= $list->KODE_MAHASISWA ?>"
                                    style="<?= $DATA_CHECKING == '0' ? 'display: none;' : '' ?>">
                                    <?= $list->ACTION_BUTTON ?></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div> <!-- end row-->

<!-- Modal QR -->
<div class="modal fade" id="modalQR" tabindex="-1" aria-labelledby="modalQRLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content p-3" style="height: 700px; position: relative;">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="modalQRLabel">Scan QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="onModalClose()"></button>
            </div>

            <div class="modal-body" style="height: 550px;">
                <div id="qrcode" class=" d-flex w-100 justify-content-center" style="height: 100% !important"></div>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-tab {
        color: white;
        font-weight: bold;
    }

    .custom-tab.active {
        color: black;
        background-color: white;
        border-radius: 5px;
    }
</style>
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">

            </div>
            <h4 class="page-title">Jadwal Mengajar</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background-color: rgba(23, 23, 23, 0.2);">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active custom-tab" id="jadwal-mengajar-tab" data-bs-toggle="tab" href="#jadwal-mengajar" role="tab" aria-controls="jadwal-mengajar" aria-selected="true">Jadwal Mengajar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-tab" id="penugasan-mengajar-tab" data-bs-toggle="tab" href="#penugasan-mengajar" role="tab" aria-controls="penugasan-mengajar" aria-selected="false">Penugasan Mengajar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-tab" id="seluruh-jadwal-tab" data-bs-toggle="tab" href="#seluruh-jadwal" role="tab" aria-controls="seluruh-jadwal" aria-selected="false">Jadwal Seluruh Kelas Aktif</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Jadwal Mengajar Tab -->
                    <div class="tab-pane fade show active" id="jadwal-mengajar" role="tabpanel" aria-labelledby="jadwal-mengajar-tab">
                        <table id="jadwal_mengajar" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Jam</th>
                                    <th>SKS</th>
                                    <th>Mata Kuliah</th>
                                    <th>Dosen</th>
                                    <th>Ruangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $order = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

                                    $groupedData = collect($jadwal)
                                        ->groupBy('HARI')
                                        ->sortBy(function ($value, $key) use ($order) {
                                            return array_search($key, $order);
                                        });
                                @endphp     

                                @foreach($groupedData as $hari => $jadwalHari)
                                    <tr>
                                        <td class="text-center fw-bolder" colspan="5" style="background-color: rgb(84, 145, 202);">{{ $hari }}</td>
                                    </tr>
                                    @foreach($jadwalHari as $item)
                                        <tr>
                                            <td>{{ $item["JAM_MULAI"] }} - {{ $item["JAM_SELESAI"] }}</td>
                                            <td>{{ $item["SKS"] }}</td>
                                            <td>{{ $item["NAMA_MATKUL"] }}</td>
                                            <td>{{ $item["NAMA_DOSEN"] }}</td>
                                            <td>{{ $item["NAMA_RUANGAN"] }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Penugasan Mengajar Tab -->
                    <div class="tab-pane fade" id="penugasan-mengajar" role="tabpanel" aria-labelledby="penugasan-mengajar-tab">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="tahun-ajaran" class="form-label">Tahun Ajaran</label>
                                    <select class="form-select" id="tahun-ajaran">
                                        <option value="">-- Pilih Tahun Ajaran --</option>
                                        @foreach ($semester as $item)
                                            <option value="{{ $item->ID_SEMESTER }}">{{ $item->TAHUN }}/{{ $item->TAHUN + 1 }} {{ $item->SEMESTER }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div id="loading-spinner" class="text-center my-3" style="display:none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div id="alert-container"></div>
                        <table id="basic-datatable" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Hari</th>
                                    <th>Jam</th>
                                    <th>SKS</th>
                                    <th>Mata Kuliah</th>
                                    <th>Dosen</th>
                                    <th>Ruangan</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="seluruh-jadwal" role="tabpanel" aria-labelledby="seluruh-jadwal-tab">
                        <table id="all-data-datatable" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Periode</th>
                                    <th>Kode Mata Kuliah</th>
                                    <th>Mata Kuliah</th>
                                    <th>Nama Ruangan</th>
                                    <th>Pengajar</th>
                                    <th>Pertemuan</th>
                                </tr>
                            </thead>
                        </table>
                    </div>  
                </div>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div> <!-- end row -->
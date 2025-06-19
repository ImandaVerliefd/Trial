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
            <h4 class="page-title">Jadwal</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background-color: rgba(23, 23, 23, 0.2);">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active custom-tab" id="jadwal-mengajar-tab" data-bs-toggle="tab"
                            href="#jadwal-mengajar" role="tab" aria-controls="jadwal-mengajar"
                            aria-selected="true">Jadwal Kuliah</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-tab" id="penugasan-mengajar-tab" data-bs-toggle="tab"
                            href="#penugasan-mengajar" role="tab" aria-controls="penugasan-mengajar"
                            aria-selected="false">Jadwal Ujian</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Jadwal Kuliah Tab -->
                    <div class="tab-pane fade show active" id="jadwal-mengajar" role="tabpanel"
                        aria-labelledby="jadwal-mengajar-tab">
                        <table id="basic-datatable" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Jam</th>
                                    <th>SKS</th>
                                    <th>Mata Kuliah</th>
                                    <th>Dosen</th>
                                    <th>Kelas</th>
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

                                @foreach ($groupedData as $hari => $jadwalHari)
                                    <tr>
                                        <td class="text-center fw-bolder" colspan="6"
                                            style="background-color: rgb(84, 145, 202);">{{ $hari }}</td>
                                    </tr>
                                    @foreach ($jadwalHari as $item)
                                        <tr>
                                            <td>{{ $item['JAM_MULAI'] }} - {{ $item['JAM_SELESAI'] }}</td>
                                            <td>{{ $item['SKS'] }}</td>
                                            <td>{{ $item['NAMA_MATKUL'] }}</td>
                                            <td>{{ $item['NAMA_DOSEN'] }}</td>
                                            <td>{{ $item['NAMA_KELAS'] }}</td>
                                            <td>{{ $item['NAMA_RUANGAN'] }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div> <!-- end row -->

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
            </div>
            <h4 class="page-title">Kehadiran Kelas</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background-color: rgba(23, 23, 23, 0.2);">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active custom-tab" id="kelas-aktif-tab" data-bs-toggle="tab"
                            href="#kelas-aktif" role="tab" aria-controls="kelas-aktif" aria-selected="true">Kelas
                            Aktif</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-tab" id="history-kelas-tab" data-bs-toggle="tab" href="#history-kelas"
                            role="tab" aria-controls="history-kelas" aria-selected="false">Riwayat Kelas</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="kelas-aktif" role="tabpanel"
                        aria-labelledby="kelas-aktif-tab">
                        <table id="basic-datatable" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Periode</th>
                                    <th>Kode Mata Kuliah</th>
                                    <th>Mata Kuliah</th>
                                    <th>Pengajar</th>
                                    <th>Kelas</th>
                                    <th>Action </th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="history-kelas" role="tabpanel" aria-labelledby="history-kelas-tab">
                        <span>Tahun Ajaran</span>
                        <select id="select-detsem" class="form-control">
                            <option disabled selected>-Pilih-</option>
                            @php
                                $grouped = collect($history_kehadiran)->groupBy(function ($item) {
                                    return $item['SEMESTER'] . ' | ' . $item['PRODI'];
                                });
                            @endphp

                            @foreach ($grouped as $groupLabel => $items)
                                <optgroup label="{{ $groupLabel }}">
                                    @foreach ($items as $item)
                                        <option value="{{ $item['ID_DETSEM'] }};{{ $item['KODE_KELAS'] }}">
                                            {{ $item['KODE_MATKUL'] }} | {{ $item['NAMA_MATKUL'] }} -
                                            {{ $item['NAMA_KELAS'] }} ({{ $item['SKS'] }} SKS)
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>

                        {{-- place here --}}
                        <div id="history-content" class="mt-3"></div>
                    </div>
                </div>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div> <!-- end row-->

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
                <ul class="nav nav-tabs card-header-tabs" id="tab-menu" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active custom-tab" id="tab-rekap" data-bs-toggle="tab"
                            href="#content-rekapitulasi" role="tab" aria-controls="content-rekapitulasi"
                            aria-selected="true">
                            <i class="ri-list-check"></i> Rekapitulasi Kehadiran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-tab" id="tab-kehadiran" data-bs-toggle="tab" href="#content-kehadiran"
                            role="tab" aria-controls="content-kehadiran" aria-selected="false">
                            <i class="ri-table-alt-line"></i> Kehadiran Per-Pertemuan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-tab" id="tab-pertemuan" data-bs-toggle="tab" href="#content-pertemuan"
                            role="tab" aria-controls="content-pertemuan" aria-selected="false">
                            <i class="ri-team-fill"></i> Pertemuan Kelas
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Rekapitulasi Kehadiran -->
                    <div class="tab-pane fade show active" id="content-rekapitulasi" role="tabpanel"
                        aria-labelledby="tab-rekap">
                        <div class="d-flex justify-content-end">
                            <div class="col-5">
                                <select id="select_pertemuan" class="form-control">
                                    <option disabled selected>-Pilih Tahun Ajaran-</option>
                                    @foreach ($tahun_ajaran as $item)
                                        <option value="{{ $item->ID_SEMESTER }}">{{ $item->SEMESTER }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="rekapitulasi-content" class="mt-3"></div>
                    </div>

                    <!-- Kehadiran Per-Pertemuan -->
                    <div class="tab-pane fade" id="content-kehadiran" role="tabpanel" aria-labelledby="tab-kehadiran">
                        <div class="d-flex justify-content-end">
                            <div class="col-5">
                                <select id="select_kehadiran" class="form-control">
                                    <option disabled selected>-Pilih Tahun Ajaran-</option>
                                    @foreach ($tahun_ajaran as $item)
                                        <option value="{{ $item->ID_SEMESTER }}">{{ $item->SEMESTER }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="kehadiran-content" class="mt-3"></div>
                    </div>

                    <!-- Pertemuan Kelas -->
                    <div class="tab-pane fade" id="content-pertemuan" role="tabpanel" aria-labelledby="tab-pertemuan">
                        <div class="d-flex justify-content-end">
                            <div class="col-5">
                                <select id="select-pertemuan" class="form-control">
                                    <option disabled selected>-Pilih-</option>
                                    @php
                                        $grouped = collect($list_matkul)->groupBy(function ($item) {
                                            return $item->SEMESTER;
                                        });
                                    @endphp

                                    @foreach ($grouped as $groupLabel => $items)
                                        <optgroup label="{{ $groupLabel }}">
                                            @foreach ($items as $item)
                                                <option value="{{ $item->ID_DETSEM }};{{ $item->KODE_KELAS }}">
                                                    {{ $item->KODE_MATKUL }} | {{ $item->NAMA_MATKUL }} -
                                                    {{ $item->NAMA_KELAS }} ({{ $item->SKS }} SKS)
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="pertemuan-content" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

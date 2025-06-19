<script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.min.js"></script>
<div class="mt-4">
    <div class="card">
        <!-- Informasi Kelas -->
        <table class="table table-bordered mb-3">
            <tr>
                <th>Program Studi</th>
                <td>{{ $detail_kelas->JENJANG }} {{ $detail_kelas->PRODI }}</td>
            </tr>
            <tr>
                <th>Periode</th>
                <td>{{ $detail_kelas->SEMESTER }}</td>
            </tr>
            <tr>
                <th>Mata Kuliah</th>
                <td>{{ $detail_kelas->KODE_MATKUL }} {{ $detail_kelas->NAMA_MATKUL }} kelas
                    {{ $detail_kelas->NAMA_KELAS }}</td>
            </tr>

            @foreach ($detail_kelas->NAMA_DOSEN as $index => $dosen)
                <tr>
                    @if ($index == 0)
                        <th>Pengajar</th>
                    @else
                        <th></th>
                    @endif
                    <td>{{ $dosen }}</td>
                </tr>
            @endforeach

            @foreach ($detail_kelas->HARI as $index => $hari)
                <tr>
                    <th>Pertemuan</th>
                    <td>
                        {{ $hari }}, {{ $detail_kelas->JAM_MULAI[$index] }} -
                        {{ $detail_kelas->JAM_SELESAI[$index] }}
                        ({{ $detail_kelas->METODE[$index] }})
                    </td>
                </tr>
            @endforeach
        </table>

        <div class="card-header" style="background-color: rgba(23, 23, 23, 0.2);">
            <ul class="nav nav-tabs card-header-tabs" id="tab-menu" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active custom-tab" id="tab-pertemuan" data-bs-toggle="tab"
                        href="#content-pertemuan" role="tab" aria-controls="content-pertemuan" aria-selected="true">
                        <i class="ri-list-check"></i> Daftar Pertemuan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link custom-tab" id="tab-rekap" data-bs-toggle="tab" href="#content-rekap"
                        role="tab" aria-controls="content-rekap" aria-selected="false">
                        <i class="ri-table-alt-line"></i> Rekapitulasi Kehadiran
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link custom-tab" id="tab-peserta" data-bs-toggle="tab" href="#content-peserta"
                        role="tab" aria-controls="content-peserta" aria-selected="false">
                        <i class="ri-team-fill"></i> Kehadiran Peserta
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link custom-tab" id="tab-berita" data-bs-toggle="tab" href="#content-berita"
                        role="tab" aria-controls="content-berita" aria-selected="false">
                        <i class="bi-newspaper"></i> Berita Acara
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Daftar Pertemuan -->
                <div class="tab-pane fade show active" id="content-pertemuan" role="tabpanel"
                    aria-labelledby="tab-pertemuan">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Ke-</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($daftar_pertemuan as $item)
                                <tr>
                                    <td>{{ $item->SESSION_NUMBER }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->TANGGAL_PERTEMUAN)->locale('id')->translatedFormat('l, d F Y') }}
                                    </td>
                                    <td>{{ $item->START_KELAS }} - {{ $item->END_KELAS }}</td>
                                    <td>
                                        {{ $item->ABSENSI }}/{{ $item->TOTAL_MAHASISWA }}
                                        ({{ $item->TOTAL_MAHASISWA > 0 ? number_format(($item->ABSENSI / $item->TOTAL_MAHASISWA) * 100, 2) . '%' : '0%' }})
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Rekapitulasi Kehadiran -->
                <div class="tab-pane fade" id="content-rekap" role="tabpanel" aria-labelledby="tab-rekap">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Total Kehadiran</th>
                                <th>Hadir</th>
                                <th>Sakit</th>
                                <th>Ijin</th>
                                <th>Tidak Ada Keterangan</th>
                                <th>Prosentase Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $nor = 1;
                            @endphp
                            @foreach ($rekapitulasi_kehadiran as $item)
                                <tr>
                                    <td>{{ $nor++ }}</td>
                                    <td>{{ $item->NIM }}</td>
                                    <td>{{ $item->NAMA_MAHASISWA }}</td>
                                    <td>{{ $item->total_kehadiran }}</td>
                                    <td>{{ $item->hadir }}</td>
                                    <td>{{ $item->sakit }}</td>
                                    <td>{{ $item->ijin }}</td>
                                    <td>{{ $item->alpha }}</td>
                                    <td>
                                        {{ $item->hadir }}/{{ $item->total_kehadiran }}
                                        ({{ $item->total_kehadiran > 0 ? number_format(($item->hadir / $item->total_kehadiran) * 100, 2) . '%' : '0%' }})
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Kehadiran Peserta -->
                <div class="tab-pane fade" id="content-peserta" role="tabpanel" aria-labelledby="tab-peserta">
                    @php
                        $grouped = collect($kehadiran_peserta)->groupBy('NIM');
                        $all_sessions = collect($kehadiran_peserta)
                            ->pluck('SESSION_NUMBER')
                            ->unique()
                            ->sort()
                            ->values();
                    @endphp

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                @foreach ($all_sessions as $session)
                                    <th>Ke-{{ $session }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach ($grouped as $nim => $list)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $nim }}</td>
                                    <td>{{ $list->first()->NAMA_MAHASISWA ?? '-' }}</td>
                                    @foreach ($all_sessions as $session)
                                        @php
                                            $item = $list->firstWhere('SESSION_NUMBER', $session);
                                            $statusMap = [
                                                0 => 'TIDAK HADIR',
                                                1 => 'HADIR',
                                                2 => 'SAKIT',
                                                3 => 'IJIN',
                                            ];
                                            $status = $item ? $statusMap[$item->STATUS] ?? '-' : '-';

                                            $bgColor = match ($status) {
                                                'HADIR' => 'bg-success text-dark',
                                                'SAKIT', 'IJIN' => 'bg-warning text-dark',
                                                'TIDAK HADIR' => 'bg-danger text-dark',
                                                default => '',
                                            };
                                        @endphp
                                        <td class="text-center">
                                            <span
                                                class="{{ $bgColor }} px-2 py-1 rounded d-inline-block">{{ $status }}</span>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Berita Acara -->
                <div class="tab-pane fade" id="content-berita" role="tabpanel" aria-labelledby="tab-berita">
                    <div class="text-end">
                        <button type="button" class="btn btn-outline-secondary mb-1" onclick="printDiv('print-out-rKep')">
                            <i class="bi-printer-fill"></i> Print Berita Acara
                        </button>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="white-space: nowrap;">Pertemuan Ke</th>
                                <th style="white-space: nowrap;">Tanggal Pelaksanaan BA</th>
                                <th>Materi</th>
                                <th style="white-space: nowrap;">Metode Pembelajaran</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($berita_acara as $item)
                                <tr>
                                    <td>{{ $item->SESSION_NUMBER }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->TANGGAL_PERTEMUAN)->locale('id')->translatedFormat('l, d F Y') }}
                                    <td>{{ $item->MATERI }}</td>
                                    <td>{{ $item->METODE_PEMBELAJARAN }}</td>
                                    <td>{{ $item->CATATAN }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div hidden>
                    <div id="print-out-rKep">
                        <table style="width: 100%; border-collapse: collapse;" class="print-wrapper">
                            <thead class="print-header">
                                <tr>
                                    <td>
                                        <div class="text-center" style="margin-bottom: 10px;">
                                            <hr style="border: none; border-top: 1.5px solid black; margin: 4px 0;">
                                            <h2 class="fw-bold" style="font-size: 18px; margin: 4px 0;">SEKOLAH TINGGI
                                                ILMU KESEHATAN PEMKAB JOMBANG</h2>
                                            <h4 class="fw-bold" style="font-size: 14px; margin: 2px 0;">BERITA ACARA
                                                PERKULIAHAN</h4>
                                            <hr style="border: none; border-top: 1.5px solid black; margin: 4px 0;">
                                        </div>

                                        <div class="info-grid mb-2"
                                            style="display: grid; grid-template-columns: 1fr 1fr; row-gap: 4px; column-gap: 40px; font-size: 13px; line-height: 1.2;">
                                            <div>
                                                <div style="min-width: 120px;">Program Studi</div>
                                                <div>:&nbsp;</div>
                                                <div>{{ $detail_kelas->JENJANG }} {{ $detail_kelas->PRODI }}</div>
                                            </div>
                                            <div>
                                                <div style="min-width: 120px;">Tahun Ajaran</div>
                                                <div>:&nbsp;</div>
                                                <div>{{ $detail_kelas->PERIODE[1] }}</div>
                                            </div>
                                            <div>
                                                <div style="min-width: 120px;">Kode Mata Kuliah</div>
                                                <div>:&nbsp;</div>
                                                <div>{{ $detail_kelas->KODE_MATKUL }}</div>
                                            </div>
                                            <div>
                                                <div style="min-width: 120px;">Semester</div>
                                                <div>:&nbsp;</div>
                                                <div>{{ $detail_kelas->PERIODE[0] }}</div>
                                            </div>
                                            <div>
                                                <div style="min-width: 120px;">SKS</div>
                                                <div>:&nbsp;</div>
                                                <div>{{ $detail_kelas->SKS }} sks</div>
                                            </div>
                                            <div>
                                                <div style="min-width: 120px;">Kelas</div>
                                                <div>:&nbsp;</div>
                                                <div>{{ $detail_kelas->NAMA_KELAS }}</div>
                                            </div>
                                            <div>
                                                <div style="min-width: 120px;">Mata Kuliah</div>
                                                <div>:&nbsp;</div>
                                                <div>{{ $detail_kelas->NAMA_MATKUL }}</div>
                                            </div>
                                            <div>
                                                <div style="min-width: 120px;">Nama Dosen</div>
                                                <div>:&nbsp;</div>
                                                <div>{{ implode(', ', $detail_kelas->NAMA_DOSEN) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </thead>

                            <tfoot class="print-footer">
                                <tr>
                                    <td>
                                        <div class="text-center"
                                            style="margin-top: 30px; font-size: 12px; color: black;">
                                            Dokumen dicetak dari <span id="print-url">http://dummy.web/dev</span> pada
                                            tanggal
                                            <span
                                                id="print-date">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('LLLL') }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>

                            <tbody>
                                <tr>
                                    <td>
                                        <div class="table-responsive">
                                            <table class="table-print print-table"
                                                style="width: 100%; font-size: 13px; border-collapse: collapse;">
                                                <thead>
                                                    <tr>
                                                        <th width="70px">Pert. Ke</th>
                                                        <th width="130px" style="text-align: center;">Tanggal</th>
                                                        <th style="text-align: center;">Materi Bahasan</th>
                                                        <th width="90px" style="text-align: center;">Metode</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($berita_acara as $item)
                                                        <tr>
                                                            <td>{{ $item->SESSION_NUMBER }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($item->TANGGAL_PERTEMUAN)->locale('id')->translatedFormat('l, d F Y') }}
                                                            </td>
                                                            <td>{{ $item->MATERI }}</td>
                                                            <td>{{ $item->METODE_PELAKSANAAN }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <div style="margin-top: 20px;">
                                            <label><input type="checkbox"> Sesuai dengan SAP</label><br>
                                            <label><input type="checkbox"> Tidak Sesuai dengan SAP</label>
                                        </div>

                                        <div class="page-break"></div>

                                        <table class="print-table">
                                            <thead>
                                                <tr>
                                                    <th>No.</th>
                                                    <th>NIM</th>
                                                    <th>Mahasiswa</th>
                                                    <th>Pertemuan</th>
                                                    <th>Hadir</th>
                                                    <th>Sakit</th>
                                                    <th>Izin</th>
                                                    <th>Tidak Hadir</th>
                                                    <th>Persentase</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <tbody>
                                                @foreach ($rekapitulasi_kehadiran as $i => $mhs)
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>{{ $mhs->NIM }}</td>
                                                        <td style="text-align: left; text-transform: uppercase;">{{ $mhs->NAMA_MAHASISWA }}</td>
                                                        <td>{{ $mhs->total_kehadiran }}</td>
                                                        <td>{{ $mhs->hadir }}</td>
                                                        <td>{{ $mhs->sakit }}</td>
                                                        <td>{{ $mhs->ijin }}</td>
                                                        <td>{{ $mhs->alpha }}</td>
                                                        <td>
                                                            {{ $mhs->total_kehadiran > 0 ? number_format(($mhs->hadir / $mhs->total_kehadiran) * 100, 2) . '%' : '0%' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <style>
                    .print-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 13px;
                    }

                    .print-table th,
                    .print-table td {
                        border: 1px solid black;
                        padding: 6px;
                        text-align: center;
                    }

                    .info-grid {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 4px 40px;
                        font-size: 13px;
                        line-height: 1.2;
                        margin-bottom: 10px;
                    }

                    .info-grid>div {
                        display: flex;
                    }

                    .info-grid>div>div:first-child {
                        min-width: 120px;
                    }

                    @media print {
                        @page {
                            size: portrait;
                            margin: 10mm;
                        }

                        thead {
                            display: table-header-group;
                        }

                        tfoot {
                            display: table-footer-group;
                        }

                        .page-break {
                            page-break-before: always;
                        }

                        .print-header,
                        .print-footer {
                            color: black !important;
                        }
                    }
                </style>

            </div>
        </div>
    </div>
</div>

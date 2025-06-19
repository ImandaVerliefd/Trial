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
                    <th>Pengajar</th>
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

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Ke-</th>
                    <th>Tanggal</th>
                    <th>Kehadiran</th>
                    <th>Pengajar</th>
                    <th>Materi Bahasan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detail as $item)
                    <tr>
                        <td>{{ $item->SESSION_NUMBER }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->TANGGAL_PERTEMUAN)->locale('id')->translatedFormat('l, d F Y') }}</td>
                        @php
                            $statusMap = [
                                0 => 'TIDAK HADIR',
                                1 => 'HADIR',
                                2 => 'SAKIT',
                                3 => 'IJIN',
                            ];
                            $status = $statusMap[$item->STATUS] ?? '-';

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
                        <td>{{ $item->NAMA_DOSEN }}</td>
                        <td>{{ $item->MATERI }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

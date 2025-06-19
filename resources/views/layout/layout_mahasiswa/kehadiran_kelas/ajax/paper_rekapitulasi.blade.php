<div class="mt-4">
    <div class="card">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Mata Kuliah</th>
                    <th>Kelas</th>
                    <th>Terselenggara</th>
                    <th>Hadir</th>
                    <th>Sakit</th>
                    <th>Ijin</th>
                    <th>Tidak Hadir</th>
                    <th>Presentase Hadir</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                @endphp
                @foreach ($matkul as $item)
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $item->KODE_MATKUL }}</td>
                        <td>{{ $item->NAMA_MATKUL }}</td>
                        <td>{{ $item->NAMA_KELAS }}</td>
                        <td>{{ $item->TOTAL_KEHADIRAN }}</td>
                        <td>{{ $item->KEHADIRAN }}</td>
                        <td>{{ $item->SAKIT }}</td>
                        <td>{{ $item->IJIN }}</td>
                        <td>{{ $item->ALPHA }}</td>
                        <td>
                            {{ $item->KEHADIRAN }}/{{ $item->TOTAL_KEHADIRAN }}
                            ({{ $item->TOTAL_KEHADIRAN > 0 ? number_format(($item->KEHADIRAN / $item->TOTAL_KEHADIRAN) * 100, 2) . '%' : '0%' }})
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

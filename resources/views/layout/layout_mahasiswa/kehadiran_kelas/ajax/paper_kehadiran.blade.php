<div class="mt-4">
    <div class="card">
        @php
            $grouped = collect($matkul)->groupBy('KODE_MATKUL');
            $all_sessions = collect($matkul)->pluck('SESSION_NUMBER')->unique()->sort()->values();
        @endphp
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Mata Kuliah</th>
                    <th>Kelas</th>
                    @foreach ($all_sessions as $session)
                        <th>Ke-{{ $session }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                @endphp
                @foreach ($grouped as $item)
                    @php $first = $item->first(); @endphp
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $first->KODE_MATKUL }}</td>
                        <td>{{ $first->NAMA_MATKUL }}</td>
                        <td>{{ $first->NAMA_KELAS }}</td>
                        @foreach ($all_sessions as $session)
                            @php
                                $record = $item->firstWhere('SESSION_NUMBER', $session);
                                $statusMap = [
                                    0 => 'TIDAK HADIR',
                                    1 => 'HADIR',
                                    2 => 'SAKIT',
                                    3 => 'IJIN',
                                ];
                                $status = $record ? $statusMap[$record->STATUS_KEHADIRAN] ?? '-' : '-';

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
</div>

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Detail Rekap Penilaian: {{ $matkul->NAMA_MATKUL }}</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th rowspan="2" style="vertical-align: middle;">No</th>
                                <th rowspan="2" style="vertical-align: middle;">NIM</th>
                                <th rowspan="2" style="vertical-align: middle; width: 25%;">Nama Mahasiswa</th>
                                <th colspan="{{ count($sub_cpmks) }}">Sub-CPMK</th>
                                <th rowspan="2" style="vertical-align: middle;">Total Nilai Akhir</th>
                                <th rowspan="2" style="vertical-align: middle;">Grade</th>
                            </tr>
                            <tr class="text-center">
                                @foreach($sub_cpmks as $cpmk)
                                    <th>
                                        Sub {{ $cpmk->ORDERING + 1 }}
                                        <br>
                                        <small>(Bobot: {{ $cpmk->BOBOT_SUBCPMK ?? 0 }}%)</small>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @if(empty($rekap))
                                <tr>
                                    {{-- Colspan ditambah 1 untuk kolom Grade --}}
                                    <td colspan="{{ count($sub_cpmks) + 5 }}" class="text-center">Data tidak ditemukan.</td>
                                </tr>
                            @else
                                @foreach($rekap as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $item['nim'] }}</td>
                                    <td>{{ $item['nama'] }}</td>
                                    @foreach($sub_cpmks as $cpmk)
                                        {{-- Tampilkan nilai apa adanya (bisa angka atau string kosong) --}}
                                        <td class="text-center">{{ $item['nilai_subcpmk'][$cpmk->ORDERING] }}</td>
                                    @endforeach
                                    <td class="text-center fw-bold">{{ $item['total_akhir'] }}</td>
                                    <td class="text-center fw-bold">{{ $item['grade'] }}</td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button class="btn btn-danger" type="button" onclick="location.href='<?= url('rekap-penilaian') ?>'">Kembali</button>
                </div>
            </div>
        </div>
    </div>
</div>

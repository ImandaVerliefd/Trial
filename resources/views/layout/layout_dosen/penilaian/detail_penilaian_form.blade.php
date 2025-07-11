<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Penilaian {{ $mappingMatkul->NAMA_MATKUL }}</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="row">
                <div class="col-12 mb-2">
                    <div class="row">
                        <div class="col-12">
                            <ul class="nav nav-tabs" id="tab-btn" role="tablist">
                                @foreach($detailCpmk as $index => $item)
                                <li class="nav-item" role="presentation" id="li-inputan-{{$item->ORDERING + 1}}" data-ordering="{{ $item->ORDERING + 1}}" onclick="renderDetailNilai(<?= $index ?>)">
                                    <a href="#inputan-{{ $item->ORDERING + 1 }}" data-bs-toggle="tab" aria-expanded="true"
                                        class="nav-link {{$item->ORDERING == 0 ? 'active' : ''}}" aria-selected="true" role="tab">
                                        Sub CPMK-{{$item->ORDERING + 1}}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            <div class="tab-content" id="tab-content">
                                @foreach($detailCpmk as $index => $item)
                                <div class="tab-pane {{$index == 0 ? 'active' : ''}}" id="inputan-{{$index + 1}}" role="tabpanel">
                                    <form id="form-penilaian" action="<?= url('penilaian/submit') ?>" method="post">
                                        @csrf
                                        <div id="container-main-input-{{ $item->ORDERING }}">
                                            <input type="hidden" name="id_cpmk" value="{{ $index }}" />
                                            <input type="hidden" name="kode_matkul" value="{{ $kodeMatkul }}" />
                                            <input type="hidden" name="id_kurikulum" value="{{ $id_kurikulum }}" />
                                            <input type="hidden" name="ordering_subcpmk" value="{{ $item->ORDERING }}" />

                                            <input type="hidden" name="id_siakad_bobot" value="{{ $item->ID_SIAKAD }}" />
                                            <input type="hidden" name="bobot" value="{{ $item->BOBOT }}" />
                                        </div>
                                        <div class="card-body pb-0">
                                            <p>
                                                <b>Deskripsi: </b>
                                                {{$item->NAMA_PEMBELAJARAN}}
                                            </p>
                                            <table class="table table-bordered mt-3" style="table-layout: fixed;">
                                                <thead>
                                                    <tr class="text-center" style="vertical-align: middle;">
                                                        <th width="4%" rowspan="2">No</th>
                                                        <th width="20%" rowspan="2">Nama</th>
                                                        <th rowspan="2">Program Studi</th>
                                                        @foreach($mappingSiakad as $mapIndex => $itemMapping)
                                                        <th>
                                                            {{ $itemMapping->SIAKAD }}
                                                        </th>
                                                        @endforeach
                                                        <th width="5%" rowspan="2">Total Nilai</th>
                                                        <th width="5%" rowspan="2"></th>
                                                    </tr>
                                                    <tr>
                                                        @foreach($mappingSiakad as $mapIndex => $itemMapping)
                                                        <th>
                                                            <?php
                                                            $idSiakadSubCPMK = explode(';', $item->ID_SIAKAD);
                                                            $bobotSubCPMK = explode(';', $item->BOBOT);

                                                            $bobotValue = 0;
                                                            $indexMapping = array_search($itemMapping->ID_SIAKAD, $idSiakadSubCPMK);
                                                            if ($indexMapping !== false && isset($bobotSubCPMK[$indexMapping])) {
                                                                $bobotValue = $bobotSubCPMK[$indexMapping];
                                                            }
                                                            echo 'Bobot: ' . $bobotValue;
                                                            ?>
                                                        </th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($listMhs as $ind => $itemMhs)
                                                    <tr style="vertical-align: middle;">
                                                        <td class="text-center">
                                                            {{ $ind + 1 }}
                                                            <input type="hidden" name="kode_mhs[]" value="{{ $itemMhs->KODE_MHS }}">
                                                            <input type="hidden" name="id_penilaian_head[{{ $index }}][{{ $itemMhs->KODE_MHS }}]" value="{{ $penilaianHead[$item->ORDERING][$itemMhs->KODE_MHS] ?? '' }}">
                                                        </td>
                                                        <td>{{ $itemMhs->NRP }} <br> {{ $itemMhs->NAMA }}</td>
                                                        <td>{{ $itemMhs->PROGRAM_STUDI }}</td>
                                                        @foreach($mappingSiakad as $itemMapping)
                                                            <?php
                                                            $idSiakadSubCPMK = explode(';', $item->ID_SIAKAD);
                                                            $bobotSubCPMK = explode(';', $item->BOBOT);
                                                            $bobotValue = 0;
                                                            $indexMapping = array_search($itemMapping->ID_SIAKAD, $idSiakadSubCPMK);
                                                            if ($indexMapping !== false && isset($bobotSubCPMK[$indexMapping])) {
                                                                $bobotValue = $bobotSubCPMK[$indexMapping];
                                                            }
                                                            ?>
                                                            <td>
                                                                <input type="hidden" name="id_siakad[{{ $itemMhs->KODE_MHS . '|' . $item->ORDERING }}][{{ $itemMapping->ID_SIAKAD }}]" value="{{ $itemMapping->ID_SIAKAD }}">
                                                                <input type="hidden" name="id_feeder[{{ $itemMhs->KODE_MHS . '|' . $item->ORDERING }}][{{ $itemMapping->ID_SIAKAD }}]" value="{{ $itemMapping->ID_FEEDER }}">
                                                                
                                                                <input type="number" name="nilai[{{ $itemMhs->KODE_MHS . '|' . $item->ORDERING }}][{{ $itemMapping->ID_SIAKAD }}]" class="form-control" id="presentase" min='0' value="{{ $bobotValue == 0 ? '0' : '' }}" {{ $bobotValue == 0 ? 'disabled' : 'required' }}>
                                                            </td>
                                                        @endforeach
                                                        <td class="text-center fw-bold" id="total_nilai" data-key="{{ $itemMhs->KODE_MHS . '|' . $item->ORDERING }}">0</td>
                                                        <td class="text-center fw-bold">
                                                            <button class="btn btn-success" onclick="submitPerRow(this, <?= $item->ORDERING ?>)" type="button"><i class="ri-save-2-fill"></i></button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="card-footer">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button class="btn btn-danger" type="button" onclick="location.href='<?= url('penilaian') ?>'">Kembali</button>
                                                <button class="btn btn-success" type="submit">Simpan</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
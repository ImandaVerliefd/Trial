<?php

namespace App\Http\Controllers;

use App\Models\Capaian;
use App\Models\Kurikulum;
use App\Models\PemetaanFeeder;
use App\Models\PemetaanSiakad;
use App\Models\Prodi;
use App\Models\TipeCapaian;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class RPSController extends Controller
{
    // CAPAIAN DETAIL / RPS FUNCTION
    public function capaianDetailIndex()
    {
        $data['title'] = "Rancangan Pembelajaran Semester";

        $data['prodi'] = Prodi::whereNull('DELETED_AT')->get();
        $data['tipeCapaian'] = TipeCapaian::where(['IS_ACTIVE' => 1])->get();

        $data['script'] = 'layout/layout_admin/rps/_html_script';
        $data['content_page'] = 'layout/layout_admin/rps/detail_capaian_list';
        return view('templates/main', $data);
    }

    public function getAllDetailCapaianData(Request $req)
    {
        $orderData = $req->input('order')[0]['column'];
        $orderType = strtoupper($req->input('order')[0]['dir']);
        $valOrder = $req->input('columns')[$orderData]['data'];
        $search = $req->input('search')['value'];
        $start = $req->input('start');
        $perPage = $req->input('length');
        $prodi = $req->input('prodi');

        $resp = Capaian::get_data_detail_capaian_datatable($orderType, $valOrder, $search, $start, $perPage, $prodi);
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;

            $htmlCapaian = '';
            if (!empty($item->CAPAIAN)) {
                $htmlCapaian .= '<ol>';
                foreach (explode(';', $item->CAPAIAN) as $txt) {
                    $htmlCapaian .= '<li>' . $txt . '</li>';
                }
                $htmlCapaian .= '</ol>';
            }

            $data = array(
                "NAMA_MATKUL" => $item->NAMA_MATKUL,
                "CAPAIAN" => $htmlCapaian,
                "PRODI" => $item->PRODI,
                "KURIKULUM" => $item->KURIKULUM
            );

            $dataEdit = [
                "id_kurikulum" => $item->ID_KURIKULUM,
                "kode_matkul" => $item->KODE_MATKUL,
            ];
            $data['ACTION_BUTTON'] = '
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle arrow-none card-drop p-0" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-more-fill"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" style="">
                        <a href="javascript:void(0);" class="dropdown-item" onclick="location.href=`' . url('rps/form/index?data=') . Crypt::encrypt($dataEdit) . '`">
                            <i class="bi bi-pencil-square fs-20"></i> Edit
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item" onclick="location.href=`' . url('rps/form-bobot/index?data=') . Crypt::encrypt($dataEdit) . '`">
                            <i class="bi bi-pencil-square fs-20"></i> Edit Bobot Nilai
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item" onclick="generateRPS(`' . Crypt::encrypt($dataEdit) . '`)">
                            <i class="bi bi-file-earmark-pdf fs-20"></i> Print PDF
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item" onclick="deleteFormDetailCapaian(`' . Crypt::encrypt($dataEdit) . '`)">
                            <i class="bi bi-trash fs-20"></i> Delete
                        </a>
                    </div>
                </div>
            ';

            array_push($NewData_all, $data);
        }

        $draw   = $req->input('draw');
        return response([
            'status_code'       => 200,
            'status_message'    => 'Data berhasil diambil!',
            'draw'              => intval($draw),
            'recordsFiltered'   => ($counter_all != 0) ? $Tot->DataTrans : 0,
            'recordsTotal'      => ($counter_all != 0) ? $Tot->DataTrans : 0,
            'data'              => $NewData_all
        ], 200);
    }

    public function capaianDetailFormIndex(Request $req)
    {
        $data['title'] = "Rancangan Pembelajaran Semester";

        $data['mappingSiakad'] = DB::select("
            SELECT 
                mps.ID_SIAKAD ,
                mps.SIAKAD 
            FROM 
                md_siakad mps 
            WHERE 
                mps.IS_DELETE IS NULL
        ");

        if (!empty($req->input('data'))) {
            $Rawdata = Crypt::decrypt($req->input('data'));
            $data['kodeMatkul'] = $Rawdata['kode_matkul'];
            $data['id_kurikulum'] = $Rawdata['id_kurikulum'];

            if (!empty($data['id_kurikulum'] && !empty($data['kodeMatkul']))) {
                $data['detailCapaian'] = Capaian::get_data_detail_capaian($data['kodeMatkul'], $data['id_kurikulum']);
                $data['dataCapSecond'] = [];
                foreach ($data['detailCapaian'] as $key => $dataDetCap) {
                    $idCap = explode(';', $dataDetCap['ID_CAPAIAN_DETAIL']);
                    foreach ($idCap as $IDCapDet) {
                        $dataSubSecond = DB::selectOne("
                            SELECT 
                                GROUP_CONCAT(tcs.KODE_CAPAIAN SEPARATOR ';') AS KODE_CAPAIAN ,
                                GROUP_CONCAT(mc.CAPAIAN SEPARATOR ';') AS CAPAIAN ,
                                GROUP_CONCAT(mc.JENIS_CAPAIAN SEPARATOR ';') AS JENIS_CAPAIAN
                            FROM 
                                tb_cpmk_subcpmk tcs
                            LEFT JOIN md_capaian mc ON 
                                mc.KODE_CAPAIAN = tcs.KODE_CAPAIAN
                            LEFT JOIN tb_capaian_detail tcd ON 
                                tcd.ID_CAPAIAN_DETAIL = tcs.ID_CAPAIAN_DETAIL 
                            WHERE 
                                tcs.ID_CAPAIAN_DETAIL = '$IDCapDet'
                            GROUP BY
                                tcd.`ORDERING` ,
                                tcd.KODE_CAPAIAN
                        ");

                        $data['dataCapSecond'][$key] = (array)$dataSubSecond;
                    }
                }
            }
        }

        $data['tipeCapaian'] = TipeCapaian::where(['IS_ACTIVE' => 1])->get();
        $data['script'] = 'layout/layout_admin/rps/_html_script';
        $data['content_page'] = 'layout/layout_admin/rps/detail_capaian_form';
        return view('templates/main', $data);
    }

    public function capaianDetailSubmit(Request $req)
    {
        $dataWhere = [
            "kode_matkul" => $req->input('kode-matkul'),
            "id_kurikulum" => $req->input('id-kurikulum')
        ];
        $redirectUrl = 'rps/form/index?data=' . Crypt::encrypt($dataWhere);
        try {
            DB::statement("SET FOREIGN_KEY_CHECKS = 0");
            DB::beginTransaction();
            $idsToDelete = DB::table('tb_capaian_detail')
                ->where([
                    'KODE_MATKUL' => $req->input('kode-matkul'),
                    'ID_KURIKULUM' => $req->input('id-kurikulum')
                ])->pluck('ID_CAPAIAN_DETAIL');

            // DB::table('mapping_subcpmk')->whereIn('ID_CAPAIAN_DETAIL', $idsToDelete)->delete();
            DB::table('tb_capaian_detail')->whereIn('ID_CAPAIAN_DETAIL', $idsToDelete)->delete();
            DB::table('tb_cpmk_subcpmk')->whereIn('ID_CAPAIAN_DETAIL', $idsToDelete)->delete();

            $cpmkData = [];
            foreach ($req->input('pertemuan') as $parentKey => $parentItem) {
                foreach ($parentItem as $key => $pertemuanKe) {
                    $data = [
                        'KODE_MATKUL' => $req->input('kode-matkul'),
                        'ID_KURIKULUM' => $req->input('id-kurikulum'),
                        'ORDERING' => $parentKey,
                        'PERTEMUAN' => $pertemuanKe,
                        'KODE_CAPAIAN' => explode(';', base64_decode($req->input('capaian')[$parentKey][0]))[0],
                        'NAMA_PEMBELAJARAN' => $req->input('sub-capaian')[$parentKey],
                        'KAJIAN' => $req->input('bahan-kajian')[$parentKey],
                        'BENTUK_PEMBELAJARAN' => $req->input('bentuk-pembelajaran')[$parentKey],
                        'ESTIMASI_WAKTU' => $req->input('estimasi-waktu')[$parentKey],
                        'PENGALAMAN' => $req->input('pengalaman-belajar')[$parentKey],
                        'INDIKATOR' => $req->input('indikator')[$parentKey],
                        'KRITERIA' => $req->input('kriteria-penilaian')[$parentKey],
                        'LOG_TIME' => date('Y-m-d H:i:s')
                    ];

                    $insertedId = DB::table('tb_capaian_detail')->insertGetId($data);

                    foreach ($req->input('capaian')[$parentKey] as $key => $kodeCap) {
                        $cpmkData[] = [
                            "KODE_CAPAIAN" => explode(';', base64_decode($kodeCap))[0],
                            "ID_CAPAIAN_DETAIL" => $insertedId
                        ];
                    }
                }
            }

            DB::table('tb_cpmk_subcpmk')->insert($cpmkData);

            DB::commit();
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            return redirect($redirectUrl)->with('resp_msg', 'Berhasil menyimpan rancangan pembelajaran semester');
        } catch (Exception $err) {
            DB::rollBack();
            return redirect($redirectUrl)->with('err_msg', 'Gagal menyimpan detail capaian, error ' . $err->getMessage());
        }
    }

    public function capaianDetailBobotFormIndex(Request $req)
    {
        $data['title'] = "Form Bobot";

        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        $data['dataSiakad'] = DB::select("
            SELECT 
                ms.ID_SIAKAD ,
                ms.SIAKAD
            FROM
                md_siakad ms 
            WHERE
                ms.IS_DELETE IS NULL
        ");
        $data['subCPMK'] = [];
        if (!empty($req->input('data'))) {
            $Rawdata = Crypt::decrypt($req->input('data'));
            $data['idKurikulum'] = $Rawdata['id_kurikulum'];
            $data['kodeMatkul'] = $Rawdata['kode_matkul'];

            $data['subCPMK'] = DB::select("
                SELECT 
                    GROUP_CONCAT(tcd.ID_CAPAIAN_DETAIL SEPARATOR ';') AS ID_CAPAIAN_DETAIL ,
                    tcd.NAMA_PEMBELAJARAN ,
                    tcd.ID_KURIKULUM ,
                    tcd.KODE_MATKUL ,
                    tcd.NAMA_PEMBELAJARAN ,
                    GROUP_CONCAT(tcd.PERTEMUAN SEPARATOR ';') AS PERTEMUAN ,
                    tcd.ID_SIAKAD ,
                    tcd.BOBOT
                FROM
                    tb_capaian_detail tcd 
                WHERE 
                    tcd.ID_KURIKULUM = '" . $data['idKurikulum'] . "'
                    AND
                    tcd.KODE_MATKUL = '" . $data['kodeMatkul'] . "'
                GROUP BY 
                    tcd.KODE_CAPAIAN
            ");
        }

        $data['script'] = 'layout/layout_admin/rps/_html_script';
        $data['content_page'] = 'layout/layout_admin/rps/detail_capaian_bobot_form';
        return view('templates/main', $data);
    }

    public function capaianDetailBobotSubmit(Request $req)
    {
        try {
            $dataWhere = [
                "kode_matkul" => $req->input('kode-matkul'),
                "id_kurikulum" => $req->input('id-kurikulum')
            ];
            $redirectUrl = 'rps/form-bobot/index?data=' . Crypt::encrypt($dataWhere);
            foreach ($req->input('id_capaian_detail') as $key => $id_capaian_detail) {
                $data = [
                    'ID_SIAKAD' => implode(';', $req->input('id_siakad')[$key]),
                    'BOBOT' => implode(';', $req->input('bobot')[$key]),
                    'TOTAL_BOBOT' => array_sum($req->input('bobot')[$key]),
                    'LOG_TIME' => date('Y-m-d H:i:s')
                ];
                DB::table('tb_capaian_detail')->whereIn('ID_CAPAIAN_DETAIL', explode(';', $id_capaian_detail))->update($data);
            }
            DB::commit();
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            return redirect($redirectUrl)->with('resp_msg', 'Berhasil menyimpan bobot penilaian');
        } catch (Exception $err) {
            DB::rollBack();
            return redirect($redirectUrl)->with('err_msg', 'Gagal menyimpan bobot penilaian, error ' . $err->getMessage());
        }
    }

    public function capaianDetailDelete(Request $req)
    {
        $Rawdata = Crypt::decrypt($req->input('data'));
        $kodeMatkul = $Rawdata['kode_matkul'];
        $idKurikulum = $Rawdata['id_kurikulum'];
        try {
            DB::statement("SET FOREIGN_KEY_CHECKS = 0");
            DB::beginTransaction();

            DB::table('tb_capaian_detail')->where([
                "KODE_MATKUL" => $kodeMatkul,
                "ID_KURIKULUM" => $idKurikulum
            ])->update([
                "IS_DELETE" => date('Y-m-d H:i:s')
            ]);

            DB::commit();
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            return redirect('rps')->with('resp_msg', 'Berhasil menghapus rancangan pembelajaran semester');
        } catch (Exception $err) {
            DB::rollBack();
            return redirect('rps')->with('err_msg', 'Gagal menghapus rancangan pembelajaran, error ' . $err->getMessage());
        }
    }

    public function checkRPS(Request $req)
    {
        $kodeMatkul = explode(';', $req->input('matkul'))[0];
        $idKurikulum = explode(';', $req->input('matkul'))[3];

        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $data = DB::selectOne("
            SELECT
                tcd.* ,
                mm.KODE_MATKUL,
                mm.JUMLAH_PERTEMUAN,
                mm.NAMA_MATKUL ,
                mcm.ID_KURIKULUM ,
                mk.KURIKULUM
            FROM
                tb_capaian_detail tcd
            LEFT JOIN md_matkul mm ON
                mm.KODE_MATKUL = tcd.KODE_MATKUL
            LEFT JOIN mapping_capaian_matkul mcm ON
                mcm.KODE_MATKUL = mm.KODE_MATKUL 
            LEFT JOIN md_kurikulum mk ON 
                mk.ID_KURIKULUM = mcm.ID_KURIKULUM 
            WHERE
                tcd.KODE_MATKUL = '$kodeMatkul'
                AND
                tcd.ID_KURIKULUM = '$idKurikulum'
            GROUP BY 
                mm.KODE_MATKUL
        ");

        if (!empty($data)) {
            $dataEdit = [
                "kode_matkul" => $kodeMatkul,
                "id_kurikulum" => $idKurikulum
            ];
            $txtMatkul = $data->NAMA_MATKUL . ' (' . $kodeMatkul . ') ' . $data->KURIKULUM;
            return response('
                <div class="card">
                    <div class="card-body">                        
                        <div class="alert alert-danger text-center mb-0" role="alert">
                            <div class="avatar-sm mb-2 mx-auto">
                                <span class="avatar-title bg-danger rounded-circle">
                                    <i class="ri-close-line align-middle fs-22"></i>
                                </span>
                            </div>
                            <h4 class="alert-heading">Sudah memiliki RPS!</h4>
                            <p>Maaf mata kuliah ini sudah memiliki rancangan pemebelajaran semester (RPS). 
                                Klik link dibawah untuk berpindah ke mata kulaih yang sudah ada.</p>
                            <hr class="border-danger border-opacity-25">
                            <p class="mb-0"><a href="' . url('rps/form/index?data=') . Crypt::encrypt($dataEdit) . '">' . $txtMatkul . '</a></p>
                        </div>
                    </div>
                </div>            
            ', 200);
        }
    }

    public function GeneratePDF(Request $req)
    {
        $rawdata = Crypt::decrypt($req->input('dataRPS'));
        $idMatkul = $rawdata['kode_matkul'];
        $idKur = $rawdata['id_kurikulum'];

        $dataMatkul = DB::selectOne("
            SELECT 
                mpk.* ,
                mp.PRODI AS NAMPROD,
                mp.JENJANG ,
                ms.NAMA_DOSEN ,                
                trj.HARI ,
                trj.JAM_MULAI ,
                trj.SKS_MATKUL ,
                trj.JAM_SELESAI ,
                trj.ID_RUANGAN ,
                trj.NAMA_RUANGAN ,
                trj.KAPASITAS_RUANGAN ,
                trj.ID_METODE,
                trj.KODE
            FROM 
                mapping_peta_kurikulum mpk 
            LEFT JOIN md_prodi mp ON
                mp.ID_PRODI = mpk.ID_PRODI
            LEFT JOIN 
                (
                    SELECT 
                        ms.ID_DETSEM ,
                        GROUP_CONCAT(md.NAMA_DOSEN SEPARATOR ';') AS NAMA_DOSEN 
                    FROM 
                        mapping_subcpmk ms 
                    LEFT JOIN md_dosen md ON 
                        md.KODE_DOSEN = ms.KODE_DOSEN 
                    GROUP BY 
                        ms.ID_DETSEM
                ) ms ON 
                ms.ID_DETSEM = mpk.ID_DETSEM 
            LEFT JOIN (
                SELECT 
                    trj.ID_DETSEM,
                    GROUP_CONCAT(trj.KODE SEPARATOR ';') AS KODE,
                    GROUP_CONCAT(trj.HARI SEPARATOR ';') AS HARI,
                    GROUP_CONCAT(trj.JAM_MULAI SEPARATOR ';') AS JAM_MULAI,
                    GROUP_CONCAT(trj.JAM_SELESAI SEPARATOR ';') AS JAM_SELESAI,
                    GROUP_CONCAT(trj.ID_RUANGAN SEPARATOR ';') AS ID_RUANGAN,
                    GROUP_CONCAT(trj.NAMA_RUANGAN SEPARATOR ';') AS NAMA_RUANGAN,
                    GROUP_CONCAT(trj.KAPASITAS_RUANGAN SEPARATOR ';') AS KAPASITAS_RUANGAN,
                    GROUP_CONCAT(trj.SKS_MATKUL SEPARATOR ';') AS SKS_MATKUL,
                    GROUP_CONCAT(trj.ID_METODE SEPARATOR ';') AS ID_METODE, 
                    GROUP_CONCAT(mma.METODE SEPARATOR ';') AS METODE 
                FROM 
                    tb_ruang_jadwal trj 
                LEFT JOIN md_metode_ajar mma ON 
                    mma.ID_METODE = trj.ID_METODE 
                GROUP BY 
                    trj.ID_DETSEM
            ) trj ON 
                trj.ID_DETSEM = mpk.ID_DETSEM 
            WHERE 
                mpk.KODE_MATKUL = ?
                AND
                mpk.ID_KURIKULUM = ?
        ", [$idMatkul, $idKur]);

        $dataCPMK = DB::select("
            SELECT 
                mc.KODE_CAPAIAN ,
                mc.KODE_CAPAIAN_PARENT ,
                mc.CAPAIAN 
            FROM 
                mapping_capaian_matkul mcm 
            LEFT JOIN md_capaian mc ON
                mcm.KODE_CAPAIAN = mc.KODE_CAPAIAN 
            WHERE 
                mcm.KODE_MATKUL = ?
                AND
                mcm.ID_KURIKULUM = ?
        ", [$idMatkul, $idKur]);

        $dataSKS = explode(';', $dataMatkul->SKS_MATKUL);

        $dataJenjang = ['S1' => 'Sarjana', 'D3' => 'Ahli Madya'];
        $data['prodi'] = (!empty($dataMatkul->JENJANG) ? $dataJenjang[$dataMatkul->JENJANG] : '') . ' ' . $dataMatkul->NAMPROD;
        $data['sks'] = array_sum($dataSKS);
        $data['matkul'] = $dataMatkul->NAMA_MATKUL;
        $data['kodeMK'] = $dataMatkul->KODE_MATKUL;
        $data['rumpun'] = $dataMatkul->RUMPUN_MATKUL ?? "-";
        $data['sksKuliah'] = $dataSKS[0] ?? 0;
        $data['sksPraktek'] = $dataSKS[1] ?? 0;
        $data['semester'] = $dataMatkul->KODE_SEMESTER;
        $data['tglPengesahan'] = "";
        $data['pjmk'] = "-";
        $data['kaprodi'] = "-";
        $data['waket'] = "-";
        $data['katMatkul'] = $dataMatkul->KATEGORI_MATKUL ?? "-";
        $data['descMatkul'] = $dataMatkul->DESKRIPSI_MATKUL ?? "-";

        $idCPL = [];
        $data['cpmk'] = [];
        foreach ($dataCPMK as $key => $itemCPMK) {
            $dataCPL = explode(';', $itemCPMK->KODE_CAPAIAN_PARENT);
            foreach ($dataCPL as $cplData) {
                if (!in_array($cplData, $idCPL)) {
                    $idCPL[] = $cplData;
                }
            }

            $data['cpmk'][$itemCPMK->KODE_CAPAIAN] = [
                "ID" => $itemCPMK->KODE_CAPAIAN,
                "PARENT" => $itemCPMK->KODE_CAPAIAN_PARENT,
                "TXT" => '<strong>CPMK ' . ($key + 1) . ':</strong> ' . $itemCPMK->CAPAIAN
            ];
        }

        $data['cpl'] = [];
        foreach ($idCPL as $key => $idCPLData) {
            $dataCPL = DB::selectOne("
                SELECT 
                    mc.KODE_CAPAIAN ,
                    mc.CAPAIAN 
                FROM 
                    md_capaian mc 
                WHERE 
                    mc.KODE_CAPAIAN = ?
            ", [$idCPLData]);

            $data['cpl'][$dataCPL->KODE_CAPAIAN] = '<strong>CPL ' . ($key + 1) . ':</strong> ' . $dataCPL->CAPAIAN;
        }

        $data['subCpmk'] = [];
        $data['allSubCPMK'] = Capaian::get_data_detail_capaian($idMatkul, $idKur);
        $data['subCpmkFeeder'] = [];
        $data['dataFeed'] = PemetaanSiakad::get_all_siakad_with_feeder();

        foreach ($data['allSubCPMK'] as $key => $dataSubCPMK) {

            foreach ($data['dataFeed'] as $feedItem) {
                $bobot = 0;
                foreach (explode(';', $feedItem->ID_SIAKAD) as $feedSiakad) {
                    $arrKey = array_search($feedSiakad, explode(';', $dataSubCPMK['ID_SIAKAD']));
                    $bobot += ((!empty($dataSubCPMK['BOBOT'])) ? explode(';', $dataSubCPMK['BOBOT'])[$arrKey] : 0);
                }

                $data['subCpmkFeeder'][$key][$feedItem->FEEDER] = $bobot;
            }

            $data['dataCapSecond'] = [];
            $idCap = explode(';', $dataSubCPMK['ID_CAPAIAN_DETAIL']);
            foreach ($idCap as $IDCapDet) {
                $dataSubSecond = DB::selectOne("
                    SELECT 
                        GROUP_CONCAT(tcs.KODE_CAPAIAN SEPARATOR ';') AS KODE_CAPAIAN
                    FROM 
                        tb_cpmk_subcpmk tcs
                    LEFT JOIN tb_capaian_detail tcd ON 
                        tcd.ID_CAPAIAN_DETAIL = tcs.ID_CAPAIAN_DETAIL 
                    WHERE 
                        tcs.ID_CAPAIAN_DETAIL = '$IDCapDet'
                    GROUP BY
                        tcd.`ORDERING` ,
                        tcd.KODE_CAPAIAN
                ");

                $data['dataCapSecond'] = (array)$dataSubSecond;
            }

            $data['subCpmk'][base64_decode($dataSubCPMK["ID_CAPAIAN_DETAIL"])] = [
                "PARENT" => $data['dataCapSecond']['KODE_CAPAIAN'],
                "TXT" => '<strong>Sub CPMK ' . ($key + 1) . ':</strong> ' . $dataSubCPMK["NAMA_PEMBELAJARAN"]
            ];
        }

        $data['referensi'] = [];
        $data['dosen'] = [];
        foreach (explode(';', $dataMatkul->NAMA_DOSEN) as $dosenItem) {
            if (!in_array($dosenItem, $data['dosen'])) {
                $data['dosen'][] = $dosenItem;
            }
        }

        return view('layout/layout_admin/rps/rps_generate_template', $data)->render();
    }

    // STANDALONE FUNCTION    
    public function tipeCapaianSearch(Request $req)
    {
        $keyword = $req->input('keyword');
        $dataCapaian = DB::select("
            SELECT
                msc.ID_SUB_CAPAIAN,
                msc.SUB_CAPAIAN,
                msc.KETERANGAN
            FROM
                md_sub_capaian msc
            WHERE
                msc.SUB_CAPAIAN LIKE '%$keyword%'
                AND 
                msc.IS_ACTIVE = 1
                AND
                msc.IS_DELETE = 0
        ");
        return json_decode(json_encode($dataCapaian), true);
    }

    public function matkulSearch(Request $req)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $keyword = $req->input('keyword');
        $dataCapaian = DB::select("
            SELECT
                mm.KODE_MATKUL,
                mm.JUMLAH_PERTEMUAN,
                mm.NAMA_MATKUL ,
                mcm.ID_KURIKULUM ,
                mk.KURIKULUM
            FROM
                md_matkul mm
            LEFT JOIN mapping_capaian_matkul mcm ON
                mcm.KODE_MATKUL = mm.KODE_MATKUL 
            LEFT JOIN md_kurikulum mk ON 
                mk.ID_KURIKULUM = mcm.ID_KURIKULUM 
            WHERE
                (                    
                    mm.KODE_MATKUL LIKE '%$keyword%'
                    OR
                    mm.NAMA_MATKUL LIKE '%$keyword%'
                )
                AND 
                mm.IS_ACTIVE = 1
                AND
                mm.IS_DELETE = 0
                AND 
                mk.ID_KURIKULUM IS NOT NULL
            GROUP BY 
                mm.KODE_MATKUL
        ");

        return json_decode(json_encode($dataCapaian), true);
    }

    public function GenerateUniqChild($first, $val)
    {
        $input = $val;
        $hash = md5($input);
        $sixDigitID = strtoupper(substr($hash, 0, 6));
        $generatedID = $first . '_' . $sixDigitID;
        return $generatedID;
    }

    function cleanString($string)
    {
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        $asciiOnly = preg_replace('/[^\x20-\x7E]/', '', $normalized);
        $cleaned = preg_replace('/[^a-zA-Z0-9\s-]/', '', $asciiOnly);
        return trim(preg_replace('/\s+/', ' ', $cleaned));
    }
}

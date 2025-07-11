<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class PenilaianController extends Controller
{
    public function index()
    {
        $data['title'] = "Penilaian";
        $data['content_page'] = 'layout/layout_dosen/penilaian/index';
        $data['script'] = 'layout/layout_dosen/penilaian/_html_script';
        return view('templates/main', $data);
    }

    public function getAllMatkulData(Request $req)
    {
        $orderData = $req->input('order')[0]['column'];
        $orderType = strtoupper($req->input('order')[0]['dir']);
        $valOrder = $req->input('columns')[$orderData]['data'];
        $search = $req->input('search')['value'];
        $start = $req->input('start');
        $perPage = $req->input('length');
        $prodi = $req->input('prodi');

        $data = DB::select("
            SELECT DISTINCT 
                mm.NAMA_MATKUL,
                mm.KODE_MATKUL,
                tkm.NAMA_KELAS AS KODE_KELAS,
                mm.ID_PRODI AS ID_KURIKULUM
            FROM md_matkul mm
            JOIN tb_kelas_matkul tkm 
                ON mm.KODE_MATKUL = tkm.KODE_MATKUL
            LEFT JOIN mapping_subcpmk msc 
                ON tkm.KODE_KELAS = msc.KODE_KELAS 
                AND tkm.ID_DETSEM = msc.ID_DETSEM
            WHERE mm.IS_ACTIVE = 1 AND msc.KODE_DOSEN = '". session('user')[0]['kode_user'] ."'
        ");

        $resp = [
            "DATA" => $data,
            "TOTAL_DATA" => (object)[
                "DataTrans" => count($data)
            ]
        ];

        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;

            $data = array(
                "NAMA_MATKUL" => $item->NAMA_MATKUL,
                "KODE_KELAS" => $item->KODE_KELAS,
            );

            $dataEdit = [
                "id_kurikulum" => $item->ID_KURIKULUM,
                "kode_matkul" => $item->KODE_MATKUL,
            ];
            $data['ACTION_BUTTON'] = '
                    <a href="javascript:void(0);" class="btn btn-warning" onclick="location.href=`' . url('penilaian/form/index?data=') . Crypt::encrypt($dataEdit) . '`">
                        <i class="ri-pencil-line"></i>
                    </a>
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

    public function penilaianDetailFormIndex(Request $req)
    {
        $data['title'] = "Penilaian";

        $Rawdata = Crypt::decrypt($req->input('data'));
        $data['kodeMatkul'] = $Rawdata['kode_matkul'];
        $data['id_kurikulum'] = $Rawdata['id_kurikulum'];

        $data['mappingMatkul'] = DB::selectOne("
            SELECT
                mm.NAMA_MATKUL ,
                mpk.ID_DETSEM
            FROM
                mapping_peta_kurikulum mpk
            LEFT JOIN md_matkul mm ON
                mm.KODE_MATKUL = mpk.KODE_MATKUL
            WHERE
                mpk.KODE_MATKUL = '" . $data['kodeMatkul'] . "'
                AND
                mpk.ID_KURIKULUM = '" . $data['id_kurikulum'] . "'
        ");

        $data['mappingSiakad'] = DB::select("
            SELECT 
                mps.ID_SIAKAD ,
                mps.ID_FEEDER ,
                mps.SIAKAD 
            FROM 
                md_siakad mps 
            WHERE 
                mps.IS_DELETE IS NULL
        ");

        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $data['detailCpmk'] = DB::select("
            SELECT 
                tcd.`ORDERING` ,
                tcd.NAMA_PEMBELAJARAN ,
                GROUP_CONCAT(tcd.PERTEMUAN) AS PERTEMUAN ,
                tcd.ID_SIAKAD ,
                tcd.BOBOT
            FROM
                tb_capaian_detail tcd 
            WHERE
                tcd.KODE_MATKUL = '" . $data['kodeMatkul'] . "'
                AND
                tcd.ID_KURIKULUM = '" . $data['id_kurikulum'] . "'
            GROUP BY
                tcd.`ORDERING`
        ");

        $data['listMhs'] = [];
        $mhsRaw = DB::select("
            SELECT 
                tp.ID_DETSEM ,
                tp.KODE_MAHASISWA ,
                mm.NAMA_MAHASISWA ,
                mm.NIM ,
                CONCAT_WS(' ', mp.JENJANG, mp.PRODI) AS PRODI
            FROM
                tb_perwalian tp 
            LEFT JOIN md_mahasiswa mm ON
                mm.KODE_MAHASISWA = tp.KODE_MAHASISWA
            LEFT JOIN md_prodi mp ON
                mp.ID_PRODI = mm.ID_PRODI
            WHERE
                tp.ID_DETSEM = '" . $data['mappingMatkul']->ID_DETSEM . "'
        ");
        if (!empty($mhsRaw)) {
            foreach ($mhsRaw as $item) {
                $data['listMhs'][] = (object)[
                    "KODE_MHS" => $item->KODE_MAHASISWA,
                    "NAMA" => $item->NAMA_MAHASISWA,
                    "NRP" => $item->NIM,
                    "PROGRAM_STUDI" => $item->PRODI
                ];
            }
        }
        
        $penilaianHead = (array)DB::select("
            SELECT 
                tph.*
            FROM
                tb_penilaian_head tph 
            WHERE 
                tph.KODE_MATKUL = '" . $data['kodeMatkul'] . "'
                AND
                tph.ID_KURIKULUM = '" . $data['id_kurikulum'] . "'
        ");
        $data['penilaianHead'] = [];
        foreach ($penilaianHead as $item) {
            $data['penilaianHead'][$item->ORDERING_SUBCPMK][$item->KODE_MAHASISWA] = Crypt::encrypt($item->ID_PENILAIAN_HEAD);
        }

        $data['content_page'] = 'layout/layout_dosen/penilaian/detail_penilaian_form';
        $data['script'] = 'layout/layout_dosen/penilaian/_html_script';
        return view('templates/main', $data);
    }

    public function penilaianDetailSubmit(Request $req)
    {
        $dataEdit = [
            "id_kurikulum" => $req->input('id_kurikulum'),
            "kode_matkul" => $req->input('kode_matkul'),
        ];

        try {
            $siakadBobot = explode(';', $req->input('id_siakad_bobot'));
            $bobot = explode(';', $req->input('bobot'));

            $realBobot = [];
            foreach ($siakadBobot as $index => $idSiakadBobot) {
                $realBobot[$idSiakadBobot] = $bobot[$index];
            }

            DB::beginTransaction();
            DB::statement("SET FOREIGN_KEY_CHECKS = 0");

            $idPenilaian = $req->input('id_penilaian_head');
            foreach ($req->input('kode_mhs') as $index => $kodeMhs) {
                $orderSubCPMK = $req->input('ordering_subcpmk');
                $idGetData = $kodeMhs . '|' . $req->input('ordering_subcpmk');
                $idPenilaianHead = !empty($idPenilaian[$orderSubCPMK][$kodeMhs]) ? Crypt::decrypt($idPenilaian[$orderSubCPMK][$kodeMhs]) : $this->GenerateUniqChild('PENILAIAN', $idGetData . uniqid());

                $dataDetail = [];
                $totNilai = 0;
                foreach ($req->input('id_siakad')[$idGetData] as $idSiakad) {
                    $nilaiBobot = ($realBobot[$idSiakad] / 100) * $req->input('nilai')[$idGetData][$idSiakad];
                    $totNilai += $nilaiBobot;
                    $dataDetail[] = [
                        "ID_PENILAIAN_HEAD" => $idPenilaianHead,

                        "ID_SIAKAD" => $idSiakad,
                        "ID_FEEDER" => $req->input('id_feeder')[$idGetData][$idSiakad],
                        "NILAI" => $req->input('nilai')[$idGetData][$idSiakad],

                        "BOBOT" => $realBobot[$idSiakad],
                        "NILAI_BOBOT" => $nilaiBobot
                    ];
                }

                $dataHead = [
                    "ID_PENILAIAN_HEAD" => $idPenilaianHead,

                    "KODE_KELAS" => NULL,
                    "KODE_DOSEN" => NULL,

                    "KODE_MAHASISWA" => $kodeMhs,
                    "ORDERING_SUBCPMK" => $orderSubCPMK,
                    "KODE_MATKUL" => $req->input('kode_matkul'),
                    "ID_KURIKULUM" => $req->input('id_kurikulum'),
                    "TOTAL_NILAI" => $totNilai
                ];

                if (!empty($idPenilaian)) {
                    DB::table("tb_penilaian_detail")->where(["ID_PENILAIAN_HEAD" => $idPenilaianHead])->delete();
                    DB::table("tb_penilaian_head")->where(["ID_PENILAIAN_HEAD" => $idPenilaianHead])->delete();
                }
                DB::table("tb_penilaian_head")->insert($dataHead);
                DB::table("tb_penilaian_detail")->insert($dataDetail);

                // dump($dataHead, $dataDetail);
            }
            // die;

            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            DB::commit();

            return redirect('penilaian/form/index?data=' . Crypt::encrypt($dataEdit))->with('resp_msg', 'Berhasil menyimpan capaian');
        } catch (Exception $err) {
            DB::rollBack();

            return redirect('penilaian/form/index?data=' . Crypt::encrypt($dataEdit))->with('err_msg', 'Gagal menyimpan capaian, error ' . $err->getMessage());
        }
    }

    public function penilaianDetailIndex(Request $req)
    {
        $idHeads = $req->input('id_penilaian_heads');
        if (!empty($idHeads)) {
            $decryptedIds = array_map(function ($encryptedId) {
                return Crypt::decrypt($encryptedId);
            }, $idHeads);
            $bindings = implode(',', array_map('intval', $decryptedIds));

            $penilaianDetailRaw = DB::select("
                SELECT 
                    tpd.* ,
                    tph.KODE_MAHASISWA ,
                    tph.ORDERING_SUBCPMK ,
                    tph.TOTAL_NILAI
                FROM 
                    tb_penilaian_detail tpd
                LEFT JOIN tb_penilaian_head tph ON 
                    tph.ID_PENILAIAN_HEAD = tpd.ID_PENILAIAN_HEAD
                WHERE 
                    tpd.ID_PENILAIAN_HEAD IN ($bindings)
            ");

            $penilaianDetail = [];
            foreach ($penilaianDetailRaw as $item) {
                $idGetData = $item->KODE_MAHASISWA . '|' . $item->ORDERING_SUBCPMK;
                $penilaianDetail[$idGetData][$item->ID_SIAKAD] = $item->NILAI;
                $penilaianDetail[$idGetData]['TOTAL_NILAI'] = $item->TOTAL_NILAI;
            }
            return $penilaianDetail;
        } else {
            return '';
        }
    }

    public function penilaianDetailSubmitPerRow(Request $req)
    {
        try {
            $siakadBobot = explode(';', $req->input('id_siakad_bobot'));
            $bobot = explode(';', $req->input('bobot'));
            $reqtotNilai = 0;

            $realBobot = [];
            foreach ($siakadBobot as $index => $idSiakadBobot) {
                $realBobot[$idSiakadBobot] = $bobot[$index];
            }

            DB::beginTransaction();
            DB::statement("SET FOREIGN_KEY_CHECKS = 0");

            $idPenilaian = $req->input('id_penilaian_head');
            foreach ($req->input('kode_mhs') as $index => $kodeMhs) {
                $orderSubCPMK = $req->input('ordering_subcpmk');
                $idGetData = $kodeMhs . '|' . $req->input('ordering_subcpmk');
                $idPenilaianHead = !empty($idPenilaian[$orderSubCPMK][$kodeMhs]) ? Crypt::decrypt($idPenilaian[$orderSubCPMK][$kodeMhs]) : $this->GenerateUniqChild('PENILAIAN', $idGetData . uniqid());

                $dataDetail = [];
                $totNilai = 0;
                foreach ($req->input('id_siakad')[$idGetData] as $idSiakad) {
                    $nilaiBobot = ($realBobot[$idSiakad] / 100) * $req->input('nilai')[$idGetData][$idSiakad];
                    $totNilai += $nilaiBobot;
                    $dataDetail[] = [
                        "ID_PENILAIAN_HEAD" => $idPenilaianHead,

                        "ID_SIAKAD" => $idSiakad,
                        "ID_FEEDER" => $req->input('id_feeder')[$idGetData][$idSiakad],
                        "NILAI" => $req->input('nilai')[$idGetData][$idSiakad],

                        "BOBOT" => $realBobot[$idSiakad],
                        "NILAI_BOBOT" => $nilaiBobot
                    ];
                }

                $dataHead = [
                    "ID_PENILAIAN_HEAD" => $idPenilaianHead,

                    "KODE_KELAS" => NULL,
                    "KODE_DOSEN" => NULL,

                    "KODE_MAHASISWA" => $kodeMhs,
                    "ORDERING_SUBCPMK" => $orderSubCPMK,
                    "KODE_MATKUL" => $req->input('kode_matkul'),
                    "ID_KURIKULUM" => $req->input('id_kurikulum'),
                    "TOTAL_NILAI" => $totNilai
                ];

                $reqtotNilai = ceil($totNilai * 10) / 10;

                if (!empty($idPenilaian)) {
                    DB::table("tb_penilaian_detail")->where(["ID_PENILAIAN_HEAD" => $idPenilaianHead])->delete();
                    DB::table("tb_penilaian_head")->where(["ID_PENILAIAN_HEAD" => $idPenilaianHead])->delete();
                }
                DB::table("tb_penilaian_head")->insert($dataHead);
                DB::table("tb_penilaian_detail")->insert($dataDetail);

                // dump($dataHead, $dataDetail);
            }
            // die;

            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            DB::commit();

            return response([
                'status' => 'success',
                'msg' => 'Berhasil menyimpan penilaian!',
                'total' => $reqtotNilai
            ], 200);
        } catch (Exception $err) {
            DB::rollBack();

            return response([
                'status' => 'error',
                'msg' => 'Gagal menyimpan penilaian, error ' . $err->getMessage()
            ], 200);
        }
    }

    public function rekapIndex()
    {
        $data['title'] = "Rekap Penilaian";
        $data['content_page'] = 'layout/layout_dosen/penilaian_rekap/index';
        $data['script'] = 'layout/layout_dosen/penilaian_rekap/_html_script';
        return view('templates/main', $data);
    }

    public function getAllMatkulDataForRekap(Request $req)
    {
        // --- Get DataTables parameters ---
        $orderColumnIndex = $req->input('order')[0]['column'];
        $orderColumnName = $req->input('columns')[$orderColumnIndex]['data'];
        $orderDir = $req->input('order')[0]['dir'];
        $searchValue = $req->input('search')['value'];
        $start = (int)$req->input('start', 0);
        $length = (int)$req->input('length', 10);
        $draw   = $req->input('draw');

        $kode_dosen = session('user')[0]['kode_user'];
        $baseBindings = [$kode_dosen];

        $baseSql = "
            FROM md_matkul mm
            JOIN tb_kelas_matkul tkm ON mm.KODE_MATKUL = tkm.KODE_MATKUL
            JOIN mapping_peta_kurikulum mpk ON mm.KODE_MATKUL = mpk.KODE_MATKUL
            LEFT JOIN mapping_subcpmk msc ON tkm.KODE_KELAS = msc.KODE_KELAS AND tkm.ID_DETSEM = msc.ID_DETSEM
            WHERE mm.IS_ACTIVE = 1 AND msc.KODE_DOSEN = ?
        ";

        $totalSql = "SELECT COUNT(*) as total FROM (SELECT mm.KODE_MATKUL $baseSql GROUP BY mm.KODE_MATKUL, tkm.NAMA_KELAS) as sub";
        $recordsTotal = DB::selectOne($totalSql, $baseBindings)->total;

        $searchSql = "";
        $searchBindings = [];
        if (!empty($searchValue)) {
            $searchSql = " AND (mm.NAMA_MATKUL LIKE ? OR tkm.NAMA_KELAS LIKE ?)";
            $searchBindings = ['%' . $searchValue . '%', '%' . $searchValue . '%'];
        }

        $filteredSql = "SELECT COUNT(*) as total FROM (SELECT mm.KODE_MATKUL $baseSql $searchSql GROUP BY mm.KODE_MATKUL, tkm.NAMA_KELAS) as sub";
        $recordsFiltered = DB::selectOne($filteredSql, array_merge($baseBindings, $searchBindings))->total;

        $allowedOrderColumns = ['NAMA_MATKUL' => 'mm.NAMA_MATKUL', 'KODE_KELAS' => 'tkm.NAMA_KELAS'];
        $orderColumn = $allowedOrderColumns[$orderColumnName] ?? 'mm.NAMA_MATKUL';

        $dataSql = "
            SELECT DISTINCT mm.NAMA_MATKUL, mm.KODE_MATKUL, tkm.NAMA_KELAS as KODE_KELAS, mpk.ID_KURIKULUM
            $baseSql
            $searchSql
            ORDER BY $orderColumn $orderDir
            LIMIT ?
            OFFSET ?
        ";

        $dataBindings = array_merge($baseBindings, $searchBindings, [$length, $start]);
        $data = DB::select($dataSql, $dataBindings);

        $NewData_all = array();
        foreach ($data as $item) {
            $rowData = array(
                "NAMA_MATKUL" => $item->NAMA_MATKUL,
                "KODE_KELAS" => $item->KODE_KELAS,
            );

            $dataDetail = [
                "id_kurikulum" => $item->ID_KURIKULUM,
                "kode_matkul" => $item->KODE_MATKUL,
            ];
            $rowData['ACTION_BUTTON'] = '
                <a href="'. url('rekap-penilaian/detail?data=') . Crypt::encrypt($dataDetail) .'" class="btn btn-info">
                    <i class="ri-eye-line"></i> Detail
                </a>
            ';
            $NewData_all[] = $rowData;
        }

        return response([
            'status_code'       => 200,
            'status_message'    => 'Data berhasil diambil!',
            'draw'              => intval($draw),
            'recordsFiltered'   => $recordsFiltered,
            'recordsTotal'      => $recordsTotal,
            'data'              => $NewData_all
        ], 200);
    }
    
    public function rekapDetailIndex(Request $req)
    {
        $data['title'] = "Detail Rekap Penilaian";
    
        $Rawdata = Crypt::decrypt($req->input('data'));
        $kodeMatkul = $Rawdata['kode_matkul'];
        $idKurikulum = $Rawdata['id_kurikulum'];
    
        $data['matkul'] = DB::table('md_matkul')->where('KODE_MATKUL', $kodeMatkul)->first();
    
        $allData = DB::select("
            SELECT
                students.KODE_MAHASISWA,
                students.NIM,
                students.NAMA_MAHASISWA,
                sub_cpmks.ORDERING,
                sub_cpmks.BOBOT_SUBCPMK,
                penilaian.TOTAL_NILAI
            FROM
                (
                    SELECT mm.KODE_MAHASISWA, mm.NIM, mm.NAMA_MAHASISWA
                    FROM mapping_peta_kurikulum mpk
                    JOIN tb_perwalian tp ON mpk.ID_DETSEM = tp.ID_DETSEM
                    JOIN md_mahasiswa mm ON tp.KODE_MAHASISWA = mm.KODE_MAHASISWA
                    WHERE mpk.KODE_MATKUL = '$kodeMatkul' AND mpk.ID_KURIKULUM = '$idKurikulum'
                ) AS students
            CROSS JOIN
                (
                    SELECT ORDERING, BOBOT_SUBCPMK
                    FROM tb_capaian_detail
                    WHERE KODE_MATKUL = '$kodeMatkul' AND ID_KURIKULUM = '$idKurikulum'
                    GROUP BY ORDERING, BOBOT_SUBCPMK
                ) AS sub_cpmks
            LEFT JOIN
                tb_penilaian_head AS penilaian
                ON students.KODE_MAHASISWA = penilaian.KODE_MAHASISWA
                AND sub_cpmks.ORDERING = penilaian.ORDERING_SUBCPMK
                AND penilaian.KODE_MATKUL = '$kodeMatkul'
                AND penilaian.ID_KURIKULUM = '$idKurikulum'
            ORDER BY
                students.NAMA_MAHASISWA, sub_cpmks.ORDERING ASC
        ");
    
        $data['rekap'] = [];
        $data['sub_cpmks'] = [];
        $rekapMapped = [];
        $subCpmksMapped = [];
    
        foreach ($allData as $row) {
            if (!isset($subCpmksMapped[$row->ORDERING])) {
                $subCpmksMapped[$row->ORDERING] = (object)[
                    'ORDERING' => $row->ORDERING,
                    'BOBOT_SUBCPMK' => $row->BOBOT_SUBCPMK,
                ];
            }
    
            if (!isset($rekapMapped[$row->KODE_MAHASISWA])) {
                $rekapMapped[$row->KODE_MAHASISWA] = [
                    'nim' => $row->NIM,
                    'nama' => $row->NAMA_MAHASISWA,
                    'nilai_subcpmk' => [],
                    'totalTertimbang' => 0,
                    'adaNilai' => false,
                ];
            }
    
            $nilai = $row->TOTAL_NILAI;
            $bobot = $row->BOBOT_SUBCPMK ?? 0;
            
            $rekapMapped[$row->KODE_MAHASISWA]['nilai_subcpmk'][$row->ORDERING] = ($nilai !== null) ? $nilai : '';
            if ($nilai !== null) {
                $rekapMapped[$row->KODE_MAHASISWA]['totalTertimbang'] += (float)$nilai * ($bobot / 100);
                $rekapMapped[$row->KODE_MAHASISWA]['adaNilai'] = true;
            }
        }

        $data['sub_cpmks'] = array_values($subCpmksMapped);
    
        foreach ($rekapMapped as $rekapMhs) {
            if ($rekapMhs['adaNilai']) {
                $total_akhir = round($rekapMhs['totalTertimbang'], 2);
                $rekapMhs['total_akhir'] = $total_akhir;
                
                if ($total_akhir >= 76) $rekapMhs['grade'] = 'A';
                elseif ($total_akhir >= 66) $rekapMhs['grade'] = 'B';
                elseif ($total_akhir >= 56) $rekapMhs['grade'] = 'C';
                elseif ($total_akhir >= 41) $rekapMhs['grade'] = 'D';
                else $rekapMhs['grade'] = 'E';
            } else {
                $rekapMhs['total_akhir'] = '';
                $rekapMhs['grade'] = '';
            }
            
            unset($rekapMhs['totalTertimbang'], $rekapMhs['adaNilai']);
            $data['rekap'][] = $rekapMhs;
        }
    
        $data['content_page'] = 'layout/layout_dosen/penilaian_rekap/detail_rekap_form';
        $data['script'] = 'layout/layout_dosen/penilaian_rekap/_html_script';
        return view('templates/main', $data);
    }


    // STANDALONE FUNCTION    
    public function GenerateUniqChild($first, $val)
    {
        $input = $val;
        $hash = md5($input);
        $sixDigitID = strtoupper(substr($hash, 0, 6));
        $generatedID = $first . '_' . $sixDigitID;
        return $generatedID;
    }
}

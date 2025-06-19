<?php

namespace App\Http\Controllers;

use App\Models\Kurikulum;
use App\Models\Matkul;
use App\Models\Prodi;
use App\Models\Semester;
use App\Models\PetaKurikulum;
use App\Models\TahunAjar;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PetaKurikulumController extends Controller
{
    public function index()
    {
        $data['title'] = "Mapping Peta Kurikulum";
        $data['prodi'] = Prodi::whereNull('DELETED_AT')->get();
        $data['matkul'] = Matkul::get_matkul_with_capaian();
        $data['semester'] = Semester::get_all_semester();
        $data['script'] = 'layout/layout_admin/PetaKurikulum/_html_script';
        $data['content_page'] = 'layout/layout_admin/PetaKurikulum/index';
        return view('templates/main', $data);
    }

    public function index_form()
    {
        $data['title'] = "Mapping Peta Kurikulum";
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        // $data['prodi'] = DB::select("
        //     SELECT 
        //         mp.ID_PRODI ,
        //         CONCAT(COALESCE(mp.JENJANG, 'Pendidikan Profesi'), ' ', mp.PRODI) AS PRODI
        //     FROM
        //         md_prodi mp
        //     WHERE 
        //         mp.DELETED_AT IS NULL
        // ");
        $paketMatkulRaw = DB::select("
            SELECT 
                KODE_PAKET ,
                tpm.ID_PRODI ,
                CONCAT(COALESCE(mp.JENJANG, 'Pendidikan Profesi'), ' ', mp.PRODI) AS PRODI ,
                tpm.ID_TAHUN_AJAR ,
                mta.TAHUN_AJAR ,
                tpm.KODE_SEMESTER
            FROM
                tb_paket_matkul tpm 
            LEFT JOIN md_prodi mp ON
                mp.ID_PRODI = tpm.ID_PRODI
            LEFT JOIN md_tahun_ajaran mta ON
                mta.ID_TAHUN_AJAR = tpm.ID_TAHUN_AJAR
            GROUP BY 
                tpm.ID_PRODI ,
                tpm.ID_TAHUN_AJAR ,
                tpm.KODE_SEMESTER
        ");

        $data['paketMatkul'] = [];
        $data['paketDropdown'] = [];
        foreach ($paketMatkulRaw as $item) {
            $data['paketMatkul'][] = [
                'KODE_PAKET' => $item->KODE_PAKET,
                'PRODI' => $item->PRODI,
                'TAHUN_AJAR' => $item->TAHUN_AJAR,
                'KODE_SEMESTER' => $item->KODE_SEMESTER
            ];

            $data['paketDropdown'][$item->PRODI][$item->TAHUN_AJAR][] = $item->KODE_SEMESTER;
        }

        // dump($data['paketDropdown']);
        // dd($data['paketMatkul']);

        $data['script'] = 'layout/layout_admin/PetaKurikulum/_html_script';
        $data['content_page'] = 'layout/layout_admin/PetaKurikulum/index_form';
        return view('templates/main', $data);
    }

    public function renderPaketForm(Request $req)
    {
        $data['title'] = "Mapping Peta Kurikulum";
        $data['prodi'] = Prodi::whereNull('DELETED_AT')->get();
        $data['semester'] = Semester::get_all_semester();
        $data['tahunAjar'] = TahunAjar::whereNull('IS_DELETE')->get();

        $kodePaket = $req->input('kode_paket');
        $data['matkulByPaket'] = DB::select("
            SELECT 
                tpm.KODE_PAKET ,
                tpm.KODE_MATKUL ,
                tpm.ID_PRODI ,
                tpm.ID_TAHUN_AJAR ,
                tpm.KODE_SEMESTER ,
                CONCAT(mp.JENJANG, ' ', mp.PRODI) AS PRODI ,
                mm.NAMA_MATKUL ,
                mta.TAHUN_AJAR ,
                mpk.ID_DETSEM ,
                mpk.ID_KURIKULUM ,
                mpk.KATEGORI_MATKUL,
                mpk.RUMPUN_MATKUL,
                mpk.DESKRIPSI_MATKUL,
                mpk.IS_DIADAKAN,
                mpk.ID_SEMESTER
            FROM
                tb_paket_matkul tpm 
            LEFT JOIN mapping_peta_kurikulum mpk ON
                mpk.KODE_MATKUL = tpm.KODE_MATKUL 
                AND
                mpk.ID_TAHUN_AJAR = tpm.ID_TAHUN_AJAR 
                AND
                mpk.ID_PRODI = tpm.ID_PRODI 
            LEFT JOIN md_matkul mm ON 
                mm.KODE_MATKUL = tpm.KODE_MATKUL
            LEFT JOIN md_prodi mp ON
                mp.ID_PRODI = tpm.ID_PRODI
            LEFT JOIN md_tahun_ajaran mta ON
                mta.ID_TAHUN_AJAR = tpm.ID_TAHUN_AJAR
            WHERE
                tpm.KODE_PAKET = '$kodePaket'
        ");

        if (!empty($data['matkulByPaket'])) {
            $data['matkulTerkait'] = DB::select("
                SELECT
                    mm.NAMA_MATKUL,
                    mpk.ID_DETSEM,
                    mpk.ID_KURIKULUM,
                    mpk.ID_SEMESTER,
                    mpk.ID_TAHUN_AJAR,
                    mpk.ID_PRODI,
                    mpk.PRODI ,
                    mpk.SEMESTER,
                    mpk.KODE_SEMESTER,
                    mpk.KATEGORI_MATKUL,
                    mpk.RUMPUN_MATKUL,
                    mpk.DESKRIPSI_MATKUL,
                    mpk.KODE_MATKUL ,
                    mta.TAHUN_AJAR
                FROM
                    mapping_peta_kurikulum mpk
                LEFT JOIN (
                    SELECT
                        DISTINCT KODE_MATKUL
                    FROM
                        tb_paket_matkul
                    WHERE
                        KODE_PAKET = '$kodePaket'
                ) AS paket ON
                    mpk.KODE_MATKUL = paket.KODE_MATKUL
                LEFT JOIN md_matkul mm ON
                    mpk.KODE_MATKUL = mm.KODE_MATKUL
                LEFT JOIN md_tahun_ajaran mta ON
                    mta.ID_TAHUN_AJAR = mpk.ID_TAHUN_AJAR
                WHERE
                    mpk.ID_DETSEM IS NOT NULL
            ");

            $data['content_page'] = 'layout/layout_admin/PetaKurikulum/paper_form';
            $data['script_page'] = 'layout/layout_admin/PetaKurikulum/_html_script_form';
            return base64_encode(view($data['content_page'], $data)->render());
        } else {
            return '';
        }
    }

    public function getAllData(Request $req)
    {
        $orderData = $req->input('order')[0]['column'];
        $orderType = strtoupper($req->input('order')[0]['dir']);
        $valOrder = $req->input('columns')[$orderData]['data'];
        $search = $req->input('search')['value'];
        $start = $req->input('start');
        $perPage = $req->input('length');
        $prodi = $req->input('prodi');

        $resp = PetaKurikulum::get_data_datatable($orderType, $valOrder, $search, $start, $perPage, $prodi);
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;
            $data = array(
                "KODE_MATKUL" => $item->KODE_MATKUL,
                "NAMA_MATKUL" => $item->NAMA_MATKUL,
                "SEMESTER" => $item->SEMESTER,
                "KURIKULUM" => $item->KURIKULUM,
                "PRODI" => $item->PRODI,
                "IS_ACTIVE" => (($item->IS_ACTIVE == 1) ? '<span class="badge bg-success-subtle text-success">Aktif</span>' : '<span class="badge bg-danger-subtle text-danger">Tidak Aktif</span>'),
            );

            $parseData = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
            $data['ACTION_BUTTON'] = '
                <button type="button" class="btn btn-warning" onclick="openModal(`' . $parseData . '`)">
                    <i class="ri-pencil-line"></i>
                </button>
                <button type="button" class="btn btn-danger" onclick="modalConfirmDelete(`' . $item->ID_DETSEM . '`)">
                    <i class="ri-delete-bin-line"></i>
                </button>
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

    public function submit(Request $req)
    {
        try {
            DB::beginTransaction();
            $idDetSem = $req->input("idDetSem");

            foreach ($idDetSem as $i => $ID_DETSEM) {
                $kode_paket = $req->input("kode_paket")[$i];
                $nama_matkul = $req->input("nama_matkul")[$i];
                $kode_matkul = $req->input("kode_matkul")[$i];
                $id_kurikulum = $req->input("id_kurikulum")[$i];
                $tahun_ajar = $req->input("tahun_ajar")[$i];
                $id_semester = $req->input("id_semester")[$i];
                $used_semester = $req->input("used_semester")[$i];
                $rumpun_matkul = $req->input("rumpun_matkul")[$i];
                $kate_matkul = $req->input("kate_matkul")[$i];
                $desc_matkul = $req->input("desc_matkul")[$i];
                $status_matkul = $req->input("status_matkul")[$i];

                $dataSemester = Semester::whereRaw("ID_SEMESTER = '$id_semester'")->first();
                $dataKurikulum = Kurikulum::whereRaw("ID_KURIKULUM = '$id_kurikulum'")->first();
                $semester = $dataSemester->SEMESTER;

                $dataMatkul = DB::selectOne("
                    SELECT
                        mm.* ,
                        mp.PRODI ,
                        mp.JENJANG ,
                        mm.IS_UMUM
                    FROM
                        md_matkul mm 
                    LEFT JOIN md_prodi mp ON
                        mp.ID_PRODI = mm.ID_PRODI
                    WHERE
                        mm.KODE_MATKUL = '$kode_matkul'
                ");

                $randomNum = $this->GenerateKodeMatkul($dataMatkul->IS_UMUM, $dataMatkul->PRODI, $kode_matkul, $semester, $tahun_ajar);
                $idPetaKurikulum = !empty($ID_DETSEM) ? $ID_DETSEM : $randomNum;
                $prodiTxt = !empty($dataMatkul->JENJANG) ? $dataMatkul->JENJANG . ' ' . $dataMatkul->PRODI : $dataMatkul->PRODI;

                $data = [
                    "ID_SEMESTER" => $id_semester,
                    "ID_KURIKULUM" => $id_kurikulum,
                    "ID_TAHUN_AJAR" => $tahun_ajar,
                    "KODE_PAKET" => $kode_paket,
                    "KODE_MATKUL" => $kode_matkul,
                    "KODE_SEMESTER" => $used_semester,
                    "NAMA_MATKUL" => $nama_matkul,
                    "RUMPUN_MATKUL" => $this->cleanString($rumpun_matkul),
                    "KATEGORI_MATKUL" => $this->cleanString($kate_matkul),
                    "DESKRIPSI_MATKUL" => $this->cleanString($desc_matkul),
                    "IS_DIADAKAN" => $status_matkul,

                    "ID_DETSEM" => $idPetaKurikulum,
                    "PRODI" => $prodiTxt,
                    "ID_PRODI" => $dataMatkul->ID_PRODI,
                    "KURIKULUM" => $dataKurikulum->KURIKULUM ?? '',
                    "SEMESTER" => $dataSemester->SEMESTER

                ];

                DB::table("mapping_peta_kurikulum")->updateOrInsert(["ID_DETSEM" => $idPetaKurikulum], $data);
            }
            DB::commit();

            return redirect('peta-kurikulum')->with('resp_msg', 'Berhasil menyimpan Peta Kurikulum.');
        } catch (Exception $err) {
            DB::rollBack();

            return $err->getMessage();
            return redirect('peta-kurikulum')->with('err_msg', 'Gagal menyimpan Peta Kurikulum, error ' . $err->getMessage());
        }
    }

    public function delete(Request $req)
    {
        DB::statement("SET FOREIGN_KEY_CHECKS = 0");
        try {
            $idDetsem = $req->input("id");

            $data = [
                "IS_DELETE" => date('Y-m-d H:i:s')
            ];

            DB::beginTransaction();
            DB::table("mapping_peta_kurikulum")->updateOrInsert(["ID_DETSEM" => $idDetsem], $data);
            DB::commit();

            DB::statement("SET FOREIGN_KEY_CHECKS = 1");

            return redirect('peta-kurikulum')->with('resp_msg', 'Berhasil menghapus Peta Kurikulum.');
        } catch (Exception $err) {
            DB::rollBack();
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");

            return redirect('peta-kurikulum')->with('err_msg', 'Gagal menghapus Peta Kurikulum, error ' . $err->getMessage());
        }
    }

    // STANDALONE FUNCTION
    public function GenerateKodeMatkul($is_umum, $prodi, $kodeMatkul, $semester, $year)
    {
        $hash = md5(date('Y-m-d H:i:s'));
        $sixDigitID = strtoupper(substr($hash, 0, 6));

        $kodeProdi = '';
        if (!empty($is_umum)) {
            $kodeProdi = 'UMUM';
        } else {
            $prodi = str_replace(['S1 ', 'D3 ', 'S1', 'D3'], '', (string)$prodi);
            $kodeProdi = count(explode(' ', $prodi)) > 1 ? strtoupper(preg_replace('/\b(\w)\w*\s*/', '$1', $prodi)) : strtoupper(preg_replace('/[aeiou]/i', '', $prodi));
        }

        $kodeMatkul = preg_replace('/[^a-zA-Z0-9\s]/', '', $kodeMatkul);
        $kodeSemester = '';
        if (preg_match('/\b(genap|ganjil)\b/i', $semester, $match)) {
            $kodeSemester = strtoupper(preg_replace('/[aeiou]/i', '', $match[1]));
        }

        $year = str_replace('0', '', (string)$year);
        $kodeYear = strlen($year) > 3 ? substr($year, -3) : substr($year, -2);

        $generatedID = $kodeProdi . ' ' . $kodeMatkul . '-' . $kodeSemester . $kodeYear;
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

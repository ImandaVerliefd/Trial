<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\KelompokPraktik;
use App\Models\Mahasiswa;
use App\Models\PetaKurikulum;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelompokController extends Controller
{
    public function index()
    {
        $data['title'] = "Dosen Wali";
        $data['dosen'] = Dosen::whereRaw("IS_ACTIVE = 1")->get();
        $data['mhs'] = Mahasiswa::whereRaw("IS_ACTIVE = 1")->get();
        $data['matkul_praktik'] = DB::select("
            SELECT 
                mpk.* ,
                mm.LINPROD ,
                mm.ID_LINPROD ,
                mm.IS_LAPANGAN ,
                mm.IS_LINTAS_PRODI ,
                mm.IS_UMUM
            FROM
                mapping_peta_kurikulum mpk 
            LEFT JOIN md_matkul mm ON
                mm.KODE_MATKUL = mpk.KODE_MATKUL
            WHERE
                mm.IS_LAPANGAN = 1
        ");

        $data['content_page'] = 'layout/layout_admin/kelompokPraktik/index';
        $data['script'] = 'layout/layout_admin/kelompokPraktik/_html_script';
        return view('templates/main', $data);
    }

    public function getAllData(Request $req)
    {
        $orderData = $req->input('order')[0]['column'];
        $orderType = strtoupper($req->input('order')[0]['dir']);
        $valOrder = $req->input('columns')[$orderData]['data'];
        $search = $req->input('search')['value'];
        $start = $req->input('start');
        $perPage = $req->input('length');

        $resp = KelompokPraktik::get_data_datatable($orderType, $valOrder, $search, $start, $perPage);
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;
            $data = array(
                "NAMA_KELOMPOK" => $item->NAMA_KELOMPOK,
                "NAMA_MATKUL" => $item->NAMA_MATKUL,
                "DOSEN_LIST" => implode(', ', explode(';', $item->DOSEN_LIST)),
                "MAHASISWA_LIST" => implode(', ', explode(';', $item->MHS_LIST)),
            );


            $parseData = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
            $data['ACTION_BUTTON'] = '
                <button type="button" class="btn btn-warning" onclick="openModal(`' . $parseData . '`)">
                    <i class="ri-pencil-line"></i>
                </button>
                <button type="button" class="btn btn-danger" onclick="modalConfirmDelete(`' . $parseData . '`)">
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

    public function getMahasiswaPerwalian(Request $req)
    {
        $idDetsem = $req->input('id_detsem');
        return DB::select('
            SELECT
                tp.ID_DETSEM ,
                mm.*
            FROM
                tb_perwalian tp 
            LEFT JOIN md_mahasiswa mm ON
                tp.KODE_MAHASISWA = mm.KODE_MAHASISWA 
            WHERE 
                tp.ID_DETSEM = "' . $idDetsem . '"
        ');
    }

    public function submit(Request $req)
    {
        DB::statement("SET FOREIGN_KEY_CHECKS = 0");
        try {
            $nama_kelompok = $req->input("nama_kelompok");
            $id_kelompok_head = $req->input("id_kelompok_head") ?? $this->GenerateUniqChild('KLMPK', $nama_kelompok);
            $id_detsem = $req->input("id_detsem");
            $kode_dosen = $req->input("kode_dosen");
            $kode_mahasiswa = $req->input("kode_mahasiswa");

            $dataDosen = [];
            foreach ($kode_dosen as $kodeDosen) {
                $dosenData = Dosen::where("KODE_DOSEN", $kodeDosen)->first();
                $dataDosen[] = [
                    "ID_KELOMPOK_HEAD" => $id_kelompok_head,
                    "KODE_DOSEN" => $dosenData->KODE_DOSEN,
                    "NIP_DOSEN" => $dosenData->NIP_DOSEN,
                    "NAMA_DOSEN" => $dosenData->NAMA_DOSEN,
                    "CREATED_AT" => date('Y-m-d H:i:s')
                ];
            }

            $dataMhs = [];
            foreach ($kode_mahasiswa as $kodeMHS) {
                $mhsData = Mahasiswa::where("KODE_MAHASISWA", $kodeMHS)->first();
                $dataMhs[] = [
                    "ID_KELOMPOK_HEAD" => $id_kelompok_head,
                    "KODE_MAHASISWA" => $mhsData->KODE_MAHASISWA,
                    "NIM" => $mhsData->NIM,
                    "NAMA_MAHASISWA" => $mhsData->NAMA_MAHASISWA,
                    "CREATED_AT" => date('Y-m-d H:i:s')
                ];
            }

            $dataHead = [
                "ID_KELOMPOK_HEAD" => $id_kelompok_head,
                "NAMA_KELOMPOK" => $nama_kelompok,
                "ID_DETSEM" => $id_detsem,
                "CREATED_AT" => date('Y-m-d H:i:s')
            ];

            DB::beginTransaction();
            DB::table("tb_kelompok_dosen")->where(['ID_KELOMPOK_HEAD' => $id_kelompok_head])->delete();
            DB::table("tb_kelompok_mhs")->where(['ID_KELOMPOK_HEAD' => $id_kelompok_head])->delete();

            DB::table("tb_kelompok_dosen")->insert($dataDosen);
            DB::table("tb_kelompok_mhs")->insert($dataMhs);

            DB::table("tb_kelompok_head")->updateOrInsert(['ID_KELOMPOK_HEAD' => $id_kelompok_head], $dataHead);
            DB::commit();

            DB::statement("SET FOREIGN_KEY_CHECKS = 1");

            return redirect('kelompok-praktik')->with('resp_msg', 'Berhasil menyimpan kelompok praktik.');
        } catch (Exception $err) {
            DB::rollBack();
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            return redirect('kelompok-praktik')->with('err_msg', 'Gagal menyimpan kelompok praktik, error ' . $err->getMessage());
        }
    }

    public function delete(Request $req)
    {
        DB::statement("SET FOREIGN_KEY_CHECKS = 0");
        try {
            $dataReq = json_decode(base64_decode($req->input("id")), true);
            $id_kelompok_head = $dataReq['ID_KELOMPOK_HEAD'];

            DB::beginTransaction();
            DB::table("tb_kelompok_dosen")->where(['ID_KELOMPOK_HEAD' => $id_kelompok_head])->delete();
            DB::table("tb_kelompok_mhs")->where(['ID_KELOMPOK_HEAD' => $id_kelompok_head])->delete();
            DB::table("tb_kelompok_head")->where(['ID_KELOMPOK_HEAD' => $id_kelompok_head])->delete();
            DB::commit();

            DB::statement("SET FOREIGN_KEY_CHECKS = 1");

            return redirect('kelompok-praktik')->with('resp_msg', 'Berhasil menghapus kelompok.');
        } catch (Exception $err) {
            DB::rollBack();
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            return redirect('kelompok-praktik')->with('err_msg', 'Gagal menghapus kelompok, error ' . $err->getMessage());
        }
    }

    public function GenerateUniqChild($first, $val)
    {
        $input = $val;
        $hash = md5($input);
        $sixDigitID = strtoupper(substr($hash, 0, 6));
        $generatedID = $first . '_' . $sixDigitID;
        return $generatedID;
    }
}

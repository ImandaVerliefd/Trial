<?php

namespace App\Http\Controllers;

use App\Models\Capaian;
use App\Models\CapaianMatkul;
use App\Models\Kurikulum;
use App\Models\Matkul;
use App\Models\Prodi;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CapaianMatkulController extends Controller
{
    public function index()
    {
        $data['title'] = "CPMK";

        $data['prodi'] = Prodi::whereNull('DELETED_AT')->get();
        $data['capaian'] = Capaian::whereRaw("KODE_CAPAIAN_PARENT IS NOT NULL AND IS_ACTIVE = 1 AND IS_DELETE = 0 AND JENIS_CAPAIAN = 'CPMK'")->get();
        $data['kurikulum'] = Kurikulum::whereRaw("IS_ACTIVE = 1 AND IS_DELETE = 0")->get();
        $data['mataKuliah'] = DB::select("
            SELECT
                mm.* ,
                mp.PRODI ,
                mp.JENJANG
            FROM
                md_matkul mm
            LEFT JOIN md_prodi mp ON
                mm.ID_PRODI = mp.ID_PRODI
            WHERE
                mm.IS_ACTIVE = 1 AND mm.IS_DELETE = 0
        ");

        $data['script'] = 'layout/layout_admin/pemetaanCpmk/_html_script';
        $data['content_page'] = 'layout/layout_admin/pemetaanCpmk/index';
        return view('templates/main', $data);
    }

    public function getFilteredCPMK(Request $req)
    {
        $search = $req->input('q');
        if (!empty($search)) {
            $conditions[] = "
                (
                    mc.CAPAIAN LIKE '%$search%'
                )
            ";
        }
        $conditions[] = "mc.KODE_CAPAIAN_PARENT IS NOT NULL";
        $conditions[] = "mc.IS_ACTIVE = 1";
        $conditions[] = "mc.IS_DELETE = 0";
        $conditions[] = "mc.JENIS_CAPAIAN = 'CPMK'";
        if (!empty($req->input('kurikulum_id'))) {
            $conditions[] = "mc.ID_KURIKULUM = '" . $req->input('kurikulum_id') . "'";
        }

        $conditionWhere = "";
        if (!empty($conditions)) {
            $conditionWhere = "WHERE " . implode(" AND ", $conditions);
        }

        $sql = "
            SELECT 
                mc.* ,
                (CASE WHEN mp.JENJANG IS NOT NULL THEN mp.JENJANG ELSE 'Pendidikan Ners' END) AS JENJANG
            FROM 
                md_capaian mc
            LEFT JOIN md_prodi mp ON
                mp.ID_PRODI = mc.ID_PRODI
            $conditionWhere
        ";

        return DB::select($sql);
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

        $resp = CapaianMatkul::get_data_datatable($orderType, $valOrder, $search, $start, $perPage, $prodi);
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
                "KURIKULUM" => $item->KURIKULUM,
            );


            $parseData = htmlspecialchars(str_replace('\t', ' ', json_encode((array)$item)), ENT_QUOTES, 'UTF-8');
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

    public function submit(Request $req)
    {
        DB::statement("SET FOREIGN_KEY_CHECKS = 0");
        try {
            $kode_matkul = $req->input("kode_matkul");
            $id_kurikulum = $req->input("id_kurikulum");
            $id_capaian = $req->input("id_capaian");

            DB::table("mapping_capaian_matkul")->where(["KODE_MATKUL" => $kode_matkul, "ID_KURIKULUM" => $id_kurikulum])->delete();

            $data = [];
            foreach ($id_capaian as $IdCapaian) {
                $data[] = [
                    "ID_MAPPING" => $this->GenerateUniqChild("MAPPING", $kode_matkul . '' . $id_kurikulum . uniqid()),
                    "KODE_MATKUL" => $kode_matkul,
                    "ID_KURIKULUM" => $id_kurikulum,
                    "KODE_CAPAIAN" => $IdCapaian,
                    "LOG_TIME" => date('Y-m-d H:i:s')
                ];
            }

            DB::beginTransaction();
            DB::table("mapping_capaian_matkul")->insert($data);
            DB::commit();

            DB::statement("SET FOREIGN_KEY_CHECKS = 1");

            return redirect('capaian-matkul')->with('resp_msg', 'Berhasil menyimpan pemetaan CPMK.');
        } catch (Exception $err) {
            DB::rollBack();
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");

            return redirect('capaian-matkul')->with('err_msg', 'Gagal menyimpan pemetaan CPMK, error ' . $err->getMessage());
        }
    }

    public function delete(Request $req)
    {
        DB::statement("SET FOREIGN_KEY_CHECKS = 0");
        try {
            $dataReq = json_decode(base64_decode($req->input("id")), true);

            $data = [
                "IS_DELETE" => date('Y-m-d H:i:s')
            ];

            DB::beginTransaction();
            DB::table("mapping_capaian_matkul")->where(["KODE_MATKUL" => $dataReq['KODE_MATKUL'], "ID_KURIKULUM" => $dataReq['ID_KURIKULUM']])->update($data);
            DB::commit();

            DB::statement("SET FOREIGN_KEY_CHECKS = 1");

            return redirect('capaian-matkul')->with('resp_msg', 'Berhasil menghapus pemetaan CPMK.');
        } catch (Exception $err) {
            DB::rollBack();
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");

            return redirect('capaian-matkul')->with('err_msg', 'Gagal menghapus pemetaan CPMK, error ' . $err->getMessage());
        }
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

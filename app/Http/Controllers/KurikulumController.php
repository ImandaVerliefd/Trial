<?php

namespace App\Http\Controllers;

use App\Models\Kurikulum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class KurikulumController extends Controller
{
    public function index()
    {
        $data['title'] = "Kurikulum";
        $data['script'] = 'layout/layout_admin/kurikulum/_html_script';
        $data['content_page'] = 'layout/layout_admin/kurikulum/index';
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

        $resp = Kurikulum::get_data_datatable($orderType, $valOrder, $search, $start, $perPage);
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;
            $data = array(
                "ID_KURIKULUM" => $item->ID_KURIKULUM,
                "KURIKULUM" => $item->KURIKULUM,
                "TAHUN" => $item->TAHUN,
                "IS_ACTIVE" => (($item->IS_ACTIVE == 1) ? '<span class="badge bg-success-subtle text-success">Aktif</span>' : '<span class="badge bg-danger-subtle text-danger">Tidak Aktif</span>'),
            );

            if ($item->IS_ACTIVE == 0) {
                $extendBtn = '                
                    <button type="button" class="btn btn-secondary" onclick="modalConfirmActive(`' . $item->ID_KURIKULUM . '`, 1)">
                        <i class="ri-shut-down-line"></i>
                    </button>
                ';
            } else {
                $extendBtn = '                
                    <button type="button" class="btn btn-success" onclick="modalConfirmActive(`' . $item->ID_KURIKULUM . '`, 0)">
                        <i class="ri-shut-down-line"></i>
                    </button>
                ';
            }


            $parseData = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
            $data['ACTION_BUTTON'] = '
                <button type="button" class="btn btn-warning" onclick="openModal(`' . $parseData . '`)">
                    <i class="ri-pencil-line"></i>
                </button>
                <button type="button" class="btn btn-danger" onclick="modalConfirmDelete(`' . $item->ID_KURIKULUM . '`)">
                    <i class="ri-delete-bin-line"></i>
                </button>
                ' . $extendBtn . '
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
            $kurikulum = $req->input("kurikulum");
            $tahun = $req->input("tahun");
            $idKurikulum = $req->input("id_kurikulum") ?: null;

            $data = [
                "KURIKULUM" => $kurikulum,
                "TAHUN" => $tahun,
                "LOG_TIME" => date('Y-m-d H:i:s'),
                "IS_ACTIVE" => 1,
                "IS_DELETE" => 0
            ];

            DB::beginTransaction();
            DB::table("md_kurikulum")->updateOrInsert(["ID_KURIKULUM" => $idKurikulum], $data);
            DB::commit();

            return redirect('kurikulum')->with('resp_msg', 'Berhasil menyimpan kurikulum.');
        } catch (Exception $err) {
            DB::rollBack();

            return redirect('kurikulum')->with('err_msg', 'Gagal menyimpan kurikulum, error ' . $err->getMessage());
        }
    }

    public function changeActiveStatus(Request $req)
    {
        try {
            $active = $req->input('active');
            DB::table('md_kurikulum')->where(['ID_KURIKULUM' => $req->input('id')])->update(['IS_ACTIVE' => $active]);
            return redirect('kurikulum')->with('resp_msg', 'Berhasil ' . (($active == 0) ? 'menonaktifkan' : 'mengaktifkan') . ' kurikulum.');
        } catch (Exception $err) {
            return redirect('kurikulum')->with('err_msg', 'Gagal merubah status kurikulum, error ' . $err->getMessage());
        }
    }

    public function delete(Request $req)
    {
        try {
            DB::table('md_kurikulum')->where(['ID_KURIKULUM' => $req->input('id')])->delete();
            return redirect('kurikulum')->with('resp_msg', 'Berhasil menghapus kurikulum.');
        } catch (Exception $err) {
            return redirect('kurikulum')->with('err_msg', 'Gagal menghapus kurikulum, error ' . $err->getMessage());
        }
    }

    public function kurikulumSearch(Request $req)
    {
        $kode_matkul = $req->input('kode_matkul');
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $rawData = DB::select("
            SELECT 
                mm.NAMA_MATKUL ,
                GROUP_CONCAT(tcd.ID_CAPAIAN_DETAIL) AS ID_CAPAIAN_DETAIL ,
                MAX(mk.ID_KURIKULUM) AS ID_KURIKULUM ,
                MAX(mk.KURIKULUM) AS KURIKULUM
            FROM 
                md_matkul mm 
            LEFT JOIN tb_capaian_detail tcd ON
                tcd.KODE_MATKUL = mm.KODE_MATKUL 
            LEFT JOIN md_kurikulum mk ON
                mk.ID_KURIKULUM = tcd.ID_KURIKULUM
            WHERE 
                mm.KODE_MATKUL = '$kode_matkul'
            GROUP BY 
                mm.KODE_MATKUL ,
                tcd.ID_KURIKULUM
            HAVING 
                ID_CAPAIAN_DETAIL IS NOT NULL
        ");
        return response($rawData, 200);
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

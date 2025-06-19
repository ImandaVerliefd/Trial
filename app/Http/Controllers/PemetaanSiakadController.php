<?php

namespace App\Http\Controllers;

use App\Models\PemetaanSiakad;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PemetaanSiakadController extends Controller
{
    // CAPAIAN FUNCTION
    public function PetaSiakadIndex()
    {
        $data['title'] = "Pemetaan Siakad";
        $data['script'] = 'layout/layout_admin/pemetaanSiakad/_html_script';
        $data['content_page'] = 'layout/layout_admin/pemetaanSiakad/siakad_index';
        $data['feeder'] = PemetaanSiakad::get_siakad_feeder();
        return view('templates/main', $data);
    }
    
    public function getAllPetaSiakadData(Request $req)
    {
        $orderData = $req->input('order')[0]['column'];
        $orderType = strtoupper($req->input('order')[0]['dir']);
        $valOrder = $req->input('columns')[$orderData]['data'];
        $search = $req->input('search')['value'];
        $start = $req->input('start');
        $perPage = $req->input('length');

        $resp = PemetaanSiakad::get_data_datatable($orderType, $valOrder, $search, $start, $perPage);
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;
            $data = array(
                "SIAKAD" => $item->SIAKAD ,
                "FEEDER" => $item->FEEDER
            );

            $parseData = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
            $data['ACTION_BUTTON'] = '
                <button type="button" class="btn btn-warning" onclick="openModal(`' . $parseData . '`)">
                    <i class="ri-pencil-line"></i>
                </button>
                <button type="button" class="btn btn-danger" onclick="modalConfirmDelete(`' . $item->ID_SIAKAD . '`)">
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

    public function PetaSiakadSubmit(Request $req)
    {
        try {
            $data = [
                "ID_FEEDER" => $req->input("id_feeder"),
                "SIAKAD" => $req->input("siakad"),
            ];

            DB::beginTransaction();
            DB::table("md_siakad")->updateOrInsert(["ID_SIAKAD" => $req->input('id_siakad')], $data);
            DB::commit();

            return redirect('pemetaan-siakad')->with('resp_msg', 'Berhasil menyimpan pemetaan siakad');
        } catch (Exception $err) {
            DB::rollBack();

            return redirect('pemetaan-siakad')->with('err_msg', 'Gagal menyimpan pemetaan siakad, error ' . $err->getMessage());
        }
    }
    
    public function deletePetaSiakad(Request $req)
    {
        try {
            DB::table('md_siakad')->where(['ID_SIAKAD' => $req->input('kode')])->update(['IS_DELETE' => date('Y-m-d H:i:s')]);
            
            return redirect('pemetaan-siakad')->with('resp_msg', 'Berhasil menghapus pemetaan siakad.');
        } catch (Exception $err) {
            return redirect('pemetaan-siakad')->with('err_msg', 'Gagal menghapus pemetaan siakad, error ' . $err->getMessage());
        }
    }
}

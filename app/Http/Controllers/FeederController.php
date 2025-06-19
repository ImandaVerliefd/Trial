<?php

namespace App\Http\Controllers;

use App\Models\PemetaanFeeder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeederController extends Controller
{
    // CAPAIAN FUNCTION
    public function feederIndex()
    {
        $data['title'] = "Feeder";
        $data['script'] = 'layout/layout_admin/feeder/_html_script';
        $data['content_page'] = 'layout/layout_admin/feeder/feeder_index';
        return view('templates/main', $data);
    }
    
    public function getAllFeederData(Request $req)
    {
        $orderData = $req->input('order')[0]['column'];
        $orderType = strtoupper($req->input('order')[0]['dir']);
        $valOrder = $req->input('columns')[$orderData]['data'];
        $search = $req->input('search')['value'];
        $start = $req->input('start');
        $perPage = $req->input('length');

        $resp = PemetaanFeeder::get_data_datatable($orderType, $valOrder, $search, $start, $perPage);
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;
            $data = array(
                "FEEDER" => $item->FEEDER
            );

            $parseData = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
            $data['ACTION_BUTTON'] = '
                <button type="button" class="btn btn-warning" onclick="openModal(`' . $parseData . '`)">
                    <i class="ri-pencil-line"></i>
                </button>
                <button type="button" class="btn btn-danger" onclick="modalConfirmDelete(`' . $item->ID_FEEDER . '`)">
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

    public function feederSubmit(Request $req)
    {
        try {
            $data = [
                "FEEDER" => $req->input("feeder"),
            ];

            DB::beginTransaction();
            DB::table("md_feeder")->updateOrInsert(["ID_FEEDER" => $req->input('id_feeder')], $data);
            DB::commit();

            return redirect('feeder')->with('resp_msg', 'Berhasil menyimpan feeder');
        } catch (Exception $err) {
            DB::rollBack();

            return redirect('feeder')->with('err_msg', 'Gagal menyimpan feeder, error ' . $err->getMessage());
        }
    }
    
    public function deleteFeeder(Request $req)
    {
        try {
            DB::table('md_feeder')->where(['ID_FEEDER' => $req->input('kode')])->update(['IS_DELETE' => date('Y-m-d H:i:s')]);
            
            return redirect('feeder')->with('resp_msg', 'Berhasil menghapus feeder.');
        } catch (Exception $err) {
            return redirect('feeder')->with('err_msg', 'Gagal menghapus feeder, error ' . $err->getMessage());
        }
    }
}

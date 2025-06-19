<?php

namespace App\Http\Controllers;

use App\Models\Prodi;
use App\Models\UMSApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ProdiController extends Controller
{
    public function index()
    {
        $data['title'] = "Prodi";
        $data['script'] = 'layout/layout_admin/prodi/_html_script';
        $data['content_page'] = 'layout/layout_admin/prodi/index';
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

        $resp = Prodi::get_data_datatable($orderType, $valOrder, $search, $start, $perPage);
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;
            $data = array(
                "ID_PRODI" => $item->ID_PRODI ,
                "PRODI" => $item->JENJANG . ' ' . $item->PRODI ,
            );

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
    
    public function syncronize()
    {
        set_time_limit(3600);
        
        try {
            $prodi = UMSApi::MasterDataProdi();

            if (empty($prodi)) {
                return response()->json([
                    'message' => 'No data to synchronize.'
                ], 204);
            }
            
            DB::beginTransaction();
            foreach ($prodi as $item) {
                $dataProdi = [
                    "ID_PRODI" => $item['ID_PRODI'],
                    "PRODI" => $item['PRODI'],
                    "JENJANG" => $item['JENJANG'],
                    "CREATED_AT" => $item['CREATED_AT'],
                    "DELETED_AT" => $item['DELETED_AT']
                ];
                DB::table("md_prodi")->updateOrInsert(
                    ["ID_PRODI" => $item['ID_PRODI']],
                    $dataProdi
                );
            }
            DB::commit();

            return response()->json([
                'message' => 'Synchronization successful!'
            ], 200);
        } catch (\Exception $err) {
            DB::rollBack();

            return response()->json([
                'message' => 'Synchronization failed!',
                'error' => $err->getMessage()
            ], 500);
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

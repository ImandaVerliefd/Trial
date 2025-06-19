<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\UMSApi;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DosenController extends Controller
{
    public function index()
    {
        $data['title'] = "Dosen";
        $data['content_page'] = 'layout/layout_admin/dosen/dosen_index';
        $data['script'] = 'layout/layout_admin/dosen/_html_script';
        return view('templates/main', $data);
    }

    public function syncronize()
    {
        try {
            $dosen = UMSApi::MasterDataDosen();

            if (empty($dosen)) {
                return response()->json([
                    'message' => 'No data to synchronize.'
                ], 204);
            }

            DB::beginTransaction();
            foreach ($dosen as $item) {
                $dataDosen = [
                    "KODE_DOSEN" => $item['ID_DOSEN'],
                    "ID_USER" => $item['ID_USER'],
                    "EMAIL_DOSEN" => $item['EMAIL'],
                    "NIP_DOSEN" => $item['NIP'],
                    "NAMA_DOSEN" => $item['NAMA'],
                    "JENIS_KELAMIN" => $item['JENIS_KELAMIN'],
                    "JABATAN" => $item['JABATAN'],
                    "LOG_TIME" => date('Y-m-d H:i:s'),
                    "IS_ACTIVE" => $item['IS_ACTIVE'],
                ];
                DB::table("md_dosen")->updateOrInsert(
                    ["KODE_DOSEN" => $item['ID_DOSEN']],
                    $dataDosen
                );
            }
            DB::commit();

            return response()->json([
                'message' => 'Synchronization successful!'
            ], 200);
        } catch (\Exception $err) {
            DB::rollBack();

            \Log::error('Synchronization failed: ' . $err->getMessage());
            
            return response()->json([
                'message' => 'Synchronization failed!',
                'error' => $err->getMessage()
            ], 500);
        }
    }

    public function getAllDosenData(Request $req)
    {
        $orderData = $req->input('order')[0]['column'];
        $orderType = strtoupper($req->input('order')[0]['dir']);
        $valOrder = $req->input('columns')[$orderData]['data'];
        $search = $req->input('search')['value'];
        $start = $req->input('start');
        $perPage = $req->input('length');

        $search_per_colomn = [];

        foreach ($req->input('columns') as $key => $value) {
            if (!empty($value['search']['value'])) {
                $search_per_colomn[$value['data']] = $value['search']['value'];
            }
        }

        $resp = Dosen::get_data_datatable($orderType, $valOrder, $search, $start, $perPage, $search_per_colomn);
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;
            $data = array(
                "NAMA_DOSEN" => $item->NAMA_DOSEN,
                "EMAIL_DOSEN" => $item->EMAIL_DOSEN,
                "JABATAN" => $item->JABATAN,
                "JENIS_KELAMIN" => $item->JENIS_KELAMIN,
                "IS_ACTIVE" => (($item->IS_ACTIVE == 1) ? '<span class="badge bg-success-subtle text-success">Aktif</span>' : '<span class="badge bg-danger-subtle text-danger">Tidak Aktif</span>'),
            );

            // $parseData = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
            // $data['ACTION_BUTTON'] = '
            //     <button type="button" class="btn btn-warning" onclick="openModal(`' . $parseData . '`)">
            //         <i class="ri-pencil-line"></i>
            //     </button>
            //     <button type="button" class="btn btn-danger" onclick="modalConfirmDelete(`' . $item->ID_FEEDER . '`)">
            //         <i class="ri-delete-bin-line"></i>
            //     </button>
            // ';

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
}

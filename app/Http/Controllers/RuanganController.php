<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class RuanganController extends Controller
{
    public function index()
    {
        $data['title'] = "Ruangan";
        $data['script'] = 'layout/layout_admin/ruangan/_html_script';
        $data['content_page'] = 'layout/layout_admin/ruangan/index';
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

        $resp = Ruangan::get_data_datatable($orderType, $valOrder, $search, $start, $perPage);
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;
            $data = array(
                "ID_RUANGAN" => $item->ID_RUANGAN,
                "NAMA_RUANGAN" => $item->NAMA_RUANGAN,
                "TIPE" => $item->TIPE,
                "IS_ACTIVE" => (($item->IS_ACTIVE == 1) ? '<span class="badge bg-success-subtle text-success">Aktif</span>' : '<span class="badge bg-danger-subtle text-danger">Tidak Aktif</span>'),
            );

            if ($item->IS_ACTIVE == 0) {
                $extendBtn = '                
                    <button type="button" class="btn btn-secondary" onclick="modalConfirmActive(`' . $item->ID_RUANGAN . '`, 1)">
                        <i class="ri-shut-down-line"></i>
                    </button>
                ';
            } else {
                $extendBtn = '                
                    <button type="button" class="btn btn-success" onclick="modalConfirmActive(`' . $item->ID_RUANGAN . '`, 0)">
                        <i class="ri-shut-down-line"></i>
                    </button>
                ';
            }


            $parseData = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
            $data['ACTION_BUTTON'] = '
                <button type="button" class="btn btn-warning" onclick="openModal(`' . $parseData . '`)">
                    <i class="ri-pencil-line"></i>
                </button>
                <button type="button" class="btn btn-danger" onclick="modalConfirmDelete(`' . $item->ID_RUANGAN . '`)">
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
            $namaRuangan = $req->input("nama_ruangan");
            $tipe = $req->input("tipe");
            $idRuangan = (!empty($req->input("id_ruangan")) ? $req->input("id_ruangan") : $this->GenerateUniqChild($namaRuangan, $tipe . '' . date('Y-m-d H:i:s')));

            $data = [
                "ID_RUANGAN" => $idRuangan,
                "NAMA_RUANGAN" => $namaRuangan,
                "TIPE" => $tipe,
                "LOG_TIME" => date('Y-m-d H:i:s'),
                "IS_ACTIVE" => 1,
                "IS_DELETE" => 0
            ];

            DB::beginTransaction();
            DB::table("md_ruangan")->updateOrInsert(["ID_RUANGAN" => $idRuangan], $data);
            DB::commit();

            return redirect('ruangan')->with('resp_msg', 'Berhasil menyimpan ruangan.');
        } catch (Exception $err) {
            DB::rollBack();

            return redirect('ruangan')->with('err_msg', 'Gagal menyimpan ruangan, error ' . $err->getMessage());
        }
    }

    public function changeActiveStatus(Request $req)
    {
        try {
            $active = $req->input('active');
            DB::table('md_ruangan')->where(['ID_RUANGAN' => $req->input('id')])->update(['IS_ACTIVE' => $active]);
            return redirect('ruangan')->with('resp_msg', 'Berhasil ' . (($active == 0) ? 'menonaktifkan' : 'mengaktifkan') . ' ruangan.');
        } catch (Exception $err) {
            return redirect('ruangan')->with('err_msg', 'Gagal merubah status ruangan, error ' . $err->getMessage());
        }
    }

    public function delete(Request $req)
    {
        try {
            DB::table('md_ruangan')->where(['ID_RUANGAN' => $req->input('id')])->delete();
            return redirect('ruangan')->with('resp_msg', 'Berhasil menghapus ruangan.');
        } catch (Exception $err) {
            return redirect('ruangan')->with('err_msg', 'Gagal menghapus ruangan, error ' . $err->getMessage());
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

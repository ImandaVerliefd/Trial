<?php

namespace App\Http\Controllers;

use App\Models\DosenWali;
use Illuminate\Http\Request;
use App\Models\Dosen;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Mahasiswa;

class DosenWaliController extends Controller
{
    public function index()
    {
        $data['title'] = "Dosen Wali";
        $data['dosen'] = Dosen::whereRaw("IS_ACTIVE = 1")->get();
        $Kode_Mhs_taken = DosenWali::where("IS_ACTIVE", 1)->pluck('KODE_MAHASISWA');
        $data['mhs_tdk_lengkap'] = Mahasiswa::where("IS_ACTIVE", 1)
                        ->whereNotIn('KODE_MAHASISWA', $Kode_Mhs_taken)
                        ->get();
        $data['mhs'] = Mahasiswa::where("IS_ACTIVE", 1)->get();

        $data['content_page'] = 'layout/layout_admin/dosenWali/index';
        $data['script'] = 'layout/layout_admin/dosenWali/_html_script';
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

        $resp = DosenWali::get_data_datatable($orderType, $valOrder, $search, $start, $perPage);
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;
            $data = array(
                "ID_DOSEN_WALI" => $item->ID_DOSEN_WALI,
                "NAMA_DOSEN" => $item->NAMA_DOSEN,
                "KODE_DOSEN" => $item->KODE_DOSEN,
                "MAHASISWA_LIST" => $item->MAHASISWA_LIST,
                "KODE_MAHASISWA" => $item->KODE_MAHASISWA,
                "LOG_TIME" => $item->LOG_TIME,
            );


            $parseData = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
            $data['ACTION_BUTTON'] = '
                <button type="button" class="btn btn-warning" onclick="openUpdateModal(`' . $parseData . '`)">
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
            $kode_dosen = $req->input("kode_dosen");
            $nama_dosen = $req->input("nama_dosen");
            $kode_mahasiswas = $req->input("kode_mahasiswa");

            $data = [];
            foreach($kode_mahasiswas as $kode_mahasiswa) {
                $nama_mhs = Mahasiswa::where("KODE_MAHASISWA", $kode_mahasiswa)->get('NAMA_MAHASISWA')->first()->NAMA_MAHASISWA;
                $data[] = [
                    "KODE_DOSEN" => $kode_dosen,
                    "KODE_MAHASISWA" => $kode_mahasiswa,
                    "NAMA_MAHASISWA" => $nama_mhs,
                    "NAMA_DOSEN" => $nama_dosen,
                    "IS_ACTIVE" => 1,
                    "LOG_TIME" => date('Y-m-d H:i:s')
                ];
            }

            DB::beginTransaction();
            DB::table("tb_dosen_wali")->insert($data);
            DB::commit();
            
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            
            return redirect('dosen-wali')->with('resp_msg', 'Berhasil menyimpan pemetaan Dosen Wali.');
        } catch (Exception $err) {
            DB::rollBack();
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            return redirect('dosen-wali')->with('err_msg', 'Gagal menyimpan pemetaan Dosen Wali, error ' . $err->getMessage());
        }
    }

    public function update(Request $req)
    {
        $up_kode_mhs = $req->input("up_kode_mhs", []); // Default to an empty array if not provided
        $up_id_wali_dosen = $req->input("up_id_wali_dosen", []); // Default to an empty array if not provided
        $delete_id_wali_dosen = $req->input("delete_id_wali_dosen", []); // Default to an empty array if not provided

        $total_delete = is_array($delete_id_wali_dosen) ? count($delete_id_wali_dosen) : 0; // Safely count if it's an array
        $total = is_array($up_id_wali_dosen) ? count($up_id_wali_dosen) : 0; // Safely count if it's an array

        DB::statement("SET FOREIGN_KEY_CHECKS = 0");
        try {
            DB::beginTransaction();
            for ($i = 0; $i < $total_delete; $i++) {
                $data = [
                    "IS_ACTIVE" => 0
                ];
                DB::table("tb_dosen_wali")->where(["ID_DOSEN_WALI" => $delete_id_wali_dosen[$i]])->update($data);
            }
            for ($i = 0; $i < $total; $i++) {
                $data = [
                    "KODE_MAHASISWA" => $up_kode_mhs[$i],
                    "NAMA_MAHASISWA" => Mahasiswa::where("KODE_MAHASISWA", $up_kode_mhs[$i])->get('NAMA_MAHASISWA')->first()->NAMA_MAHASISWA,
                    "LOG_TIME" => date('Y-m-d H:i:s')
                ];
                DB::table("tb_dosen_wali")->where(["ID_DOSEN_WALI" => $up_id_wali_dosen[$i]])->update($data);
            }
            DB::commit();
            
        DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            
            return redirect('dosen-wali')->with('resp_msg', 'Berhasil menyimpan pemetaan Dosen Wali.');
        } catch (Exception $err) {
            DB::rollBack();

            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            return redirect('dosen-wali')->with('err_msg', 'Gagal menyimpan pemetaan Dosen Wali, error ' . $err->getMessage());
        }
    }

    public function delete(Request $req)
    {
        DB::statement("SET FOREIGN_KEY_CHECKS = 0");
        try {
            $dataReq = json_decode(base64_decode($req->input("id")), true);

            $data = [
                "IS_ACTIVE" => 0
            ];
            DB::beginTransaction();
            DB::table("tb_dosen_wali")->where(["KODE_DOSEN" => $dataReq['KODE_DOSEN']])->update($data);
            DB::commit();
            
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            
            return redirect('dosen-wali')->with('resp_msg', 'Berhasil menghapus pemetaan Dosen Wali.');
        } catch (Exception $err) {
            DB::rollBack();
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            return redirect('dosen-wali')->with('err_msg', 'Gagal menghapus pemetaan Dosen Wali, error ' . $err->getMessage());
        }
    }
}

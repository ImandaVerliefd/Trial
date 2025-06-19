<?php

namespace App\Http\Controllers;

use App\Models\Kurikulum;
use App\Models\Semester;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SemesterController extends Controller
{
    public function index()
    {
        $data['title'] = "Semester";
        $data['kurikulum'] = Kurikulum::where(['IS_DELETE' => 0])->get();
        $data['script'] = 'layout/layout_admin/semester/_html_script';
        $data['content_page'] = 'layout/layout_admin/semester/index';
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

        $resp = Semester::get_data_datatable($orderType, $valOrder, $search, $start, $perPage);
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;
            $data = array(
                "SEMESTER" => $item->SEMESTER,
                "TAHUN" => $item->TAHUN,
                "KURIKULUM" => $item->KURIKULUM,
            );


            $parseData = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
            $data['ACTION_BUTTON'] = '
                <button type="button" class="btn btn-warning" onclick="openModal(`' . $parseData . '`)">
                    <i class="ri-pencil-line"></i>
                </button>
                <button type="button" class="btn btn-danger" onclick="modalConfirmDelete(`' . $item->ID_SEMESTER . '`)">
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
            $id_semester = $req->input("id_semester");
            $semester = $req->input("semester");
            $sem_year_start = $req->input("sem_year_start");
            $sem_year_end = $req->input("sem_year_end");
            $id_kurikulum = $req->input("id_kurikulum");
            $tahun_kurikulum = $req->input("tahun_kurikulum");
            $start_date = $req->input("start_date");
            $end_Date = $req->input("end_date");
            $start_perwalian = $req->input("start_perwalian");
            $status_perwalian = $req->input("status_perwalian");

            if (empty($id_semester)) {
                $dataExist = DB::selectOne("
                    SELECT
                        ms.* ,
                        mk.KURIKULUM
                    FROM
                        md_semester ms
                    LEFT JOIN md_kurikulum mk ON
                        mk.ID_KURIKULUM = mk.ID_KURIKULUM
                    WHERE
                        ms.SEMESTER = '$semester'
                        AND
                        ms.TAHUN = '$tahun_kurikulum'
                        AND
                        ms.ID_KURIKULUM = '$id_kurikulum'
                ");

                if (!empty($dataExist)) {
                    DB::statement("SET FOREIGN_KEY_CHECKS = 1");
                    return redirect('semester')->with('err_msg', 'Gagal menyimpan semester, data semester "' . $semester . ' ' . $dataExist->KURIKULUM . '" sudah ada');
                }
            }

            $data = [
                "SEMESTER" => $semester . ' ' . $sem_year_start . '/' . $sem_year_end,
                "TAHUN" => $tahun_kurikulum,
                "ID_KURIKULUM" => $id_kurikulum,
                "START_SEMESTER" => $start_date,
                "END_SEMESTER" => $end_Date,
                "START_PERWALIAN" => $start_perwalian,
                "STATUS_PERWALIAN" => $status_perwalian
            ];

            DB::beginTransaction();
            DB::table("md_semester")->updateOrInsert(["ID_SEMESTER" => $id_semester], $data);
            DB::commit();

            DB::statement("SET FOREIGN_KEY_CHECKS = 1");

            return redirect('semester')->with('resp_msg', 'Berhasil menyimpan semester.');
        } catch (Exception $err) {
            DB::rollBack();
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");

            return redirect('semester')->with('err_msg', 'Gagal menyimpan semester, error ' . $err->getMessage());
        }
    }

    public function delete(Request $req)
    {
        DB::statement("SET FOREIGN_KEY_CHECKS = 0");
        try {
            $id_semester = $req->input("id");

            $data = [
                "IS_DELETE" => date('Y-m-d H:i:s')
            ];

            DB::beginTransaction();
            DB::table("md_semester")->updateOrInsert(["ID_SEMESTER" => $id_semester], $data);
            DB::commit();

            DB::statement("SET FOREIGN_KEY_CHECKS = 1");

            return redirect('semester')->with('resp_msg', 'Berhasil menghapus semester.');
        } catch (Exception $err) {
            DB::rollBack();
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");

            return redirect('semester')->with('err_msg', 'Gagal menghapus semester, error ' . $err->getMessage());
        }
    }
}

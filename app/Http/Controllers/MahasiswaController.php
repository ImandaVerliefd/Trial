<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\Semester;
use App\Models\UMSApi;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MahasiswaController extends Controller
{
    public function index()
    {
        $data['title'] = "Mahasiswa";
        $data['prodi'] = Prodi::whereNull('DELETED_AT')->get();
        $data['content_page'] = 'layout/layout_admin/mahasiswa/mahasiswa_index';
        $data['script'] = 'layout/layout_admin/mahasiswa/_html_script';
        return view('templates/main', $data);
    }

    public function syncronize()
    {
        try {
            set_time_limit(3600);
            $semester_model = Semester::whereRaw('CURDATE() BETWEEN START_SEMESTER AND END_SEMESTER')->first();
            if (!$semester_model) {
                return response()->json([
                    'message' => 'Synchronization failed!',
                    'error' => 'No active semester found in the database. Please set an active semester period.'
                ], 404);
            }
            $semester_aktif = $semester_model->toArray();

            $allMahasiswa = UMSApi::MasterDataMahasiswa(); // Get all students first

            if (empty($allMahasiswa)) {
                return response()->json(['message' => 'No data to synchronize.'], 204);
            }

            // Process the students in chunks of 200
            $chunks = array_chunk($allMahasiswa, 200);

            foreach ($chunks as $mahasiswaChunk) {
                DB::beginTransaction(); // Start a new transaction for each chunk
                foreach ($mahasiswaChunk as $item) {
                    $semActive = (($semester_aktif['TAHUN'] - $item['TAHUN_MASUK']) * 2);
                    $status = (((int)date('Y') - $item['TAHUN_MASUK']) >= 8) ? 0 : 1;
                    $dataMahasiswa = [
                        "KODE_MAHASISWA" => $item['ID_MAHASISWA'],
                        "ID_USER" => $item['ID_USER'],
                        "NIM" => $item['NIM'],
                        "NAMA_MAHASISWA" => $item['NAMA'],
                        "EMAIL_MAHASISWA" => $item['EMAIL'],
                        "JENIS_KELAMIN" => $item['JENIS_KELAMIN'],
                        "ID_PRODI" => $item['ID_PRODI'],
                        "PRODI" => $item['PRODI'],
                        "TAHUN_MASUK" => $item['TAHUN_MASUK'],
                        "LOG_TIME" => date('Y-m-d H:i:s'),
                        "IS_ACTIVE" => $status,
                        "SEMESTER_ACTIVE" => $semActive,
                    ];
                    DB::table("md_mahasiswa")->updateOrInsert(
                        ["KODE_MAHASISWA" => $item['ID_MAHASISWA']],
                        $dataMahasiswa
                    );
                }
                DB::commit(); // Commit the transaction for the current chunk
            }

            return response()->json(['message' => 'Synchronization successful!'], 200);

        } catch (\Exception $err) {
            DB::rollBack(); // Roll back the current transaction if an error occurs
            return response()->json([
                'message' => 'Synchronization failed!',
                'error' => $err->getMessage()
            ], 500);
        }
    }

    public function getAllMahasiswaData(Request $req)
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

        $resp = Mahasiswa::get_data_datatable($orderType, $valOrder, $search, $start, $perPage, $search_per_colomn);
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;
            $data = array(
                "NAMA_MAHASISWA" => $item->NAMA_MAHASISWA,
                "NIM" => $item->NIM,
                "PRODI" => $item->PRODI,
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

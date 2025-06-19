<?php

namespace App\Http\Controllers;

use App\Models\KRS;
use Exception;
use Illuminate\Http\Request;
use App\Models\DosenWali;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\DB;

class MahasiswaWaliController extends Controller
{
    public function index()
    {
        $data['title'] = "Mahasiswa Wali";
        $data['data_wali'] = DosenWali::join_data_dosen();

        $data['content_page'] = 'layout/layout_dosen/MahasiswaWali/index';
        $data['script'] = 'layout/layout_dosen/MahasiswaWali/_html_script';
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
        $search_per_colomn = [];

        foreach ($req->input('columns') as $key => $value) {
            if (!empty($value['search']['value'])) {
                $search_per_colomn[$value['data']] = $value['search']['value'];
            }
        }

        $resp = DosenWali::get_data_datatable_for_dosen($orderType, $valOrder, $search, $start, $perPage, $search_per_colomn);
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;
            $data = array(
                "NIM" => $item->NIM ,
                "NAMA_MAHASISWA" => $item->NAMA_MAHASISWA,
                "IS_ACTIVE" => $item->IS_ACTIVE == 1 ? "Aktif" : "Tidak Aktif",
                "TAHUN_MASUK" => $item->TAHUN_MASUK,
                "EMAIL_MAHASISWA" => $item->EMAIL_MAHASISWA
            );

            $parseData = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
            $data['ACTION_BUTTON'] = '
                <div class="dropdown">
                    <button class="btn btn-white dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-more-2-line"></i>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li>
                            <a class="dropdown-item" href="#lihat-ringkasan">Lihat Ringkasan</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#lihat-pengambilan">Lihat Pengambilan</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="' . url('mahasiswa-wali/validasi-krs') . '?kd-mhs=' . $item->KODE_MAHASISWA . '">Validasi KRS</a>
                        </li>
                    </ul>
                </div>
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

    public function detailValidasi(Request $req)
    {
        $kode_mahasiswa = $req->query('kd-mhs');
        $data['data_mahasiswa'] = Mahasiswa::where(["KODE_MAHASISWA" => $kode_mahasiswa])->first()->toArray();
        $data['data_validasi'] = KRS::get_data_validasi($kode_mahasiswa);
        
        if (empty($data['data_validasi']) || $data['data_validasi'][0]->STATUS_VERIF != "0") {
            return redirect ("mahasiswa-wali")->with('resp_msg', 'Tidak ada validasi yang dilakukan pada mahasiswa ' . $data['data_mahasiswa']['NAMA_MAHASISWA']);
        }
        
        $data['title'] = "Validasi KRS - " . $data['data_mahasiswa']['NAMA_MAHASISWA'];
        $data['content_page'] = 'layout/layout_dosen/MahasiswaValidasi/index';
        $data['script'] = 'layout/layout_dosen/MahasiswaValidasi/_html_script';
        return view('templates/main', $data);
    }

    public function validasiAccept(Request $req)
    {
        $kode_mahasiswa = $req->input("KODE_MAHASISWA");
        $data_mahasiswa = Mahasiswa::where(["KODE_MAHASISWA" => $kode_mahasiswa])->first()->toArray();
        $redirectUrl = 'mahasiswa-wali/validasi-krs?kd-mhs=' . $kode_mahasiswa;
        
        try {
            $data = [
                "STATUS_VERIF"  => 1,
                "TANGGAL_VERIF" => date('Y-m-d H:i:s')
            ];
            
            DB::beginTransaction();
            DB::table('tb_perwalian')->where(["KODE_MAHASISWA" => $kode_mahasiswa, "STATUS_VERIF" => "0"])->update($data);
            DB::commit();

            return redirect("mahasiswa-wali")->with('resp_msg', 'Berhasil memvalidasi KRS milik ' . $data_mahasiswa['NAMA_MAHASISWA']);
        } catch (Exception $err) {
            DB::rollBack();
            return redirect($redirectUrl)->with('err_msg', 'Gagal melakukan validasi, error ' . $err->getMessage());
        }
    }

    public function validasiDecline(Request $req)
    {
        $kode_mahasiswa = $req->input("KODE_MAHASISWA");
        $data_mahasiswa = Mahasiswa::where(["KODE_MAHASISWA" => $kode_mahasiswa])->first()->toArray();
        $redirectUrl = 'mahasiswa-wali/validasi-krs?kd-mhs=' . $kode_mahasiswa;

        try {
            DB::beginTransaction();
            DB::table('tb_perwalian')->where(["KODE_MAHASISWA" => $kode_mahasiswa, "STATUS_VERIF" => "0"])->update(["STATUS_VERIF" => 3]);
            DB::commit();

            return redirect($redirectUrl)->with('resp_msg', 'Telah menolak KRS milik ' . $data_mahasiswa['NAMA_MAHASISWA']);
        } catch (Exception $err) {
            DB::rollBack();
            return redirect($redirectUrl)->with('err_msg', 'Gagal melakukan validasi, error ' . $err->getMessage());
        }
    }
}

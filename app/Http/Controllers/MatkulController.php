<?php

namespace App\Http\Controllers;

use App\Models\Kurikulum;
use App\Models\Prodi;
use App\Models\Matkul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class MatkulController extends Controller
{
    public function index()
    {
        $data['title'] = "Mata Kuliah";
        $data['kurikulum'] = Kurikulum::where(['IS_DELETE' => 0])->get();
        $data['prodi'] = Prodi::whereNull('DELETED_AT')->get();
        $data['script'] = 'layout/layout_admin/matkul/_html_script';
        $data['content_page'] = 'layout/layout_admin/matkul/index';
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

        $resp = Matkul::get_data_datatable($orderType, $valOrder, $search, $start, $perPage, $search_per_colomn);
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;
            $data = array(
                "KODE_MATKUL" => $item->KODE_MATKUL,
                "NAMA_MATKUL" => $item->NAMA_MATKUL,
                "SKS" => $item->SKS,
                "JUMLAH_PERTEMUAN" => $item->JUMLAH_PERTEMUAN,
                "ID_PRODI" => $item->ID_PRODI,
                "PRODI" => $item->JENJANG . " " . $item->PRODI,
                "IS_ACTIVE" => (($item->IS_ACTIVE == 1) ? '<span class="badge bg-success-subtle text-success">Aktif</span>' : '<span class="badge bg-danger-subtle text-danger">Tidak Aktif</span>'),
            );

            if ($item->IS_ACTIVE == 0) {
                $extendBtn = '                
                    <button type="button" class="btn btn-secondary" onclick="modalConfirmActive(`' . $item->KODE_MATKUL . '`, 1)">
                        <i class="ri-shut-down-line"></i>
                    </button>
                ';
            } else {
                $extendBtn = '                
                    <button type="button" class="btn btn-success" onclick="modalConfirmActive(`' . $item->KODE_MATKUL . '`, 0)">
                        <i class="ri-shut-down-line"></i>
                    </button>
                ';
            }


            $parseData = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
            $data['ACTION_BUTTON'] = '
                <button type="button" class="btn btn-warning" onclick="openModal(`' . $parseData . '`)">
                    <i class="ri-pencil-line"></i>
                </button>
                <button type="button" class="btn btn-danger" onclick="modalConfirmDelete(`' . $item->KODE_MATKUL . '`)">
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
            $kodeMatkul = $req->input("kode_matkul");
            $namaMatkul = $req->input("nama_matkul");
            $sks = $req->input("sks");
            $jumlahPertemuan = $req->input("jumlah_pertemuan");
            $kurikulum = $req->input("kurikulum");
            $prodi = $req->input("prodi");

            $is_umum = $req->input('is_umum') ?? '';
            $is_lintas_prodi = $req->input('is_lintas_prodi') ?? '';
            $id_linprod = $req->input('id_linprod') ?? '';
            $linprod = $req->input('linprod') ?? '';
            $is_lapangan = $req->input('is_lapangan') ?? '';

            $data = [
                "KODE_MATKUL" => $kodeMatkul,
                "NAMA_MATKUL" => $this->cleanString($namaMatkul),
                "SKS" => $sks,
                "JUMLAH_PERTEMUAN" => $jumlahPertemuan,
                "ID_PRODI" => $prodi,
                "LOG_TIME" => date('Y-m-d H:i:s'),
                "IS_ACTIVE" => 1,
                "IS_DELETE" => 0
            ];

            if (!empty($is_umum)) {
                $data['IS_UMUM'] = $is_umum;
            }

            if (!empty($is_lintas_prodi)) {
                $data['IS_LINTAS_PRODI'] = $is_lintas_prodi;
                $data['ID_LINPROD'] = implode(';', $id_linprod);
                $data['LINPROD'] = implode(';', $linprod);
            }

            if (!empty($is_lapangan)) {
                $data['IS_LAPANGAN'] = $is_lapangan;
            }

            DB::beginTransaction();
            DB::table("md_matkul")->updateOrInsert(["KODE_MATKUL" => $kodeMatkul], $data);
            DB::commit();

            return redirect('mata-kuliah')->with('resp_msg', 'Berhasil menyimpan mata kuliah.');
        } catch (Exception $err) {
            DB::rollBack();

            return redirect('mata-kuliah')->with('err_msg', 'Gagal menyimpan mata kuliah, error ' . $err->getMessage());
        }
    }

    public function changeActiveStatus(Request $req)
    {
        try {
            $active = $req->input('active');
            DB::table('md_matkul')->where(['KODE_MATKUL' => $req->input('id')])->update(['IS_ACTIVE' => $active]);
            return redirect('mata-kuliah')->with('resp_msg', 'Berhasil ' . (($active == 0) ? 'menonaktifkan' : 'mengaktifkan') . ' mata kuliah.');
        } catch (Exception $err) {
            return redirect('mata-kuliah')->with('err_msg', 'Gagal merubah status mata kuliah, error ' . $err->getMessage());
        }
    }

    public function delete(Request $req)
    {
        try {
            DB::table('md_matkul')->where(['KODE_MATKUL' => $req->input('id')])->delete();
            return redirect('mata-kuliah')->with('resp_msg', 'Berhasil menghapus mata kuliah.');
        } catch (Exception $err) {
            return redirect('mata-kuliah')->with('err_msg', 'Gagal menghapus mata kuliah, error ' . $err->getMessage());
        }
    }

    function cleanString($string)
    {
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        $asciiOnly = preg_replace('/[^\x20-\x7E]/', '', $normalized);
        $cleaned = preg_replace('/[^a-zA-Z0-9\s-]/', '', $asciiOnly);
        return trim(preg_replace('/\s+/', ' ', $cleaned));
    }
}

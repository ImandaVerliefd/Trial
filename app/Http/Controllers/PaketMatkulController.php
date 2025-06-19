<?php

namespace App\Http\Controllers;

use App\Models\Kurikulum;
use App\Models\Matkul;
use App\Models\PaketMatkul;
use App\Models\PetaKurikulum;
use App\Models\Prodi;
use App\Models\Semester;
use App\Models\TahunAjar;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class PaketMatkulController extends Controller
{
    public function index()
    {
        $data['title'] = "Paket Mata Kuliah";

        $data['script'] = 'layout/layout_admin/paketMatkul/_html_script';
        $data['content_page'] = 'layout/layout_admin/paketMatkul/index';
        return view('templates/main', $data);
    }

    public function indexForm(Request $req)
    {
        $data['title'] = "Paket Mata Kuliah";

        $data['semester'] = Semester::whereNull('IS_DELETE')->get();
        $data['tahunajar'] = TahunAjar::whereNull('IS_DELETE')->get();
        $data['prodi'] = Prodi::whereNull('DELETED_AT')->get();

        if (!empty($req->input('id'))) {
            $rawData = Crypt::decrypt($req->input('id'));
            $kodepaket = $rawData['KODE_PAKET'];
            $data['detail_paket'] = PaketMatkul::get_detail_paket($kodepaket);
        }

        $data['script'] = 'layout/layout_admin/paketMatkul/_html_script';
        $data['content_page'] = 'layout/layout_admin/paketMatkul/form';
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

        $resp = PaketMatkul::get_data_datatable($orderType, $valOrder, $search, $start, $perPage);
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;
            $data = array(
                "KODE_PAKET" => $item->KODE_PAKET,
                "JENJANG" => $item->JENJANG,
                "MATKUL" => $item->PRODI,
                "TOT_SKS" => $item->TOT_SKS,
                "KODE_SEMESTER" => $item->KODE_SEMESTER,
                "TAHUN_AJAR" => $item->TAHUN_AJAR
            );

            $parseData = [
                "KODE_PAKET" => $item->KODE_PAKET,
            ];
            $data['ACTION_BUTTON'] = '
                <a type="button" class="btn btn-warning" href="' . url('paket-mata-kuliah/form') . '?id=' . Crypt::encrypt($parseData) . '">
                    <i class="ri-pencil-line"></i>
                </a>
                <button type="button" class="btn btn-danger" onclick="modalConfirmDelete(`' . $item->KODE_PAKET . '`)">
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
        try {
            $prodi = $req->input("prodi");
            $tahunajar = $req->input("tahunajar");
            $paketMatkul = $req->input("paket-matkul");
            $used_semester = $req->input("used_semester");

            $data = [];
            $kodePaket = $this->GenerateUniqChild('PAKET', date('Y-m-d H:i:s') . uniqid());
            foreach ($paketMatkul as $item) {
                $data[] = [
                    "KODE_PAKET" => $kodePaket,
                    "KODE_MATKUL" => $item,
                    "ID_PRODI" => $prodi,
                    "ID_TAHUN_AJAR" => $tahunajar,
                    "KODE_SEMESTER" => $used_semester,
                    "LOG_TIME" => date('Y-m-d H:i:s')
                ];
            }

            DB::beginTransaction();
            DB::table("tb_paket_matkul")->where(["ID_PRODI" => $prodi, "ID_TAHUN_AJAR" => $tahunajar, "KODE_SEMESTER" => $used_semester])->delete();
            DB::table("tb_paket_matkul")->insert($data);
            DB::commit();

            return redirect('paket-mata-kuliah')->with('resp_msg', 'Berhasil menyimpan paket mata kuliah.');
        } catch (Exception $err) {
            DB::rollBack();

            return redirect('paket-mata-kuliah')->with('err_msg', 'Gagal menyimpan paket mata kuliah, error ' . $err->getMessage());
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

    public function paketMatkulsearch(Request $req)
    {
        $semester = $req->input('semester');
        $prodi = $req->input('prodi');
        $kode_semester = $req->input('kode_semester');
        $query = "
            (
                SELECT
                    mpk.ID_DETSEM ,
                    mm.ID_PRODI ,
                    mpk.ID_SEMESTER ,
                    mm.NAMA_MATKUL ,
                    mm.SKS ,
                    mp.JENJANG ,
                    trj.HARI ,
                    trj.JAM_MULAI ,
                    trj.SKS_MATKUL ,
                    trj.JAM_SELESAI ,
                    trj.ID_RUANGAN ,
                    trj.NAMA_RUANGAN ,
                    trj.KAPASITAS_RUANGAN ,
                    trj.ID_METODE,
                    trj.KODE
                FROM
                    mapping_peta_kurikulum mpk
                LEFT JOIN md_matkul mm ON 
                    mm.KODE_MATKUL = mpk.KODE_MATKUL 
                LEFT JOIN md_prodi mp ON
                    mm.ID_PRODI = mp.ID_PRODI
                LEFT JOIN (
                    SELECT 
                        trj.ID_DETSEM,
                        GROUP_CONCAT(trj.KODE SEPARATOR ';') AS KODE,
                        GROUP_CONCAT(trj.HARI SEPARATOR ';') AS HARI,
                        GROUP_CONCAT(trj.JAM_MULAI SEPARATOR ';') AS JAM_MULAI,
                        GROUP_CONCAT(trj.JAM_SELESAI SEPARATOR ';') AS JAM_SELESAI,
                        GROUP_CONCAT(trj.ID_RUANGAN SEPARATOR ';') AS ID_RUANGAN,
                        GROUP_CONCAT(trj.NAMA_RUANGAN SEPARATOR ';') AS NAMA_RUANGAN,
                        GROUP_CONCAT(trj.KAPASITAS_RUANGAN SEPARATOR ';') AS KAPASITAS_RUANGAN,
                        GROUP_CONCAT(trj.SKS_MATKUL SEPARATOR ';') AS SKS_MATKUL,
                        GROUP_CONCAT(trj.ID_METODE SEPARATOR ';') AS ID_METODE, 
                        GROUP_CONCAT(mma.METODE SEPARATOR ';') AS METODE 
                    FROM 
                        tb_ruang_jadwal trj 
                    LEFT JOIN md_metode_ajar mma ON 
                        mma.ID_METODE = trj.ID_METODE 
                    GROUP BY 
                        trj.ID_DETSEM
                ) trj ON 
                    trj.ID_DETSEM = mpk.ID_DETSEM 
                WHERE 
                    mpk.ID_PRODI = " . (int)$prodi . "
                    AND 
                    mpk.ID_SEMESTER = " . (int)$semester . "
                    AND 
                    mpk.KODE_SEMESTER = " . (int)$kode_semester . "
            ) UNION ALL (
                SELECT
                mpk.ID_DETSEM ,
                mm.ID_PRODI ,
                mpk.ID_SEMESTER ,
                mm.NAMA_MATKUL ,
                mm.SKS ,
                mp.JENJANG ,
                trj.HARI ,
                trj.JAM_MULAI ,
                trj.SKS_MATKUL ,
                trj.JAM_SELESAI ,
                trj.ID_RUANGAN ,
                trj.NAMA_RUANGAN ,
                trj.KAPASITAS_RUANGAN ,
                trj.ID_METODE,
                trj.KODE
            FROM
                mapping_peta_kurikulum mpk
            LEFT JOIN md_matkul mm ON 
                mm.KODE_MATKUL = mpk.KODE_MATKUL 
            LEFT JOIN md_prodi mp ON
                mm.ID_PRODI = mp.ID_PRODI
            LEFT JOIN (
                SELECT 
                    trj.ID_DETSEM,
                    GROUP_CONCAT(trj.KODE SEPARATOR ';') AS KODE,
                    GROUP_CONCAT(trj.HARI SEPARATOR ';') AS HARI,
                    GROUP_CONCAT(trj.JAM_MULAI SEPARATOR ';') AS JAM_MULAI,
                    GROUP_CONCAT(trj.JAM_SELESAI SEPARATOR ';') AS JAM_SELESAI,
                    GROUP_CONCAT(trj.ID_RUANGAN SEPARATOR ';') AS ID_RUANGAN,
                    GROUP_CONCAT(trj.NAMA_RUANGAN SEPARATOR ';') AS NAMA_RUANGAN,
                    GROUP_CONCAT(trj.KAPASITAS_RUANGAN SEPARATOR ';') AS KAPASITAS_RUANGAN,
                    GROUP_CONCAT(trj.SKS_MATKUL SEPARATOR ';') AS SKS_MATKUL,
                    GROUP_CONCAT(trj.ID_METODE SEPARATOR ';') AS ID_METODE, 
                    GROUP_CONCAT(mma.METODE SEPARATOR ';') AS METODE 
                FROM 
                    tb_ruang_jadwal trj 
                LEFT JOIN md_metode_ajar mma ON 
                    mma.ID_METODE = trj.ID_METODE 
                GROUP BY 
                    trj.ID_DETSEM
            ) trj ON 
                trj.ID_DETSEM = mpk.ID_DETSEM 
            WHERE 
                (mm.IS_UMUM = 1
            OR
                mpk.IS_LINTAS_PRODI = 1)
            AND
                mpk.ID_LINPROD = " . (int)$prodi . "
            )
        ";

        $rawData = DB::select($query);
        return response($rawData, 200);
    }

    public function matkulsearch(Request $req)
    {
        $prodi = $req->input('prodi');
        $query = "
            (
                SELECT
                    mm.KODE_MATKUL ,
                    mm.ID_PRODI ,
                    mm.NAMA_MATKUL ,
                    mm.SKS ,
                    mp.JENJANG 
                FROM
                    md_matkul mm
                LEFT JOIN md_prodi mp ON
                    mm.ID_PRODI = mp.ID_PRODI
                WHERE 
                    (mm.ID_PRODI = " . (int)$prodi . " OR mm.IS_UMUM = '1')
                    AND
                    mm.IS_ACTIVE = '1'
            )
        ";

        $rawData = DB::select($query);
        return response($rawData, 200);
    }

    public function copyPaketMatkul(Request $req)
    {
        try {
            $prodiSource = $req->input("prodi_source");
            $tahunajarSource = $req->input("tahunajar_source");
            $semesterSource = $req->input("semester_source");
            $matkulToCopy = $req->input("matkul_to_copy"); 
            $prodiTarget = $req->input("prodi_target"); 
            $tahunajarTarget = $req->input("tahunajar_target");
            $semesterTarget = $req->input("semester_target");

            $query = DB::table('tb_paket_matkul')
                        ->where('ID_PRODI', $prodiSource)
                        ->where('ID_TAHUN_AJAR', $tahunajarSource)
                        ->where('KODE_SEMESTER', $semesterSource);

            if (!empty($matkulToCopy)) {
                $query->whereIn('KODE_MATKUL', $matkulToCopy);
            }

            $sourceData = $query->get();

            if ($sourceData->isEmpty()) {
                return redirect('paket-mata-kuliah/copy-form')->with('err_msg', 'Tidak ada mata kuliah ditemukan untuk disalin dari sumber yang dipilih.');
            }

            $newData = [];
            DB::beginTransaction();

            $kodePaket = $this->GenerateUniqChild('PAKET', date('Y-m-d H:i:s') . uniqid());

            DB::table("tb_paket_matkul")->where([
                "ID_PRODI" => $prodiTarget,
                "ID_TAHUN_AJAR" => $tahunajarTarget,
                "KODE_SEMESTER" => $semesterTarget
            ])->delete();

            foreach ($sourceData as $item) {
                $newData[] = [
                    "KODE_PAKET" => $kodePaket, 
                    "KODE_MATKUL" => $item->KODE_MATKUL,
                    "ID_PRODI" => $prodiTarget, 
                    "ID_TAHUN_AJAR" => $tahunajarTarget,
                    "KODE_SEMESTER" => $semesterTarget,
                    "LOG_TIME" => date('Y-m-d H:i:s')
                ];
            }

            DB::table("tb_paket_matkul")->insert($newData);
            DB::commit();

            return redirect('paket-mata-kuliah')->with('resp_msg', 'Berhasil menyalin paket mata kuliah.');

        } catch (Exception $err) {
            DB::rollBack();
            return redirect('paket-mata-kuliah/copy-form')->with('err_msg', 'Gagal menyalin paket mata kuliah, error: ' . $err->getMessage());
        }
    }

    public function indexCopyForm()
    {
        $data['title'] = "Copy Paket Mata Kuliah";
        $data['prodi'] = Prodi::whereNull('DELETED_AT')->get();
        $data['tahunajar'] = TahunAjar::whereNull('IS_DELETE')->get();
        $data['semester'] = Semester::whereNull('IS_DELETE')->get();

        $data['script'] = 'layout/layout_admin/paketMatkul/_html_script_copy';
        $data['content_page'] = 'layout/layout_admin/paketMatkul/copy_form';
        return view('templates/main', $data);
    }


    public function getPaketMatkulForCopy(Request $req)
    {
        $idProdi = $req->input('id_prodi');
        $idTahunAjar = $req->input('id_tahun_ajar');
        $kodeSemester = $req->input('kode_semester');

        $matkuls = DB::table('tb_paket_matkul as tpm')
                    ->join('md_matkul as mm', 'tpm.KODE_MATKUL', '=', 'mm.KODE_MATKUL')
                    ->select('mm.KODE_MATKUL', 'mm.NAMA_MATKUL', 'mm.SKS')
                    ->where('tpm.ID_PRODI', (int)$idProdi)
                    ->where('tpm.ID_TAHUN_AJAR', (int)$idTahunAjar)
                    ->where('tpm.KODE_SEMESTER', (int)$kodeSemester)
                    ->get();
        
        return response()->json($matkuls);
    }

    // STANDALONE FUNCTION    
    public function GenerateUniqChild($first, $val)
    {
        $input = $val;
        $hash = md5($input);
        $sixDigitID = strtoupper(substr($hash, 0, 6));
        $generatedID = $first . '_' . $sixDigitID;
        return $generatedID;
    }
}

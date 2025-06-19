<?php

namespace App\Http\Controllers;

use App\Models\LogAkademik;
use App\Models\Mahasiswa;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\KRS;
use App\Models\PetaKurikulum;
use Exception;

class KRSController extends Controller
{
    public function index()
    {
        $data['title'] = "KRS ";
        $data['script'] = 'layout/layout_mahasiswa/krs/_html_script';
        $data['content_page'] = 'layout/layout_mahasiswa/krs/index';
        $results = [];
        $dataSelected = [];
        $selected = "";
        $data['NewData_all'] = "";

        $perwalian = KRS::get_data_perwalian();
        $semester_aktif = Semester::whereRaw('CURDATE() BETWEEN START_SEMESTER AND END_SEMESTER')->first()->toArray();
        $kode_mahasiswa = Mahasiswa::whereRaw("ID_USER = ?", [session('user')[0]['id_user']])->first()->toArray();

        $data_selected = KRS::where([
            "KODE_MAHASISWA" => $kode_mahasiswa['KODE_MAHASISWA'],
            "ID_SEMESTER" => $semester_aktif['ID_SEMESTER']
        ])->get()->toArray();

        $checking_perwalian = KRS::where([
            "KODE_MAHASISWA" => $kode_mahasiswa['KODE_MAHASISWA'],
            "ID_SEMESTER" => $semester_aktif['ID_SEMESTER']
        ])->whereNotNull("TANGGAL_AMBIL")->first(['STATUS_VERIF', 'TANGGAL_VERIF']);

        $checking_mahasiswa = LogAkademik::where([
            "KODE_MAHASISWA" => $kode_mahasiswa['KODE_MAHASISWA'],
            "KODE_SEMESTER" => $semester_aktif['ID_SEMESTER']
        ])->get()->toArray();
        
        // NOTE : STATUS PERWALIAN STATUS (dibuka atau ditutup) belum dibuat UPDATE: TANGGAL PADA MD_SEMESTER ms.TGL_PERWALIAN
        $checking_perwalian_status = $semester_aktif['STATUS_PERWALIAN']; // 1 = diijinkan mengambil untuk semua prodi
        $checking_pengambilan = isset($checking_perwalian) ? $checking_perwalian : ['STATUS_VERIF' => 2]; // 1 = diijinkan mengambil (mahasiswa belum mengambil krs semester ini)
        $checking_status_mahasiswa = !empty($checking_mahasiswa) ? 0 : 1; // 1 = diijinkan mengambil (tidak ada catatan cuti semester ini)
        $data['error'] = true;
        
        if ($checking_perwalian_status == 0) {
            $data['resp_msg'] = "<p>Saat ini Anda tidak dapat melakukan pengambilan mata kuliah dikarenakan alasan sebagai berikut :
            Program studi Anda tidak diijinkan untuk melakukan perwalian.</p>";
            
            return view('templates/main', $data);
        }else if($checking_status_mahasiswa == 0) {
            $data['resp_msg'] = "<p>Saat ini Anda tidak dapat melakukan pengambilan mata kuliah dikarenakan alasan sebagai berikut :
            Program studi Anda tidak diijinkan untuk melakukan perwalian.</p>";
            
            return view('templates/main', $data);
        }else if ($checking_pengambilan['STATUS_VERIF'] == 1) {
            $data['resp_msg'] = "<p>Saat ini Anda tidak dapat melakukan pengambilan KRS dikarenakan alasan sebagai berikut :</p>
            <p>KRS Anda sudah divalidasi, pengambilan telah dinyatakan selesai pada '" . $checking_pengambilan['TANGGAL_VERIF'] . "' </p>";
            // dd($data, $kode_mahasiswa['KODE_MAHASISWA']);
            return view('templates/main', $data);
        } else if ($checking_pengambilan['STATUS_VERIF'] == 0) {
            $data['resp_msg'] = "<p>Anda sudah mengambil KRS, harap hubungi dosen wali anda untuk melakukan validasi KRS.</p>";

            return view('templates/main', $data);
        }
        $data['error'] = false;
        
        foreach ($data_selected as $datakey) {
            $kd_kelas = $datakey["KODE_KELAS"];
            if (!isset($dataSelected[$kd_kelas])) {
                $dataSelected[$kd_kelas] = [];
            }
            $dataSelected[$kd_kelas] = $datakey;
        }

        foreach ($perwalian as $item) {
            $id_detsem = $item->ID_DETSEM;
            $kode_kelas = $item->KODE_KELAS;

            if (!isset($results[$id_detsem])) {
                $results[$id_detsem] = [];
            }

            $selected = null;
            if (isset($dataSelected[$item->KODE_KELAS])) {
                $selected = "_" . rtrim(base64_encode($item->KODE_KELAS), '=');
            }

            $CHECKING_DATA = KRS::checking_kelas($item->ID_DETSEM, $item->KODE_KELAS);
            $item->TERDAFTAR = $CHECKING_DATA->total_students ?? 0;

            $item->ID_DETSEM         = rtrim(base64_encode($item->ID_DETSEM), '=');
            $item->KODE_KELAS        = rtrim(base64_encode($item->KODE_KELAS), '=');
            $item->KODE              = explode(";", $item->KODE);
            $item->HARI              = explode(";", $item->HARI);
            $item->JAM_MULAI         = explode(";", $item->JAM_MULAI);
            $item->JAM_SELESAI       = explode(";", $item->JAM_SELESAI);
            $item->NAMA_RUANGAN      = explode(";", $item->NAMA_RUANGAN);
            $item->KAPASITAS_RUANGAN = explode(";", $item->KAPASITAS_RUANGAN);
            $item->METODE            = explode(";", $item->METODE);
            $item->SELECTED          = $selected;
            $item->rowCount          = count($item->NAMA_RUANGAN);

            $results[$id_detsem][$kode_kelas] = $item;
        }

        $dayOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        foreach ($results as $id_detsem => &$kelasList) {
            $kelasArray = array_values($kelasList);

            usort($kelasArray, function ($a, $b) use ($dayOrder) {
                $dayA = $a->HARI[0] ?? '';
                $dayB = $b->HARI[0] ?? '';
                $indexA = array_search($dayA, $dayOrder);
                $indexB = array_search($dayB, $dayOrder);

                if ($indexA === $indexB) {
                    return strcmp($a->JAM_MULAI[0] ?? '', $b->JAM_MULAI[0] ?? '');
                }

                return $indexA <=> $indexB;
            });

            $kelasList = [];
            foreach ($kelasArray as $item) {
                $kelasList[$item->KODE_KELAS] = $item;
            }

            $results[$id_detsem] = $kelasList;
        }
        unset($kelasList);
        
        $data['NewData_all'] = $results;

        $data['script'] = 'layout/layout_mahasiswa/krs/_html_script';
        $data['content_page'] = 'layout/layout_mahasiswa/krs/index';

        return view('templates/main', $data);
    }

    public function submitKelas(Request $req)
    {
        try {
            $KODE_KELAS = base64_decode($req->input('ID_KELAS'));
            $ID_DETSEM = base64_decode($req->input('ID_DETSEM'));
            $kode_mahasiswa = Mahasiswa::whereRaw("ID_USER = ?", [session('user')[0]['id_user']])->first();
            $semester_aktif = Semester::whereRaw('CURDATE() BETWEEN START_SEMESTER AND END_SEMESTER')->first();
            $check_kelas = KRS::where([
                "ID_DETSEM" => $ID_DETSEM,
                "KODE_KELAS" => $KODE_KELAS
            ])
                ->selectRaw('COUNT(ID_PERWALIAN) AS total_students')->first();
            $check_kapasitas = KRS::checking_kelas_kapasitas($ID_DETSEM, $KODE_KELAS);
            $total_students = $check_kelas["total_students"];

            $perwalian = [
                'KODE_MAHASISWA' => $kode_mahasiswa->KODE_MAHASISWA,
                'ID_DETSEM'      => $ID_DETSEM,
                'KODE_KELAS'     => $KODE_KELAS,
                'ID_SEMESTER'    => $semester_aktif->ID_SEMESTER
            ];
 
            if ($total_students < $check_kapasitas) {
                $total_students++;

                DB::beginTransaction();
                DB::table("tb_perwalian")->updateOrInsert([
                    "ID_DETSEM" => $ID_DETSEM,
                    "KODE_MAHASISWA" => $kode_mahasiswa->KODE_MAHASISWA
                ], $perwalian);
                DB::commit();
            }

            $CHECKING_DATA = collect(json_decode(json_encode(KRS::get_all_kelas()), true))
            ->mapWithKeys(function ($item) {
                $key = rtrim(base64_encode($item['ID_DETSEM']), '=') . "_" . rtrim(base64_encode($item['KODE_KELAS']), '=');
                return [$key => [
                    'total_students' => $item['total_students']
                ]];
            }); 

            return response()->json([
                'status'        => $total_students < $check_kapasitas ? 'success' : 'failed',
                'message'       => $total_students < $check_kapasitas ? 'Berhasil mendaftarkan kelas.' : 'Gagal mendaftarkan kelas.',
                'ketersediaan'  => $total_students,
                'CHECKING_DATA' => $CHECKING_DATA
            ], 200);
        } catch (Exception $err) {
            DB::rollback();
        }
    }
    public function submitPerwalian()
    {
        try {
            $kode_mahasiswa = Mahasiswa::whereRaw("ID_USER = ?", [session('user')[0]['id_user']])->first();
            $semester_aktif = Semester::whereRaw('CURDATE() BETWEEN START_SEMESTER AND END_SEMESTER')->first();

            DB::beginTransaction();
            DB::table("tb_perwalian")->where([
                "ID_SEMESTER" => $semester_aktif->ID_SEMESTER,
                "KODE_MAHASISWA" => $kode_mahasiswa->KODE_MAHASISWA
            ])->update(['STATUS_VERIF' => 0, 'TANGGAL_AMBIL'  => date('Y-m-d H:i:s')]);
            DB::commit();

            return redirect('krs')->with('resp_msg', 'Berhasil menyelesaikan KRS.');
        } catch (Exception $err) {
            DB::rollBack();

            return redirect('krs')->with('resp_msg', 'Terjadi kesalahan pada sistem. error ' . $err->getMessage());
        }
    }
}
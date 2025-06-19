<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Matkul;
use App\Models\MetodeAjar;
use App\Models\Penjadwalan;
use App\Models\PetaKurikulum;
use App\Models\Prodi;
use App\Models\Ruangan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class PenjadwalanController extends Controller
{
    public function index()
    {
        $data['title'] = "Penugasan Dosen";
        $data['prodi'] = Prodi::whereNull('DELETED_AT')->get();
        $data['script'] = 'layout/layout_admin/Penjadwalan/_html_script';
        $data['content_page'] = 'layout/layout_admin/Penjadwalan/index';
        return view('templates/main', $data);
    }

    public function detail($rawKode)
    {
        $raw = Crypt::decrypt($rawKode);
        $rawData = PetaKurikulum::where(['KODE_MATKUL' => $raw['kode_matkul'], 'ID_SEMESTER' => $raw['id_semester'], 'KODE_SEMESTER' => $raw['kode_semester']])->first();
        $data['detail_matkul'] = Matkul::get_detail_matkul($rawData['KODE_KELAS'], $rawData['KODE_MATKUL'], $rawData['ID_SEMESTER'], $rawData['KODE_SEMESTER']) ?? [];
        $data['list_kelas'] = Penjadwalan::list_kelas_by_id_detsem($rawData['ID_DETSEM']);
        $data['list_kelas_active_now'] = Penjadwalan::list_kelas_now($rawData['ID_DETSEM']);

        $data['title'] = "Pemetaan Sub CPMK";
        $data['script'] = 'layout/layout_admin/Penjadwalan/_html_script';
        $data['content_page'] = 'layout/layout_admin/Penjadwalan/detail';
        $data['content_form'] = 'layout/layout_admin/Penjadwalan/form_kelas';
        return view('templates/main', $data);
    }

    public function showFormRuangan(Request $req)
    {
        if (empty($req->input('kodeKelas'))) {
            return '<div class="alert alert-danger mt-3" role="alert">
                    <i class="ri-close-circle-line me-1 align-middle fs-16"></i>
                    Harap untuk memilih kelas terlebih dahulu!
                </div>';
        }

        $rawData = PetaKurikulum::where(['ID_DETSEM' => $req->input('idDetsem')])->first();
        $data['detail_matkul'] = Matkul::get_detail_matkul($req->input('kodeKelas'), $rawData['KODE_MATKUL'], $rawData['ID_SEMESTER'], $rawData['KODE_SEMESTER']) ?? [];
        $data['data_ruangan'] = Ruangan::whereRaw("IS_DELETE = 0 AND IS_ACTIVE = 1")->orderByRaw("CAST(NAMA_RUANGAN AS SIGNED) ASC")->get();
        $data['metode_ajar'] = MetodeAjar::whereRaw("IS_DELETE IS NULL")->get();

        return view('layout/layout_admin/Penjadwalan/_html_script_form', $data)
            . view('layout/layout_admin/Penjadwalan/form_matkul', $data)->render();
    }

    public function showFormDosen(Request $req)
    {
        if (empty($req->input('kodeKelas'))) {
            return '<div class="alert alert-danger mt-3" role="alert">
                    <i class="ri-close-circle-line me-1 align-middle fs-16"></i>
                    Harap untuk memilih kelas terlebih dahulu!
                </div>';
        }

        $rawData = PetaKurikulum::where(['ID_DETSEM' => $req->input('idDetsem')])->first();
        $data['detail_matkul'] = Matkul::get_detail_matkul($req->input('kodeKelas'), $rawData['KODE_MATKUL'], $rawData['ID_SEMESTER'], $rawData['KODE_SEMESTER']) ?? [];
        $data['detail_subCPMK'] = Penjadwalan::get_detail_sub_cpmk($req->input('kodeKelas'), $rawData['KODE_MATKUL'], $rawData['ID_SEMESTER'], $rawData['KODE_SEMESTER']);
        $data['data_dosen'] = Dosen::all_data_dosen();

        return view('layout/layout_admin/Penjadwalan/_html_script_form', $data)
            . view('layout/layout_admin/Penjadwalan/form_dosen', $data)->render();
    }

    public function submit(Request $req)
    {
        DB::statement("SET FOREIGN_KEY_CHECKS = 0");
        $encryptedKodeMatkul = Crypt::encrypt([
            "kode_matkul" => $req->input("kode_matkul"),
            "id_semester" => $req->input("id_semester"),
            "kode_semester" => $req->input("kode_semester")
        ]);
        $redirectURLs = 'penjadwalan/detail/' . $encryptedKodeMatkul;
        try {
            DB::beginTransaction();

            foreach ($req->input("kode_dosen") as $key => $kode_dosen) {
                $idMapping = $req->input("id_mapping")[$key] ?? null;
                $data = [
                    "ID_CAPAIAN_DETAIL" => $req->input("id_capaian_detail")[$key],
                    "KODE_KELAS" => $req->input("kode_kelas"),
                    "ID_DETSEM" => $req->input("id_detsem"),
                    "KODE_DOSEN" => $kode_dosen,
                    "LOG_TIME" => date('Y-m-d H:i:s'),
                ];

                if ($idMapping) {
                    DB::table("mapping_subcpmk")->where(["ID_MAPPING" => $idMapping])->update($data);
                } else {
                    DB::table("mapping_subcpmk")->insert($data);
                }
            }

            DB::commit();

            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            return redirect($redirectURLs)->with('resp_msg', 'Berhasil menyimpan penjadwalan dosen');
        } catch (Exception $err) {
            DB::rollBack();
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            return redirect($redirectURLs)->with('err_msg', 'Gagal menyimpan penjadwalan dosen, error ' . $err->getMessage());
        }
    }

    public function submitDetailMatkul(Request $req)
    {
        DB::statement("SET FOREIGN_KEY_CHECKS = 0");
        $encryptedKodeMatkul = Crypt::encrypt([
            "kode_matkul" => $req->input("kode_matkul"),
            "id_semester" => $req->input("id_semester"),
            "kode_semester" => $req->input("kode_semester")
        ]);
        $redirectURLs = 'penjadwalan/detail/' . $encryptedKodeMatkul;

        try {
            $idDetSem = $req->input("id_detsem");
            $id_semester = $req->input("id_semester");

            DB::beginTransaction();

            foreach ($req->input("id_ruangan") as $key => $idRuangan) {
                $dataRuang = Ruangan::where("ID_RUANGAN", $idRuangan)->first();

                $kode_kelas = $req->input("kode_kelas");
                $kode_jadwal = $req->input("kode_jadwal")[$key];
                $metode_ajar = $req->input("metode_ajar")[$key];
                $hari_matkul = $req->input("hari_matkul")[$key];
                $jam_mulai = $req->input("jam_mulai")[$key];     
                $jam_selesai = $req->input("jam_selesai")[$key]; 
                $id_ruangan = $idRuangan;
                $kapasitas = $req->input("kapasitas")[$key];
                $sks_digunakan = $req->input("sks_digunakan")[$key];
                $nama_ruang = $dataRuang->NAMA_RUANGAN;

                $kode_dosen = DB::table('mapping_subcpmk')
                    ->where('ID_DETSEM', $idDetSem)
                    ->where('KODE_KELAS', $kode_kelas)
                    ->value('KODE_DOSEN');

                if (!$kode_dosen) {
                    DB::rollBack();
                    DB::statement("SET FOREIGN_KEY_CHECKS = 1");
                    return redirect($redirectURLs)->with('err_msg', 'Dosen belum ditentukan untuk kelas ini. Silakan isi data di Mapping SubCPMK terlebih dahulu.');
                }

                $jadwalBentrok = DB::table('tb_ruang_jadwal')
                    ->where('ID_RUANGAN', $id_ruangan)
                    ->where('HARI', $hari_matkul)
                    ->where(function ($query) use ($jam_mulai, $jam_selesai) {
                        $query->where('JAM_MULAI', '<', $jam_selesai)
                            ->where('JAM_SELESAI', '>', $jam_mulai); 
                    })
                    ->exists();

                if ($jadwalBentrok) {
                    DB::rollBack();
                    DB::statement("SET FOREIGN_KEY_CHECKS = 1");
                    return redirect($redirectURLs)->with('err_msg', 'Maaf, ruangan ini digunakan di jam dan hari itu.');
                }

                $jadwalBentrokDosen = DB::table('tb_ruang_jadwal')
                    ->join('mapping_subcpmk', function ($join) {
                        $join->on('tb_ruang_jadwal.ID_DETSEM', '=', 'mapping_subcpmk.ID_DETSEM')
                            ->on('tb_ruang_jadwal.KODE_KELAS', '=', 'mapping_subcpmk.KODE_KELAS');
                    })
                    ->where('mapping_subcpmk.KODE_DOSEN', $kode_dosen)
                    ->where('tb_ruang_jadwal.HARI', $hari_matkul)
                    ->where(function ($query) use ($jam_mulai, $jam_selesai) {
                        $query->where('tb_ruang_jadwal.JAM_MULAI', '<', $jam_selesai)
                            ->where('tb_ruang_jadwal.JAM_SELESAI', '>', $jam_mulai); 
                    })
                    ->exists();

                if ($jadwalBentrokDosen) {
                    DB::rollBack();
                    DB::statement("SET FOREIGN_KEY_CHECKS = 1");
                    return redirect($redirectURLs)->with('err_msg', 'Maaf, dosen memiliki jadwal bentrok pada waktu yang sama.');
                }

                $dataJadwal = [
                    "KODE" => (!empty($kode_jadwal) ? $kode_jadwal : $this->GenerateUniqChild('JADWAL_MK', $id_semester . '' . uniqid())),
                    "KODE_KELAS" => $kode_kelas,
                    "ID_DETSEM" => $idDetSem,
                    "ID_SEMESTER" => $id_semester,
                    "ID_METODE" => $metode_ajar,
                    "HARI" => $hari_matkul,
                    "JAM_MULAI" => $jam_mulai,
                    "JAM_SELESAI" => $jam_selesai,
                    "ID_RUANGAN" => $id_ruangan,
                    "KAPASITAS_RUANGAN" => $kapasitas,
                    "SKS_MATKUL" => $sks_digunakan,
                    "NAMA_RUANGAN" => $nama_ruang
                ];

                DB::table("tb_ruang_jadwal")->updateOrInsert(['KODE' => $dataJadwal['KODE']], $dataJadwal);
            }

            DB::commit();
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            return redirect($redirectURLs)->with('resp_msg', 'Berhasil menyimpan penjadwalan mata kuliah');
        } catch (Exception $err) {
            DB::rollBack();
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            return redirect($redirectURLs)->with('err_msg', 'Gagal menyimpan penjadwalan mata kuliah, error: ' . $err->getMessage());
        }
    }

    public function submitDetailKelas(Request $req)
    {
        DB::statement("SET FOREIGN_KEY_CHECKS = 0");
        $encryptedKodeMatkul = Crypt::encrypt([
            "kode_matkul" => $req->input("kode_matkul"),
            "id_semester" => $req->input("id_semester"),
            "kode_semester" => $req->input("kode_semester")
        ]);
        $redirectURLs = 'penjadwalan/detail/' . $encryptedKodeMatkul;
        try {
            $idDetSem = $req->input("id_detsem");
            $kode_matkul = $req->input("kode_matkul");
            DB::beginTransaction();

            foreach ($req->input("kode_kelas") as $key => $kodeKelas) {
                $is_delete = $req->input("is_delete")[$key];
                if ($is_delete == 1) {
                    DB::table("tb_kelas_matkul")->where(['KODE_KELAS' => $kodeKelas])->delete();
                } else {
                    $nama_kelas = $req->input("nama_kelas")[$key];
                    $dataKelas = [
                        "KODE_KELAS" => (!empty($kodeKelas) ? $kodeKelas : $this->GenerateUniqChild('KELAS', $nama_kelas . '' . uniqid())),
                        "ID_DETSEM" => $idDetSem,
                        "KODE_MATKUL" => $kode_matkul,
                        "NAMA_KELAS" => $nama_kelas
                    ];

                    // dump($dataKelas);
                    DB::table("tb_kelas_matkul")->updateOrInsert(['KODE_KELAS' => $kodeKelas], $dataKelas);
                }
            }
            // die;

            DB::commit();

            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            return redirect($redirectURLs)->with('resp_msg', 'Berhasil menyimpan penjadwalan mata kuliah');
        } catch (Exception $err) {
            DB::rollBack();
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            return redirect($redirectURLs)->with('err_msg', 'Gagal menyimpan penjadwalan mata kuliah, error: ' . $err->getMessage());
        }
    }

    public function getMatkulData(Request $req)
    {
        $orderData = $req->input('order')[0]['column'];
        $orderType = strtoupper($req->input('order')[0]['dir']);
        $valOrder = $req->input('columns')[$orderData]['data'];
        $search = $req->input('search')['value'];
        $start = $req->input('start');
        $perPage = $req->input('length');
        $prodi = $req->input('prodi');

        $resp = Penjadwalan::get_data_datatable($orderType, $valOrder, $search, $start, $perPage, $prodi);
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;

            $dataDosen = [];
            foreach (explode(';', $item->DOSEN_PENGAMPU) as $dosen) {
                if (!in_array($dosen, $dataDosen)) {
                    $dataDosen[] = $dosen;
                }
            }

            $data = array(
                "KODE_MATKUL" => $item->KODE_MATKUL . '<br>' . $item->NAMA_MATKUL,
                "SEMESTER" => $item->SEMESTER . ' (' . $item->KODE_SEMESTER . ')',
                "TAHUN_AJAR" => $item->TAHUN_AJAR,
                "KURIKULUM" => $item->PRODI . ' <br> ' . $item->KURIKULUM,
                "DOSEN_PENGAMPU" => implode('<br>', $dataDosen),
                "IS_ACTIVE" => (($item->IS_ACTIVE == 1) ? '<span class="badge bg-success-subtle text-success">Aktif</span>' : '<span class="badge bg-danger-subtle text-danger">Tidak Aktif</span>'),
            );

            if (empty($item->IS_ACTIVE)) {
                $btn = '';
            } else {
                $encryptedKodeMatkul = Crypt::encrypt([
                    "kode_matkul" => $item->KODE_MATKUL,
                    "id_semester" => $item->ID_SEMESTER,
                    "kode_semester" => $item->KODE_SEMESTER
                ]);
                $btn = '
                    <button type="button" class="btn btn-warning" onclick="location.href=\'' . url('penjadwalan/detail/' . $encryptedKodeMatkul) . '\'">
                        <i class="ri-pencil-line"></i>
                    </button>
                ';
            }
            $data['ACTION_BUTTON'] = $btn;

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

    public function checkingRuangan(Request $req)
    {
        try {
            $kode_matkul = $req->input("kode_matkul");
            $id_semester = $req->input("id_semester");
            $hari_matkul = $req->input("hari_matkul");
            $jam_mulai = $req->input("jam_mulai");
            $id_ruangan = $req->input("id_ruangan");
            $checkingData = [];
            foreach ($hari_matkul as $key => $matkulDay) {
                $execQuery = DB::select("
                    SELECT 
                        mpk.ID_DETSEM 
                    FROM 
                        mapping_peta_kurikulum mpk 
                    WHERE
                        mpk.ID_SEMESTER = '$id_semester'
                        AND
                        FIND_IN_SET('" . $matkulDay . "', REPLACE(mpk.HARI, ';', ','))
                        AND
                        FIND_IN_SET('" . $jam_mulai[$key] . "', REPLACE(mpk.JAM_MULAI, ';', ','))
                        AND
                        FIND_IN_SET('" . $id_ruangan[$key] . "', REPLACE(mpk.ID_RUANGAN, ';', ','))
                ");
                foreach ($execQuery as $item) {
                    $checkingData[] = $item->ID_DETSEM;
                }
            }

            if (!empty($checkingData)) {
                return response([
                    'status' => false,
                    'msg' => 'Gagal menyimpan penjadwalan mata kuliah, error: Ruangan sudah digunakan pada hari dan jam yang sama'
                ], 200);
            } else {
                return response([
                    'status' => true
                ], 200);
            }
        } catch (Exception $err) {
            return response([
                'status' => false,
                'msg' => 'Sistem sedang mengalamai error, error: ' . $err->getMessage()
            ], 200);
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

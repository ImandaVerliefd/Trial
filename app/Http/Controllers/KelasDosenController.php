<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\Semester;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class KelasDosenController extends Controller
{
    public function index()
    {
        $data['title'] = "Kehadiran Kelas ";
        
        $result = Semester::get_history_kehadiran();
        $data['history_kehadiran'] = [];

        foreach ($result as $item) {
            $data['history_kehadiran'][] = [
                'ID_DETSEM'     => $item->ID_DETSEM,
                'SEMESTER'      => $item->SEMESTER,
                'PRODI'         => $item->PRODI,
                'KODE_KELAS'    => $item->KODE_KELAS,
                'NAMA_KELAS'    => $item->NAMA_KELAS,
                'KODE_MATKUL'   => $item->KODE_MATKUL,
                'NAMA_MATKUL'   => $item->NAMA_MATKUL,
                'SKS'           => $item->SKS,
            ];
        }

        $data['detail_kelas'] = "";
        $data['script'] = 'layout/layout_dosen/kehadiran_kelas/_html_script';
        $data['content_page'] = 'layout/layout_dosen/kehadiran_kelas/kehadiran_index';

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

        $id = "";
        $dosen = true;
        $results = Dosen::get_jadwal_dosen($id, $dosen);
        $Tot = count($results);

        $counter_all = $start;
        $kelas = [];

        foreach ($results as $item) {
            $kelas[] = [
                "PERIODE"       => $item->SEMESTER,
                "PRODI"         => $item->PRODI,
                "KODE_MATKUL"   => $item->KODE_MATKUL,
                "NAMA_MATKUL"   => $item->NAMA_MATKUL,
                "HARI"          => $item->HARI,
                "ID_RUANGAN"    => $item->ID_RUANGAN,
                "NAMA_RUANGAN"  => $item->NAMA_RUANGAN,
                "NAMA_KELAS"    => $item->NAMA_KELAS,
                "NAMA_DOSEN"    => $item->NAMA_DOSEN,
                "ACTION_BUTTON" => '
                    <div class="dropdown">
                        <button class="btn btn-white dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ri-more-2-line"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item" href="' . url('kehadiran_kelas/att_class') . '?id-detsem=' .
                                $item->ID_DETSEM . '&kode-kelas=' . $item->KODE_KELAS . '">Buka Pertemuan</a>
                            </li>
                            <li>
                                <a class="dropdown-item" onclick="handleHistoryClick(this)" style="cursor: pointer;"
                                data-id-detsem="' . $item->ID_DETSEM . '" data-kode-kelas="' . $item->KODE_KELAS . '">History Pertemuan</a>
                            </li>
                        </ul>
                    </div>
                    '
                ];
            }
                    
                    // <li>
                    //     <a class="dropdown-item" href="' . url('kehadiran_kelas/history_class') . '?id-detsem=' . $item->ID_DETSEM . '&kode-kelas=' . $item->KODE_KELAS . '">history Pertemuan</a>
                    // </li>
        $draw   = $req->input('draw');
        return response([
            'status_code'       => 200,
            'status_message'    => 'Data berhasil diambil!',
            'draw'              => intval($draw),
            'recordsFiltered'   => ($counter_all != 0) ? $Tot : 0,
            'recordsTotal'      => ($counter_all != 0) ? $Tot : 0,
            'data'              => $kelas
        ], 200);
    }

    public function getDataAtt(Request $req)
    {
        $is_null = true;
        $id_detsem = $req->get('id-detsem');
        $kode_kelas = $req->get('kode-kelas');

        $checking_data = Kelas::check_open_kehadiran($id_detsem, $kode_kelas, $is_null);
        $idKehadiran = !empty($checking_data[0]->ID_KEHADIRAN) ? $checking_data[0]->ID_KEHADIRAN : "";
        $result = Kelas::get_detail_data_kelas($id_detsem, $kode_kelas, $idKehadiran);
        $data['DATA_CHECKING'] = !empty($checking_data) ? "1" : "0";
        $data['tot_pertemuan'] = $result['tot_pertemuan']->tot_pertemuan + 1;

        foreach ($result['mahasiswa'] as $value) {
            $value->ACTION_BUTTON = $value->ACTION_BUTTON = '<select class="form-control status-select" 
            name="status_kehadiran" id="status_kehadiran" data-kode-mahasiswa="' . $value->KODE_MAHASISWA . '" data-id-kehadiran="' . (!empty($checking_data[0]->ID_KEHADIRAN) ? $checking_data[0]->ID_KEHADIRAN : '') . '">
                <option value="0" ' . ($value->STATUS == "0" || $value->STATUS == NULL ? 'selected' : '') . '>Tidak Hadir</option>
                <option value="1" ' . ($value->STATUS == 1 ? 'selected' : '') . '>Hadir</option>
                <option value="2" ' . ($value->STATUS == 2 ? 'selected' : '') . '>Sakit</option>
                <option value="3" ' . ($value->STATUS == 3 ? 'selected' : '') . '>Ijin</option>
            </select>';
        }
        
        $result['detail']->ID_KEHADIRAN     = !empty($checking_data[0]->ID_KEHADIRAN) ? $checking_data[0]->ID_KEHADIRAN : '';
        $result['detail']->JUMLAH_PERTEMUAN = !empty($result['detail']->JUMLAH_PERTEMUAN) ? $result['detail']->JUMLAH_PERTEMUAN : '';
        $result['detail']->DATE_PERTEMUAN   = !empty($checking_data[0]->START_KELAS) ? Carbon::parse($checking_data[0]->START_KELAS)->locale('id')->translatedFormat('l, d F Y | H.i') . " - Sekarang" : "";
        $result['detail']->KODE             = explode(';', $result['detail']->KODE);
        $result['detail']->HARI             = explode(';', $result['detail']->HARI);
        $result['detail']->JAM_MULAI        = explode(';', $result['detail']->JAM_MULAI);
        $result['detail']->JAM_SELESAI      = explode(';', $result['detail']->JAM_SELESAI);
        $result['detail']->ID_RUANGAN       = explode(';', $result['detail']->ID_RUANGAN);
        $result['detail']->NAMA_RUANGAN     = explode(';', $result['detail']->NAMA_RUANGAN);
        $result['detail']->NAMA_DOSEN       = explode(';', $result['detail']->NAMA_DOSEN);

        $data['mahasiswa'] = $result['mahasiswa'];
        $data['detail_kelas'] = $result['detail'];
        
        $data['title'] = "Detail Kelas " . $data["detail_kelas"]->NAMA_MATKUL;
        $data['script'] = 'layout/layout_dosen/kehadiran_kelas/_html_script';
        $data['content_page'] = 'layout/layout_dosen/kehadiran_kelas/detail_kehadiran_index';
        return view('templates/main', $data);
    }

    public function openKelas(Request $req)
    {
        try {
            $id_detsem = $req->input('ID_DETSEM');
            $kode_kelas = $req->input('KODE_KELAS');
            $is_null = false;
            $checking_data = Kelas::check_open_kehadiran($id_detsem, $kode_kelas, $is_null);
            $result = Kelas::get_detail_data_kelas($id_detsem, $kode_kelas, "");

            $resp = $result['mahasiswa'];
            $jml_pertemuan = $result['detail']->JUMLAH_PERTEMUAN;

            $mahasiswa = "";
            $Tot_pertemuan = count($checking_data);

            $id_kehadiran = DB::table("tb_kehadiran")->insertGetId([
                'ID_DETSEM'      => $id_detsem,
                'KODE_KELAS'     => $kode_kelas,
                'SESSION_NUMBER' => $Tot_pertemuan + 1,
                'START_KELAS'    => date('Y-m-d H:i:s')
            ]);

            $berita = [
                'ID_KEHADIRAN' => $id_kehadiran
            ];

            DB::beginTransaction();
            DB::table("tb_berita_acara")->insert($berita);

            $mahasiswa = [];
            foreach ($resp as $item) {
                $mahasiswa[] = [
                    'ID_KEHADIRAN'   => $id_kehadiran,
                    'KODE_MAHASISWA' => $item->KODE_MAHASISWA,
                    'STATUS'         => 0
                ];
            }
            
            foreach ($mahasiswa as $item) {
                DB::table('tb_list_absensi')->updateOrInsert(
                    [
                        'ID_KEHADIRAN' => $item['ID_KEHADIRAN'],
                        'KODE_MAHASISWA' => $item['KODE_MAHASISWA']
                    ],
                    [
                        'STATUS' => $item['STATUS']
                    ]
                );
            }
            
            DB::commit();

            $is_null = true;
            $checking_data = Kelas::check_open_kehadiran($id_detsem, $kode_kelas, $is_null);

            return response([
                'status_code'       => 200,
                'status_message'    => 'Kelas berhasil dibuka!',
                'id_kehadiran'      => $id_kehadiran,
                'jml_pertemuan'     => $jml_pertemuan,
                'start_kelas'       => Carbon::parse($checking_data[0]->START_KELAS)->locale('id')->translatedFormat('l, d F Y | H.i')
            ], 200);
        } catch (Exception $err) {
            DB::rollback();
        }
    }

    public function submitAbsen(Request $req)
    {
        try {
            DB::beginTransaction();

            DB::table('tb_list_absensi')->where(['ID_KEHADIRAN' => $req->input('id_kehadiran'), 'KODE_MAHASISWA' => $req->input('kode_mahasiswa')])->update(['STATUS' => $req->input('status_kehadiran')]);
            DB::commit();

            return response([
                'status_code'       => 200,
                'status_message'    => 'Berhasil melakukan absensi!'
            ], 200);
        } catch (Exception $err) {
            DB::rollback();
        }
    }

    public function submitPertemuan(Request $req)
    {
        try {
            DB::beginTransaction();
            $data = [
                'MATERI'                => $req->input('materi'),
                'TANGGAL_PERTEMUAN'     => $req->input('tanggal'),
                'CATATAN'               => $req->input('catatan'),
                'METODE_PEMBELAJARAN'   => $req->input('metode_pembelajaran'),
                'METODE_PELAKSANAAN'    => $req->input('metode_pelaksanaan')
            ];

            DB::table('tb_kehadiran')->where(['ID_KEHADIRAN' => $req->input('id_kehadiran')])->update(['END_KELAS' => date('Y-m-d H:i:s')]);
            DB::table('tb_berita_acara')->where(['ID_KEHADIRAN' => $req->input('id_kehadiran')])->update($data);
            DB::commit();

            return redirect('kehadiran_kelas')->with('resp_msg', 'Berhasil melakukan presensi.');
        } catch (Exception $err) {
            DB::rollBack();

            return redirect('kehadiran_kelas')->with('resp_msg', 'Terjadi kesalahan saat mengirim data. error ' . $err->getMessage());
        }
    }

    public function getHistory(Request $req)
    {
        $id_detsem = $req->input('id_detsem');
        $kode_kelas = $req->input('kode_kelas');

        $data['detail_kelas'] = Kelas::getDetail($id_detsem, $kode_kelas);
        $data['detail_kelas']->PERIODE = explode(' ', $data['detail_kelas']->SEMESTER);
        $data['detail_kelas']->HARI = explode(';', $data['detail_kelas']->HARI);
        $data['detail_kelas']->JAM_MULAI = explode(';', $data['detail_kelas']->JAM_MULAI);
        $data['detail_kelas']->JAM_SELESAI = explode(';', $data['detail_kelas']->JAM_SELESAI);
        $data['detail_kelas']->NAMA_DOSEN = explode(';', $data['detail_kelas']->NAMA_DOSEN);
        $data['detail_kelas']->METODE = explode(';', $data['detail_kelas']->METODE);

        $data['daftar_pertemuan'] = Kelas::daftar_pertemuan($id_detsem, $kode_kelas);
        $data['rekapitulasi_kehadiran'] = Kelas::rekapitulasi_kehadiran($id_detsem, $kode_kelas);
        $data['kehadiran_peserta'] = Kelas::kehadiran_peserta($id_detsem, $kode_kelas);
        $data['berita_acara'] = Kelas::berita_acara($id_detsem, $kode_kelas);

        $html = view('layout.layout_dosen.kehadiran_kelas.ajax.paper_history', $data)->render();

        return response()->json(['html' => $html]);
    }
}

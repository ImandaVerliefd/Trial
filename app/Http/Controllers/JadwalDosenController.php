<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Http\Request;
use App\Models\DosenWali;
use App\Models\Dosen;

class JadwalDosenController extends Controller
{
    public function index()
    {
        $data['title'] = "Jadwal";
        $data['data_wali'] = DosenWali::join_data_dosen();
        $id = "";
        $kode_dosen = true;
        $getJadwal = Dosen::get_jadwal_dosen($id, $kode_dosen);
        $data['jadwal'] = [];
        foreach ($getJadwal as $item) {
            $HARI          = explode(";", $item->HARI);
            $JAM_MULAI     = explode(";", $item->JAM_MULAI);
            $JAM_SELESAI   = explode(";", $item->JAM_SELESAI);
            $ID_RUANGAN    = explode(";", $item->ID_RUANGAN);
            $NAMA_RUANGAN  = explode(";", $item->NAMA_RUANGAN);
            foreach ($HARI as $index => $hari) {
                $data['jadwal'][] = [
                    "PRODI"        => $item->PRODI,
                    "KODE_MATKUL"  => $item->KODE_MATKUL,
                    "NAMA_MATKUL"  => $item->NAMA_MATKUL,
                    "HARI"         => $hari,
                    "JAM_MULAI"    => $JAM_MULAI[$index] ?? null,
                    "JAM_SELESAI"  => $JAM_SELESAI[$index] ?? null,
                    "ID_RUANGAN"   => $ID_RUANGAN[$index] ?? null,
                    "NAMA_RUANGAN" => $NAMA_RUANGAN[$index] ?? null,
                    "START_DATE"   => $item->START_SEMESTER,
                    "START_END"    => $item->END_SEMESTER,
                    "SKS"          => $item->SKS,
                    "NAMA_DOSEN"   => $item->NAMA_DOSEN
                ];
            }
        }

        $data['semester'] = Semester::get_all_semester();
        
        $data['content_page'] = 'layout/layout_dosen/jadwal/index';
        $data['script'] = 'layout/layout_dosen/jadwal/_html_script';
        return view('templates/main', $data);
    }

    public function getDataAjaran(Request $req)
    {
        $kode_dosen = true;
        $getJadwal = Dosen::get_jadwal_dosen($req->input("ID_SEMESTER"), $kode_dosen);

        $jadwal = [];
        foreach ($getJadwal as $item) {
            $HARI          = explode(";", $item->HARI);
            $JAM_MULAI     = explode(";", $item->JAM_MULAI);
            $JAM_SELESAI   = explode(";", $item->JAM_SELESAI);
            $ID_RUANGAN    = explode(";", $item->ID_RUANGAN);
            $NAMA_RUANGAN  = explode(";", $item->NAMA_RUANGAN);
            foreach ($HARI as $index => $hari) {
                $jadwal[] = [
                    "PRODI"        => $item->PRODI,
                    "KODE_MATKUL"  => $item->KODE_MATKUL,
                    "NAMA_MATKUL"  => $item->NAMA_MATKUL,
                    "HARI"         => $hari,
                    "JAM_MULAI"    => $JAM_MULAI[$index] ?? null,
                    "JAM_SELESAI"  => $JAM_SELESAI[$index] ?? null,
                    "ID_RUANGAN"   => $ID_RUANGAN[$index] ?? null,
                    "NAMA_RUANGAN" => $NAMA_RUANGAN[$index] ?? null,
                    "START_DATE"   => $item->START_SEMESTER,
                    "START_END"    => $item->END_SEMESTER,
                    "SKS"          => $item->SKS,
                    "NAMA_DOSEN"   => $item->NAMA_DOSEN
                ];
            }
        }

        return response([
            'status_code'       => 200,
            'status_message'    => 'Data berhasil diambil!',
            'data'              => $jadwal
        ], 200);
    }

    public function getAllData (Request $req)
    {
        $orderData = $req->input('order')[0]['column'];
        $orderType = strtoupper($req->input('order')[0]['dir']);
        $valOrder = $req->input('columns')[$orderData]['data'];
        $search = $req->input('search')['value'];
        $start = $req->input('start');
        $perPage = $req->input('length');

        $resp = Dosen::get_all_jadwal();
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $counter_all = $start;
        $jadwal = [];

        foreach ($results as $item) {
            ++$counter_all;

            $HARI          = explode(";", $item->HARI);
            $JAM_MULAI     = explode(";", $item->JAM_MULAI);
            $JAM_SELESAI   = explode(";", $item->JAM_SELESAI);
            $ID_RUANGAN    = explode(";", $item->ID_RUANGAN);
            $NAMA_RUANGAN  = explode(";", $item->NAMA_RUANGAN);
                
            foreach ($HARI as $key => $value) {
                $jadwal[] = [
                    "PERIODE"      => $item->SEMESTER,
                    "PRODI"        => $item->PRODI,
                    "KODE_MATKUL"  => $item->KODE_MATKUL,
                    "NAMA_MATKUL"  => $item->NAMA_MATKUL,
                    "KODE_JADWAL"  => $item->KODE,
                    "NAMA_KELAS"   => $item->NAMA_KELAS,
                    "HARI"         => $HARI[$key],
                    "JAM_MULAI"    => $JAM_MULAI[$key] ?? null,
                    "JAM_SELESAI"  => $JAM_SELESAI[$key] ?? null,
                    "ID_RUANGAN"   => $ID_RUANGAN[$key] ?? null,
                    "NAMA_RUANGAN" => $NAMA_RUANGAN[$key] ?? null,
                    "START_DATE"   => $item->START_SEMESTER,
                    "END_DATE"     => $item->END_SEMESTER,
                    "NAMA_DOSEN"   => $item->NAMA_DOSEN,
                    "PERTEMUAN"    => $item->START_SEMESTER . " s/d " . $item->END_SEMESTER . " " . $NAMA_RUANGAN[$key] . " s " . $JAM_MULAI[$key] . " - " . $JAM_SELESAI[$key]
                ];
            }
        }

        $draw   = $req->input('draw');
        return response([
            'status_code'       => 200,
            'status_message'    => 'Data berhasil diambil!',
            'draw'              => intval($draw),
            'recordsFiltered'   => ($counter_all != 0) ? $Tot : 0,
            'recordsTotal'      => ($counter_all != 0) ? $Tot : 0,
            'data'              => $jadwal
        ], 200);
    }
}

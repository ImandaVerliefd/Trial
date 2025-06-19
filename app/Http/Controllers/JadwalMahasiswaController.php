<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class JadwalMahasiswaController extends Controller
{
    public function index()
    {
        $data['title'] = "Jadwal";
        $getJadwal = Mahasiswa::get_jadwal_mahasiswa();
        $data["jadwal"] = [];

        foreach ($getJadwal as $item) {
            $HARI          = explode(";", $item->HARI);
            $JAM_MULAI     = explode(";", $item->JAM_MULAI);
            $JAM_SELESAI   = explode(";", $item->JAM_SELESAI);
            $ID_RUANGAN    = explode(";", $item->ID_RUANGAN);
            $NAMA_RUANGAN  = explode(";", $item->NAMA_RUANGAN);

            foreach ($HARI as $key => $value) {
                $data['jadwal'][] = [
                    "PRODI"        => $item->PRODI,
                    "KODE_KELAS"   => $item->KODE_KELAS,
                    "NAMA_KELAS"   => $item->NAMA_KELAS,
                    "KODE_MATKUL"  => $item->KODE_MATKUL,
                    "NAMA_MATKUL"  => $item->NAMA_MATKUL,
                    "HARI"         => $HARI[$key],
                    "JAM_MULAI"    => $JAM_MULAI[$key],
                    "JAM_SELESAI"  => $JAM_SELESAI[$key],
                    "ID_RUANGAN"   => $ID_RUANGAN[$key],
                    "NAMA_RUANGAN" => $NAMA_RUANGAN[$key],
                    "START_DATE"   => $item->START_DATE,
                    "START_END"    => $item->END_DATE,
                    "SKS"          => $item->SKS,
                    "NAMA_DOSEN"   => $item->NAMA_DOSEN
                ];
            }
        }

        $data['content_page'] = 'layout/layout_mahasiswa/jadwal/index';
        $data['script'] = 'layout/layout_mahasiswa/jadwal/_html_script';
        return view('templates/main', $data);
    }

    public function jadwalUjian()
    {
        // 
    }
}

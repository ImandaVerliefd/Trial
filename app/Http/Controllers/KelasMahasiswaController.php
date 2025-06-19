<?php

namespace App\Http\Controllers;

use App\Models\CapaianDetail;
use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\KRS;
use App\Models\Semester;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class KelasMahasiswaController extends Controller
{
    public function index()
    {
        $data['title'] = "Kehadiran Kuliah ";
        $kodeMHS = session('user')[0]['kode_user'];

        $data['tahun_ajaran'] = Kelas::getSemester($kodeMHS);
        $data['list_matkul'] = Kelas::getMatkul($kodeMHS);
        
        $data['script'] = 'layout/layout_mahasiswa/kehadiran_kelas/_html_script';
        $data['content_page'] = 'layout/layout_mahasiswa/kehadiran_kelas/kehadiran_index';

        return view('templates/main', $data);
    }

    public function getRekap(Request $req)
    {
        $id_semester = $req->input('id_semester');
        
        $data['matkul'] = Kelas::getRekapitulasi($id_semester);
        $html = view('layout.layout_mahasiswa.kehadiran_kelas.ajax.paper_rekapitulasi', $data)->render();

        return response()->json(['html' => $html]);
    }

    public function getKehadiran(Request $req)
    {
        $id_semester = $req->input('id_semester');
        $data['matkul'] = Kelas::getKehadiran($id_semester);
        $html = view('layout.layout_mahasiswa.kehadiran_kelas.ajax.paper_kehadiran', $data)->render();

        return response()->json(['html' => $html]);
    }

    public function getPertemuan(Request $req)
    {
        $id_detsem = $req->input('id_detsem');
        $kode_kelas = $req->input('kode_kelas');

        $data['detail_kelas'] = Kelas::getDetail($id_detsem, $kode_kelas);
        $data['detail_kelas']->HARI = explode(';', $data['detail_kelas']->HARI);
        $data['detail_kelas']->JAM_MULAI = explode(';', $data['detail_kelas']->JAM_MULAI);
        $data['detail_kelas']->JAM_SELESAI = explode(';', $data['detail_kelas']->JAM_SELESAI);
        $data['detail_kelas']->NAMA_DOSEN = explode(';', $data['detail_kelas']->NAMA_DOSEN);
        $data['detail_kelas']->METODE = explode(';', $data['detail_kelas']->METODE);
        
        $data['detail'] = Kelas::getDetailPertemuan($id_detsem, $kode_kelas);
        
        $html = view('layout.layout_mahasiswa.kehadiran_kelas.ajax.paper_pertemuan', $data)->render();

        return response()->json(['html' => $html]);
    }
}

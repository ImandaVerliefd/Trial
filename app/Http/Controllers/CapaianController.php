<?php

namespace App\Http\Controllers;

use App\Models\Capaian;
use App\Models\Kurikulum;
use App\Models\Prodi;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CapaianController extends Controller
{
    // CAPAIAN FUNCTION
    public function capaianIndex()
    {
        $data['title'] = "Capaian Pembelajaran";
        $data['kurikulum'] = Kurikulum::where(['IS_DELETE' => 0])->get();
        $data['prodi'] = Prodi::whereNull('DELETED_AT')->get();
        $data['script'] = 'layout/layout_admin/capaian/_html_script';
        $data['content_page'] = 'layout/layout_admin/capaian/capaian_index';
        return view('templates/main', $data);
    }

    public function getAllCapaianData(Request $req)
    {
        $orderData = $req->input('order')[0]['column'];
        $orderType = strtoupper($req->input('order')[0]['dir']);
        $valOrder = $req->input('columns')[$orderData]['data'];
        $search = $req->input('search')['value'];
        $start = $req->input('start');
        $perPage = $req->input('length');
        $tipe = $req->input('tipe');
        $prodi = $req->input('prodi');

        $resp = Capaian::get_data_capaian_datatable($orderType, $valOrder, $search, $start, $perPage, $tipe, $prodi);
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;

            $parentData = [];
            foreach (explode(';', $item->KODE_CAPAIAN_PARENT) as $kodeCapParent) {
                $parentData[] = (array)DB::selectOne("
                    SELECT
                        mc.KODE_CAPAIAN,
                        mc.CAPAIAN ,
                        mc.JENIS_CAPAIAN
                    FROM 
                        md_capaian mc 
                    WHERE
                        KODE_CAPAIAN = '$kodeCapParent'
                ");
            }

            $data = array(
                "KODE_CAPAIAN" => $item->KODE_CAPAIAN,
                "CAPAIAN" => $item->CAPAIAN,
                "IS_ACTIVE" => (($item->IS_ACTIVE == 1) ? '<span class="badge bg-success-subtle text-success">Aktif</span>' : '<span class="badge bg-danger-subtle text-danger">Tidak Aktif</span>'),
                "JENIS_CAPAIAN" => $item->JENIS_CAPAIAN
            );

            if ($item->IS_ACTIVE == 1) {
                $extendBtn = '                
                    <button type="button" class="btn btn-secondary" onclick="modalConfirmActive(`' . $item->KODE_CAPAIAN . '`, 0)">
                        <i class="ri-shut-down-line"></i>
                    </button>
                ';
            } else {
                $extendBtn = '                
                    <button type="button" class="btn btn-success" onclick="modalConfirmActive(`' . $item->KODE_CAPAIAN . '`, 1)">
                        <i class="ri-shut-down-line"></i>
                    </button>
                ';
            }

            $parseData = htmlspecialchars(str_replace('\t', ' ', json_encode((array)$item)), ENT_QUOTES, 'UTF-8');
            $parseDataParent = htmlspecialchars(str_replace('\t', ' ', json_encode((array)$parentData)), ENT_QUOTES, 'UTF-8');
            $data['ACTION_BUTTON'] = '
                <button type="button" class="btn btn-warning" onclick="openModal(`' . $parseData . '`, `' . $parseDataParent . '`)">
                    <i class="ri-pencil-line"></i>
                </button>
                <button type="button" class="btn btn-danger" onclick="modalConfirmDelete(`' . $item->KODE_CAPAIAN . '`)">
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

    public function changeActiveStatusCapaian(Request $req)
    {
        try {
            $active = $req->input('active');
            DB::table('md_capaian')->where(['KODE_CAPAIAN' => $req->input('kode')])->update(['IS_ACTIVE' => $active]);
            return redirect('capaian')->with('resp_msg', 'Berhasil ' . (($active == 0) ? 'menonaktifkan' : 'mengaktifkan') . ' capaian.');
        } catch (Exception $err) {
            return redirect('capaian')->with('err_msg', 'Gagal merubah status capaian, error ' . $err->getMessage());
        }
    }

    public function deleteCapaian(Request $req)
    {
        try {
            DB::table('tb_capaian_detail')->where(['KODE_CAPAIAN' => $req->input('kode')])->update([
                "IS_DELETE" => date('Y-m-d H:i:s')
            ]);
            DB::table('md_capaian')->where(['KODE_CAPAIAN' => $req->input('kode')])->update([
                "IS_DELETE" => 1
            ]);
            return redirect('capaian')->with('resp_msg', 'Berhasil menghapus capaian.');
        } catch (Exception $err) {
            return redirect('capaian')->with('err_msg', 'Gagal menghapus capaian, error ' . $err->getMessage());
        }
    }

    public function capaianSubmit(Request $req)
    {
        try {
            $capaian = $req->input("capaian");
            $jenisCapaian = $req->input("jenis_capaian");
            $kodeCapaian = (!empty($req->input("kode_capaian")) ? $req->input("kode_capaian") : $this->GenerateUniqChild($jenisCapaian, $capaian . '' . date('Y-m-d H:i:s')));

            $capaianParent = [];
            if (!empty($req->input("capaian_parent"))) {
                foreach ($req->input("capaian_parent") as $capParent) {
                    $capaianParent[] = explode(';', base64_decode($capParent))[0];
                }
            }

            $data = [
                "KODE_CAPAIAN" => $kodeCapaian,
                "CAPAIAN" => $capaian,
                "ID_KURIKULUM" => $req->input("kurikulum_capaian"),
                "ID_PRODI" => $req->input("prodi_capaian"),
                "JENIS_CAPAIAN" => $jenisCapaian,
                "KODE_CAPAIAN_PARENT" => !empty($capaianParent) ? implode(';', $capaianParent) : NULL,
                "LOG_TIME" => date('Y-m-d H:i:s'),
                "IS_ACTIVE" => 1,
                "IS_DELETE" => 0
            ];

            DB::beginTransaction();
            DB::table("md_capaian")->updateOrInsert(["KODE_CAPAIAN" => $kodeCapaian], $data);
            DB::commit();

            return redirect('capaian')->with('resp_msg', 'Berhasil menyimpan capaian');
        } catch (Exception $err) {
            DB::rollBack();

            return redirect('capaian')->with('err_msg', 'Gagal menyimpan capaian, error ' . $err->getMessage());
        }
    }

    // TIPE CAPAIAN FUNCTION
    public function tipeCapaianIndex()
    {
        $data['title'] = "Tipe Capaian Pembelajaran";
        $data['script'] = 'layout/layout_admin/capaian/_html_script';
        $data['content_page'] = 'layout/layout_admin/capaian/capaian_tipe_index';
        return view('templates/main', $data);
    }

    public function getAllTipeCapaianData(Request $req)
    {
        $orderData = $req->input('order')[0]['column'];
        $orderType = strtoupper($req->input('order')[0]['dir']);
        $valOrder = $req->input('columns')[$orderData]['data'];
        $search = $req->input('search')['value'];
        $start = $req->input('start');
        $perPage = $req->input('length');

        $resp = Capaian::get_data_tipe_capaian_datatable($orderType, $valOrder, $search, $start, $perPage);
        $results = $resp['DATA'];
        $Tot = $resp['TOTAL_DATA'];

        $NewData_all = array();
        $counter_all = $start;
        foreach ($results as $item) {
            ++$counter_all;
            $data = array(
                "TIPE_PENILAIAN" => $item->TIPE_PENILAIAN,
                "IS_ACTIVE" => (($item->IS_ACTIVE == 1) ? '<span class="badge bg-success-subtle text-success">Aktif</span>' : '<span class="badge bg-danger-subtle text-danger">Tidak Aktif</span>')
            );

            if ($item->IS_ACTIVE == 1) {
                $extendBtn = '                
                    <button type="button" class="btn btn-secondary" onclick="modalConfirmActiveTipeCapaian(`' . $item->ID_TIPE . '`, 0)">
                        <i class="ri-shut-down-line"></i>
                    </button>
                ';
            } else {
                $extendBtn = '                
                    <button type="button" class="btn btn-success" onclick="modalConfirmActiveTipeCapaian(`' . $item->ID_TIPE . '`, 1)">
                        <i class="ri-shut-down-line"></i>
                    </button>
                ';
            }

            $parseData = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
            $data['ACTION_BUTTON'] = '
                <button type="button" class="btn btn-warning" onclick="openModalTipeCapaian(`' . $parseData . '`)">
                    <i class="ri-pencil-line"></i>
                </button>
                <button type="button" class="btn btn-danger" onclick="modalConfirmDeleteTipeCapaian(`' . $item->ID_TIPE . '`)">
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

    public function changeActiveStatusTipeCapaian(Request $req)
    {
        try {
            $active = $req->input('active');
            DB::table('md_tipe_capaian')->where(['ID_TIPE' => $req->input('kode')])->update(['IS_ACTIVE' => $active]);
            return redirect('tipe-capaian')->with('resp_msg', 'Berhasil ' . (($active == 0) ? 'menonaktifkan' : 'mengaktifkan') . ' sub capaian.');
        } catch (Exception $err) {
            return redirect('tipe-capaian')->with('err_msg', 'Gagal merubah status sub capaian, error ' . $err->getMessage());
        }
    }

    public function tipeCapaianDelete(Request $req)
    {
        try {
            DB::table('md_tipe_capaian')->where(['ID_TIPE' => $req->input('kode')])->delete();
            DB::table('tb_capaian_detail')->where(['ID_TIPE' => $req->input('kode')])->delete();
            return redirect('tipe-capaian')->with('resp_msg', 'Berhasil menghapus sub capaian.');
        } catch (Exception $err) {
            return redirect('tipe-capaian')->with('err_msg', 'Gagal menghapus sub capaian, error ' . $err->getMessage());
        }
    }

    public function tipeCapaianSubmit(Request $req)
    {
        try {
            $idTipe = $req->input("id_tipe");
            $tipePenilaian = $req->input("tipe_penilaian");
            $ket = $req->input("keterangan");

            DB::beginTransaction();
            $data = [
                "TIPE_PENILAIAN" => $tipePenilaian,
                "KETERANGAN" => $ket,
                "LOG_TIME" => date('Y-m-d H:i:s'),
                "IS_ACTIVE" => 1,
                "IS_DELETE" => 0
            ];
            DB::commit();

            DB::table("md_tipe_capaian")->updateOrInsert(["ID_TIPE" => $idTipe], $data);
            return redirect('tipe-capaian')->with('resp_msg', 'Berhasil menyimpan sub capaian');
        } catch (Exception $err) {
            DB::rollBack();

            return redirect('tipe-capaian')->with('err_msg', 'Gagal menyimpan sub capaian, error ' . $err->getMessage());
        }
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

    public function capaianSearch(Request $req)
    {
        $keyword = $req->input('keyword');
        $jenis = (!empty($req->input('jenis')) ? "AND mc.JENIS_CAPAIAN = '" . $req->input('jenis') . "'" : "");
        $kurikulum = (!empty($req->input('kurikulum')) ? "AND mc.ID_KURIKULUM = '" . $req->input('kurikulum') . "'" : "");
        $prodi = (!empty($req->input('id_prodi')) ? "AND mc.ID_PRODI = '" . $req->input('id_prodi') . "'" : "");
        $query = "
            SELECT
                mc.KODE_CAPAIAN,
                mc.CAPAIAN,
                mc.JENIS_CAPAIAN as JENIS
            FROM
                md_capaian mc
            WHERE
                (                    
                    mc.CAPAIAN LIKE '%$keyword%'
                    OR
                    mc.JENIS_CAPAIAN LIKE '%$keyword%'
                )
                AND 
                mc.IS_ACTIVE = 1
                AND
                mc.IS_DELETE = 0
                $jenis
                $kurikulum
                $prodi
        ";

        $dataCapaian = DB::select($query);

        return json_decode(json_encode($dataCapaian), true);
    }

    public function capaianSearchByMapping(Request $req)
    {
        $keyword = $req->input('keyword');
        $jenis = (!empty($req->input('jenis')) ? "AND mc.JENIS_CAPAIAN = '" . $req->input('jenis') . "'" : "");
        $kurikulum = (!empty($req->input('kurikulum')) ? "AND mc.ID_KURIKULUM = '" . $req->input('kurikulum') . "'" : "");
        $matkul = (!empty($req->input('matkul')) ? "AND mcm.KODE_MATKUL = '" . explode(';', base64_decode($req->input('matkul')))[0] . "'" : "");
        
        $query = "
            SELECT
                mc.KODE_CAPAIAN,
                mc.CAPAIAN,
                mc.JENIS_CAPAIAN as JENIS
            FROM
                mapping_capaian_matkul mcm 
            LEFT JOIN md_capaian mc ON 
                mc.KODE_CAPAIAN = mcm.KODE_CAPAIAN 
            WHERE
                (                    
                    mc.CAPAIAN LIKE '%$keyword%'
                    OR
                    mc.JENIS_CAPAIAN LIKE '%$keyword%'
                )
                AND 
                mc.IS_ACTIVE = 1
                AND
                mc.IS_DELETE = 0
                $matkul
                $jenis
                $kurikulum
        ";

        $dataCapaian = DB::select($query);

        return json_decode(json_encode($dataCapaian), true);
    }
}

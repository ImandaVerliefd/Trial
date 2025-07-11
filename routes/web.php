<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', 'AuthController@index');
Route::post('/auth/login', 'AuthController@AuthenticateLogin');
Route::get('/auth/logout', 'AuthController@logout');

Route::middleware(['usersession:1,2,3'])->group(function () {
    Route::get('/dashboard', 'DashboardController@index');

    Route::get('/profile', 'ProfileController@index');
});

Route::middleware(['usersession:1,2'])->group(function () {
    // Route::get('/dashboard', 'DashboardController@index');

    Route::get('/capaian', 'CapaianController@capaianIndex');
    Route::post('/capaian/submit', 'CapaianController@capaianSubmit');
    Route::get('/capaian/delete', 'CapaianController@deleteCapaian');
    Route::get('/capaian/change-status', 'CapaianController@changeActiveStatusCapaian');
    Route::post('/capaian/get-all-data', 'CapaianController@getAllCapaianData');
    Route::post('/capaian/search', 'CapaianController@capaianSearch');
    Route::post('/capaian/search-by-mapping', 'CapaianController@capaianSearchByMapping');

    Route::get('/tipe-capaian', 'CapaianController@tipeCapaianIndex');
    Route::post('/tipe-capaian/submit', 'CapaianController@tipeCapaianSubmit');
    Route::get('/tipe-capaian/delete', 'CapaianController@tipeCapaianDelete');
    Route::get('/tipe-capaian/change-status', 'CapaianController@changeActiveStatusTipeCapaian');
    Route::post('/tipe-capaian/get-all-data', 'CapaianController@getAllTipeCapaianData');

    Route::get('/rps', 'RPSController@capaianDetailIndex');
    Route::get('/rps/form/index', 'RPSController@capaianDetailFormIndex');
    Route::post('/rps/submit', 'RPSController@capaianDetailSubmit');
    Route::get('/rps/form-bobot/index', 'RPSController@capaianDetailBobotFormIndex');
    Route::post('/rps/bobot/submit', 'RPSController@capaianDetailBobotSubmit');
    
    Route::get('/rps/delete', 'RPSController@capaianDetailDelete');
    Route::post('/rps/get-all-data', 'RPSController@getAllDetailCapaianData');
    Route::post('/rps/checking', 'RPSController@checkRPS');
    Route::post('/rps/tipe-capaian/search', 'RPSController@tipeCapaianSearch');
    Route::post('/rps/matkul/search', 'RPSController@matkulSearch');
    Route::get('/rps/generate-pdf', 'RPSController@GeneratePDF');

    Route::get('/feeder', 'FeederController@feederIndex');
    Route::post('/feeder/submit', 'FeederController@feederSubmit');
    Route::get('/feeder/delete', 'FeederController@deleteFeeder');
    Route::post('/feeder/get-all-data', 'FeederController@getAllFeederData');

    Route::get('/dosen', 'DosenController@index');
    Route::post('/dosen/get-all-data', 'DosenController@getAllDosenData');
    Route::post('/dosen/syncronize', 'DosenController@syncronize');

    Route::get('/mahasiswa', 'MahasiswaController@index');
    Route::post('/mahasiswa/get-all-data', 'MahasiswaController@getAllMahasiswaData');
    Route::post('/mahasiswa/syncronize', 'MahasiswaController@syncronize');

    Route::get('/pemetaan-siakad', 'PemetaanSiakadController@PetaSiakadIndex');
    Route::post('/pemetaan-siakad/submit', 'PemetaanSiakadController@PetaSiakadSubmit');
    Route::get('/pemetaan-siakad/delete', 'PemetaanSiakadController@deletePetaSiakad');
    Route::post('/pemetaan-siakad/get-all-data', 'PemetaanSiakadController@getAllPetaSiakadData');

    Route::get('/penjadwalan', 'PenjadwalanController@index');
    Route::post('/penjadwalan/matkul', 'PenjadwalanController@getMatkulData');
    Route::get('/penjadwalan/detail/{id}', 'PenjadwalanController@detail');
    Route::post('/penjadwalan/submit', 'PenjadwalanController@submit');
    Route::post('/penjadwalan/submit-detail-matkul', 'PenjadwalanController@submitDetailMatkul');
    Route::post('/penjadwalan/submit-detail-kelas', 'PenjadwalanController@submitDetailKelas');
    Route::post('/penjadwalan/check-ruangan', 'PenjadwalanController@checkingRuangan');
    Route::post('/penjadwalan/form-ruangan', 'PenjadwalanController@showFormRuangan');
    Route::post('/penjadwalan/form-dosen', 'PenjadwalanController@showFormDosen');

    Route::get('/kurikulum', 'KurikulumController@index');
    Route::post('/kurikulum/submit', 'KurikulumController@submit');
    Route::get('/kurikulum/delete', 'KurikulumController@delete');
    Route::get('/kurikulum/change-status', 'KurikulumController@changeActiveStatus');
    Route::post('/kurikulum/search', 'KurikulumController@kurikulumSearch');
    Route::post('/kurikulum/get-all-data', 'KurikulumController@getAllData');

    Route::get('/ruangan', 'RuanganController@index');
    Route::post('/ruangan/submit', 'RuanganController@submit');
    Route::get('/ruangan/delete', 'RuanganController@delete');
    Route::get('/ruangan/change-status', 'RuanganController@changeActiveStatus');
    Route::post('/ruangan/get-all-data', 'RuanganController@getAllData');

    Route::get('/prodi', 'ProdiController@index');
    Route::post('/prodi/syncronize', 'ProdiController@syncronize');
    Route::post('/prodi/get-all-data', 'ProdiController@getAllData');

    Route::get('/mata-kuliah', 'MatkulController@index');
    Route::post('/mata-kuliah/submit', 'MatkulController@submit');
    Route::get('/mata-kuliah/delete', 'MatkulController@delete');
    Route::get('/mata-kuliah/change-status', 'MatkulController@changeActiveStatus');
    Route::post('/mata-kuliah/get-all-data', 'MatkulController@getAllData');

    Route::get('/paket-mata-kuliah', 'PaketMatkulController@index');
    Route::get('/paket-mata-kuliah/form', 'PaketMatkulController@indexForm');
    Route::post('/paket-mata-kuliah/search', 'PaketMatkulController@paketMatkulsearch');
    Route::post('/paket-mata-kuliah/matkul/search', 'PaketMatkulController@matkulsearch');
    Route::post('/paket-mata-kuliah/submit', 'PaketMatkulController@submit');
    Route::get('/paket-mata-kuliah/delete', 'PaketMatkulController@delete');
    Route::post('/paket-mata-kuliah/get-all-data', 'PaketMatkulController@getAllData');
    Route::get('paket-mata-kuliah/copy-form', 'PaketMatkulController@indexCopyForm');
    Route::post('paket-mata-kuliah/copy', 'PaketMatkulController@copyPaketMatkul');
    Route::get('paket-mata-kuliah/get-matkul-for-copy', 'PaketMatkulController@getPaketMatkulForCopy');


    Route::get('/semester', 'SemesterController@index');
    Route::post('/semester/submit', 'SemesterController@submit');
    Route::get('/semester/delete', 'SemesterController@delete');
    Route::post('/semester/get-all-data', 'SemesterController@getAllData');

    // Route::get('/peta-kurikulum', 'PetaKurikulumController@index');
    Route::get('/peta-kurikulum', 'PetaKurikulumController@index_form');
    Route::post('/peta-kurikulum/submit', 'PetaKurikulumController@submit');
    Route::get('/peta-kurikulum/delete', 'PetaKurikulumController@delete');
    Route::post('/peta-kurikulum/get-all-data', 'PetaKurikulumController@getAllData');
    Route::post('/peta-kurikulum/render-paket-form', 'PetaKurikulumController@renderPaketForm');

    Route::get('/capaian-matkul', 'CapaianMatkulController@index');
    Route::post('/capaian-matkul/submit', 'CapaianMatkulController@submit');
    Route::get('/capaian-matkul/delete', 'CapaianMatkulController@delete');
    Route::post('/capaian-matkul/get-all-data', 'CapaianMatkulController@getAllData');
    Route::post('/capaian-matkul/filtered-cpmk', 'CapaianMatkulController@getFilteredCPMK');

    Route::get('/dosen-wali', 'DosenWaliController@index');
    Route::post('/dosen-wali/submit', 'DosenWaliController@submit');
    Route::post('/dosen-wali/update', 'DosenWaliController@update');
    Route::get('/dosen-wali/delete', 'DosenWaliController@delete');
    Route::post('/dosen-wali/get-all-data', 'DosenWaliController@getAllData');

    Route::get('/kelompok-praktik', 'KelompokController@index');
    Route::post('/kelompok-praktik/submit', 'KelompokController@submit');
    Route::get('/kelompok-praktik/delete', 'KelompokController@delete');
    Route::post('/kelompok-praktik/get-all-data', 'KelompokController@getAllData');
    Route::post('/kelompok-praktik/get-mhs-data', 'KelompokController@getMahasiswaPerwalian');

    Route::get('/penilaian', 'PenilaianController@index');
    Route::post('/penilaian/get-all-matkul', 'PenilaianController@getAllMatkulData');
    Route::get('/penilaian/form/index', 'PenilaianController@penilaianDetailFormIndex');
    Route::post('/penilaian/form/detail', 'PenilaianController@penilaianDetailIndex');
    Route::post('/penilaian/submit', 'PenilaianController@penilaianDetailSubmit');
    Route::post('/penilaian/submit-per-row', 'PenilaianController@penilaianDetailSubmitPerRow');

    Route::get('/rekap-penilaian', 'PenilaianController@rekapIndex');
    Route::post('/rekap-penilaian/get-all-matkul', 'PenilaianController@getAllMatkulDataForRekap');
    Route::get('/rekap-penilaian/detail', 'PenilaianController@rekapDetailIndex');
});

Route::middleware(['usersession:2'])->group(callback: function () {
    Route::get('/mahasiswa-wali', 'MahasiswaWaliController@index');
    Route::post('/mahasiswa-wali/get-all-data', 'MahasiswaWaliController@getAllData');
    Route::get('/mahasiswa-wali/validasi-krs', 'MahasiswaWaliController@detailValidasi');
    Route::post('/mahasiswa-wali/validasi-krs-accept', 'MahasiswaWaliController@validasiAccept');
    Route::post('/mahasiswa-wali/validasi-krs-decline', 'MahasiswaWaliController@validasiDecline');

    Route::get('/jadwal-dosen', 'JadwalDosenController@index');
    Route::post('/jadwal-dosen/get-data-ajaran', 'JadwalDosenController@getDataAjaran');
    Route::post('/jadwal-dosen/get-all-data', 'JadwalDosenController@getAllData');

    Route::get('/kehadiran_kelas', 'KelasDosenController@index');
    Route::post('/kehadiran_kelas/get-all-data', 'KelasDosenController@getAllData');
    Route::get('/kehadiran_kelas/att_class', 'KelasDosenController@getDataAtt');
    Route::post('/kehadiran_kelas/open_class', 'KelasDosenController@openKelas');
    Route::post('/kehadiran_kelas/submit_absen', 'KelasDosenController@submitAbsen');
    Route::post('/kehadiran_kelas/submit_pertemuan', 'KelasDosenController@submitPertemuan');
    Route::post('/kehadiran_kelas/get_history_kehadiran', 'KelasDosenController@getHistory');
    Route::post('/kehadiran_kelas/get_new_kehadiran', 'KelasDosenController@getNewKehadiran');

    Route::post('/kehadiran_kelas/history_class', 'KelasDosenController@getDataKelas');
});

Route::middleware(['usersession:3'])->group(function () {
    Route::get('/krs', 'KRSController@index');
    Route::post('/krs/submit', 'KRSController@submitPerwalian');
    Route::post('/krs/ajax/submit', 'KRSController@submitKelas');

    Route::get('/jadwal-mahasiswa', 'JadwalMahasiswaController@index');

    Route::get('/kehadiran_kuliah', 'KelasMahasiswaController@index');
    Route::post('/kehadiran_kuliah/get_rekapitulasi', 'KelasMahasiswaController@getRekap');
    Route::post('/kehadiran_kuliah/get_kehadiran', 'KelasMahasiswaController@getKehadiran');
    Route::post('/kehadiran_kuliah/get_pertemuan', 'KelasMahasiswaController@getPertemuan');

    Route::get('/ringkasan', 'PerwalianController@index');

    Route::get('/khs', 'KHSController@index');
});

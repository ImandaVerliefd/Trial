<?php

use Illuminate\Support\Facades\Request;
?>
<style>
  html[data-sidenav-size=condensed]:not([data-layout=topnav]) .wrapper .leftside-menu .side-nav .side-nav-item .side-nav-link .long-text {
    visibility: hidden;
  }

  html[data-sidenav-size=condensed]:not([data-layout=topnav]) .wrapper .leftside-menu .side-nav .side-nav-item .long-href {
    height: 50px !important;
  }

  html[data-sidenav-size=condensed]:not([data-layout=topnav]) .wrapper .leftside-menu .side-nav .side-nav-item:hover .long-href {
    height: auto !important;
  }

  html[data-sidenav-size=condensed]:not([data-layout=topnav]) .wrapper .leftside-menu .side-nav .side-nav-item:hover .side-nav-link .long-text {
    visibility: visible;
  }

  html[data-sidenav-size=condensed]:not([data-layout=topnav]) .wrapper .leftside-menu .side-nav .side-nav-item:hover .capaian {
    width: 18rem;
  }

  html[data-sidenav-size=condensed]:not([data-layout=topnav]) .wrapper .leftside-menu .side-nav .side-nav-item:hover>.collapse>ul {
    width: 13.5rem;
  }
</style>

<!-- ========== Left Sidebar Start ========== -->
<div class="leftside-menu">
  <!-- Brand Logo Light -->
  <a href="index.php" class="logo logo-light">
    <span class="logo-lg">
      <img src="{{ asset('assets') }}/images/logo-lg.png" alt="logo" />
    </span>
    <span class="logo-sm">
      <img src="{{ asset('assets') }}/images/logo.png" alt="small logo" />
    </span>
  </a>

  <!-- Brand Logo Dark -->
  <a href="index.php" class="logo logo-dark">
    <span class="logo-lg">
      <img src="{{ asset('assets') }}/images/logo-lg.png" alt="dark logo" />
    </span>
    <span class="logo-sm">
      <img src="{{ asset('assets') }}/images/logo.png" alt="small logo" />
    </span>
  </a>

  <!-- Sidebar Hover Menu Toggle Button -->
  <div
    class="button-sm-hover"
    data-bs-toggle="tooltip"
    data-bs-placement="right"
    title="Show Full Sidebar">
    <i class="ri-checkbox-blank-circle-line align-middle"></i>
  </div>

  <!-- Full Sidebar Menu Close Button -->
  <div class="button-close-fullsidebar">
    <i class="ri-close-fill align-middle"></i>
  </div>

  <!-- Sidebar -left -->
  <div class="h-100" id="leftside-menu-container" data-simplebar>
    <!-- Leftbar User -->
    <!-- <div class="leftbar-user">
      <a href="pages-profile.php">
        <img
          src="{{ asset('assets') }}/images/users/avatar-1.jpg"
          alt="user-image"
          height="42"
          class="rounded-circle shadow-sm" />
        <span class="leftbar-user-name mt-2">Tosha Minner</span>
      </a>
    </div> -->

    <!--- Sidemenu -->
    <ul class="side-nav">
      <li class="side-nav-title">Navigation</li>

      <li class="side-nav-item">
        <a href="<?= url('dashboard') ?>" class="side-nav-link">
          <i class="ri-home-4-line"></i>
          <span> Dashboards </span>
        </a>
      </li>

      <?php if (session('user')[0]['id_role'] == 1) :  ?>

        <!-- <li class="side-nav-title">Master Pembelajaran</li> -->

        <li class="side-nav-item <?= Request::is('feeder*') ? 'menuitem-active' : '' ?>">
          <a href="<?= url('feeder') ?>" class="side-nav-link long-href <?= Request::is('feeder*') ? 'active' : '' ?>" style="display:flex">
            <div style="width: fit-content; padding-top: 1px"><i class="ri-profile-line"></i></div>
            <div class="long-text" style="width: fit-content;white-space: normal;padding-left: 4px;"> NeoFeeder </div>
          </a>
        </li>

        <li class="side-nav-item <?= Request::is('pemetaan-siakad*') ? 'menuitem-active' : '' ?>">
          <a href="<?= url('pemetaan-siakad') ?>" class="side-nav-link long-href <?= Request::is('pemetaan-siakad*') ? 'active' : '' ?>" style="display:flex">
            <div style="width: fit-content; padding-top: 1px"><i class="ri-profile-line"></i></div>
            <div class="long-text" style="width: fit-content;white-space: normal;padding-left: 4px;"> Aspek Penilaian </div>
          </a>
        </li>

        <li class="side-nav-item <?= Request::is('paket-mata-kuliah*') ? 'menuitem-active' : '' ?>">
          <a href="<?= url('paket-mata-kuliah') ?>" class="side-nav-link long-href <?= Request::is('paket-mata-kuliah*') ? 'active' : '' ?>" style="display:flex">
            <div style="width: fit-content; padding-top: 1px"><i class="ri-stack-line"></i></div>
            <div class="long-text" style="width: fit-content;white-space: normal;padding-left: 4px;"> Paket Mata Kuliah </div>
          </a>
        </li>

        <li class="side-nav-item <?= (Request::is('capaian*') || Request::is('peta-kurikulum*') || Request::is('rps*')) ? 'menuitem-active' : '' ?>">
          <a
            data-bs-toggle="collapse"
            href="#MappingPages"
            aria-expanded="false"
            aria-controls="MappingPages"
            id="Mapping-collapse"
            class="side-nav-link Mapping">
            <i class="ri-pages-line"></i>
            <span> Mapping RPS</span>
            <span class="menu-arrow"></span>
          </a>
          <div class="collapse" id="MappingPages">
            <ul class="side-nav-second-level">
              <li class="<?= Request::is('capaian', 'capaian/*') ? 'menuitem-active' : '' ?>">
                <a href="<?= url('capaian') ?>">Capaian Pembelajaran</a>
              </li>
              <li class="<?= Request::is('capaian-matkul*') ? 'menuitem-active' : '' ?>">
                <a href="<?= url('capaian-matkul') ?>">Capaian Mata Kuliah <!-- <br> (Matkul - Capaian) --></a>
              </li>
              <li class="<?= Request::is('rps*') ? 'menuitem-active' : '' ?>">
                <a href="<?= url('rps') ?>">Rancangan Pembelajaran <!-- <br> (Sub CPMK - Siakad) --></a>
              </li>
              <li class="<?= Request::is('peta-kurikulum*') ? 'menuitem-active' : '' ?>">
                <a href="<?= url('peta-kurikulum') ?>">Peta Kurikulum <!-- <br> (Matkul - Kurikulum - <br> Semester) --></a>
              </li>
            </ul>
          </div>
        </li>

        <li class="side-nav-item <?= Request::is('penjadwalan*') ? 'menuitem-active' : '' ?>">
          <a href="<?= url('penjadwalan') ?>" class="side-nav-link long-href <?= Request::is('penjadwalan*') ? 'active' : '' ?>" style="display:flex">
            <div style="width: fit-content; padding-top: 1px"><i class="ri-stack-line"></i></div>
            <div class="long-text" style="width: fit-content;white-space: normal;padding-left: 4px;"> Penjadwalan </div>
          </a>
        </li>

        <li class="side-nav-item <?= Request::is('dosen-wali*') ? 'menuitem-active' : '' ?>">
          <a href="<?= url('dosen-wali') ?>" class="side-nav-link long-href <?= Request::is('dosen-wali*') ? 'active' : '' ?>" style="display:flex">
            <div style="width: fit-content; padding-top: 1px"><i class="ri-user-voice-line"></i></div>
            <div class="long-text" style="width: fit-content;white-space: normal;padding-left: 4px;"> Dosen Wali </div>
          </a>
        </li>

        <li class="side-nav-item <?= Request::is('kelompok-praktik*') ? 'menuitem-active' : '' ?>">
          <a href="<?= url('kelompok-praktik') ?>" class="side-nav-link long-href <?= Request::is('kelompok-praktik*') ? 'active' : '' ?>" style="display:flex">
            <div style="width: fit-content; padding-top: 1px"><i class="ri-group-line"></i></div>
            <div class="long-text" style="width: fit-content;white-space: normal;padding-left: 4px;"> Kelompok Praktek </div>
          </a>
        </li>

        <li class="side-nav-title">Master Data</li>

        <li class="side-nav-item <?= Request::is('dosen') ? 'menuitem-active' : '' ?>">
          <a href="<?= url('dosen') ?>" class="side-nav-link long-href <?= Request::is('dosen*') ? 'active' : '' ?>" style="display:flex">
            <div style="width: fit-content; padding-top: 1px"><i class="ri-user-voice-line"></i></div>
            <div class="long-text" style="width: fit-content;white-space: normal;padding-left: 4px;"> Dosen </div>
          </a>
        </li>

        <li class="side-nav-item <?= Request::is('mahasiswa*') ? 'menuitem-active' : '' ?>">
          <a href="<?= url('mahasiswa') ?>" class="side-nav-link long-href <?= Request::is('mahasiswa*') ? 'active' : '' ?>" style="display:flex">
            <div style="width: fit-content; padding-top: 1px"><i class="ri-user-voice-line"></i></div>
            <div class="long-text" style="width: fit-content;white-space: normal;padding-left: 4px;"> Mahasiswa </div>
          </a>
        </li>

        <li class="side-nav-item">
          <a href="<?= url('kurikulum') ?>" class="side-nav-link">
            <i class="ri-calendar-2-line"></i>
            <span> Kurikulum </span>
          </a>
        </li>

        <!-- <li class="side-nav-item">
          <a href="<?= url('penjadwalan') ?>" class="side-nav-link">
            <i class="ri-calendar-line"></i>
            <span> Penjadwalan </span>
          </a>
        </li> -->

        <li class="side-nav-item">
          <a href="<?= url('ruangan') ?>" class="side-nav-link">
            <i class="ri-home-office-line"></i>
            <span> Ruangan </span>
          </a>
        </li>

        <li class="side-nav-item">
          <a href="<?= url('mata-kuliah') ?>" class="side-nav-link">
            <i class="ri-honour-line"></i>
            <span> Mata Kuliah </span>
          </a>
        </li>

        <li class="side-nav-item">
          <a href="<?= url('semester') ?>" class="side-nav-link">
            <i class="ri-honour-line"></i>
            <span> Semester </span>
          </a>
        </li>

        <li class="side-nav-item">
          <a href="<?= url('prodi') ?>" class="side-nav-link">
            <i class="ri-pass-pending-line"></i>
            <span> Prodi </span>
          </a>
        </li>

      <?php endif; ?>

      <?php if (session('user')[0]['id_role'] == 2) :  ?>

        <li class="side-nav-title">Master Pembelajaran</li>

        <li class="side-nav-item <?= (Request::is('capaian*') || Request::is('penjadwalan*') || Request::is('peta-kurikulum*') || Request::is('rps*')) ? 'menuitem-active' : '' ?>">
          <a
            data-bs-toggle="collapse"
            href="#MappingPages"
            aria-expanded="false"
            aria-controls="MappingPages"
            id="Mapping-collapse"
            class="side-nav-link Mapping">
            <i class="ri-pages-line"></i>
            <span> Mapping RPS</span>
            <span class="menu-arrow"></span>
          </a>
          <div class="collapse" id="MappingPages">
            <ul class="side-nav-second-level">
              <li class="<?= Request::is('capaian', 'capaian/*') ? 'menuitem-active' : '' ?>">
                <a href="<?= url('capaian') ?>">Capaian Pembelajaran</a>
              </li>
              <li class="<?= Request::is('capaian-matkul*') ? 'menuitem-active' : '' ?>">
                <a href="<?= url('capaian-matkul') ?>">Capaian Mata Kuliah <!-- <br> (Matkul - Capaian) --></a>
              </li>
              <li class="<?= Request::is('rps*') ? 'menuitem-active' : '' ?>">
                <a href="<?= url('rps') ?>">Rancangan Pembelajaran <!-- <br> (Sub CPMK - Siakad) --></a>
              </li>
              <li class="<?= Request::is('peta-kurikulum*') ? 'menuitem-active' : '' ?>">
                <a href="<?= url('peta-kurikulum') ?>">Peta Kurikulum <!-- <br> (Matkul - Kurikulum - <br> Semester) --></a>
              </li>
              <!-- <li class="<?= Request::is('penjadwalan*') ? 'menuitem-active' : '' ?>">
                <a href="<?= url('penjadwalan') ?>">Penjadwalan <br> (Sub CPMK - Dosen)</a>
              </li> -->
            </ul>
          </div>
        </li>

        <li class="side-nav-item <?= Request::is('mahasiswa-wali*') ? 'menuitem-active' : '' ?>">
          <a href="<?= url('mahasiswa-wali') ?>" class="side-nav-link long-href <?= Request::is('mahasiswa-wali*') ? 'active' : '' ?>" style="display:flex">
            <div style="width: fit-content; padding-top: 1px"><i class="ri-user-voice-line"></i></div>
            <div class="long-text" style="width: fit-content;white-space: normal;padding-left: 4px;"> Mahasiswa Wali </div>
          </a>
        </li>
        <li class="side-nav-item <?= Request::is('jadwal-dosen*') ? 'menuitem-active' : '' ?>">
          <a href="<?= url('jadwal-dosen') ?>" class="side-nav-link long-href <?= Request::is('jadwal-dosen*') ? 'active' : '' ?>" style="display:flex">
            <div style="width: fit-content; padding-top: 1px"><i class="ri-calendar-event-fill"></i></div>
            <div class="long-text" style="width: fit-content;white-space: normal;padding-left: 4px;"> Jadwal </div>
          </a>
        </li>
        <li class="side-nav-item <?= Request::is('kehadiran_kelas*') ? 'menuitem-active' : '' ?>">
          <a href="<?= url('kehadiran_kelas') ?>" class="side-nav-link long-href <?= Request::is('kehadiran_kelas*') ? 'active' : '' ?>" style="display:flex">
            <div style="width: fit-content; padding-top: 1px"><i class="ri-notification-2-fill"></i></div>
            <div class="long-text" style="width: fit-content;white-space: normal;padding-left: 4px;"> Kehadiran Kelas </div>
          </a>
        </li>
        <li class="side-nav-item <?= Request::is('penilaian*') ? 'menuitem-active' : '' ?>">
          <a href="<?= url('penilaian') ?>" class="side-nav-link long-href <?= Request::is('penilaian*') ? 'active' : '' ?>" style="display:flex">
            <div style="width: fit-content; padding-top: 1px"><i class="ri-line-chart-line"></i></div>
            <div class="long-text" style="width: fit-content;white-space: normal;padding-left: 4px;"> Penilaian </div>
          </a>
        </li>

      <?php endif; ?>

      <?php if (session('user')[0]['id_role'] == 3) :  ?>
        <li class="side-nav-item">
          <a href="<?= url('/krs') ?>" class="side-nav-link">
            <i class="ri-home-4-line"></i>
            <span> Perwalian </span>
          </a>
        </li>
        <li class="side-nav-item">
          <a href="<?= url('/khs') ?>" class="side-nav-link">
            <i class="ri-home-4-line"></i>
            <span> KHS </span>
          </a>
        </li>
        <li class="side-nav-item">
          <a href="<?= url('/Dosen_Wali') ?>" class="side-nav-link">
            <i class="ri-home-4-line"></i>
            <span> Profil Dosen Wali </span>
          </a>
        </li>
        <li class="side-nav-item">
          <a href="<?= url('/kehadiran_kuliah') ?>" class="side-nav-link">
            <i class="ri-home-4-line"></i>
            <span> Kehadiran Kuliah </span>
          </a>
        </li>
        <li class="side-nav-item">
          <a href="<?= url('/jadwal-mahasiswa') ?>" class="side-nav-link">
            <i class="ri-home-4-line"></i>
            <span> Jadwal </span>
          </a>
        </li>
      <?php endif; ?>

    </ul>
    <!--- End Sidemenu -->

    <div class="clearfix"></div>
  </div>
</div>
<!-- ========== Left Sidebar End ========== -->
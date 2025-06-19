<?php

use Illuminate\Support\Facades\Request;
?>
<!-- Main Script -->
<script>
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger me-2"
        },
        buttonsStyling: false
    });

    $(document).ready(function() {
        filterData()
    });
</script>
<script src="//cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>

<script>
    let originalContent = $(".col-8").html();
    let qrcode;
    let countdownTime = 10;
    let countdownInterval;
    let qrInterval;
    let id_kehadiran = "<?= isset($detail_kelas->ID_KEHADIRAN) ? $detail_kelas->ID_KEHADIRAN : '' ?>";
    let jml_pertemuan = <?= isset($detail_kelas->JUMLAH_PERTEMUAN) ? (int) $detail_kelas->JUMLAH_PERTEMUAN : 0 ?>;

    function printDiv(divId) {
        const dateElement = document.getElementById('print-date');
        const now = new Date();
        const formatter = new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        dateElement.innerText = formatter.format(now);

        $('#' + divId).printThis({
            importCSS: true,
            importStyle: true,
            pageTitle: "Berita Acara"
        });
    }


    function filterData() {
        var element = $('#basic-datatable')
        var totPagesLoad = 5
        var dataUrl = "<?= url('kehadiran_kelas/get-all-data') ?>"
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
        }
        var dataColumn = [{
                data: 'PERIODE'
            },
            {
                data: 'KODE_MATKUL'
            },
            {
                data: 'NAMA_MATKUL'
            },
            {
                data: 'NAMA_DOSEN'
            },
            {
                data: 'NAMA_KELAS'
            },
            {
                data: 'ACTION_BUTTON'
            },
        ]

        processingDataTable(element, totPagesLoad, dataUrl, dataBody, dataColumn)
    }

    $('#select-detsem').on('change', function() {
        $('#history-content').empty();
        let selectedValue = $(this).val();
        let [id_detsem, kode_kelas] = selectedValue.split(';');

        $.ajax({
            url: '/kehadiran_kelas/get_history_kehadiran',
            method: 'POST',
            data: {
                id_detsem: id_detsem,
                kode_kelas: kode_kelas,
                _token: '{{ csrf_token() }}'
            },
            beforeSend: function() {
                $('#history-content').html(
                    '<div class="text-center my-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>'
                );
            },
            success: function(response) {
                $('#history-content').html(response.html);
            },
            error: function(xhr) {
                console.error('Gagal:', xhr.responseText);
            }
        });
    });

    document.getElementById('btnMulai').addEventListener('click', function() {
        $('.status-select').each(function() {
            $(this).val('0')
        });

        document.getElementById('btnMulai').style.display = "none";
        document.getElementById('btnQR').style.display = "inline-block";
        document.getElementById('btnAkhiri').style.display = "inline-block";
        document.querySelector("th.action_bar").style.display = "table-cell";
        document.querySelectorAll(".action_bar").forEach(function(el) {
            el.style.display = "table-cell";
        });

        $.ajax({
            url: "<?= url('kehadiran_kelas/open_class') ?>",
            method: 'POST',
            data: {
                KODE_KELAS: "<?= isset($detail_kelas->KODE_KELAS) ? $detail_kelas->KODE_KELAS : '' ?>",
                ID_DETSEM: "<?= isset($detail_kelas->ID_DETSEM) ? $detail_kelas->ID_DETSEM : '' ?>"
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: function(response, status, xhr) {
                $('#date_pertemuan').text(response.start_kelas + ' - Sekarang');

                document.querySelectorAll(".status-select").forEach(function(selectEl) {
                    selectEl.setAttribute("data-id-kehadiran", response.id_kehadiran);
                });

                jml_pertemuan = response.jml_pertemuan;
                id_kehadiran = response.id_kehadiran;
            },
            error: function(xhr, status, error) {
                console.error("Error:", error);
            }
        });
    });

    $('.status-select').on('change', function() {
        let status_kehadiran = $(this).val();
        let kode_mahasiswa = $(this).attr('data-kode-mahasiswa');
        let id_kehadiran = $(this).attr('data-id-kehadiran');

        $.ajax({
            url: "<?= url('kehadiran_kelas/submit_absen') ?>",
            type: 'POST',
            data: {
                status_kehadiran: status_kehadiran,
                kode_mahasiswa: kode_mahasiswa,
                id_kehadiran: id_kehadiran,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: function(response) {},
            error: function(xhr, status, error) {
                alert('Terjadi kesalahan: ' + error);
            }
        });
    });

    function submitStatus() {
        $.ajax({
            url: "<?= url('kehadiran_kelas/submit_absen') ?>",
            method: 'POST',
            data: {
                ID_KEHADIRAN: id_kehadiran
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: function(response, status, xhr) {

            },
            error: function(xhr, status, error) {
                console.error("Error:", error);
            }
        });
    }

    $("#btnAkhiri").click(function() {
        let currentDate = new Date().toISOString().split("T")[0];

        let optionHtml = '<option value="" disabled selected>--Pilih Pertemuan--</option>';
        for (let i = 1; i <= jml_pertemuan; i++) {
            optionHtml += `<option value="${i}">Pertemuan Ke-${i}</option>`;
        }

        $(".col-8").html(`
            <div class="card">
                <div class="card-body">
                    <h4>Form Akhiri Kelas</h4>
                    <form id="form-submit" action="<?= url('kehadiran_kelas/submit_pertemuan') ?>" method="POST">
                        @csrf
                        <input type="hidden" name="id_kehadiran" value="${id_kehadiran}">
                        <div class="mb-3">
                            <label for="tanggal_pertemuan" class="form-label">Tanggal<small
                                    class="text-danger">*</small></label>
                            <input class="form-control date-picker" type="date" id="tanggal" name="tanggal"
                                value="${currentDate}" required>
                        </div>
                        <div class="mb-3">
                            <label for="materi" class="form-label">Materi Bahasan<small class="text-danger">*</small></label>
                            <textarea class="form-control" id="materi" name="materi" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="select2-pembelajaran">Metode Pembelajaran<small
                                    class="text-danger">*</small></label>
                            <select class="form-control select2" name="metode_pembelajaran" id="select2-pembelajaran" required>
                                <option value="" disabled selected>--Pilih Metode Pembelajaran--</option>
                                <option value="Diskusi Kelompok">Diskusi Kelompok</option>
                                <option value="Simulasi">Simulasi</option>
                                <option value="Studi Kasus">Studi Kasus</option>
                                <option value="Pembelajaran Kooperatif">Pembelajaran Kooperatif</option>
                                <option value="Pembelajaran Berbasis Proyek">Pembelajaran Berbasis Proyek</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="select2-pelaksanaan">Metode Pelaksanaan<small
                                    class="text-danger">*</small></label>
                            <select class="form-control select2" name="metode_pelaksanaan" id="select2-pelaksanaan" required>
                                <option value="" disabled selected>-- Pilih Metode Pelaksanaan--</option>
                                <option value="Luring">Luring</option>
                                <option value="Daring-Synkronus">Daring-Synkronus</option>
                                <option value="Daring-Asinkronus">Daring-Asinkronus</option>
                                <option value="Hybrid">Hybrid</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="catatan" class="form-label">Catatan Akhir</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="3"></textarea>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-primary " onclick="submitForm()">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        `);
    });

    function handleHistoryClick(element) {
        const idDetsem = element.getAttribute('data-id-detsem');
        const kodeKelas = element.getAttribute('data-kode-kelas');

        $('#history-kelas-tab').tab('show');
        $('#id_detsem_input').val(idDetsem);
        $('#kode_kelas_input').val(kodeKelas);
        $('#select-detsem').val(idDetsem + ';' + kodeKelas).trigger('change');
    }


    $(document).on("click", "#btnKembali", function() {
        $(".col-8").html(originalContent);
    });

    function randomDNASequence() {
        const now = new Date();
        const formattedDateTime =
            now.getFullYear() + "-" +
            String(now.getMonth() + 1).padStart(2, "0") + "-" +
            String(now.getDate()).padStart(2, "0") + " " +
            String(now.getHours()).padStart(2, "0") + ":" +
            String(now.getMinutes()).padStart(2, "0") + ":" +
            String(now.getSeconds()).padStart(2, "0");

        const rawLink = `https://siakad.com/absen?absen_status=1&tgl_absen=${formattedDateTime}`;
        const base64Link = btoa(rawLink);

        makeCode(base64Link);
        resetCountdown();
    }

    function makeCode(text) {
        if (qrcode) {
            document.getElementById("qrcode").innerHTML = ""; // clear previous
            qrcode = new QRCode("qrcode");
        }
        qrcode.makeCode(text);
    }

    function resetCountdown() {
        clearInterval(countdownInterval);
        let timeLeft = countdownTime;

        countdownInterval = setInterval(() => {
            timeLeft--;
            1
            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
            }
        }, 1000);
    }

    function QrModal() {
        const modal = new bootstrap.Modal(document.getElementById('modalQR'));
        modal.show();

        qrcode = new QRCode("qrcode");
        randomDNASequence();
        qrInterval = setInterval(randomDNASequence, 10000);
    }

    document.getElementById('modalQR').addEventListener('hidden.bs.modal', () => {
        clearInterval(qrInterval);
        clearInterval(countdownInterval);
    });

    function onModalClose() {
        $.ajax({
            url: '/kehadiran_kelas/get_new_kehadiran',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {

                // foreach status kehadiran change 
            },
            error: function(xhr) {
                console.error('Gagal:', xhr.responseText);
            }
        });
    }

    function submitForm() {
        var isValid = true;
        var form = $('#form-submit')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            isValid = false;
            return false;
        }

        if (isValid) {
            Swal.fire({
                title: "Saving...",
                html: "Please wait, the system is still saving...",
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            form.submit();
        }
    }
</script>

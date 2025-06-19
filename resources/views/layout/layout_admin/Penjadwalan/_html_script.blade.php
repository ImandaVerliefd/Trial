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

        var savedProdi = localStorage.getItem("prodi")
        if (savedProdi) {
            $('.nav-tabs .nav-link[data-prodi="' + savedProdi + '"]').addClass("active");
        } else {
            $('.nav-tabs .nav-link').first().addClass("active");
        }

        $(".nav-tabs .nav-link").click(function() {
            let idProdi = $(this).data("prodi"); // Get tab name from data attribute
            localStorage.setItem("prodi", idProdi); // Save to Local Storage
            $('.nav-tabs .nav-link[data-prodi="' + idProdi + '"]').addClass("active");
        });

        var prodi = $(".nav-tabs .nav-link.active").attr("data-prodi");
        filterData(prodi)
    });
</script>

<!-- Script Capaian -->
<script>
    function reRenderTable() {
        const table = $('#basic-datatable');
        if ($.fn.DataTable.isDataTable(table)) {
            table.DataTable().clear().destroy();
        }

        var prodi = $(".nav-tabs .nav-link.active").attr("data-prodi");
        filterData(prodi)
    }

    function filterData(prodi) {
        var element = $('#basic-datatable')
        var totPagesLoad = 5
        var dataUrl = "<?= url('penjadwalan/matkul') ?>"
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
            'prodi': prodi
        }
        var dataColumn = [{
                data: 'KODE_MATKUL'
            },
            {
                data: 'KURIKULUM'
            },
            {
                data: 'TAHUN_AJAR'
            },
            {
                data: 'DOSEN_PENGAMPU'
            },
            {
                data: 'SEMESTER'
            },
            {
                data: 'IS_ACTIVE'
            },
            {
                data: 'ACTION_BUTTON'
            }
        ]

        processingDataTable(element, totPagesLoad, dataUrl, dataBody, dataColumn)
    }

    function showFormMatkul() {
        let bodyData = {
            idDetsem: '<?= $detail_matkul->ID_DETSEM ?? '' ?>',
            kodeKelas: $('#kode_kelas').val()
        }
        renderForm('<?= url('/penjadwalan/form-ruangan') ?>', '#matkul-jadwal-container', bodyData)
    }

    function showFormDosen() {
        let bodyData = {
            idDetsem: '<?= $detail_matkul->ID_DETSEM ?? '' ?>',
            kodeKelas: $('#kode_kelas_dosen').val()
        }
        renderForm('<?= url('/penjadwalan/form-dosen') ?>', '#dosen-jadwal-container', bodyData)
    }

    function renderForm(URLs, containerID, bodyData) {
        $.ajax({
            url: URLs,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: bodyData,
            beforeSend: function() {
                $(containerID).html(`
                    <div class="w-100 d-flex justify-content-center">
                        <svg version="1.1" id="L9" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" enable-background="new 0 0 0 0" xml:space="preserve" style="width: 120px; ">
                            <path fill="#3f60d0" d="M73,50c0-12.7-10.3-23-23-23S27,37.3,27,50 M30.9,50c0-10.5,8.5-19.1,19.1-19.1S69.1,39.5,69.1,50">
                                <animateTransform attributeName="transform" attributeType="XML" type="rotate" dur="1s" from="0 50 50" to="360 50 50" repeatCount="indefinite"></animateTransform>
                            </path>
                        </svg>
                    </div>
                `);
            },
            success: function(response) {
                $(containerID).html(response);
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
            }
        });
    }

    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(event) {
        var activeTab = $(event.target).attr('href');

        $('.info_detail_kelas').html('')

        if (activeTab == '#dosen') {
            var klsActive = JSON.parse('<?= !empty($list_kelas_active_now) ? json_encode((array)$list_kelas_active_now) : json_encode([]) ?>');

            if (klsActive.length > 0) {
                $.each(klsActive, function(index, dataKls) {
                    let hariHtml = '';

                    $.each(dataKls.HARI.split(';'), function(subInd, hariMatkul) {
                        let jamMulai = dataKls.JAM_MULAI.split(';')[subInd]
                        let jamSelesai = dataKls.JAM_SELESAI.split(';')[subInd]
                        let kapasitas = dataKls.KAPASITAS_RUANGAN.split(';')[subInd]
                        let namaRuang = dataKls.NAMA_RUANGAN.split(';')[subInd]
                        let sks = dataKls.SKS_MATKUL.split(';')[subInd]

                        hariHtml += `
                            <div class="col-6">
                                <div class="mb-1">${hariMatkul} | ${jamMulai} - ${jamSelesai}</div>
                                <div class="mb-1">${namaRuang}</div>
                                <div class="mb-1">Kapasitas ${kapasitas} Orang</div>
                                <div class="mb-1">${sks} SKS</div>
                            </div>
                        `;
                    });

                    $('.info_detail_kelas').append(`
                        <div class="card mb-2">
                            <div class="card-body">
                                <div>
                                    <h5 class="mb-1">${dataKls.NAMA_KELAS}</h5>
                                    <div class="row">
                                        ${hariHtml}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                });
            }
        }
    });
</script>

<style>
    .vr {
        width: 2px;
        background-color: #ccc;
        height: 100%;
        margin: 0 10px;
    }
</style>

<script>
    var indexKelas = 0
    var rawDetKelas = JSON.parse('<?= !empty($list_kelas) ? json_encode((array)$list_kelas) : json_encode([]) ?>');

    if (rawDetKelas.length > 0) {
        addExistKelas(rawDetKelas);
    } else {
        addKelas();
    }

    function addKelas() {
        $('.sub-container-kelas').append(`
            <input type="hidden" name="kode_kelas[${indexKelas}]" id="kode_kelas_${indexKelas}">
            <input type="hidden" name="is_delete[${indexKelas}]" id="is_delete_${indexKelas}" value="0">
            <div id="sub-container-kelas-${indexKelas}">
                <div class="row justify-content-sm-between">
                    <div class="w-100">
                        <div class="w-100 d-flex justify-content-between align-items-end mb-1">
                            <label class="form-check-label" for="task1">
                                Masukkan Nama kelas
                            </label>
                            <button type="button" class="btn btn-danger" onclick="hapusKelas(${indexKelas})">
                                Hapus Kelas
                            </button>
                        </div>
                        <input type="text" name="nama_kelas[${indexKelas}]" id="nama_kelas_${indexKelas}" class="form-control" required>
                    </div>
                </div>
                <hr>
            </div>
        `)

        indexKelas++
    }

    function addExistKelas(rawDetKelas) {
        $.each(rawDetKelas, function(indexEach, dataReal) {
            addKelas()
            
            $(`input[name="kode_kelas[${indexEach}]"]`).val(dataReal.KODE_KELAS)
            $(`input[name="nama_kelas[${indexEach}]"]`).val(dataReal.NAMA_KELAS)
        })
    }

    function hapusKelas(index) {
        $(`input[name="is_delete[${index}]"]`).val('1')
        $(`#sub-container-kelas-${index}`).remove()
    }

    function submitFormKelas() {
        var isValid = true;
        var form = $('#form-submit-kelas')[0];
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
<?php

use Illuminate\Support\Facades\Request;
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.min.js"></script>

<!-- Main Script -->
<script>
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger me-2"
        },
        buttonsStyling: false
    });

    var detailCapaian = '<?= !empty($detailCapaian) ? json_encode($detailCapaian) : '' ?>'
    var dataCapSecond = '<?= !empty($dataCapSecond) ? json_encode($dataCapSecond) : '' ?>'
    
    // ADDED: Fungsi untuk menghitung dan memperbarui total bobot Sub-CPMK
    function updateTotalBobotSubCPMK() {
        var total = 0;
        $('input.bobot-subcpmk-input').each(function() {
            total += parseFloat($(this).val()) || 0;
        });
        
        // Memperbarui teks total
        $('#total-bobot-subcpmk').text(total.toFixed(2) + '%');
        
        // Memberi warna hijau jika total 100, selain itu merah
        if (total === 100) {
            $('#total-bobot-subcpmk').css('color', 'green');
        } else {
            $('#total-bobot-subcpmk').css('color', 'red');
        }
    }


    $(document).ready(function() {
        <?= (Request::is('rps*')) ? "$('#MappingPages').collapse('show')" : ''; ?>

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
        filterDataDetailCapaian(prodi)
        if (detailCapaian) {
            addExistingDataDetailCapaian(detailCapaian, dataCapSecond)
        }
        
        // ADDED: Panggil fungsi kalkulasi saat halaman dimuat
        updateTotalBobotSubCPMK();

        // ADDED: Event listener untuk menghitung ulang saat ada perubahan pada input
        $('.card-body').on('input', '.bobot-subcpmk-input', function() {
            updateTotalBobotSubCPMK();
        });
    });
</script>

<!-- Script RPS -->
<script>
    $('#select2-matkul').select2({
        minimumInputLength: 3,
        minimumResultsForSearch: 1,
        ajax: {
            url: "<?= url('rps/matkul/search') ?>",
            dataType: "json",
            type: "POST",
            data: function(params) {
                var queryParameters = {
                    'keyword': params.term,
                    '_token': $('meta[name="csrf-token"]').attr('content'),
                }
                return queryParameters;
            },
            processResults: function(data) {
                return {
                    results: $.map(data, function(item) {
                        var KODE_MATKUL = item.KODE_MATKUL
                        var NAMA_MATKUL = item.NAMA_MATKUL
                        var ID_KURIKULUM = item.ID_KURIKULUM
                        var KURIKULUM = item.KURIKULUM
                        var JUMLAH_PERTEMUAN = item.JUMLAH_PERTEMUAN

                        return {
                            text: NAMA_MATKUL + ' (' + KODE_MATKUL + ') ' + KURIKULUM,
                            id: btoa(KODE_MATKUL + ';' + NAMA_MATKUL + ';' + JUMLAH_PERTEMUAN + ';' + ID_KURIKULUM)
                        }
                    })
                };
            }
        }
    });

    var indForm = 0
    var containerDetailCapaian = $('.container-form-persentase')
    var containerTabBtn = $('#tab-btn')
    var containerTabContent = $('#tab-content')
    var dataMatkul = ''
    var idSemester = ''
    var kodeCapaian = ''

    function reRenderTable() {
        const table = $('#basic-datatable-rps');
        if ($.fn.DataTable.isDataTable(table)) {
            table.DataTable().clear().destroy();
        }

        var prodi = $(".nav-tabs .nav-link.active").attr("data-prodi");
        filterDataDetailCapaian(prodi)
    }

    function filterDataDetailCapaian(prodi) {
        var element = $('#basic-datatable-rps')
        var dataUrl = "<?= url('rps/get-all-data') ?>"
        var totPagesLoad = 5
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
            'prodi': prodi
        }
        var dataColumn = [{
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: ''
            },
            {
                data: 'NAMA_MATKUL'
            },
            {
                data: 'CAPAIAN',
                visible: false
            },
            {
                data: 'KURIKULUM'
            },
            {
                data: 'ACTION_BUTTON'
            }
        ]

        let table = processingDataTable(element, totPagesLoad, dataUrl, dataBody, dataColumn)

        element.on('click', 'td.dt-control', function(e) {
            let tr = e.target.closest('tr');
            let row = table.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
            } else {
                row.child(format(row.data())).show();
            }
        });

        function format(d) {
            if (d) {
                return `
                    <dl>
                        <dt>Mata Kuliah:</dt>
                        <dd>${d.NAMA_MATKUL}</dd>
                        <dt>Capaian :</dt>
                        <dd style="white-space: pre-line;">${d.CAPAIAN}</dd>
                    </dl>
                `;
            } else {
                return `
                    <dl>
                        <dt>Data error</dt>
                    </dl>
                `;
            }
        }
    }

    function showForm() {
        dataMatkul = $('#select2-matkul').val()
        $('.main-form-presentase').hide()

        if (dataMatkul) {
            showLoader()
            $('.sub-form-presentase').show()
            if (detailCapaian) {
                $('#select2-matkul').attr('disabled', true)
                $('.show-form-button').hide()
                $('.sub-form-presentase').hide()
                $('.main-form-presentase').show()
                addExistingTab()
            } else {
                $('.main-form-presentase').hide()
                $.ajax({
                    url: '<?= url('rps/checking') ?>',
                    type: 'POST',
                    dataType: 'html',
                    data: {
                        matkul: atob($('#select2-matkul').val()),
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response) {
                            $('.sub-form-presentase').html(response)
                        } else {
                            $('#select2-matkul').attr('disabled', true)
                            $('.show-form-button').hide()
                            $('.sub-form-presentase').hide()
                            $('.main-form-presentase').show()
                            addTab()
                        }
                    },
                });
            }
        } else {
            if (dataMatkul) {
                Toast.fire({
                    icon: "error",
                    title: "Mata kuliah tidak boleh kosong"
                });
            }
        }
    }

    function submitFormDetailCapaian() {
        var isValid = true;
        var form = $('#form-rps')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            isValid = false;
            return false;
        }

        if (isValid) {
            var total = 0

            $('input[name="presentase-sub-capaian[]"]').each(function() {
                var value = parseFloat($(this).val()) || 0;
                total += value;
            });

            var countFilled = 0
            $('input[name="sub-capaian[]"]').each(function() {
                if ($(this).val() !== "") {
                    countFilled++
                }
            })

            if (countFilled !== 0) {
                var msgData = ((!kodeCapaian) ? 'mengubah' : 'menyimpan')
                var msgText = ((!kodeCapaian) ? 'Jika anda mengubah data ini, maka data penjadwalan akan terhapus. Harap berhati-hati dalam melakukan perubahan RPS!' : '')
                swalWithBootstrapButtons.fire({
                    title: `Apakah anda yakin ingin ${msgData} RPS ini ?`,
                    text: msgText,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya",
                    cancelButtonText: "Tidak",
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: "Saving...",
                            html: "Please wait, the system is still saving...",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        $('#form-rps').submit()
                    }
                });
            } else {
                Toast.fire({
                    icon: "error",
                    title: "Detail RPS tidak boleh kosong!"
                });
            }
        }
    }

    <?php if (!empty($subCPMK)) : ?>
        // UPDATED: Fungsi validasi dan submit form bobot penilaian
        function submitFormBobotPenilaian() {
            var isValid = true;
            var form = $('#form-bobot-rps')[0];
            if (!form.checkValidity()) {
                form.reportValidity();
                isValid = false;
                return false;
            }

            if (isValid) {
                var total = 0;
                <?php foreach ($subCPMK as $key => $item) : ?>
                    $('input[name="bobot[<?= $key ?>][]"]').each(function() {
                        var value = parseFloat($(this).val()) || 0;
                        total += value;
                    });
                <?php endforeach; ?>

                var totalBobotSubcpmk = 0;
                $('input[name^="bobot_subcpmk"]').each(function() {
                    totalBobotSubcpmk += parseFloat($(this).val()) || 0;
                });

                if (total > 100) {
                    Toast.fire({
                        icon: "error",
                        title: "Bobot penilaian tidak boleh melebihi nilai 100!"
                    });
                    return;
                }
                
                if (totalBobotSubcpmk > 100) {
                    Toast.fire({
                        icon: "error",
                        title: "Total bobot Sub-CPMK tidak boleh melebihi 100%!"
                    });
                    return;
                }

                if (total < 100 || totalBobotSubcpmk < 100) {
                    swalWithBootstrapButtons.fire({
                        title: `Apakah anda yakin ingin menyimpan bobot penilaian ?`,
                        text: `Peringatan, total bobot penilaian atau bobot Sub-CPMK kurang dari 100!`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Ya",
                        cancelButtonText: "Tidak",
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: "Saving...",
                                html: "Please wait, the system is still saving...",
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            $('#form-bobot-rps').submit()
                        }
                    });
                } else { // Both are 100
                    Swal.fire({
                        title: "Saving...",
                        html: "Please wait, the system is still saving...",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $('#form-bobot-rps').submit()
                }
            }
        }
    <?php endif; ?>

    function deleteFormDetailCapaian(id_rps) {
        swalWithBootstrapButtons.fire({
            title: `Apakah anda yakin ingin menghapus RPS ini ?`,
            text: "",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = `<?= url('rps/delete?data=') ?>${id_rps}`
            }
        });
    }

    function addExistingDataDetailCapaian(detailCapaian, dataCapSecond) {
        $('#select2-kurikulum').attr('disabled', true)
        $('#select2-matkul').attr('disabled', true)

        const data = JSON.parse(detailCapaian)
        const id_kurikulum = data[0].ID_KURIKULUM
        const kurikulum = data[0].KURIKULUM
        const kodeMatkul = data[0].KODE_MATKUL
        const matkul = data[0].NAMA_MATKUL
        const jmlPertemuan = data[0].JUMLAH_PERTEMUAN
        const tahun = data[0].TAHUN
        const semester = data[0].SEMESTER

        const CnvrtKodeMatkul = btoa(kodeMatkul + ';' + matkul + ';' + jmlPertemuan + ';' + id_kurikulum)
        const textMatkul = matkul + ` (${kodeMatkul}) ` + kurikulum
        $('#select2-matkul').append(new Option(textMatkul, CnvrtKodeMatkul, true, true));
        $('#select2-matkul').val(CnvrtKodeMatkul).trigger('change')

        showForm()
    }

    function resetForm() {
        $(`select[name='kode-sub-capaian[]']`).val('')
        $(`select[name='sub-capaian[]']`).val('')
        $(`input[name='persentase-sub-capaian[]']`).val('')
    }

    function subtractForm(oldIndForm) {
        function removeInput() {
            $('#li-inputan-' + oldIndForm).remove();
            $('#inputan-' + oldIndForm).remove();

            var id = $('#tab-btn li').eq(0).children().eq(0).attr('href');
            $('a[href="' + id + '"]').tab('show');

            let items = $("#tab-btn li");

            items.not(":last").each(function(index) {
                $(this).children().eq(0).contents().first()[0].textContent = "Sub CPMK-" + (index + 1);
            });
            $("#tab-content").children().each(function(index) {
                $(this).find(".btn-delete").contents().first()[0].textContent = "Hapus Sub CPMK-" + (index + 1);
            });
        }

        if (detailCapaian) {
            if ($('#tab-btn li').children().length == 2) {
                Toast.fire({
                    icon: "error",
                    title: "Detail RPS tidak boleh kosong!"
                });
            } else {
                removeInput();
            }
        } else {
            if ($('#tab-btn li').children().length == 2) {
                removeInput();

                $('#select2-kurikulum').attr('disabled', false);
                $('#select2-matkul').attr('disabled', false);

                $('.main-form-presentase').hide();
                $('.show-form-button').show();
            } else {
                removeInput();
            }
        }
    }

    function addTab() {
        const CnvrtMatkul = atob(dataMatkul)

        const idKurikulum = CnvrtMatkul.split(';')[3]
        const idMatkul = CnvrtMatkul.split(';')[0]
        const totPertemuan = CnvrtMatkul.split(';')[2]

        const length = $('#tab-btn li').children().length

        containerTabBtn.children().eq(length - 1).before(`
            <li class="nav-item" role="presentation" id="li-inputan-${indForm}">
                <a href="#inputan-${indForm}" data-bs-toggle="tab" aria-expanded="true" class="nav-link" aria-selected="true" role="tab">
                    Sub CPMK-${$('#tab-btn li').children().length}
                </a>
            </li>
        `)

        containerTabContent.append(`
            <div class="tab-pane" id="inputan-${indForm}" role="tabpanel">
                <div class="card-body row">
                    <div class="mb-3 col-md-12">
                        <div class="d-flex align-items-center justify-content-between">
                            <label class="form-label" for="sub-capaian">Sub CPMK</label>
                            <button type="button" class="btn btn-delete btn-sm btn-danger mb-3" onclick="subtractForm(${indForm})">
                                Hapus  Sub CPMK-${$('#tab-btn li').children().length -1}
                            </button>
                        </div>
                        <input name="sub-capaian[]" class="form-control" id="sub-capaian" type="text" required>
                        <input name="id-kurikulum" class="form-control" id="id-kurikulum" type="hidden" value="${idKurikulum}">
                        <input name="kode-matkul" class="form-control" id="kode-matkul" type="hidden" value="${idMatkul}">
                    </div>
                    <div class="mb-3 col-md-12">
                        <label class="form-label" for="capaian">Capaian Mata Kuliah</label>
                        <select name="capaian[${indForm}][]" id="capaian-${indForm}" class="form-control" multiple>
                        </select>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="bahan-kajian">Bahan Kajian</label>
                        <input name="bahan-kajian[]" class="form-control" id="bahan-kajian" type="text">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="bentuk-pembelajaran">Bentuk & Metode Pembelajaran</label>
                        <input name="bentuk-pembelajaran[]" class="form-control" id="bentuk-pembelajaran" type="text">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="estimasi-waktu">Estimasi Waktu</label>
                        <input name="estimasi-waktu[]" class="form-control" id="estimasi-waktu" type="text">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="pengalaman-belajar">Pengalaman Belajar</label>
                        <input name="pengalaman-belajar[]" class="form-control" id="pengalaman-belajar" type="text">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="indikator">Indikator</label>
                        <input name="indikator[]" class="form-control" id="indikator" type="text">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="kriteria-penilaian">Kriteria Penilaian</label>
                        <input name="kriteria-penilaian[]" class="form-control" id="kriteria-penilaian" type="text">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="pertemuan">Pertemuan</label>
                        <select name="pertemuan[${indForm}][]" id="pertemuan-${indForm}" class="form-control">
                        </select>
                    </div>
                </div>
            </div>
        `)

        $(`#pertemuan-${indForm}`).select2({
            tags: true,
            multiple: true,
            tokenSeparators: [',', ' ']
        });

        $(`#capaian-${indForm}`).select2({
            minimumInputLength: 3,
            minimumResultsForSearch: 1,
            ajax: {
                url: "<?= url('capaian/search-by-mapping') ?>",
                dataType: "json",
                type: "POST",
                data: function(params) {
                    var queryParameters = {
                        'keyword': params.term,
                        'jenis': 'CPMK',
                        'matkul': dataMatkul,
                        '_token': $('meta[name="csrf-token"]').attr('content'),
                    }
                    return queryParameters;
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            var KODE_CAPAIAN = item.KODE_CAPAIAN
                            var CAPAIAN = item.CAPAIAN
                            var JENIS = item.JENIS

                            return {
                                text: CAPAIAN + ' (' + JENIS + ')',
                                id: btoa(KODE_CAPAIAN + ';' + CAPAIAN + ';' + JENIS)
                            }
                        })
                    }
                }
            }
        })

        for (let index = 1; index <= totPertemuan; index++) {
            var newOption = new Option(`Pertemuan ${index}`, index, false, false);
            $(`#pertemuan-${indForm}`).append(newOption).trigger('change');
        }

        $('a[href="#inputan-' + indForm + '"]').tab('show');

        indForm++
    }

    function addExistingTab() {
        const data = JSON.parse(detailCapaian)
        const dataCap = JSON.parse(dataCapSecond)

        $.each(data, function(index, item) {
            const CnvrtMatkul = atob(dataMatkul)
            const idKurikulum = CnvrtMatkul.split(';')[3]
            const idMatkul = CnvrtMatkul.split(';')[0]
            const totPertemuan = CnvrtMatkul.split(';')[2]
            const idCapaianDet = item.ID_CAPAIAN_DETAIL ?? ""

            const pembelajaran = item.NAMA_PEMBELAJARAN ?? ""
            const kajian = item.KAJIAN ?? ""
            const bentuk = item.BENTUK_PEMBELAJARAN ?? ""
            const estimasi = item.ESTIMASI_WAKTU ?? ""
            const pengalaman = item.PENGALAMAN ?? ""
            const indikator = item.INDIKATOR ?? ""
            const kriteria = item.KRITERIA ?? ""
            const presentase = item.PRESENTASE
            const pertemuan = item.PERTEMUAN.split(';')
            const mappingSiakad = <?= !empty($mappingSiakad) ? json_encode($mappingSiakad) : json_encode([]); ?>;

            containerTabBtn.children().eq(indForm).before(`
                <li class="nav-item" role="presentation" id="li-inputan-${indForm}">
                    <a href="#inputan-${indForm}" data-bs-toggle="tab" aria-expanded="true" class="nav-link" aria-selected="true" role="tab">
                        Sub CPMK-${indForm + 1 }
                    </a>
                </li>
            `)

            if (mappingSiakad.length) {

                containerTabContent.append(`
                    <div class="tab-pane" id="inputan-${indForm}" role="tabpanel">
                        <div class="card-body row">
                            <div class="mb-3 col-md-12">                                
                                <div class="d-flex align-items-center justify-content-between"> 
                                    <label class="form-label" for="sub-capaian-${indForm}">Sub CPMK</label>
                                    <button type="button" class="btn btn-delete btn-sm btn-danger mb-3" onclick="subtractForm(${indForm})">
                                        Hapus  Sub CPMK-${indForm + 1}
                                    </button>
                                </div>
                                <input name="sub-capaian[]" class="form-control" id="sub-capaian-${indForm}" type="text" value="${pembelajaran}" required>
                                <input name="id-kurikulum" class="form-control" id="id-kurikulum" type="hidden" value="${idKurikulum}">
                                <input name="kode-matkul" class="form-control" id="kode-matkul" type="hidden" value="${idMatkul}">
                                <input name="id_capaian_detail[]" class="form-control" id="id_capaian_detail" type="hidden" value="${idCapaianDet}">
                            </div>
                            <div class="mb-3 col-md-12">
                                <label class="form-label" for="capaian">Capaian Mata Kuliah</label>
                                <select name="capaian[${indForm}][]" id="capaian-${indForm}" class="form-control" multiple>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="bahan-kajian">Bahan Kajian</label>
                                <input name="bahan-kajian[]" class="form-control" id="bahan-kajian-${indForm}" type="text" value="${kajian}">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="bentuk-pembelajaran">Bentuk & Metode Pembelajaran</label>
                                <input name="bentuk-pembelajaran[]" class="form-control" id="bentuk-pembelajaran-${indForm}" type="text" value="${bentuk}">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="estimasi-waktu">Estimasi Waktu</label>
                                <input name="estimasi-waktu[]" class="form-control" id="estimasi-waktu-${indForm}" type="text" value="${estimasi}">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="pengalaman-belajar">Pengalaman Belajar</label>
                                <input name="pengalaman-belajar[]" class="form-control" id="pengalaman-belajar-${indForm}" type="text" value="${pengalaman}">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="indikator">Indikator</label>
                                <input name="indikator[]" class="form-control" id="indikator-${indForm}" type="text" value="${indikator}">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="kriteria-penilaian">Kriteria Penilaian</label>
                                <input name="kriteria-penilaian[]" class="form-control" id="kriteria-penilaian-${indForm}" type="text" value="${kriteria}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="pertemuan">Pertemuan</label>
                                <select name="pertemuan[${indForm}][]" id="pertemuan-${indForm}" class="form-control">
                                </select>
                            </div>
                        </div>
                    </div>
                `)
            }

            $.each(dataCap[index]['KODE_CAPAIAN'].split(';'), function(indCap, kodeCap) {
                var capData = dataCap[index]['CAPAIAN'].split(';')[indCap]
                var capJenisData = dataCap[index]['JENIS_CAPAIAN'].split(';')[indCap]

                const CnvrtKode = btoa(kodeCap + ';' + capData + ';' + capJenisData)
                $(`#capaian-${indForm}`).append(new Option(capData + ' (' + capJenisData + ')', CnvrtKode, true, true))
            })

            $(`#pertemuan-${indForm}`).select2({
                tags: true,
                multiple: true,
                tokenSeparators: [',', ' ']
            });

            $(`#capaian-${indForm}`).select2({
                minimumInputLength: 3,
                minimumResultsForSearch: 1,
                ajax: {
                    url: "<?= url('/capaian/search-by-mapping') ?>",
                    dataType: "json",
                    type: "POST",
                    data: function(params) {
                        var queryParameters = {
                            'keyword': params.term,
                            'jenis': 'CPMK',
                            'matkul': dataMatkul,
                            '_token': $('meta[name="csrf-token"]').attr('content'),
                        }
                        return queryParameters;
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                var KODE_CAPAIAN = item.KODE_CAPAIAN
                                var CAPAIAN = item.CAPAIAN
                                var JENIS = item.JENIS

                                return {
                                    text: CAPAIAN + ' (' + JENIS + ')',
                                    id: btoa(KODE_CAPAIAN + ';' + CAPAIAN + ';' + JENIS)
                                }
                            })
                        }
                    }
                }
            })

            for (let iPertemuan = 1; iPertemuan <= totPertemuan; iPertemuan++) {
                var newOption = new Option(`Pertemuan ${iPertemuan}`, iPertemuan, false, false);
                $(`#pertemuan-${indForm}`).append(newOption);
            }

            $(`#pertemuan-${indForm}`).val(pertemuan).trigger('change')

            $('a[href="#inputan-0"]').tab('show');

            indForm++
        })
    }

    function showLoader() {
        $('.sub-form-presentase').html('')
        $('.sub-form-presentase').html(`
            <div class="card mb-0" style="position: relative; height: 300px;">
                <div class="card-body d-flex justify-content-center align-items-center">
                    <div class="me-2" style="width: 80px;">
                        <svg version="1.1" id="L3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                            viewBox="0 0 100 100" enable-background="new 0 0 0 0" xml:space="preserve">
                            <circle fill="none" stroke="#19a5e8" stroke-width="4" cx="50" cy="50" r="44" style="opacity:0.5;" />
                            <circle fill="#19a5e8" stroke="#fff" stroke-width="3" cx="8" cy="54" r="6">
                                <animateTransform
                                    attributeName="transform"
                                    dur="2s"
                                    type="rotate"
                                    from="0 50 48"
                                    to="360 50 52"
                                    repeatCount="indefinite" />

                            </circle>
                        </svg>
                    </div>
                    Loading ...
                </div>
            </div>
        `)
    }

    function generateRPS(data) {
        let swalCustom = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger",
                actions: "swal-gap-btn"
            },
            buttonsStyling: false,
            didRender: () => {
                const actions = document.querySelector('.swal2-actions.swal-gap-btn');
                if (actions) {
                    actions.style.display = 'flex';
                    actions.style.gap = '10px';
                }
            }
        });

        swalCustom.fire({
            title: "Apakah anda yakin ingin membuat PDF?",
            text: "",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Batal",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Mohon ditunggu!",
                    html: "Sistem sedang membuat pdf.",
                    timerProgressBar: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                })

                $.ajax({
                    url: '/rps/generate-pdf',
                    method: 'GET',
                    data: {
                        dataRPS: data
                    },
                    success: function(respHTML) {
                        $('#print-container').html(respHTML);

                        $('#rps-container').printThis({
                            importCSS: false,
                            importStyle: true
                        })

                        $('#print-container').html('');
                        Swal.close();
                    },
                    error: function(xhr) {
                        Swal.close();
                        let message = 'An error occurred during generating pdf.';
                        if (xhr.status === 500) {
                            message = xhr.responseJSON?.message || 'Internal Server Error!';
                        } else if (xhr.status === 404) {
                            message = 'The synchronization endpoint was not found.';
                        } else if (xhr.status === 403) {
                            message = 'You are not authorized to perform this action.';
                        } else if (xhr.status === 422) {
                            message = 'Validation error. Please check your input.';
                        }

                        Swal.fire({
                            title: 'Error',
                            text: message,
                            icon: 'error',
                        });
                    }
                });
            }
        });
    }
</script>
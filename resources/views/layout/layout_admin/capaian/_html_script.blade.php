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

    var kodeCapaian = '<?= (!empty($kodeCapaian) ? $kodeCapaian : '') ?>'
    var capaian = '<?= (!empty($capaian) ? $capaian : '') ?>'
    var detailCapaian = '<?= !empty($detailCapaian) ? json_encode($detailCapaian) : json_encode([]) ?>'

    $(document).ready(function() {
        <?= (Request::is('sub-capaian*') || Request::is('detail-capaian*')) ? "$('#sidebarPages').collapse('show')" : ''; ?>

        <?= (Request::is('capaian*')) ? "$('#MappingPages').collapse('show')" : ''; ?>

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


        var savedCapaianType = localStorage.getItem("capaian")
        if (savedCapaianType) {
            $('.nav-pills .nav-link[data-tipe="' + savedCapaianType + '"]').addClass("active");
        } else {
            $('.nav-pills .nav-link').first().addClass("active");
        }

        $(".nav-pills .nav-link").click(function() {
            let capaianType = $(this).data("tipe"); // Get tab name from data attribute
            localStorage.setItem("capaian", capaianType); // Save to Local Storage
            $('.nav-pills .nav-link[data-tipe="' + capaianType + '"]').addClass("active");
        });


        var prodi = $(".nav-tabs .nav-link.active").attr("data-prodi");
        var tipeCapaian = $(".nav-pills .nav-link.active").attr("data-tipe");
        filterData(tipeCapaian, prodi)
        $('.main-form-presentase').hide()
    });
</script>

<!-- Script Capaian -->
<script>
    var jenis = ''
    var kurikulum = ''
    var prodi = ''
    $('#select2-capaian-cpl').select2({
        // minimumInputLength: 3,
        // minimumResultsForSearch: 1,
        dropdownParent: $('#main-modal'),
        ajax: {
            url: "<?= url('capaian/search') ?>",
            dataType: "json",
            type: "POST",
            data: function(params) {
                var queryParameters = {
                    'keyword': params.term,
                    'kurikulum': kurikulum,
                    'id_prodi': prodi,
                    'jenis': 'CPL',
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
                };
            }
        }
    });

    $('#jenis').change(function() {
        jenis = $(this).val()
        $("input[name='jenis_capaian']").val(jenis)
        renderCPL()
    })

    $('#kurikulum').change(function() {
        kurikulum = $(this).val()
        $("input[name='kurikulum_capaian']").val(kurikulum)
        renderCPL()
    })

    function renderCPL() {
        if (jenis && kurikulum && jenis == 'CPMK') {
            $('#container-cpl').show()
            $(`#select2-capaian-cpl`).attr('disabled', false)
        } else {
            $('#container-cpl').hide()
            $(`#select2-capaian-cpl`).attr('disabled', true)
        }
    }

    $('#prodi').change(function() {
        prodi = $(this).val()
        $("input[name='prodi_capaian']").val(prodi)
        renderCPL()
    })

    function reRenderTable() {
        const table = $('#basic-datatable');
        if ($.fn.DataTable.isDataTable(table)) {
            table.DataTable().clear().destroy();
        }

        var prodi = $(".nav-tabs .nav-link.active").attr("data-prodi");
        var tipeCapaian = $(".nav-pills .nav-link.active").attr("data-tipe");

        filterData(tipeCapaian, prodi)
    }

    function filterData(tipe, prodi) {
        var element = $('#basic-datatable')
        var totPagesLoad = 5
        var dataUrl = "<?= url('capaian/get-all-data') ?>"
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
            'tipe': tipe,
            'prodi': prodi,
        }
        var dataColumn = [{
                data: 'KODE_CAPAIAN'
            },
            {
                data: 'CAPAIAN'
            },
            {
                data: 'JENIS_CAPAIAN'
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

    function openModal(rawData = '', parentRawData = '') {
        resetModal()
        if (rawData) {
            var data = JSON.parse(rawData)
            var dataParent = JSON.parse(parentRawData)

            $('#modal-label').html('Rubah Capaian')

            $('#kode_capaian').val(data.KODE_CAPAIAN)
            $('#capaian').val(data.CAPAIAN)
            $('#jenis').val(data.JENIS_CAPAIAN).trigger('change')
            $('#jenis').attr('disabled', true)

            $('#kurikulum').val(data.ID_KURIKULUM).trigger('change')
            $('#prodi').val(data.ID_PRODI).trigger('change')

            if (data.JENIS_CAPAIAN == 'CPMK') {
                $('#container-cpl').show()

                let kodeCplArr = []
                $.each(dataParent, function(index, dataPar) {
                    let kodeCpl = btoa(dataPar.KODE_CAPAIAN + ';' + dataPar.CAPAIAN + ';' + dataPar.JENIS_CAPAIAN)
                    var newOption = new Option(`${dataPar.CAPAIAN} (${dataPar.JENIS_CAPAIAN})`, kodeCpl, false, false)
                    $(`#select2-capaian-cpl`).append(newOption)
                    kodeCplArr.push(kodeCpl)
                })

                $(`#select2-capaian-cpl`).val(kodeCplArr).trigger('change')
            } else {
                $('#container-cpl').hide()
            }

            $('#msg-jenis-capaian').html('*Jenis capaian tidak bisa diganti, karena akan mempengaruhi kode capaian. Silahkan tambahkan baru jika ingin mengganti jenis capaian.')
        } else {
            $('#modal-label').html('Tambahkan Capaian Baru')
        }

        $('#main-modal').modal('show')
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
            $('#main-modal').modal('hide')
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

    function resetModal() {
        $('#msg-jenis-capaian').html('')
        $('#kode_capaian').val('')
        $('#prodi').val('')
        $('#kurikulum').val('')
        $('#capaian').val('')
        $('#jenis').val('').trigger('change')

        $('#jenis').attr('disabled', false)
        $(`#select2-capaian-cpl`).html('')
        $('#select2-capaian-cpl').val([]).trigger('change')
    }

    function modalConfirmActive(kodeCapaian, status) {
        swalWithBootstrapButtons.fire({
            title: `Apakah kamu yakin ingin ${((status == 0) ? 'menonaktifkan' : 'mengaktifkan')} capaian ini ?`,
            text: "",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = `<?= url('capaian/change-status') ?>?kode=${kodeCapaian}&active=${status}`
            }
        });
    }

    function modalConfirmDelete(kodeCapaian) {
        swalWithBootstrapButtons.fire({
            title: `Apakah kamu yakin ingin menghapus capaian ini ?`,
            text: "Ini juga akan menghapus data detail capaian yang berkaitan dengan capaian ini!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = `<?= url('capaian/delete') ?>?kode=${kodeCapaian}`
            }
        });
    }
</script>

<!-- Script Tipe Capaian -->
<script>
    function filterDataDetailTipeCapaian() {
        var element = $('#basic-datatable-tipe-capaian')
        var totPagesLoad = 5
        var dataUrl = "<?= url('tipe-capaian/get-all-data') ?>"
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
        }
        var dataColumn = [{
                data: 'TIPE_PENILAIAN'
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

    function modalConfirmActiveTipeCapaian(kodeCapaian, status) {
        swalWithBootstrapButtons.fire({
            title: `Apakah kamu yakin ingin ${((status == 0) ? 'menonaktifkan' : 'mengaktifkan')} tipe capaian ini ?`,
            text: "",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = `<?= url('tipe-capaian/change-status') ?>?kode=${kodeCapaian}&active=${status}`
            }
        });
    }

    function openModalTipeCapaian(rawData = '') {
        resetModalTipeCapaian()
        if (rawData) {
            var data = JSON.parse(rawData)
            $('#modal-label').html('Rubah Tipe Capaian')

            $('#id_tipe_capaian').val(data.ID_TIPE)
            $('#tipe_penilaian').val(data.TIPE_PENILAIAN)
            $('#ket_tipe_capaian').val(data.KETERANGAN)
        } else {
            $('#modal-label').html('Tambahkan Tipe Capaian Baru')
        }

        $('#main-modal').modal('show')
    }

    function modalConfirmDeleteTipeCapaian(kodeCapaian) {
        swalWithBootstrapButtons.fire({
            title: `Apakah kamu yakin ingin menghapus tipe capaian ini ?`,
            text: "Ini juga akan menghapus data detail capaian yang berkaitan dengan tipe capaian ini!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = `<?= url('tipe-capaian/delete') ?>?kode=${kodeCapaian}`
            }
        });
    }

    function resetModalTipeCapaian() {
        $('#id_tipe_capaian').val('')
        $('#tipe_penilaian').val('')
        $('#ket_tipe_capaian').val('')
    }
</script>
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
        <?= (Request::is('peta-kurikulum*')) ? "$('#sidebarPages').collapse('show')" : ''; ?>

        // Old Script
        // var savedProdi = localStorage.getItem("prodi")
        // if (savedProdi) {
        //     $('.nav-tabs .nav-link[data-prodi="' + savedProdi + '"]').addClass("active");
        // } else {
        //     $('.nav-tabs .nav-link').first().addClass("active");
        // }

        // $(".nav-tabs .nav-link").click(function() {
        //     let idProdi = $(this).data("prodi");
        //     localStorage.setItem("prodi", idProdi);
        //     $('.nav-tabs .nav-link[data-prodi="' + idProdi + '"]').addClass("active");
        // });

        // var prodi = $(".nav-tabs .nav-link.active").attr("data-prodi");
        // filterData(prodi)

        // var prodiDropdown = JSON.parse(`<?= json_encode($prodi ?? []) ?>`);
        $('#tahunAjaran').attr('disabled', true)
        $('#semester').attr('disabled', true)

        var paketDropdown = JSON.parse(`<?= json_encode($paketDropdown ?? []) ?>`);
        $('#progStudi').select2({
            placeholder: '-- Pilih Program Studi --'
        })
        $.each(paketDropdown, function(index, item) {
            $('#progStudi').append(`
                <option value="${index}">${index}</option>
            `)
        })
        $('#progStudi').on('change', function() {
            resetDropdownTA()
            resetDropdownSem()
            var progStudi = $(this).val();
            var paketDropdownTA = paketDropdown[progStudi]
            $('#tahunAjaran').attr('disabled', false)
            $('#tahunAjaran').select2({
                placeholder: '-- Pilih Tahun Ajaran --'
            })
            $.each(paketDropdownTA, function(indexProdi, itemProdi) {
                $('#tahunAjaran').append(`
                    <option value="${indexProdi}">${indexProdi}</option>
                `)
            })

            $('#tahunAjaran').on('change', function() {
                resetDropdownSem()
                var tahunAjar = $(this).val();
                var paketDropdownSem = paketDropdownTA[tahunAjar]
                $('#semester').attr('disabled', false)
                $('#semester').select2({
                    placeholder: '-- Pilih Semester --'
                })
                $.each(paketDropdownSem, function(index, item) {
                    $('#semester').append(`
                        <option value="${item}">Semester ${item}</option>
                    `)
                })
            });
        });
    });

    function resetDropdownTA() {
        $('#tahunAjaran').empty();
        $('#tahunAjaran').html('<option value=""></option>');
        $('#tahunAjaran').attr('disabled', true)
    }

    function resetDropdownSem() {
        $('#semester').empty();
        $('#semester').html('<option value=""></option>');
        $('#semester').attr('disabled', true)
    }
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
        var dataUrl = "<?= url('peta-kurikulum/get-all-data') ?>"
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
            'prodi': prodi
        }
        var dataColumn = [{
                data: 'KODE_MATKUL'
            },
            {
                data: 'NAMA_MATKUL'
            },
            {
                data: 'SEMESTER'
            },
            {
                data: 'KURIKULUM'
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

    function renderPaperForm(e) {
        let progStud = $('#progStudi').val()
        let tahunAjaran = $('#tahunAjaran').val()
        let semester = $('#semester').val()

        if (progStud && tahunAjaran && semester) {
            var mainPaket = JSON.parse(`<?= json_encode(array_values($paketMatkul ?? [])) ?>`);
            var targetIndex = mainPaket.findIndex(item =>
                item.PRODI == progStud &&
                item.TAHUN_AJAR == tahunAjaran &&
                item.KODE_SEMESTER == semester
            );

            $.ajax({
                url: '<?= url('peta-kurikulum/render-paket-form') ?>',
                type: 'POST',
                data: {
                    '_token': $('meta[name="csrf-token"]').attr('content'),
                    'kode_paket': mainPaket[targetIndex].KODE_PAKET
                },
                beforeSend: function() {
                    $(e).attr('disabled', true)
                    $('#main-container').html(`
                        <div class="card">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="w-25">
                                    <div class="card-body">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <div class="w-100">
                                                <svg version="1.1" class="svg-loader" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 80 80" xml:space="preserve">
                                                    <path fill="#17a3b4" d="M10,40c0,0,0-0.4,0-1.1c0-0.3,0-0.8,0-1.3c0-0.3,0-0.5,0-0.8c0-0.3,0.1-0.6,0.1-0.9c0.1-0.6,0.1-1.4,0.2-2.1
                                                            c0.2-0.8,0.3-1.6,0.5-2.5c0.2-0.9,0.6-1.8,0.8-2.8c0.3-1,0.8-1.9,1.2-3c0.5-1,1.1-2,1.7-3.1c0.7-1,1.4-2.1,2.2-3.1
                                                            c1.6-2.1,3.7-3.9,6-5.6c2.3-1.7,5-3,7.9-4.1c0.7-0.2,1.5-0.4,2.2-0.7c0.7-0.3,1.5-0.3,2.3-0.5c0.8-0.2,1.5-0.3,2.3-0.4l1.2-0.1
                                                            l0.6-0.1l0.3,0l0.1,0l0.1,0l0,0c0.1,0-0.1,0,0.1,0c1.5,0,2.9-0.1,4.5,0.2c0.8,0.1,1.6,0.1,2.4,0.3c0.8,0.2,1.5,0.3,2.3,0.5
                                                            c3,0.8,5.9,2,8.5,3.6c2.6,1.6,4.9,3.4,6.8,5.4c1,1,1.8,2.1,2.7,3.1c0.8,1.1,1.5,2.1,2.1,3.2c0.6,1.1,1.2,2.1,1.6,3.1
                                                            c0.4,1,0.9,2,1.2,3c0.3,1,0.6,1.9,0.8,2.7c0.2,0.9,0.3,1.6,0.5,2.4c0.1,0.4,0.1,0.7,0.2,1c0,0.3,0.1,0.6,0.1,0.9
                                                            c0.1,0.6,0.1,1,0.1,1.4C74,39.6,74,40,74,40c0.2,2.2-1.5,4.1-3.7,4.3s-4.1-1.5-4.3-3.7c0-0.1,0-0.2,0-0.3l0-0.4c0,0,0-0.3,0-0.9
                                                            c0-0.3,0-0.7,0-1.1c0-0.2,0-0.5,0-0.7c0-0.2-0.1-0.5-0.1-0.8c-0.1-0.6-0.1-1.2-0.2-1.9c-0.1-0.7-0.3-1.4-0.4-2.2
                                                            c-0.2-0.8-0.5-1.6-0.7-2.4c-0.3-0.8-0.7-1.7-1.1-2.6c-0.5-0.9-0.9-1.8-1.5-2.7c-0.6-0.9-1.2-1.8-1.9-2.7c-1.4-1.8-3.2-3.4-5.2-4.9
                                                            c-2-1.5-4.4-2.7-6.9-3.6c-0.6-0.2-1.3-0.4-1.9-0.6c-0.7-0.2-1.3-0.3-1.9-0.4c-1.2-0.3-2.8-0.4-4.2-0.5l-2,0c-0.7,0-1.4,0.1-2.1,0.1
                                                            c-0.7,0.1-1.4,0.1-2,0.3c-0.7,0.1-1.3,0.3-2,0.4c-2.6,0.7-5.2,1.7-7.5,3.1c-2.2,1.4-4.3,2.9-6,4.7c-0.9,0.8-1.6,1.8-2.4,2.7
                                                            c-0.7,0.9-1.3,1.9-1.9,2.8c-0.5,1-1,1.9-1.4,2.8c-0.4,0.9-0.8,1.8-1,2.6c-0.3,0.9-0.5,1.6-0.7,2.4c-0.2,0.7-0.3,1.4-0.4,2.1
                                                            c-0.1,0.3-0.1,0.6-0.2,0.9c0,0.3-0.1,0.6-0.1,0.8c0,0.5-0.1,0.9-0.1,1.3C10,39.6,10,40,10,40z">
                                                        <animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 40 40" to="360 40 40" dur="0.8s" repeatCount="indefinite"></animateTransform>
                                                    </path>
                                                    <path fill="#bbdcd8" d="M62,40.1c0,0,0,0.2-0.1,0.7c0,0.2,0,0.5-0.1,0.8c0,0.2,0,0.3,0,0.5c0,0.2-0.1,0.4-0.1,0.7
                                                            c-0.1,0.5-0.2,1-0.3,1.6c-0.2,0.5-0.3,1.1-0.5,1.8c-0.2,0.6-0.5,1.3-0.7,1.9c-0.3,0.7-0.7,1.3-1,2.1c-0.4,0.7-0.9,1.4-1.4,2.1
                                                            c-0.5,0.7-1.1,1.4-1.7,2c-1.2,1.3-2.7,2.5-4.4,3.6c-1.7,1-3.6,1.8-5.5,2.4c-2,0.5-4,0.7-6.2,0.7c-1.9-0.1-4.1-0.4-6-1.1
                                                            c-1.9-0.7-3.7-1.5-5.2-2.6c-1.5-1.1-2.9-2.3-4-3.7c-0.6-0.6-1-1.4-1.5-2c-0.4-0.7-0.8-1.4-1.2-2c-0.3-0.7-0.6-1.3-0.8-2
                                                            c-0.2-0.6-0.4-1.2-0.6-1.8c-0.1-0.6-0.3-1.1-0.4-1.6c-0.1-0.5-0.1-1-0.2-1.4c-0.1-0.9-0.1-1.5-0.1-2c0-0.5,0-0.7,0-0.7
                                                            s0,0.2,0.1,0.7c0.1,0.5,0,1.1,0.2,2c0.1,0.4,0.2,0.9,0.3,1.4c0.1,0.5,0.3,1,0.5,1.6c0.2,0.6,0.4,1.1,0.7,1.8
                                                            c0.3,0.6,0.6,1.2,0.9,1.9c0.4,0.6,0.8,1.3,1.2,1.9c0.5,0.6,1,1.3,1.6,1.8c1.1,1.2,2.5,2.3,4,3.2c1.5,0.9,3.2,1.6,5,2.1
                                                            c1.8,0.5,3.6,0.6,5.6,0.6c1.8-0.1,3.7-0.4,5.4-1c1.7-0.6,3.3-1.4,4.7-2.4c1.4-1,2.6-2.1,3.6-3.3c0.5-0.6,0.9-1.2,1.3-1.8
                                                            c0.4-0.6,0.7-1.2,1-1.8c0.3-0.6,0.6-1.2,0.8-1.8c0.2-0.6,0.4-1.1,0.5-1.7c0.1-0.5,0.2-1,0.3-1.5c0.1-0.4,0.1-0.8,0.1-1.2
                                                            c0-0.2,0-0.4,0.1-0.5c0-0.2,0-0.4,0-0.5c0-0.3,0-0.6,0-0.8c0-0.5,0-0.7,0-0.7c0-1.1,0.9-2,2-2s2,0.9,2,2C62,40,62,40.1,62,40.1z">
                                                        <animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 40 40" to="-360 40 40" dur="0.6s" repeatCount="indefinite"></animateTransform>
                                                    </path>
                                                </svg>
                                            </div>
                                            <div class="w-100">
                                                <span>Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `)
                },
                success: function(response) {
                    $(e).removeAttr('disabled')
                    if (response) {
                        $('#main-container').html(atob(response))
                    } else {
                        $('#main-container').html('')
                    }
                },
                error: function() {
                    $(e).removeAttr('disabled')
                }
            });
        } else {
            Toast.fire({
                icon: "error",
                title: "Mohon untuk memasukkan data program studi, tahun ajaran, dan semester untuk menampilkan detail paket mata kuliah"
            });
        }
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

    function modalConfirmDelete(idMatkul) {
        swalWithBootstrapButtons.fire({
            title: `Apakah kamu yakin ingin menghapus Peta Kurikulum ini ?`,
            // text: "Ini juga akan menghapus data detail capaian yang berkaitan dengan capaian ini!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = `<?= url('peta-kurikulum/delete') ?>?id=${idMatkul}`
            }
        });
    }
</script>
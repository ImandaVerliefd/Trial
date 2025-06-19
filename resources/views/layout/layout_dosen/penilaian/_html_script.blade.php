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


    $(document).ready(function() {
        <?= (Request::is('penilaian/form*')) ? "$('.button-toggle-menu').click()" : ''; ?>

        filterDataPenilaian()
        <?php if (!empty($detailCpmk)) { ?>
            renderDetailNilai(0)
        <?php } ?>
    });
</script>

<!-- Script Penilaian -->
<script>
    function reRenderTable() {
        const table = $('#basic-datatable-rps');
        if ($.fn.DataTable.isDataTable(table)) {
            table.DataTable().clear().destroy();
        }

        var prodi = $(".nav-tabs .nav-link.active").attr("data-prodi");
        filterDataPenilaian(prodi)
    }

    function filterDataPenilaian() {
        var element = $('#basic-datatable-penilaian')
        var dataUrl = "<?= url('/penilaian/get-all-matkul') ?>"
        var totPagesLoad = 5
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
        }
        var dataColumn = [{
                data: 'NAMA_MATKUL'
            },
            { 
                data: 'KODE_KELAS' 
            },
            {
                data: 'ACTION_BUTTON'
            }
        ]

        let table = processingDataTable(element, totPagesLoad, dataUrl, dataBody, dataColumn)
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

    function renderDetailNilai(indexParent) {
        let dataPenilaian = [];

        $(`input[name="kode_mhs[]"]`).each(function() {
            let kodeMhs = $(this).val();
            $(`input[name="id_penilaian_head[${indexParent}][${kodeMhs}]"]`).each(function() {
                let val = $(this).val();
                if (val) {
                    dataPenilaian.push(val);
                }
            });
        });

        if (dataPenilaian) {
            $.ajax({
                url: '<?= url('penilaian/form/detail') ?>',
                type: 'POST',
                data: {
                    '_token': $('meta[name="csrf-token"]').attr('content'),
                    'id_penilaian_heads': dataPenilaian
                },
                beforeSend: function() {
                    let timerInterval;
                    Swal.fire({
                        title: "Loading...",
                        html: `Sedang mengambil data penilaian`,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => Swal.showLoading()
                    });
                },
                success: function(response) {
                    Swal.close();

                    if (response) {
                        $.each(response, function(index, item) {
                            $.each(item, function(subIndex, subItem) {
                                $(`input[name="nilai[${index}][${subIndex}]"]`).val(subItem)
                            });

                            $(`td#total_nilai[data-key="${index}"]`).html(item.TOTAL_NILAI);
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Oops...",
                            text: "Data penilaian tidak ditemukan!",
                            footer: ''
                        });
                    }

                },
                error: function(xhr) {
                    Swal.close();

                    let errorMessage = "Terjadi kesalahan saat mengambil data.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            const parsed = JSON.parse(xhr.responseText);
                            if (parsed.message) errorMessage = parsed.message;
                        } catch (e) {
                            errorMessage = xhr.responseText;
                        }
                    }

                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: errorMessage,
                        footer: ''
                    });
                }
            });

        }
    }

    function submitPerRow(button, order) {
        let $row = $(button).closest('tr');
        let $rowMain = $('#container-main-input-' + order);
        let formData = {
            '_token': $('meta[name="csrf-token"]').attr('content')
        };

        $row.find('input').each(function() {
            const name = $(this).attr('name');
            const value = $(this).val();

            formData[name] = value
        });

        $rowMain.find('input').each(function() {
            const name = $(this).attr('name');
            const value = $(this).val();

            formData[name] = value
        });

        $.ajax({
            url: '<?= url('penilaian/submit-per-row') ?>',
            type: 'POST',
            data: formData,
            beforeSend: function() {
                let timerInterval;
                Swal.fire({
                    title: "Loading...",
                    html: `Sedang mengambil data penilaian`,
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });
            },
            success: function(response) {
                Swal.close();
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });
                Toast.fire({
                    icon: response.status,
                    title: response.msg
                });

                const $totalCell = $row.find('td#total_nilai');
                $totalCell.text(response.total)
            },
            error: function(xhr) {
                Swal.close();

                let errorMessage = "Terjadi kesalahan saat mengambil data.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const parsed = JSON.parse(xhr.responseText);
                        if (parsed.message) errorMessage = parsed.message;
                    } catch (e) {
                        errorMessage = xhr.responseText;
                    }
                }

                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: errorMessage,
                    footer: ''
                });
            }
        });
    }
</script>
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
        <?= (Request::is('mata-kuliah*')) ? "$('#sidebarPages').collapse('show')" : ''; ?>

        filterData()
    });
</script>

<!-- Script Capaian -->
<script>
    function filterData() {
        var element = $('#basic-datatable')
        var totPagesLoad = 5
        var dataUrl = "<?= url('mata-kuliah/get-all-data') ?>"
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
        }
        var dataColumn = [{
                data: 'KODE_MATKUL'
            },
            {
                data: 'NAMA_MATKUL'
            },
            {
                data: 'SKS'
            },
            {
                data: 'JUMLAH_PERTEMUAN'
            },
            {
                data: 'PRODI'
            },
            {
                data: 'IS_ACTIVE'
            },
            {
                data: 'ACTION_BUTTON'
            }
        ]

        $('#basic-datatable tfoot th').each(function() {
            var title = $(this).text()
            if (title == "Aksi") {
                $(this).html('')
            } else if (title == "Prodi") {
                $(this).html(`
                    <select class="form-control">
                        <option value="" >Pilih Program Studi</option>
                        <?php foreach ($prodi as $item) : ?>
                        <option value="<?= $item->ID_PRODI ?>" ><?= $item->JENJANG ?> <?= $item->PRODI ?></option>
                        <?php endforeach ?>
                    </select>`)
            } else if (title == "Status") {
                $(this).html(`
                    <select class="form-control">
                        <option value="" >Pilih Status</option>
                            
                        <option value="Aktif" >Aktif</option>
                        <option value="Tidak Aktif" >Tidak Aktif</option>
                    </select>`)
            } else {
                $(this).html('<input type="text"  class="form-control ' + title + '" placeholder="Cari ' + title + '" />')
            }
        });

        processingDataTableWithFooterSearch(element, totPagesLoad, dataUrl, dataBody, dataColumn)
    }

    $('#prodi').change(function() {
        let prodi = $(this).val()
        $("input[name='prodi']").val(prodi)
    })

    function openModal(rawData = '') {
        resetModal()
        if (rawData) {
            var data = JSON.parse(rawData)

            $('#modal-label').html('Ubah Mata Kuliah')

            $('#kode_matkul').val(data.KODE_MATKUL)
            $('#kode_matkul').attr('readonly', true)
            $('#nama_matkul').val(data.NAMA_MATKUL)
            $('#sks').val(data.SKS)
            $('#jumlah_pertemuan').val(data.JUMLAH_PERTEMUAN)
            $('#prodi').val(data.ID_PRODI).trigger('change')

            if (data.IS_UMUM) {
                $('input[name="is_umum"]').prop('checked', true);
            }

            if (data.IS_LINTAS_PRODI) {
                $('input[name="is_lintas_prodi"]').prop('checked', true);
                if (data.ID_LINPROD) {
                    $('#lintas_prodi').val(data.ID_LINPROD.split(';')).trigger('change');
                }

                $('#lintas_prodi').select2({
                    dropdownParent: $('#main-modal'),
                    placeholder: "-- Pilih Prodi --",
                    allowClear: true,
                    width: '100%'
                });

                $('#lintas_prodi').next(".select2-container").show();
                $('#lintas_prodi').prop('required', true);

                $('#is_umum').prop('checked', false);
                $('#is_umum').prop('disabled', true);
            }

            if (data.IS_LAPANGAN) {
                $('input[name="is_lapangan"]').prop('checked', true);
            }

        } else {
            $('#modal-label').html('Tambahkan Mata Kuliah Baru')
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
        $('#kode_matkul').val('')
        $('#nama_matkul').val('')
        $('#sks').val('')
        $('#jumlah_pertemuan').val('')
        $('#prodi').val('').trigger('change')
        
        $('#is_umum').prop('checked', false);
        $('#is_lintas_prodi').prop('checked', false);
        $('#is_lapangan').prop('checked', false);

        reset_linprod()
    }

    function modalConfirmActive(idMatkul, status) {
        swalWithBootstrapButtons.fire({
            title: `Apakah kamu yakin ingin ${((status == 0) ? 'menonaktifkan' : 'mengaktifkan')} mata kuliah ini ?`,
            text: "",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = `<?= url('mata-kuliah/change-status') ?>?id=${idMatkul}&active=${status}`
            }
        });
    }

    function modalConfirmDelete(idMatkul) {
        swalWithBootstrapButtons.fire({
            title: `Apakah kamu yakin ingin menghapus mata kuliah ini ?`,
            // text: "Ini juga akan menghapus data detail capaian yang berkaitan dengan capaian ini!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = `<?= url('mata-kuliah/delete') ?>?id=${idMatkul}`
            }
        });
    }

    function reset_linprod() {
        $('#lintas_prodi').select2({
            dropdownParent: $('#main-modal'),
            placeholder: "-- Pilih Prodi --",
            allowClear: true,
            width: '100%'
        }).next(".select2-container").hide();
    }
</script>

<!-- Script Special Condition is_umum & is_lintas_prodi -->
<script>
    $('#is_lintas_prodi').change(function() {
        if ($(this).is(':checked')) {
            $('#lintas_prodi').select2({
                dropdownParent: $('#main-modal'),
                placeholder: "-- Pilih Prodi --",
                allowClear: true,
                width: '100%'
            });

            $('#lintas_prodi').next(".select2-container").show();
            $('#lintas_prodi').prop('required', true);

            $('#is_umum').prop('checked', false);
            $('#is_umum').prop('disabled', true);
        } else {
            $('#lintas_prodi').val('').trigger('change');
            $('#lintas_prodi').select2('destroy').hide();
            $('#lintas_prodi').prop('required', false);

            $('#is_umum').prop('disabled', false);
        }
    });

    $('#lintas_prodi').on('change', function() {
        let selectedTexts = $('#lintas_prodi').find(':selected').map(function() {
            return $(this).text();
        }).get();
        $('#container-linprod').empty();
        selectedTexts.forEach((text, index) => {
            $('#container-linprod').append(
                `<input type="hidden" name="linprod[]" value="${text}">`
            );
        });
    });

    $('#is_umum').change(function() {
        if ($(this).is(':checked')) {
            $('#is_lintas_prodi').prop('checked', false);
            $('#is_lintas_prodi').prop('disabled', true);
        } else {
            $('#is_lintas_prodi').prop('disabled', false);
        }
    });

    function reset_linprod() {
        $('#lintas_prodi').select2({
            dropdownParent: $('#main-modal'),
            placeholder: "-- Pilih Prodi --",
            allowClear: true,
            width: '100%'
        }).next(".select2-container").hide();
    }
</script>
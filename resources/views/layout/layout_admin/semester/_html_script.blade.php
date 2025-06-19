<?php

use Illuminate\Support\Facades\Request;
?>

<script>
    $('#year_start').datepicker({
        format: "yyyy",
        viewMode: "years",
        minViewMode: "years",
        autoclose: true
    });
    $('#year_end').datepicker({
        format: "yyyy",
        viewMode: "years",
        minViewMode: "years",
        autoclose: true
    });
</script>

<script>
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger me-2"
        },
        buttonsStyling: false
    });

    $(document).ready(function() {
        <?= (Request::is('semester*')) ? "$('#sidebarPages').collapse('show')" : ''; ?>

        filterData()
    });
</script>

<script>
    function filterData() {
        var element = $('#basic-datatable')
        var totPagesLoad = 5
        var dataUrl = "<?= url('semester/get-all-data') ?>"
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
        }
        var dataColumn = [{
                data: 'SEMESTER'
            },
            {
                data: 'TAHUN'
            },
            {
                data: 'KURIKULUM'
            },
            {
                data: 'ACTION_BUTTON'
            }
        ]

        processingDataTable(element, totPagesLoad, dataUrl, dataBody, dataColumn)
    }

    $('#id_kurikulum').change(function() {
        let kurikulum = $(this).find(':selected')
        $("input[name='tahun_kurikulum']").val(kurikulum.data('tahun'))
    })

    function openModal(rawData = '') {
        // Only reset modal if it's a new entry, otherwise populate with existing data
        if (!rawData) {
            resetModal()
            $('#modal-label').html('Tambahkan Semester Baru')
        } else {
            $('#modal-label').html('Ubah Semester')
        }
        
        if (rawData) {
            var data = JSON.parse(rawData)

            let sem = data.SEMESTER.split(' ')
            let semester = sem[0] ?? ''
            let semStartY = sem[1].split('/')[0] ?? ''
            let semEndY = sem[1].split('/')[1] ?? ''         

            $('input[name="id_semester"]').val(data.ID_SEMESTER)
            $('#semester').val(semester).trigger('change')
            $('#year_start').val(semStartY)
            $('#year_end').val(semEndY)
            $('#id_kurikulum').val(data.ID_KURIKULUM).trigger('change')

            $('input[name="start_date"]').val(formatDateTimeLocal(data.START_SEMESTER));
            $('input[name="end_date"]').val(formatDateTimeLocal(data.END_SEMESTER));
            $('input[name="start_perwalian"]').val(formatDateTimeLocal(data.START_PERWALIAN));
            $('#status_perwalian').val(data.STATUS_PERWALIAN).trigger('change');
        } 

        $('#main-modal').modal('show')
    }

    // Helper function to format DATETIME string to YYYY-MM-DDTHH:MM
    function formatDateTimeLocal(dateTimeString) {
        if (!dateTimeString) {
            return '';
        }

        const date = new Date(dateTimeString);
        if (isNaN(date.getTime())) { 
            console.error("Invalid date string:", dateTimeString);
            return '';
        }

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0'); 
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        return `${year}-${month}-${day}T${hours}:${minutes}`;
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
        $('#semester').val('').trigger('change')
        $('#id_kurikulum').val('').trigger('change')
        $('#tahun_kurikulum').val('')
        $('input[name="id_semester"]').val('')
        $('input[name="start_date"]').val('')
        $('input[name="end_date"]').val('')
        $('input[name="start_perwalian"]').val('')
        $('#status_perwalian').val('').trigger('change')
        // Also clear year inputs for new entries
        $('#year_start').val('')
        $('#year_end').val('')
    }

    function modalConfirmDelete(id_sem) {
        swalWithBootstrapButtons.fire({
            title: `Apakah kamu yakin ingin menghapus semester ini ?`,
            // text: "Ini juga akan menghapus data detail capaian yang berkaitan dengan capaian ini!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = `<?= url('semester/delete') ?>?id=${id_sem}`
            }
        });
    }
</script>
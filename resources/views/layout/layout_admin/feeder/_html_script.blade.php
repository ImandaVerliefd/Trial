<?php

use Illuminate\Support\Facades\Request;
?>
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

    function filterData() {
        var element = $('#basic-datatable')
        var totPagesLoad = 5
        var dataUrl = "<?= url('feeder/get-all-data') ?>"
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
        }
        var dataColumn = [{
                data: 'FEEDER'
            },
            {
                data: 'ACTION_BUTTON'
            }
        ]

        processingDataTable(element, totPagesLoad, dataUrl, dataBody, dataColumn)
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

    function openModal(rawData = '') {
        resetModal()
        if (rawData) {
            var data = JSON.parse(rawData)

            $('#modal-label').html('Rubah Feeder')

            $('#id_feeder').val(data.ID_FEEDER)
            $('#feeder').val(data.FEEDER)
        } else {
            $('#modal-label').html('Tambahkan Feeder Baru')
        }

        $('#main-modal').modal('show')
    }

    function resetModal() {
        $('#id_feeder').val('')
        $('#feeder').val('')
    }

    function modalConfirmDelete(kodeCapaian) {
        swalWithBootstrapButtons.fire({
            title: `Apakah kamu yakin ingin menghapus feeder ini ?`,
            text: "",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = `<?= url('feeder/delete') ?>?kode=${kodeCapaian}`
            }
        });
    }
</script>
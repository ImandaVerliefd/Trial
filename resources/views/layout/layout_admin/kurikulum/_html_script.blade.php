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

    var idKurikulum = '<?= (!empty($idKurikulum) ? $idKurikulum : '') ?>'
    var kurikulum = '<?= (!empty($kurikulum) ? $kurikulum : '') ?>'
    var tahun = '<?= !empty($tahun) ? json_encode($tahun) : json_encode([]) ?>'

    $(document).ready(function() {
        <?= (Request::is('kurikulum*')) ? "$('#sidebarPages').collapse('show')" : ''; ?>

        var date = new Date();
        var year = date.getFullYear();

        $("#tahun").datepicker({
            format: "yyyy",
            viewMode: "years",
            minViewMode: "years",
            autoclose: true
        }).datepicker("setDate", date);


        filterData()
    });
</script>

<!-- Script Capaian -->
<script>
    function filterData() {
        var element = $('#basic-datatable')
        var totPagesLoad = 5
        var dataUrl = "<?= url('kurikulum/get-all-data') ?>"
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
        }
        var dataColumn = [{
                data: 'KURIKULUM'
            },
            {
                data: 'TAHUN'
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

    function openModal(rawData = '') {
        resetModal()
        if (rawData) {
            var data = JSON.parse(rawData)

            $('#modal-label').html('Ubah Kurikulum')

            $('#id_kurikulum').val(data.ID_KURIKULUM)
            $('#kurikulum').val(data.KURIKULUM)
            $('#tahun').val(data.TAHUN).trigger('change')

        } else {
            $('#modal-label').html('Tambahkan Kurikulum Baru')
            var date = new Date();
            var year = date.getFullYear();
            $("#tahun").val(year)
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
        $('#id_kurikulum').val('')
        $('#kurikulum').val('')
        $('#tahun').val('').trigger('change')
    }

    function modalConfirmActive(idKurikulum, status) {
        swalWithBootstrapButtons.fire({
            title: `Apakah kamu yakin ingin ${((status == 0) ? 'menonaktifkan' : 'mengaktifkan')} kurikulum ini ?`,
            text: "",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = `<?= url('kurikulum/change-status') ?>?id=${idKurikulum}&active=${status}`
            }
        });
    }

    function modalConfirmDelete(idKurikulum) {
        swalWithBootstrapButtons.fire({
            title: `Apakah kamu yakin ingin menghapus kurikulum ini ?`,
            // text: "Ini juga akan menghapus data detail capaian yang berkaitan dengan capaian ini!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = `<?= url('kurikulum/delete') ?>?id=${idKurikulum}`
            }
        });
    }
</script>
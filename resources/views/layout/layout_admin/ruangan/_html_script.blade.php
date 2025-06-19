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

    var idRuangan = '<?= (!empty($idRuangan) ? $idRuangan : '') ?>'
    var namaRuangan = '<?= (!empty($namaRuangan) ? $namaRuangan : '') ?>'
    var tipe = '<?= !empty($tipe) ? json_encode($tipe) : json_encode([]) ?>'

    $(document).ready(function() {
        <?= (Request::is('ruangan*')) ? "$('#sidebarPages').collapse('show')" : ''; ?>


        filterData()
    });
</script>

<!-- Script Capaian -->
<script>
    function filterData() {
        var element = $('#basic-datatable')
        var totPagesLoad = 5
        var dataUrl = "<?= url('ruangan/get-all-data') ?>"
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
        }
        var dataColumn = [{
                data: 'NAMA_RUANGAN'
            },
            {
                data: 'TIPE'
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

            $('#modal-label').html('Ubah Ruangan')

            $('#id_ruangan').val(data.ID_RUANGAN)
            $('#nama_ruangan').val(data.NAMA_RUANGAN)
            $('#tipe').val(data.TIPE)

        } else {
            $('#modal-label').html('Tambahkan Ruangan Baru')
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
        $('#id_ruangan').val('')
        $('#nama_ruangan').val('')
        $('#tipe').val('')
    }

    function modalConfirmActive(idRuangan, status) {
        swalWithBootstrapButtons.fire({
            title: `Apakah kamu yakin ingin ${((status == 0) ? 'menonaktifkan' : 'mengaktifkan')} ruangan ini ?`,
            text: "",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = `<?= url('ruangan/change-status') ?>?id=${idRuangan}&active=${status}`
            }
        });
    }

    function modalConfirmDelete(idRuangan) {
        swalWithBootstrapButtons.fire({
            title: `Apakah kamu yakin ingin menghapus ruangan ini ?`,
            // text: "Ini juga akan menghapus data detail capaian yang berkaitan dengan capaian ini!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = `<?= url('ruangan/delete') ?>?id=${idRuangan}`
            }
        });
    }
</script>
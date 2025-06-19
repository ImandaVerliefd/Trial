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

    var idProdi = '<?= (!empty($idProdi) ? $idProdi : '') ?>'
    var prodi = '<?= (!empty($prodi) ? $prodi : '') ?>'

    $(document).ready(function() {
        <?= (Request::is('prodi*')) ? "$('#sidebarPages').collapse('show')" : ''; ?>


        filterData()
    });
</script>

<!-- Script Capaian -->
<script>
    function filterData() {
        var element = $('#basic-datatable')
        var totPagesLoad = 5
        var dataUrl = "<?= url('prodi/get-all-data') ?>"
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
        }
        var dataColumn = [
            {
                data: 'PRODI'
            }
        ]

        processingDataTable(element, totPagesLoad, dataUrl, dataBody, dataColumn)
    }

    function openModal() {
        openSyncModal('/prodi/syncronize');
    }

    function openSyncModal(syncUrl) {
        Swal.fire({
            title: 'Sinkronisasi Data Program Studi',
            text: 'Apakah anda yakin ingin melakukan sinkronisasi data prodi?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan!',
            cancelButtonText: 'Tidak',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Sinkronisasi...',
                    text: 'Harap menunggu sinkronisasi sedang dilakukan!.',
                    icon: 'info',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                        $.ajax({
                            url: syncUrl,
                            method: 'POST',
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            },
                            success: function(response, status, xhr) {
                                Swal.close();
                                if (xhr.status === 204) {
                                    Swal.fire({
                                        title: 'Tidak ada data',
                                        text: 'Tidak ada data dosen yang dapat disinkronisasikan.',
                                        icon: 'info',
                                        allowOutsideClick: false,
                                    });
                                    return;
                                }

                                Swal.fire({
                                    title: 'Success',
                                    text: response.message || 'Sinkronisasi data selesai!',
                                    icon: 'success',
                                    allowOutsideClick: false,
                                    showCancelButton: false,
                                    confirmButtonText: 'Refresh Page',
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        location.reload();
                                    }
                                });
                            },
                            error: function(xhr) {
                                Swal.close();
                                let message = 'An error occurred during synchronization.';
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
        });
    }
</script>
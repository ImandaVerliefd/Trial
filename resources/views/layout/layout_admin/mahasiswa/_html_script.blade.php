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
        <?= (Request::is('mahasiswa*')) ? "$('#sidebarPages').collapse('show')" : ''; ?>

        filterData()
    });

    function filterData() {
        var element = $('#basic-datatable')
        var totPagesLoad = 5
        var dataUrl = "<?= url('mahasiswa/get-all-data') ?>"
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
        }
        var dataColumn = [{
                data: 'NAMA_MAHASISWA'
            },
            {
                data: 'NIM'
            },
            {
                data: 'PRODI'
            },
            {
                data: 'JENIS_KELAMIN'
            },
            {
                data: 'IS_ACTIVE'
            }
        ]

        $('#basic-datatable tfoot th').each(function() {
            var title = $(this).text()
            if (title == "Prodi") {
                $(this).html(`
                    <select class="form-control">
                        <option value="" >Pilih Program Studi</option>
                        <?php foreach ($prodi as $item) : ?>
                        <option value="<?= $item->JENJANG ?> <?= $item->PRODI ?>" ><?= $item->JENJANG ?> <?= $item->PRODI ?></option>
                        <?php endforeach ?>
                    </select>`)
            } else if (title == "Jenis Kelamin") {
                $(this).html(`
                    <select class="form-control">
                        <option value="" >Pilih Jenis Kelamin</option>
                            
                        <option value="Laki-laki" >Laki-laki</option>
                        <option value="Perempuan" >Perempuan</option>
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

    function openModal() {
        openSyncModal('/mahasiswa/syncronize');
    }

    function openSyncModal(syncUrl) {
        Swal.fire({
            title: 'Sinkronisasi Data Mahasiswa',
            text: 'Apakah anda yakin ingin melakukan sinkronisasi data mahasiswa?',
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
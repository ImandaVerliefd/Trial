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
</script>

<!-- Script Perwalian -->
<script>
    function submitKelas(element) {
        let id_kelas = element.value;
        let id_detsem = element.name;
        let index = element.getAttribute("data-index");

        $.ajax({
            url: "<?= url('krs/ajax/submit') ?>",
            method: 'POST',
            data: {
                ID_KELAS: id_kelas,
                ID_DETSEM: id_detsem
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: function(response, status, xhr) {
                if (response.status == 'success') {                    
                    $(".remaining-slot").text("0");
                } else if (response.status == 'failed') {
                    element.checked = false;
                }

                let data = response.CHECKING_DATA;

                $.each(data, function(key, item) {
                    let spanElement = $("#" + key);
                    
                    if (spanElement.length > 0) {
                        spanElement.text(item.total_students);
                    }                     
                });
            },
            error: function(xhr, status, error) {
                console.error("Error:", error);
            }
        });
    }

    function submitForm() {
        Swal.fire({
            title: 'Selesaikan Perwalian',
            text: 'Apakah anda yakin ingin menyelesaikan perwalian?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan!',
            cancelButtonText: 'Tidak',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                const allRadioButtons = document.querySelectorAll('input[type="radio"]');
                const radioGroups = {};

                allRadioButtons.forEach(radio => {
                    if (!radioGroups[radio.name]) {
                        radioGroups[radio.name] = [];
                    }
                    radioGroups[radio.name].push(radio);
                });

                let allSelected = true;
                for (const groupName in radioGroups) {
                    const isGroupSelected = radioGroups[groupName].some(radio => radio.checked);
                    if (!isGroupSelected) {
                        allSelected = false;
                    }
                }

                if (!allSelected) {
                    Swal.fire({
                        title: 'Peringatan!',
                        text: 'Harap pilih semua opsi sebelum melanjutkan.',
                        icon: 'warning',
                    });
                    return;
                }
                
                Swal.fire({
                    title: '...',
                    text: 'Harap menunggu beberapa saat.',
                    icon: 'info',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                        $.ajax({
                            url: "<?= url('krs/submit') ?>",
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                    'content'),
                            },
                            success: function(response, status, xhr) {
                                Swal.close();

                                Swal.fire({
                                    title: 'Success',
                                    text: response.message ||
                                        'Pewalian selesai!',
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
                                let message =
                                    'An error occurred during synchronization.';
                                if (xhr.status === 500) {
                                    message = xhr.responseJSON?.message ||
                                        'Internal Server Error!';
                                } else if (xhr.status === 404) {
                                    message =
                                        'The synchronization endpoint was not found.';
                                } else if (xhr.status === 403) {
                                    message =
                                        'You are not authorized to perform this action.';
                                } else if (xhr.status === 422) {
                                    message =
                                        'Validation error. Please check your input.';
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

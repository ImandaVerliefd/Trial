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
        filterData()
    });
</script>

<!-- Script Capaian -->
<script>
    $('#id_detsem').select2({
        dropdownParent: $('#main-modal'),
        placeholder: "-- Pilih Mata Kuliah --",
    });

    $('#id_detsem').change(function() {
        var idDetsem = $(this).val()

        if ($.fn.select2 && $('#kode_mahasiswa').hasClass('select2-hidden-accessible')) {
            $('#kode_mahasiswa').select2('destroy');
        }
        $('#kode_mahasiswa').select2();

        $.ajax({
            url: '<?= url('kelompok-praktik/get-mhs-data') ?>',
            type: 'POST',
            data: {
                '_token': $('meta[name="csrf-token"]').attr('content'),
                'id_detsem': idDetsem
            },
            dataType: 'json',
            beforeSend: function() {
                $('#kode_mahasiswa').attr('disabled', true)
                $('#loading-container').html(`
                    <svg width="20" height="20" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                        <g transform="translate(50,50)">
                            <circle cx="0" cy="-30" r="6" fill="currentColor">
                            <animateTransform attributeName="transform" type="rotate" values="0;360" dur="1s" repeatCount="indefinite"/>
                            </circle>
                            <circle cx="0" cy="-30" r="6" fill="currentColor" transform="rotate(45)">
                            <animateTransform attributeName="transform" type="rotate" values="0;360" dur="1s" repeatCount="indefinite" begin="0.125s"/>
                            </circle>
                            <circle cx="0" cy="-30" r="6" fill="currentColor" transform="rotate(90)">
                            <animateTransform attributeName="transform" type="rotate" values="0;360" dur="1s" repeatCount="indefinite" begin="0.25s"/>
                            </circle>
                            <circle cx="0" cy="-30" r="6" fill="currentColor" transform="rotate(135)">
                            <animateTransform attributeName="transform" type="rotate" values="0;360" dur="1s" repeatCount="indefinite" begin="0.375s"/>
                            </circle>
                            <circle cx="0" cy="-30" r="6" fill="currentColor" transform="rotate(180)">
                            <animateTransform attributeName="transform" type="rotate" values="0;360" dur="1s" repeatCount="indefinite" begin="0.5s"/>
                            </circle>
                            <circle cx="0" cy="-30" r="6" fill="currentColor" transform="rotate(225)">
                            <animateTransform attributeName="transform" type="rotate" values="0;360" dur="1s" repeatCount="indefinite" begin="0.625s"/>
                            </circle>
                            <circle cx="0" cy="-30" r="6" fill="currentColor" transform="rotate(270)">
                            <animateTransform attributeName="transform" type="rotate" values="0;360" dur="1s" repeatCount="indefinite" begin="0.75s"/>
                            </circle>
                            <circle cx="0" cy="-30" r="6" fill="currentColor" transform="rotate(315)">
                            <animateTransform attributeName="transform" type="rotate" values="0;360" dur="1s" repeatCount="indefinite" begin="0.875s"/>
                            </circle>
                        </g>
                        </svg>
                        Loading ...
                `);
            },
            success: function(response) {
                $('#kode_mahasiswa').attr('disabled', false)
                $('#kode_mahasiswa').html('')
                $('#loading-container').html('');
                if (response.length > 0) {
                    $.each(response, function(index, val) {
                        $('#kode_mahasiswa').append(`<option value="${val.KODE_MAHASISWA}">${val.NAMA_MAHASISWA}</option>`);
                    });
                } else {
                    $('#kode_mahasiswa').html('');
                }

                if ($.fn.select2 && $('#kode_mahasiswa').hasClass('select2-hidden-accessible')) {
                    $('#kode_mahasiswa').select2('destroy');
                }
                $('#kode_mahasiswa').select2();
            },
            error: function() {
                $('#loading-container').html('<span class="text-danger">* Error loading data</span>');
            }
        });
    });

    $('#kode_dosen').select2();

    function filterData() {
        var element = $('#basic-datatable')
        var totPagesLoad = 5
        var dataUrl = "<?= url('kelompok-praktik/get-all-data') ?>"
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
        }
        var dataColumn = [{
                data: 'NAMA_KELOMPOK'
            },
            {
                data: 'NAMA_MATKUL'
            },
            {
                data: 'DOSEN_LIST'
            },
            {
                data: 'MAHASISWA_LIST'
            },
            {
                data: 'ACTION_BUTTON'
            }
        ]

        processingDataTable(element, totPagesLoad, dataUrl, dataBody, dataColumn)
    }

    function openModal(rawData = '') {
        resetModal();
        if (rawData) {
            // Edit Mode
            var data = JSON.parse(rawData);

            $('#modal-label').html('Ubah Kelompok');

            var arrayKodeMhs = data.KODE_MHS.split(';');
            $('#kode_mahasiswa').val(arrayKodeMhs).trigger('change');

            var arrayKodeDsn = data.KODE_DOSEN.split(';');
            $('#kode_dosen').val(arrayKodeDsn).trigger('change');

            $('#id_detsem').val(data.ID_DETSEM).trigger('change');
            $('#id_kelompok_head').val(data.ID_KELOMPOK_HEAD)
            $('#nama_kelompok').val(data.NAMA_KELOMPOK)
        } else {
            // Add Mode
            $('#modal-label').html('Tambahkan Kelompok');
        }
        $('#main-modal').modal('show');
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

    function modalConfirmDelete(data) {
        swalWithBootstrapButtons.fire({
            title: `Apakah kamu yakin ingin menghapus kelompok praktik ini ?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = `<?= url('kelompok-praktik/delete') ?>?id=${btoa(data)}`
            }
        });
    }

    function resetModal() {
        $('#id_kelompok_head').val('')
        $('#nama_kelompok').val('')
        $('#id_detsem').val('').trigger('change')
        $('#kode_mahasiswa').val('').trigger('change')
        $('#kode_dosen').val('').trigger('change')
    }
</script>
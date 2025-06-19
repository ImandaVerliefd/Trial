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

<script>
    $('#select_pertemuan').on('change', function() {
        $('#rekapitulasi-content').empty();
        let id_semester = $(this).val();

        $.ajax({
            url: '/kehadiran_kuliah/get_rekapitulasi',
            method: 'POST',
            data: {
                id_semester: id_semester,
                _token: '{{ csrf_token() }}'
            },
            beforeSend: function() {
                $('#rekapitulasi-content').html(
                    '<div class="text-center my-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>'
                );
            },
            success: function(response) {
                $('#rekapitulasi-content').html(response.html);
            },
            error: function(xhr) {
                console.error('Gagal:', xhr.responseText);
            }
        });
    });

    $('#select_kehadiran').on('change', function() {
        $('#kehadiran-content').empty();
        let id_semester = $(this).val();

        $.ajax({
            url: '/kehadiran_kuliah/get_kehadiran',
            method: 'POST',
            data: {
                id_semester: id_semester,
                _token: '{{ csrf_token() }}'
            },
            beforeSend: function() {
                $('#kehadiran-content').html(
                    '<div class="text-center my-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>'
                );
            },
            success: function(response) {
                $('#kehadiran-content').html(response.html);
            },
            error: function(xhr) {
                console.error('Gagal:', xhr.responseText);
            }
        });
    });

    $('#select-pertemuan').on('change', function() {
        $('#pertemuan-content').empty();
        let selectedValue = $(this).val();
        let [id_detsem, kode_kelas] = selectedValue.split(';');

        $.ajax({
            url: '/kehadiran_kuliah/get_pertemuan',
            method: 'POST',
            data: {
                id_detsem: id_detsem,
                kode_kelas: kode_kelas,
                _token: '{{ csrf_token() }}'
            },
            beforeSend: function() {
                $('#pertemuan-content').html(
                    '<div class="text-center my-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>'
                );
            },
            success: function(response) {                
                $('#pertemuan-content').html(response.html);
            },
            error: function(xhr) {
                console.error('Gagal:', xhr.responseText);
            }
        });
    });
</script>
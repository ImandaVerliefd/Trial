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

<script>
    function filterData() {
        var element = $('#basic-datatable')
        var totPagesLoad = 5
        var dataUrl = "<?= url('mahasiswa-wali/get-all-data') ?>"
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
        }
        var dataColumn = [{
                data: 'TAHUN_MASUK'
            },
            {
                data: 'NIM'
            },
            {
                data: 'NAMA_MAHASISWA'
            },
            {
                data: 'EMAIL_MAHASISWA'
            },
            {
                data: 'IS_ACTIVE'
            },
            {
                data: 'ACTION_BUTTON'
            },
        ]

        $('#basic-datatable tfoot th').each(function() {
            var title = $(this).text()
            if (title == "Status Aktif") {
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
</script>
<script>
    $(document).ready(function() {
        filterDataRekapPenilaian();
    });

    function filterDataRekapPenilaian() {
        var element = $('#rekap-penilaian-datatable');
        var dataUrl = "<?= url('/rekap-penilaian/get-all-matkul') ?>";
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
        };
        var dataColumn = [{
                data: 'NAMA_MATKUL'
            },
            { 
                data: 'KODE_KELAS' 
            },
            {
                data: 'ACTION_BUTTON',
                orderable: false, 
                searchable: false 
            }
        ];

        if (typeof processingDataTable === 'function') {
            processingDataTable(element, 5, dataUrl, dataBody, dataColumn);
        } else {
            console.error('Fungsi `processingDataTable` tidak ditemukan. Pastikan fungsi tersebut sudah dimuat.');

             element.DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: dataUrl,
                    type: 'POST',
                    data: dataBody,
                },
                columns: dataColumn
            });
        }
    }
</script>

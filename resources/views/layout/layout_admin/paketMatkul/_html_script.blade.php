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
        <?= (Request::is('paket-mata-kuliah*')) ? "$('#sidebarPages').collapse('show')" : ''; ?>

        filterData()
        fillUpdate()
    });
</script>

<!-- Script Capaian -->
<script>
    $('#paket-matkul').select2({
        multiple: true,
        ajax: {
            url: '<?= url('paket-mata-kuliah/matkul/search') ?>',
            dataType: 'json',
            delay: 250,
            method: 'POST',
            data: function(params) {
                let prodi = $('#prodi').val();

                if (!prodi) {
                    return false;
                }

                return {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    prodi: prodi,
                    search: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: $.map(data, function(item) {
                        return {
                            id: item.KODE_MATKUL,
                            text: item.JENJANG + ' - ' + item.NAMA_MATKUL
                        };
                    })
                };
            },
            beforeSend: function() {
                $('#paket-matkul').next('.select2-container').addClass('loading');
            },
            complete: function() {
                $('#paket-matkul').next('.select2-container').removeClass('loading');
            },
            cache: true
        },
        minimumInputLength: 0
    });

    $('#tahunajar').change(function() {
        let valueData = $(this).val();
        $("input[name='tahunajar']").val(valueData);
        updatePaketMatkul();
        filteredSemester()
    });

    $('#prodi').change(function() {
        let valueData = $(this).val();
        $("input[name='prodi']").val(valueData);
        updatePaketMatkul();
    });

    function filteredSemester() {
        var selectedOpt = $('#tahunajar').find(':selected');
        var txtOpt = selectedOpt.text().split(' ');

        var dataSem = Array.from({
            length: 9
        }, (_, i) => i).slice(1);


        var $usedSemester = $('#used_semester');
        $usedSemester.empty();
        dataSem.forEach(num => {
            $usedSemester.append(new Option(`Semester ${num}`, num));
        });
    }

    function updatePaketMatkul() {
        let semester = $('#tahunajar').val();
        let prodi = $('#prodi').val();

        if (semester && prodi) {
            $('#paket-matkul').val(null).trigger('change');
        }
    }

    function fillUpdate() {
        <?php if (!empty($detail_paket)) : ?>
            var dataRaw = JSON.parse(`<?= json_encode($detail_paket) ?>`)
            $.each(dataRaw, function(index, val) {
                $('#prodi').val(val.ID_PRODI).trigger('change')
                $('#tahunajar').val(val.ID_TAHUN_AJAR).trigger('change')
                $('#used_semester').val(val.KODE_SEMESTER).trigger('change')

                let selectedOptions = [];
                $.each(val.KODE_MATKUL.split(';'), function(index, kodeMatkul) {
                    let namaMatkul = val.NAMA_MATKUL.split(';')[index];
                    $('#paket-matkul').prepend(new Option(namaMatkul, kodeMatkul, true, true));
                    selectedOptions.push(kodeMatkul);
                });
                $('#paket-matkul').val(selectedOptions).trigger('change');
            })
        <?php endif; ?>
    }

    function filterData() {
        var element = $('#basic-datatable')
        var totPagesLoad = 5
        var dataUrl = "<?= url('paket-mata-kuliah/get-all-data') ?>"
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
        }
        var dataColumn = [{
                data: 'JENJANG'
            },
            {
                data: 'MATKUL'
            },
            {
                data: 'TOT_SKS'
            },
            {
                data: 'KODE_SEMESTER'
            },
            {
                data: 'TAHUN_AJAR'
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

    function modalConfirmDelete(idMatkul) {
        swalWithBootstrapButtons.fire({
            title: `Apakah kamu yakin ingin menghapus mata kuliah ini ?`,
            // text: "Ini juga akan menghapus data detail capaian yang berkaitan dengan capaian ini!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = `<?= url('paket-mata-kuliah/delete') ?>?id=${idMatkul}`
            }
        });
    }
</script>
<script>
    console.warn = function() {};

    $('tr').on('click', function() {
        const radio = $(this).find('input[type="radio"]');
        if (radio.length) {
            radio.prop('checked', true);
        }
    });

    $('input[name="kode_matkul[]"]').each(function(index) {
        $(`#kurikulum_${index}`).select2({
            placeholder: '-- Pilih Kurikulum --'
        });
        $(`#id_semester_${index}`).select2({
            placeholder: '-- Pilih Semester --'
        });
    });

    var dbData = JSON.parse('<?= json_encode($matkulByPaket) ?>')
    if (dbData) {
        $.each(dbData, function(index, item) {
            var elemKodeMatkul = $('input[name="kode_matkul[]"]').eq(index)
            renderKurikulumByKodeMatkul(elemKodeMatkul, index)

            $(`#id_semester_${index}`).val(item.ID_SEMESTER).trigger('change')

            $(`#tahun_ajar_txt_${index}`).val(item.TAHUN_AJAR)
            $(`#tahun_ajar_${index}`).val(item.ID_TAHUN_AJAR)

            $(`#used_semester_txt_${index}`).val(`Semester ${item.KODE_SEMESTER}`)
            $(`#used_semester_${index}`).val(item.KODE_SEMESTER)

            $(`#rumpun_matkul_${index}`).val(item.RUMPUN_MATKUL)
            $(`#kat_matkul_${index}`).val(item.KATEGORI_MATKUL)
            $(`#desc_matkul_${index}`).html(item.DESKRIPSI_MATKUL)

            $(`#status_ck_${index}`).prop('checked', !!item.IS_DIADAKAN)
            $(`#status_mk_${index}`).val(item.IS_DIADAKAN)
        })
    }

    function renderKurikulumByKodeMatkul(e, index) {
        var kodeMatkul = $(e).val();
        var $kurikulum = $(`#kurikulum_${index}`);

        if (!kodeMatkul) {
            $kurikulum.prop('disabled', true).html('<option value="">-- Pilih Kurikulum --</option>');
            return;
        }

        $kurikulum.prop('disabled', false);
        $.ajax({
            url: '<?= url('kurikulum/search') ?>',
            type: 'POST',
            data: {
                '_token': $('meta[name="csrf-token"]').attr('content'),
                'kode_matkul': kodeMatkul
            },
            dataType: 'json',
            beforeSend: function() {
                $('#container-alert').html('')
                $kurikulum.html('<option value="">Loading...</option>');
            },
            success: function(response) {
                $kurikulum.html('<option value="">-- Pilih Kurikulum --</option>');

                if (response.length > 0) {
                    $.each(response, function(_, val) {
                        $kurikulum.append(`<option value="${val.ID_KURIKULUM}">${val.KURIKULUM}</option>`);
                    });
                } else {
                    $kurikulum.append('<option value="">Tidak ada kurikulum</option>');
                    $('#container-alert').html(`
                        <div class="alert alert-danger mb-0" role="alert">
                            <i class="ri-close-circle-line me-1 align-middle fs-16"></i> Data mata kuliah belum lengkap.
                            Harap melengkapi data CPMK dan RPS terlebih dahulu agar data kurikulum dapat muncul.
                        </div>
                    `)
                }

                if (dbData) {
                    $(`#kurikulum_${index}`).val(dbData[index].ID_KURIKULUM).trigger('change')
                }
            },
            error: function() {
                $kurikulum.html('<option value="">Error loading data</option>');
            }
        });
    }

    function openModal(modalId) {
        $('input[name="selectMatkul"]').each(function() {
            $(this).prop('checked', false);
        });
        $(modalId).modal('show')
    }

    function closeModal(modalId) {
        $(modalId).modal('hide')
        $(modalId).find(':focus').trigger('blur')
    }

    function openNextModal(currModal, nextModal, parentIndex) {
        $('#detail-matkul').html('')
        var selectedRadio = $('input[name="selectMatkul"]:checked');
        var selectedValue = selectedRadio.val();

        if (selectedValue) {
            var selectedId = selectedRadio.data('used-id');
            var selectedData = JSON.parse(`<?= json_encode($matkulTerkait) ?>`)[selectedId]

            $(currModal).modal('hide');
            $(nextModal).modal('show');

            $(currModal).find(':focus').trigger('blur')

            if (nextModal === '#matkul-modal-detail') {
                if (selectedData) {
                    $('#parent_index').val(parentIndex)
                    $('#used_index').val(selectedId)
                    $('#multiple-twoModalLabel').html(`${selectedData.NAMA_MATKUL}`)
                    $('#detail-matkul').html(`
                        <p><strong>Program Studi: </strong>${selectedData.PRODI || '-'}</p>
                        <p><strong>Tahun Ajaran: </strong>${selectedData.TAHUN_AJAR || '-'}</p>
                        <p><strong>Semester: </strong>${selectedData.KODE_SEMESTER || '-'}</p>
                        <p><strong>Rumpun Mata Kuliah: </strong>${selectedData.RUMPUN_MATKUL || '-'}</p>
                        <p><strong>Kategori Mata Kuliah: </strong>${selectedData.KATEGORI_MATKUL || '-'}</p>
                        <p><strong>Deskripsi Mata Kuliah:</strong><br>${selectedData.DESKRIPSI_MATKUL || '-'}</p>
                    `)
                } else {
                    $('#detail-matkul').html(`
                        <p><strong>Detail mata kuliah tidak ditemukan!</strong></p>
                    `)
                }
            }
        } else {
            Toast.fire({
                icon: "error",
                title: "Harap untuk memilih mata kuliah terlebih dahulu!"
            });
        }

    }

    function CopyDetailMatkul(mainModal) {
        var index = $('#parent_index').val()
        var dataIndex = $('#used_index').val()
        var selectedData = JSON.parse(`<?= json_encode($matkulTerkait) ?>`)[dataIndex]
        if (selectedData) {
            var elemKodeMatkul = $('input[name="kode_matkul[]"]').eq(index)
            renderKurikulumByKodeMatkul(elemKodeMatkul, index)

            $(`#rumpun_matkul_${index}`).val(selectedData.RUMPUN_MATKUL || '-')
            $(`#kat_matkul_${index}`).val(selectedData.KATEGORI_MATKUL || '-')
            $(`#desc_matkul_${index}`).html(selectedData.DESKRIPSI_MATKUL || '-')

            $(mainModal).modal('hide');
            $(mainModal).find(':focus').trigger('blur')
        } else {
            Toast.fire({
                icon: "error",
                title: "Detail mata kuliah tidak ditemukan!"
            });
        }
    }

    function changeStatusMK(e) {
        const index = $(e).data('index');
        const checked = ($(e).is(':checked')) ? 1 : 0;

        $(`#status_mk_${index}`).val(checked)
        $(`#tab_head_${index}`).removeClass('text-danger text-success');
        
        if (checked == 1) {
            $(`#tab_head_${index}`).addClass('text-success');
        } else {
            $(`#tab_head_${index}`).addClass('text-danger');
        }
    }
</script>
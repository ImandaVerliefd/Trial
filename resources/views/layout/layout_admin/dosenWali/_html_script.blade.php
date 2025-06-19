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
    $('#kode_dosen').on('change', function() {
        let namaDosen = $(this).find(':selected').text()
        $("input[name='nama_dosen']").val(namaDosen)
    });
    $('#kode_dosen').select2({
        dropdownParent: $('#main-modal'),
        placeholder: "-- Pilih Dosen --",
    });

    $('#kode_mahasiswa').select2();

    function filterData() {
        var element = $('#basic-datatable')
        var totPagesLoad = 5
        var dataUrl = "<?= url('dosen-wali/get-all-data') ?>"
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
        }
        var dataColumn = [{
                data: 'NAMA_DOSEN'
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

            $('#modal-label').html('Ubah Dosen Wali');
            var arrayKodeMhs = data.KODE_MAHASISWA.split(', ');
            $('#kode_dosen').val(data.KODE_DOSEN).trigger('change');
            $('#kode_mahasiswa').val(arrayKodeMhs).trigger('change');
            $('#id_wali_dosen').val(data.ID_DOSEN_WALI);
        } else {
            // Add Mode
            $('#kode_mahasiswa').attr('multiple', 'multiple');
            $('#modal-label').html('Tambahkan Dosen Wali');

            let mahasiswaOptions = `
                @foreach($mhs_tdk_lengkap as $item)
                <option value="{{ $item->KODE_MAHASISWA }}">{{ $item->NIM }} - {{ $item->NAMA_MAHASISWA }}</option>
                @endforeach
            `;
            $('#kode_mahasiswa').html(mahasiswaOptions);
        }
        $('#main-modal').modal('show');
    }

    function openUpdateModal(data) {
        resetModal();

        var parsedData = JSON.parse(data);
        var arrayKodeMhs = parsedData.KODE_MAHASISWA.split(', ');
        var arrayIdWali = parsedData.ID_DOSEN_WALI.split(', ');
        var mhsOptions = <?= json_encode($mhs); ?>;
        // Dynamically generate the Mahasiswa options
        let mahasiswaOptions = '';
        arrayKodeMhs.forEach((kodeMhs) => {
            mahasiswaOptions += `
                <div class="col-md id-${arrayIdWali[arrayKodeMhs.indexOf(kodeMhs)]}">
                    <div class="mb-2 d-flex align-items-end">
                        <div style="flex: 1;">
                            <label for="up_kode_mhs" class="form-label">Mahasiswa <small class="text-danger">*</small></label>
                            <select name="up_kode_mhs[]" class="form-select" id="up_kode_mhs" required>
                                ${mhsOptions.map((item) => `
                                    <option value="${item.KODE_MAHASISWA}" ${kodeMhs === item.KODE_MAHASISWA ? 'selected' : ''}>
                                        ${item.NIM} - ${item.NAMA_MAHASISWA}
                                    </option>
                                `).join('')}
                            </select>
                            <input type="hidden" name="up_id_wali_dosen[]" id="up_id_wali_dosen" value="${arrayIdWali[arrayKodeMhs.indexOf(kodeMhs)]}">
                        </div>
                        <div style="padding-left: 10px;">
                            <button class="btn btn-danger" onclick="deleteMahasiswa('${arrayIdWali[arrayKodeMhs.indexOf(kodeMhs)]}')"><i class="ri-delete-bin-line"></i></button>
                        </div>
                    </div>
                </div>`;
        });

        // Update the modal content
        $('#div_mahasiswa').html(mahasiswaOptions);
        $('#modal-label').html('Ubah Dosen Wali');
        $('#update-modal #kode_dosen').val(parsedData.KODE_DOSEN).trigger('change');

        $('#up_id_wali_dosen').val(parsedData.ID_DOSEN_WALI);
        $('#update-modal').modal('show');
    }

    function deleteMahasiswa(id) {
        $(`.id-${id}`).remove();
        $('.delete').append(`<input type="hidden" name="delete_id_wali_dosen[]" value="${id}">`);
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

    function submitUpdateForm() {
        var isValid = true;
        var form = $('#form-update')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            isValid = false;
            return false;
        }

        if (isValid) {
            form.submit();
        }
    }

    function resetModal() {
        $('#kode_mahasiswa').val('').trigger('change')
        $('#kode_dosen').val('').trigger('change')
    }

    function modalConfirmDelete(data) {
        swalWithBootstrapButtons.fire({
            title: `Apakah kamu yakin ingin menghapus pemetaan Dosen Wali ini ?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = `<?= url('dosen-wali/delete') ?>?id=${btoa(data)}`
            }
        });
    }
</script>
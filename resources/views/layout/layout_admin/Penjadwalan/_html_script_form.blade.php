<!-- Script Form -->
<script>
    var perSKSDuration = 50;
    var RealSKS = <?= $detail_matkul->SKS ?? 0 ?>;
    var totalMinutes = RealSKS === 4 ? 2 * perSKSDuration : RealSKS * perSKSDuration;

    $("input[name='jam_mulai[]']").each(function(index) {
        var jamMulaiSelector = `#jam_mulai_${index}`;

        hitungJamSelesai(index)
        $(jamMulaiSelector).on('change', function() {
            hitungJamSelesai(index)
        });
    });

    $("input[name='sks_digunakan[]']").each(function(index, elem) {
        hitungJamSelesai(index)
        $(elem).on('change', function() {
            hitungJamSelesai(index)
        });
    });
    
    $('.select2-dosen').select2();

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

    function submitFormMatkul() {
        var isValid = true;
        var form = $('#form-submit-matkul')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            isValid = false;
            return false;
        }

        if (isValid) {
            let sksInput = 0
            let inputIndex = []
            $("input[name='sks_digunakan[]']").each(function(index, elem) {
                sksInput += parseInt($(elem).val())
                inputIndex.push(index)
            });
            if (sksInput < RealSKS) {
                $.each(inputIndex, function(i, val) {
                    showAlert(i, "Total SKS tidak boleh kurang dari " + RealSKS);
                })
                return;
            } else {
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

                $.ajax({
                    url: "<?= url('penjadwalan/check-ruangan') ?>",
                    method: 'POST',
                    data: $('#form-submit-matkul').serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: function(response, status, xhr) {
                        // response.status
                        if (true) {
                            form.submit();
                        } else {
                            let msg = 'Terjadi kesalahan saat memuat data. Silakan coba lagi.';
                            showMainAlert(response.msg)
                            swal.close()
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error:", error);
                        let msg = 'Terjadi kesalahan saat memuat data. Silakan coba lagi.';
                        showMainAlert(msg)
                        swal.close()
                    }
                });
            }
        }
    }

    function showAlert(index, message, timeout = 3500) {
        const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show mt-2 alert_id_${index}" role="alert">
                <i class="ri-close-circle-line me-1 align-middle fs-16"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        $('#alert-container_' + index).html(alertHtml);

        setTimeout(() => {
            $(`.alert_id_${index}`).alert('close');
        }, timeout);
    }

    function showMainAlert(message, timeout = 5500) {
        const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show mt-2 alert_id_main" role="alert">
                <i class="ri-close-circle-line me-1 align-middle fs-16"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        $('#alert_container_main').html(alertHtml);

        setTimeout(() => {
            $(`.alert_id_main`).alert('close');
        }, timeout);
    }

    function hitungJamSelesai(index) {
        var jamMulaiSelector = `#jam_mulai_${index}`;
        var jamSelesaiSelector = `#jam_selesai_${index}`;
        var sksSelector = `#sks_digunakan_${index}`;

        var jamMulai = $(jamMulaiSelector).val();
        var jamMulaiHour = parseInt(jamMulai.split(":")[0], 10);
        var totSKS = $(sksSelector).val();
        var sksInput = 0

        if (jamMulaiHour < 8) {
            showAlert(index, "Jam mulai tidak boleh di bawah <strong>08:00</strong>!");
            $(jamMulaiSelector).val('08:00');
            return;
        }

        if (!sksSelector || sksSelector < 1) {
            showAlert(index, "Total SKS tidak boleh kurang dari 1");
            $(sksSelector).val('1');
            return;
        }

        $("input[name='sks_digunakan[]']").each(function(index, elem) {
            sksInput += parseInt($(elem).val())
        });
        if (sksInput > RealSKS) {
            showAlert(index, "Total SKS tidak boleh lebih dari " + RealSKS);
            $(sksSelector).val('1');
            return;
        }

        totalMinutes = totSKS * perSKSDuration
        var maxTime = addMinutes(jamMulai, totalMinutes);
        $(jamSelesaiSelector).val(maxTime);
    }

    function addMinutes(time, minutesToAdd) {
        const [hours, minutes] = time.split(':').map(Number);
        var totalMinutes = hours * 60 + minutes + minutesToAdd;
        const newHours = Math.floor(totalMinutes / 60) % 24;
        const newMinutes = totalMinutes % 60;

        return `${String(newHours).padStart(2, '0')}:${String(newMinutes).padStart(2, '0')}`;
    }
</script>
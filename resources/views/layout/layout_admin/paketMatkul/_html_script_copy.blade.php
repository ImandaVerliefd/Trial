<script>
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger me-2"
        },
        buttonsStyling: false
    });

    $(document).ready(function() {
        // Initialize Select2 for all dropdowns on this page
        $('#prodi_source').select2({
            placeholder: "-- Pilih Program Studi --",
            allowClear: false, // Changed to false
            dropdownParent: $('#form-copy-paket')
        });

        $('#tahunajar_source').select2({
            placeholder: "-- Pilih Tahun Ajaran --",
            allowClear: false, // Changed to false
            dropdownParent: $('#form-copy-paket')
        });

        // Removed Select2 for semester_source
        // $('#semester_source').select2({
        //     placeholder: "-- Pilih Semester --",
        //     allowClear: false, // Changed to false
        //     dropdownParent: $('#form-copy-paket')
        // });

        $('#prodi_target').select2({
            placeholder: "-- Pilih Program Studi Target --",
            allowClear: false, // Changed to false
            dropdownParent: $('#form-copy-paket')
        });

        $('#tahunajar_target').select2({
            placeholder: "-- Pilih Tahun Ajaran Target --",
            allowClear: false, // Changed to false
            dropdownParent: $('#form-copy-paket')
        });

        // Removed Select2 for semester_target
        // $('#semester_target').select2({
        //     placeholder: "-- Pilih Semester Target --",
        //     allowClear: false, // Changed to false
        //     dropdownParent: $('#form-copy-paket')
        // });

        $('#matkul_to_copy').select2({
            placeholder: "-- Pilih Mata Kuliah (Opsional) --",
            allowClear: false, // Changed to false
            dropdownParent: $('#form-copy-paket')
        });

        // Event listener for source selections to fetch matkuls
        $('#prodi_source, #tahunajar_source, #semester_source').on('change', function() {
            let prodiSource = $('#prodi_source').val();
            let tahunajarSource = $('#tahunajar_source').val();
            let semesterSource = $('#semester_source').val();

            // Only fetch if all source selections are made
            if (prodiSource && tahunajarSource && semesterSource) {
                fetchMatkulForCopy(prodiSource, tahunajarSource, semesterSource);
            } else {
                $('#matkul_to_copy').empty().trigger('change'); // Clear options if not all selected
            }
        });

        // New: Event listener for source tahun ajaran to populate semester dropdown
        $('#tahunajar_source').on('change', function() {
            populateSemesters('tahunajar_source', 'semester_source');
        });

        // New: Event listener for target tahun ajaran to populate semester dropdown
        $('#tahunajar_target').on('change', function() {
            populateSemesters('tahunajar_target', 'semester_target');
        });

        // New: Event listener to automatically set target prodi to source prodi
        $('#prodi_source').on('change', function() {
            let selectedProdi = $(this).val();
            $('#prodi_target').val(selectedProdi).trigger('change');
        });


        // Initial population if academic years are already selected on page load
        populateSemesters('tahunajar_source', 'semester_source');
        populateSemesters('tahunajar_target', 'semester_target');
    });

    /**
     * Populates the semester dropdown based on the selected academic year.
     * @param {string} tahunAjarSelectId The ID of the academic year select element (e.g., 'tahunajar_source').
     * @param {string} semesterSelectId The ID of the semester select element (e.g., 'semester_source').
     */
    function populateSemesters(tahunAjarSelectId, semesterSelectId) {
        var selectedTahunAjar = $('#' + tahunAjarSelectId).val();
        var $semesterSelect = $('#' + semesterSelectId);
        $semesterSelect.empty(); // Clear existing options

        // Add default option
        $semesterSelect.append(new Option(`-- Pilih Semester ${semesterSelectId.includes('source') ? '' : 'Target'} --`, ''));

        if (selectedTahunAjar) {
            // Your original logic for generating semesters (1 to 8)
            var dataSem = Array.from({
                length: 9
            }, (_, i) => i).slice(1);

            dataSem.forEach(num => {
                $semesterSelect.append(new Option(`Semester ${num}`, num));
            });
        }
    }


    function fetchMatkulForCopy(idProdi, idTahunAjar, kodeSemester) {
        $.ajax({
            url: "<?= url('paket-mata-kuliah/get-matkul-for-copy') ?>",
            type: "GET",
            data: {
                id_prodi: idProdi,
                id_tahun_ajar: idTahunAjar,
                kode_semester: kodeSemester
            },
            beforeSend: function() {
                // Clear existing options and add a loading message
                $('#matkul_to_copy').empty().append(new Option('Loading Mata Kuliah...', '', true, true)).prop('disabled', true);
                $('#matkul_to_copy').trigger('change'); // Update Select2 display
            },
            success: function(response) {
                $('#matkul_to_copy').empty(); // Clear the loading message
                if (response.length > 0) {
                    let options = [];
                    response.forEach(function(item) {
                        options.push(new Option(item.NAMA_MATKUL + ' (' + item.SKS + ' SKS)', item.KODE_MATKUL, false, false));
                    });
                    $('#matkul_to_copy').append(options);
                } else {
                    $('#matkul_to_copy').append(new Option('Tidak ada mata kuliah ditemukan', '', true, true));
                    swalWithBootstrapButtons.fire(
                        'Informasi',
                        'Tidak ada mata kuliah ditemukan untuk kombinasi Prodi, Tahun Ajaran, dan Semester ini.',
                        'info'
                    );
                }
                $('#matkul_to_copy').prop('disabled', false).trigger('change'); // Re-enable and update Select2
            },
            error: function(xhr, status, error) {
                console.error("Error fetching matkuls:", error);
                $('#matkul_to_copy').empty().append(new Option('Error loading mata kuliah', '', true, true)).prop('disabled', false).trigger('change'); // Re-enable and show error
                swalWithBootstrapButtons.fire(
                    'Error!',
                    'Terjadi kesalahan saat mengambil data mata kuliah. Silakan coba lagi.',
                    'error'
                );
            }
        });
    }

    function submitCopyForm() {
        var isValid = true;
        var form = $('#form-copy-paket')[0];

        // Check if all required fields are filled
        if (!form.checkValidity()) {
            form.reportValidity();
            isValid = false;
            return false;
        }

        if (isValid) {
            Swal.fire({
                title: "Menyalin...",
                html: "Mohon tunggu, sistem sedang menyalin paket mata kuliah...",
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            form.submit();
        }
    }
</script>

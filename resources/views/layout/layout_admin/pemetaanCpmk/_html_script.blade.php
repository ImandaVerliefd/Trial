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
            <?= (Request::is('semester*')) ? "$('#sidebarPages').collapse('show')" : ''; ?>

            var savedProdi = localStorage.getItem("prodi")
            if (savedProdi) {
                $('.nav-tabs .nav-link[data-prodi="' + savedProdi + '"]').addClass("active");
            } else {
                $('.nav-tabs .nav-link').first().addClass("active");
            }

            $(".nav-tabs .nav-link").click(function() {
                let idProdi = $(this).data("prodi"); // Get tab name from data attribute
                localStorage.setItem("prodi", idProdi); // Save to Local Storage
                $('.nav-tabs .nav-link[data-prodi="' + idProdi + '"]').addClass("active");
            });

            var prodi = $(".nav-tabs .nav-link.active").attr("data-prodi");
            filterData(prodi)
        });
    </script>

    <!-- Script Capaian -->
    <script>
        $('#kode_matkul').select2({
            dropdownParent: $('#main-modal'),
            placeholder: "-- Pilih Mata Kuliah --",
        });

        $('#id_kurikulum').select2({
            dropdownParent: $('#main-modal'),
            placeholder: "-- Pilih Kurikulum --",
        }).change(function() {
            let kurikulum = $(this).find(':selected');
            $("input[name='tahun_kurikulum']").val(kurikulum.data('tahun'));
            var selectedKurikulum = kurikulum.val()

            $('#id_capaian').val(null).trigger('change');
            $('#id_capaian').select2({
                dropdownParent: $('#main-modal'),
                ajax: {
                    url: '<?= url('capaian-matkul/filtered-cpmk') ?>',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: function(params) {
                        return {
                            q: params.term,
                            kurikulum_id: (selectedKurikulum ?? '')
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(item => ({
                                id: item.KODE_CAPAIAN,
                                text: item.JENJANG + ' - ' + item.CAPAIAN
                            }))
                        };
                    },
                    cache: true
                }
            });
        });

        function reRenderTable() {
            const table = $('#basic-datatable');
            if ($.fn.DataTable.isDataTable(table)) {
                table.DataTable().clear().destroy();
            }

            var prodi = $(".nav-tabs .nav-link.active").attr("data-prodi");
            filterData(prodi)
        }

        function filterData(prodi) {
            var element = $('#basic-datatable');
            var totPagesLoad = 5;
            var dataUrl = "<?= url('capaian-matkul/get-all-data') ?>";
            var dataBody = {
                '_token': $('meta[name="csrf-token"]').attr('content'),
                'prodi': prodi,
            };
            var dataColumn = [{
                    className: 'dt-control',
                    orderable: false,
                    data: null,
                    defaultContent: ''
                },
                {
                    data: 'NAMA_MATKUL'
                },
                {
                    data: 'CAPAIAN',
                    visible: false
                },
                {
                    data: 'KURIKULUM'
                },
                {
                    data: 'ACTION_BUTTON'
                }
            ];

            let table = processingDataTable(element, totPagesLoad, dataUrl, dataBody, dataColumn);

            // table.order([
            //     [1, 'asc']
            // ]).draw();

            element.on('click', 'td.dt-control', function(e) {
                let tr = e.target.closest('tr');
                let row = table.row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                } else {
                    row.child(format(row.data())).show();
                }
            });

            function format(d) {
                if (d) {
                    return `
                        <dl>
                            <dt>Mata Kuliah:</dt>
                            <dd>${d.NAMA_MATKUL}</dd>
                            <dt>Capaian Mata Kuliah:</dt>
                            <dd style="white-space: pre-line;">${d.CAPAIAN}</dd>
                        </dl>
                    `;
                } else {
                    return `
                        <dl>
                            <dt>Data error</dt>
                        </dl>
                    `;
                }
            }

        }

        function openModal(rawData = '') {
            resetModal()
            if (rawData) {
                var data = JSON.parse(rawData)

                $('#kode_matkul').val(data.KODE_MATKUL).trigger('change')
                $('#id_kurikulum').val(data.ID_KURIKULUM).trigger('change')

                $('#modal-label').html('Ubah CPMK')
                let kodeCapaianArray = (data.KODE_CAPAIAN ? String(data.KODE_CAPAIAN) : "").split(';')
                let nameCapaianArray = (data.CAPAIAN ? String(data.CAPAIAN) : "").split(';')

                $.each(kodeCapaianArray, function(index, val) {
                    let option = new Option(nameCapaianArray[index], val, false, kodeCapaianArray.includes(String(val)));
                    $('#id_capaian').append(option);
                })
                $('#id_capaian').val(kodeCapaianArray).trigger('change')
            } else {
                $('#modal-label').html('Tambahkan CPMK Baru')
            }

            $('#main-modal').modal('show')
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

        function resetModal() {
            $('#kode_matkul').val('').trigger('change')
            $('#id_capaian').val('').trigger('change')
            $('#id_kurikulum').val('').trigger('change')
        }

        function modalConfirmDelete(data) {
            swalWithBootstrapButtons.fire({
                title: `Apakah kamu yakin ingin menghapus pemetaan CPMK ini ?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya",
                cancelButtonText: "Tidak",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    location.href = `<?= url('capaian-matkul/delete') ?>?id=${btoa(data)}`
                }
            });
        }
    </script>
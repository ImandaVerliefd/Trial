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
        allData()
    })
</script>

<script>
    document.getElementById("tahun-ajaran").addEventListener("change", function() {
        let id_value = this.value;

        if (id_value === "") {
            return;
        }

        document.getElementById("loading-spinner").style.display = "block";
        document.getElementById("alert-container").innerHTML = ""; // Hapus alert sebelumnya

        $.ajax({
            url: "<?= url('jadwal-dosen/get-data-ajaran') ?>",
            method: 'POST',
            data: {
                ID_SEMESTER: id_value
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: function(response, status, xhr) {
                document.getElementById("loading-spinner").style.display = "none";

                let tableBody = $('#basic-datatable tbody');
                tableBody.empty();

                if (response.data[0] == null) {
                    document.getElementById("alert-container").innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            Data tidak ditemukan untuk Tahun Ajaran yang dipilih.
                        </div>
                    `;
                } else {
                    const orderHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu',
                        'Minggu'
                    ];

                    let groupedData = response.data.reduce((acc, item) => {
                        if (!acc[item.PRODI]) {
                            acc[item.PRODI] = [];
                        }
                        acc[item.PRODI].push(item);
                        return acc;
                    }, {});

                    Object.keys(groupedData).forEach(prodi => {
                        let prodiHeader = `
                            <tr>
                                <td class="text-center fw-bolder" colspan="6" style="background-color: rgb(84, 145, 202);">${prodi}</td>
                            </tr>
                        `;
                        tableBody.append(prodiHeader);

                        let sortedData = groupedData[prodi].sort((a, b) => {
                            return orderHari.indexOf(a.HARI) - orderHari.indexOf(b
                                .HARI);
                        });

                        sortedData.forEach(item => {
                            let row = `
                                <tr>
                                    <td>${item.HARI}</td>
                                    <td>${item.JAM_MULAI} - ${item.JAM_SELESAI}</td>
                                    <td>${item.SKS}</td>
                                    <td>${item.NAMA_MATKUL}</td>
                                    <td>${item.NAMA_DOSEN}</td>
                                    <td>${item.NAMA_RUANGAN}</td>
                                </tr>
                            `;
                            tableBody.append(row);
                        });
                    });
                }
            },
            error: function(xhr, status, error) {
                document.getElementById("loading-spinner").style.display = "none";
                console.error("Error:", error);
                document.getElementById("alert-container").innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        Terjadi kesalahan saat memuat data. Silakan coba lagi.
                    </div>
                `;
            }
        });
    });

    function allData() {
        var element = $('#all-data-datatable')
        var totPagesLoad = 10
        var dataUrl = "<?= url('jadwal-dosen/get-all-data') ?>"
        var dataBody = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
        }
        var dataColumn = [{
                data: 'PERIODE'
            },
            {
                data: 'KODE_MATKUL'
            },
            {
                data: 'NAMA_MATKUL'
            },
            {
                data: 'NAMA_RUANGAN'
            },
            {
                data: 'NAMA_DOSEN'
            },
            {
                data: 'PERTEMUAN',
            }
        ]

        processingDataTable(element, totPagesLoad, dataUrl, dataBody, dataColumn)
    }
</script>

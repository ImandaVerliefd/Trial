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
    function declineForm(event) {
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

    function acceptForm() {
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
</script>
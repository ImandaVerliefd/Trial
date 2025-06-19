<!-- Daterangepicker css -->
<link rel="stylesheet" href="{{ asset('assets') }}/vendor/daterangepicker/daterangepicker.css">


<!-- Vector Map css -->
<link rel="stylesheet" href="{{ asset('assets') }}/vendor/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css">

<!-- sweetalert2 -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/sweetalert2@11.6.6/dist/sweetalert2.all.min.js"></script>

<!-- Datatables css -->
<link href="{{ asset('assets') }}/vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets') }}/vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets') }}/vendor/datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets') }}/vendor/datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets') }}/vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets') }}/vendor/datatables.net-select-bs5/css/select.bootstrap5.min.css" rel="stylesheet" type="text/css" />


<!-- Bootstrap Datepicker css -->
<link href="{{ asset('assets') }}/vendor/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

<!-- Select2 css -->
<link href="{{ asset('assets') }}/vendor/select2/css/select2.min.css" rel="stylesheet" type="text/css" />

<!-- Gantt -->

<script src="{{ asset('assets') }}/gantt-master/codebase/dhtmlxgantt.js"></script>
<link href="{{ asset('assets') }}/gantt-master/codebase/dhtmlxgantt.css" rel="stylesheet">

<style>
    .form-control[readonly] {
        background-color: #e9ecef;
        pointer-events: none;
        color: #6c757d;
        opacity: 1;
    }

    .form-control[readonly]::placeholder {
        color: #6c757d;
    }

    .select2-container--default.select2-container--disabled .select2-selection--single {
        background-color: var(--ct-tertiary-bg) !important;
        cursor: default;
    }

    .swal2-timer-progress-bar {
        background: var(--ct-body-color) !important;
    }

    .swal2-popup.swal2-toast {
        background: var(--ct-secondary-bg) !important;
        color: var(--ct-body-color) !important;
    }

    .swal2-modal {
        background: var(--ct-secondary-bg) !important;
        color: var(--ct-body-color) !important;
    }
</style>

<style>
    .select2-container .select2-selection--multiple .select2-selection__rendered {
        padding: 0px !important;
        width: 99% !important;
        display: block !important;
        margin-bottom: 0.25rem !important;
    }
</style>

<!-- MAKE SURE THIS THING IN BOTTOM!!! -->
<!-- Theme Config Js -->
<script src="{{ asset('assets') }}/js/config.js"></script>

<!-- App css -->
<link href="{{ asset('assets') }}/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

<!-- Icons css -->
<link href="{{ asset('assets') }}/css/icons.min.css" rel="stylesheet" type="text/css" />
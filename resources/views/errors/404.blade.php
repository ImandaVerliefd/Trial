@include ('templates/main')

<head>
    <title>Error 404 | SIAKAD STIKES Pemkab Jombang</title>
    @include ('templates/title-meta')
    @include ('templates/head-css')
</head>

<body class="authentication-bg">

    <!-- @include ('templates/background') -->

    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-4 col-lg-5">
                    <div class="card">
                        <!-- Logo -->
                        <div class="card-header py-4 text-center bg-primary">
                            <a href="index.php">
                                <span><img src="assets/images/logo.png" alt="logo" height="22"></span>
                            </a>
                        </div>

                        <div class="card-body p-4">
                            <div class="text-center">
                                <h1 class="text-error">4<i class="ri-emotion-sad-line"></i>4</h1>
                                <h4 class="text-uppercase text-danger mt-3">Halaman tidak ditemukan</h4>
                                <p class="text-muted mt-3">
                                    Maaf sekali, halaman yang Anda cari saat ini tidak tersedia.
                                    Mungkin Anda bisa mencoba mencari dengan kata kunci yang berbeda
                                    atau kembali ke halaman beranda.</p>

                                <a class="btn btn-info mt-3" href="<?= url('dashboard') ?>"><i class="ri-home-4-line"></i> Kembali ke beranda</a>
                            </div>
                        </div> <!-- end card-body-->
                    </div>
                    <!-- end card -->
                </div> <!-- end col -->
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
    <!-- end page -->

    <footer class="footer footer-alt fw-medium">
        <span class="bg-body">
            <script>
                document.write(new Date().getFullYear())
            </script> © Attex - Coderthemes.com
        </span>
    </footer>
    <script src="assets/js/vendor.min.js"></script>


    <!-- App js -->
    <script src="assets/js/app.min.js"></script>

</body>

</html>
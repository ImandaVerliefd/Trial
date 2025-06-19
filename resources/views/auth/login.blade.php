<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ $title }} - SIAKAD STIKES Pemkab Jombang</title>
    @include('templates/title-meta')
    @include('templates/head-css')
</head>

<body class="authentication-bg pb-0">
    <script>
        <?php if (!empty(session('resp_msg'))) : ?>
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            })

            Toast.fire({
                icon: 'info',
                title: "<?= session('resp_msg') ?>"
            })
        <?php endif; ?>
    </script>
    <div class="auth-fluid">
        <!--Auth fluid left content -->
        <div class="auth-fluid-form-box">

            <div class="card-body d-flex flex-column h-100 gap-3">

                <!-- Logo -->
                <div class="auth-brand text-center text-lg-start">
                    <a href="/" class="logo-dark">
                        <span><img src="assets/images/logo-lg.png" alt="dark logo" height="22"></span>
                    </a>
                    <a href="/" class="logo-light">
                        <span><img src="assets/images/logo.png" alt="logo" height="22"></span>
                    </a>
                </div>

                <div class="my-auto">
                    <!-- title-->
                    <h4 class="mt-0">Masuk</h4>
                    <p class="text-muted mb-4">Silakan masukkan email dan password Anda untuk masuk.</p>

                    <!-- form -->
                    <form class="needs-validation" novalidate action="<?= url('auth/login') ?>" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="emailaddress" class="form-label">Email</label>
                            <input class="form-control" type="email" id="emailaddress" name="email" required placeholder="Masukkan email Anda">
                            <div class="invalid-feedback">
                                Mohon berikan email yang benar.
                            </div>
                        </div>
                        <div class="mb-3">
                            <a href="auth-recoverpw-2.php" class="text-muted float-end"><small>Lupa password anda?</small></a>
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control" name="password" placeholder="Masukkan password Anda" required>
                                <div class="input-group-text" data-password="false" style="border-radius: 0 .25rem .25rem 0;">
                                    <span class="password-eye"></span>
                                </div>
                                <div class="invalid-feedback">
                                    Mohon masukkan password yang benar.
                                </div>
                            </div>
                        </div>
                        <div class="d-grid mb-0 text-center">
                            <button class="btn btn-primary" type="submit"><i class="ri-login-box-line"></i> Masuk </button>
                        </div>

                    </form>
                    <!-- end form-->
                </div>


            </div> <!-- end .card-body -->
        </div>
        <!-- end auth-fluid-form-box-->

        <!-- Auth fluid right content -->
        <div class="auth-fluid-right text-center">
            <div class="auth-user-testimonial">
                <div id="carouselExampleFade" class="carousel slide carousel-fade" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <h2 class="mb-3">I love the color!</h2>
                            <p class="lead"><i class="ri-double-quotes-l"></i> Everything you need is in this template. Love the overall look and feel. Not too flashy, and still very professional and smart.
                            </p>
                            <p>
                                - Admin User
                            </p>
                        </div>
                        <div class="carousel-item">
                            <h2 class="mb-3">Flexibility !</h2>
                            <p class="lead"><i class="ri-double-quotes-l"></i> Pretty nice theme, hoping you guys could add more features to this. Keep up the good work.
                            </p>
                            <p>
                                - Admin User
                            </p>
                        </div>
                        <div class="carousel-item">
                            <h2 class="mb-3">Feature Availability!</h2>
                            <p class="lead"><i class="ri-double-quotes-l"></i> This is a great product, helped us a lot and very quick to work with and implement.
                            </p>
                            <p>
                                - Admin User
                            </p>
                        </div>
                    </div>
                </div>
            </div> <!-- end auth-user-testimonial-->
        </div>
        <!-- end Auth fluid right content -->
    </div>
    <!-- end auth-fluid-->
    <script src="assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="assets/js/app.min.js"></script>

</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>

    <title><?= $title ?> | SIAKAD STIKES Pemkab Jombang</title>
    @include('templates/title-meta')

    @include('templates/head-css')
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        @include('templates/menu')

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">
                    @include($content_page)
                </div>
                <!-- container -->

            </div>
            <!-- content -->

            @include('templates/footer')

        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->

    @include('templates/right-sidebar')

    @include('templates/footer-js')

</body>

</html>
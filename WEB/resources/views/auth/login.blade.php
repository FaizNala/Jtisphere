<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>JTISphere</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
    <link rel="icon" href="{{ asset('jti.png') }}" type="image/png">

    <style>
        body {
            background: #f0f2f5;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Source Sans Pro', sans-serif;
        }

        .login-box {
            width: 900px;
            margin: 0 auto;
        }

        .card {
            display: flex;
            flex-direction: row;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .left-side {
            flex: 1;
            background: linear-gradient(135deg, #2b65d1 0%, #4172db 100%);
            padding: 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .left-side img {
            max-width: 150px;
            margin-bottom: 10px;
        }

        .left-side h3 {
            color: white;
            font-size: 40px;
            margin-top: 10px;
            font-weight: 900;
        }

        .left-side h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .left-side p {
            font-size: 1.1em;
            opacity: 0.9;
            line-height: 1.6;
        }

        .right-side {
            flex: 1;
            background: white;
            padding: 40px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            color: #2b5db8;
            font-size: 24px;
            font-weight: 700;
        }

        .form-control {
            height: 45px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 10px 15px;
            font-size: 16px;
        }

        .input-group-text {
            background: transparent;
            border: none;
            color: #666;
        }

        .btn-primary {
            background: #4172db;
            border: none;
            height: 45px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
        }

        .btn-primary:hover {
            background: #234a94;
        }

        .remember-me {
            margin-top: 15px;
        }

        .text-center {
            text-align: center;
        }

        .mb-4 {
            margin-bottom: 1.5rem;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        @media (max-width: 768px) {
            .login-box {
                width: 95%;
            }

            .card {
                flex-direction: column;
            }

            .left-side {
                padding: 30px;
            }

            .right-side {
                padding: 30px;
            }
        }

        /* Mencegah pergeseran saat modal muncul */
        body.swal2-shown {
            overflow-y: scroll !important;
            padding-right: 0 !important;
        }

        .swal2-container {
            z-index: 9999;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .swal2-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        /* Tambahkan di bagian <style> */
        .swal2-container.swal2-backdrop-show {
            background-color: rgba(0, 0, 0, 0.4) !important;
        }

        .swal2-container {
            z-index: 9999 !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .swal2-popup {
            margin: 0 !important;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <div class="card">
            <div class="left-side">
                <div class="text-center mb-4">
                    <img src="{{ asset('jti.png') }}" alt="JTI Logo" style="width: 150px; height: auto;">
                    <h3 class="mt-3" style="font-weight: bold;">JTISphere</h3>
                </div>
            </div>
            <div class="right-side">
                <div class="login-header">
                    <h2>Sign In to Your Account</h2>
                </div>
                <form action="{{ url('login') }}" method="POST" id="form-login">
                    @csrf
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-group mb-3">
                            <input type="text" id="username" name="username" class="form-control"
                                placeholder="Enter your username">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                            </div>
                        </div>
                        <small id="error-username" class="error-text text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group mb-3">
                            <input type="password" id="password" name="password" class="form-control"
                                placeholder="Enter your password">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                            </div>
                        </div>
                        <small id="error-password" class="error-text text-danger"></small>
                    </div>

                    <div class="remember-me">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="remember">
                            <label class="custom-control-label" for="remember">Remember me</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Sign In</button>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- jquery-validation -->
    <script src="{{ asset('adminlte/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/jquery-validation/additional-methods.min.js') }}"></script>
    <!-- SweetAlert2 -->
    <script src="{{ asset('adminlte/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- AdminL te App -->
    <script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            $("#form-login").validate({
                rules: {
                    username: {
                        required: true,
                        minlength: 4,
                        maxlength: 20
                    },
                    password: {
                        required: true,
                        minlength: 5,
                        maxlength: 20
                    }
                },
                submitHandler: function(form) {
                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.status) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: response.message,
                                    showClass: {
                                        popup: 'animate__animated animate__zoomIn'
                                    },
                                    hideClass: {
                                        popup: 'animate__animated animate__zoomOut'
                                    },
                                    backdrop: 'rgba(0,0,0,0.4)', // Background overlay
                                    customClass: {
                                        popup: 'my-custom-popup-class', // Kelas kustom untuk popup
                                    },
                                    heightAuto: false, // Mencegah perubahan tinggi otomatis
                                    scrollbarPadding: false // Mencegah penambahan padding
                                }).then(function() {
                                    window.location = response.redirect;
                                });
                            } else {
                                $('.error-text').text('');
                                $.each(response.msgField, function(prefix, val) {
                                    $('#error-' + prefix).text(val[0]);
                                });
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message,
                                    showClass: {
                                        popup: 'animate__animated animate__shakeX'
                                    },
                                    hideClass: {
                                        popup: 'animate__animated animate__fadeOut'
                                    },
                                    backdrop: 'rgba(0,0,0,0.4)',
                                    customClass: {
                                        popup: 'my-custom-popup-class',
                                    },
                                    heightAuto: false,
                                    scrollbarPadding: false
                                });
                            }
                        }
                    });
                    return false;
                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.input-group').append(error);
                },
                highlight: function(element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid');
                }
            });
        });
    </script>
</body>

</html>

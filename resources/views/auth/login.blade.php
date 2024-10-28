<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login POS</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
    <link rel="icon" href="{{ asset('tab_logo.png') }}" type="image/png">

    <style>
        body {
            background: linear-gradient(135deg, #74ebd5 0%, #9face6 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-box {
            width: 400px;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            border: none;
        }

        .card-header {
            background-color: #5a67d8;
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            text-align: center;
            padding: 20px 0;
        }

        .card-header .h1 {
            font-weight: 700;
            letter-spacing: 1px;
            font-size: 24px;
        }

        .login-box-msg {
            font-size: 18px;
            color: #6b7280;
            margin-bottom: 30px;
        }

        .btn-primary {
            background-color: #4caf50;
            border-color: #4caf50;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #43a047;
            border-color: #388e3c;
        }

        .input-group-text {
            background-color: #5a67d8;
            border-color: #5a67d8;
            color: white;
            border-radius: 0 0.25rem 0.25rem 0;
        }

        .form-control {
            font-size: 16px;
            height: 45px;
            border-radius: 0.25rem;
        }

        .icheck-primary label {
            font-size: 14px;
            color: #6b7280;
        }

        .card-footer {
            background-color: #f9fafb;
        }

        .login-box .card-body {
            padding: 40px;
            background-color: white;
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
        }

        .btn-block {
            border-radius: 25px;
            padding: 10px;
        }

        /* Small tweaks for error messages */
        .error-text {
            font-size: 13px;
            margin-top: 5px;
        }
    </style>
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <a href="{{ url('/') }}" class="h1"><b>QuickSales</b></a>
            </div>
            <div class="card-body">
                <p class="login-box-msg">Sign in to access POS system</p>
                <form action="{{ url('login') }}" method="POST" id="form-login">
                    @csrf
                    <div class="input-group mb-4">
                        <input type="text" id="username" name="username" class="form-control" placeholder="Username">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                        <small id="error-username" class="error-text text-danger"></small>
                    </div>
                    <div class="input-group mb-4">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        <small id="error-password" class="error-text text-danger"></small>
                    </div>
                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember">
                                <label for="remember">Remember Me</label>
                            </div>
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                        </div>
                    </div>
                </form>
                <a href="{{ url('register') }}" class="text-center d-block mt-3">Don't have an account? Sign up</a>
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
    <!-- AdminLTE App -->
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
                                    text: response.message
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>{{ __('Login') }} | {{ settingHelper('admin_panel_title') != '' ? settingHelper('admin_panel_title') : __('Yoori') }}</title>

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ static_asset('admin/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ static_asset('admin/css/toastr.min.css') }}">

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ static_asset('admin/css/yoori.css') }}">
    <link rel="stylesheet" href="{{ static_asset('admin/css/style.css') }}">
    <link rel="stylesheet" href="{{ static_asset('admin/css/custom.css') }}">

    @php
        $icon = settingHelper('favicon');
    @endphp
    <!-- Favicons... -->

    <style>
        
        :root {
            --primary-light: #7AC6D2;
            --primary-dark: #3D90D7;
            --text-dark: #2D3748;
            --text-light: #718096;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .auth-container {
            background-color: #fff;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
            width: 900px;
            max-width: 100%;
            min-height: 550px;
            display: flex;
        }

        /* Logo Styles */
        .auth-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-logo img {
            height: 80px;
            width: auto;
            transition: var(--transition);
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Form Container */
        .form-container {
            padding: 60px 50px;
            width: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: var(--transition);
        }

        .form-container h1 {
            color: var(--text-dark);
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
            text-align: center;
        }

        .form-container p {
            color: var(--text-light);
            font-size: 14px;
            text-align: center;
            margin-bottom: 30px;
        }

        /* Form Elements */
        .form-group {
            width: 100%;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-size: 14px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
            background-color: #f8fafc;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(122, 198, 210, 0.2);
        }

        /* Button Styles */
        .btn {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
            box-shadow: var(--shadow-sm);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-light) 100%);
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        /* Toggle Panel */
        .toggle-container {
            width: 50%;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary-dark) 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            text-align: center;
        }

        .toggle-container h1 {
            font-size: 28px;
            margin-bottom: 15px;
        }

        .toggle-container p {
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .toggle-btn {
            background: transparent;
            border: 2px solid white;
            color: white;
            padding: 10px 30px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .toggle-btn:hover {
            background: white;
            color: var(--primary-dark);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column;
                min-height: auto;
            }
            
            .form-container,
            .toggle-container {
                width: 100%;
                padding: 40px 30px;
            }
            
            .toggle-container {
                padding: 30px;
            }
        }

        /* Additional Elements */
        .auth-footer {
            margin-top: 20px;
            text-align: center;
        }

        .auth-footer a {
            color: var(--primary-dark);
            text-decoration: none;
            font-size: 13px;
            transition: var(--transition);
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .custom-checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .custom-checkbox input {
            margin-right: 10px;
        }

        .invalid-feedback {
            color: #e53e3e;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="auth-container" id="auth-container">
        <!-- Login Form -->
        <div class="form-container sign-in-container">
            <div class="auth-logo">
                @php
                    $logo = settingHelper('admin_dark_logo')
                @endphp
                <img src="{{($logo && $logo != [] && @is_file_exists($logo['image_100x38'])) ? static_asset($logo['image_100x38']) : static_asset('images/default/dark-logo.png') }}"
                     alt="Logo">
            </div>
            
            <h1>Welcome Back</h1>
            <p>Enter your credentials to access your account</p>
            
            <form method="POST" class="login_form" action="{{route('admin.seller.login')}}">
                @csrf
                
                <div class="form-group">
                    <input id="email" type="email" class="form-control"
                           value="{{config('app.demo_mode') ? request()->path() == 'admin/login' ? 'admin@spagreen.net' : (request()->path() == 'seller/login' ? 'seller@spagreen.net' : old('email')) : ''}}"
                           name="email" placeholder="Email Address" required autofocus>
                    @if($errors->has('email'))
                        <div class="invalid-feedback">
                            {{ $errors->first('email') }}
                        </div>
                    @endif
                </div>
                
                <div class="form-group">
                    <input id="password" type="password" class="form-control"
                           value="{{config('app.demo_mode') ? request()->path() == 'admin/login' ? '123456' : (request()->path() == 'seller/login' ? '123456' : old('password')) : ''}}"
                           name="password" placeholder="Password" required>
                    <input type="hidden" value="{{ request()->path() }}" name="request_path" />
                    @if($errors->has('password'))
                        <div class="invalid-feedback">
                            {{ $errors->first('password') }}
                        </div>
                    @endif
                </div>

                @if(settingHelper('is_recaptcha_activated') == 1)
                    <div class="form-group">
                        <div class="g-recaptcha" data-callback="myCallback"
                             data-sitekey="{{ settingHelper('recaptcha_Site_key') }}"></div>
                        <input name="recaptcha_check" type="hidden">
                    </div>
                @endif

                <div class="custom-checkbox">
                    <input type="checkbox" name="remember" id="remember-me">
                    <label for="remember-me">Remember Me</label>
                </div>
                
                <button type="submit" class="btn">Sign In</button>
                
                <div class="auth-footer">
                    <a href="#">{{__('Forgot Your Password?')}}</a>
                </div>
            </form>
        </div>
        
        <!-- Toggle Panel -->
        <div class="toggle-container">
            <h1>Hello, Friend!</h1>
            <p>Enter your personal details and start your journey with us</p>
            <button class="toggle-btn" id="register">Sign Up</button>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ static_asset('admin/js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ static_asset('admin/js/bootstrap.min.js') }}"></script>
    <script src="{{ static_asset('admin/js/toastr.min.js') }}"></script>
    <script src="{{ static_asset('admin/js/scripts.js') }}"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('auth-container');
            const registerBtn = document.getElementById('register');
            
            if (registerBtn) {
                registerBtn.addEventListener('click', () => {
                    container.classList.add("right-panel-active");
                });
            }

            window.myCallback = function (val) {
                $('input[name="recaptcha_check"]').val(val);
            };
        });

        {!! Toastr::message() !!}
    </script>
    @include('admin.partials.message')
</body>
</html>
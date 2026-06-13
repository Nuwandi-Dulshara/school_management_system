<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>School Management System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />

    <style>
    :root {
        --bg-primary: #f8f9fa;
        --navy-secondary: #1e3a8a;
        --amber-accent: #f59e0b;
        --navy-hover: #172d6b;
    }

    body {
        background-color: var(--bg-primary);
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        height: 100vh;
        overflow: hidden;
    }

    .auth-container {
        height: 100vh;
        overflow: hidden;
    }

    /* Brand Side Panel */
    .brand-panel {
        background: linear-gradient(135deg,
                var(--navy-secondary) 0%,
                #11224e 100%);
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 4rem;
        overflow-y: auto;
        max-height: 100vh;
    }

    .accent-line {
        width: 60px;
        height: 4px;
        background-color: var(--amber-accent);
        margin: 1.5rem 0;
        border-radius: 2px;
    }

    /* Form Side Panel */
    .form-panel {
        background-color: var(--bg-primary);
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 2rem;
        overflow-y: auto;
        max-height: 100vh;
    }

    .card-custom {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .btn-academic {
        background-color: var(--navy-secondary);
        color: white;
        border: none;
        transition: all 0.2s ease;
    }

    .btn-academic:hover {
        background-color: var(--navy-hover);
        color: white;
    }

    .btn-outline-navy {
        color: var(--navy-secondary);
        border-color: var(--navy-secondary);
    }

    .btn-outline-navy:hover {
        background-color: var(--navy-secondary);
        color: white;
        border-color: var(--navy-secondary);
    }

    .form-control:focus {
        border-color: var(--navy-secondary);
        box-shadow: 0 0 0 0.25rem rgba(30, 58, 138, 0.15);
    }

    .text-accent {
        color: var(--amber-accent);
    }

    .text-navy {
        color: var(--navy-secondary);
    }

    /* Responsive Tweaks */
    @media (max-width: 767.98px) {
        .brand-panel {
            padding: 2rem;
            text-align: center;
            align-items: center;
        }

        .form-panel {
            padding: 2rem 1.5rem;
        }

        .auth-container {
            height: auto;
        }
    }
    </style>
</head>

<body>
    <div class="container-fluid p-0 overflow-hidden">
        <div class="row g-0 auth-container">
            <div class="col-md-5 col-lg-6 brand-panel text-start">
                <div class="mb-4">
                    <i class="bi bi-mortarboard-fill display-1 text-accent"></i>
                </div>
                <h1 class="display-5 fw-bold text-white">EduPulse</h1>
                <p class="lead text-white-50">
                    The complete School Management & Analytics Ecosystem.
                </p>
                <div class="accent-line"></div>
                <p class="small text-white-50 max-width-350">
                    Access your grades, schedules, attendance, and administrative tools
                    all in one unified portal.
                </p>
            </div>

            <div class="col-md-7 col-lg-6 form-panel">
                <div class="w-100 mx-auto" style="max-width: 450px">
                    <div class="d-md-none text-center mb-4">
                        <i class="bi bi-mortarboard-fill display-4 text-navy"></i>
                        <h2 class="fw-bold text-navy mt-2">EduPulse</h2>
                    </div>

                    <div class="card card-custom p-4 p-sm-5">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>There were errors with your submission:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="mb-4">
                            <h3 class="fw-bold text-navy">Welcome Back</h3>
                            <p class="text-muted small">
                                Please enter your academic credentials to sign in.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('login.submit') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="login" class="form-label small fw-semibold text-secondary">Email or Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted"><i
                                            class="bi bi-person"></i></span>
                                    <input type="text" class="form-control py-2 @error('login') is-invalid @enderror" id="login" name="login" value="{{ old('login') }}"
                                        placeholder="Enter email or username" required />
                                </div>
                                @error('login')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="password"
                                        class="form-label small fw-semibold text-secondary mb-0">Password</label>
                                    <a href="#" class="small text-navy text-decoration-none">Forgot password?</a>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control py-2 @error('password') is-invalid @enderror" id="password" name="password"
                                        placeholder="••••••••" required />
                                </div>
                                @error('password')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} />
                                <label class="form-check-label small text-muted" for="remember">Keep me signed in on
                                    this device</label>
                            </div>

                            <button type="submit" class="btn btn-academic w-100 py-2.5 fw-semibold mb-3">
                                Sign In <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <p class="small text-muted mb-0">
                                Don't you have an account?
                                <a href="{{ route('register') }}" class="btn btn-outline-navy btn-sm ms-2">
                                    <i class="bi bi-person-plus me-1"></i>Register
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>School Management System - Create Account</title>
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
    }

    .register-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    .card-custom {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        width: 100%;
        max-width: 650px;
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

    .form-control:focus,
    .form-select:focus {
        border-color: var(--navy-secondary);
        box-shadow: 0 0 0 0.25rem rgba(30, 58, 138, 0.15);
    }

    .text-navy {
        color: var(--navy-secondary);
    }

    .text-accent {
        color: var(--amber-accent);
    }
    </style>
</head>

<body>
    <div class="container register-container">
        <div class="card card-custom p-4 p-sm-5">
            <div class="text-center mb-4">
                <i class="bi bi-mortarboard-fill display-5 text-navy"></i>
                <h2 class="fw-bold text-navy mt-2">Create Account</h2>
                <p class="text-muted small">
                    Create your account to access the school management system.
                </p>
            </div>

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

            <form action="{{ route('register.submit') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label for="first_name" class="form-label small fw-semibold text-secondary">First Name</label>
                        <input type="text" class="form-control py-2 @error('first_name') is-invalid @enderror"
                            id="first_name" name="first_name" value="{{ old('first_name') }}" required
                            placeholder="John" />
                        @error('first_name')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-sm-6">
                        <label for="last_name" class="form-label small fw-semibold text-secondary">Last Name</label>
                        <input type="text" class="form-control py-2 @error('last_name') is-invalid @enderror"
                            id="last_name" name="last_name" value="{{ old('last_name') }}" required placeholder="Doe" />
                        @error('last_name')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-sm-6">
                        <label for="username" class="form-label small fw-semibold text-secondary">Username</label>
                        <input type="text" class="form-control py-2 @error('username') is-invalid @enderror"
                            id="username" name="username" value="{{ old('username') }}" required
                            placeholder="johndoe123" />
                        @error('username')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-sm-6">
                        <label for="email" class="form-label small fw-semibold text-secondary">Email Address</label>
                        <input type="email" class="form-control py-2 @error('email') is-invalid @enderror" id="email"
                            name="email" value="{{ old('email') }}" placeholder="johndoe@school.edu" required />
                        @error('email')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold text-secondary">User Role</label>
                        <select class="form-select py-2 @error('role') is-invalid @enderror" name="role" required>
                            <option value="" disabled selected>Select your role...</option>
                            <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super
                                Admin</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin / Office Staff
                            </option>
                            <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                            <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Student</option>
                        </select>
                        @error('role')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-sm-6">
                        <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
                        <input type="password" class="form-control py-2 @error('password') is-invalid @enderror"
                            id="password" name="password" placeholder="Min. 8 characters" required />
                        @error('password')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-sm-6">
                        <label for="password_confirmation" class="form-label small fw-semibold text-secondary">Confirm
                            Password</label>
                        <input type="password"
                            class="form-control py-2 @error('password_confirmation') is-invalid @enderror"
                            id="password_confirmation" name="password_confirmation" placeholder="Repeat password"
                            required />
                        @error('password_confirmation')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-12 mt-3">
                        <div class="form-check">
                            <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox"
                                id="terms" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required />
                            <label class="form-check-label small text-muted" for="terms">
                                I agree to the school's
                                <a href="#" class="text-navy text-decoration-none fw-semibold">Acceptable Use Policy</a>
                                and privacy guidelines.
                            </label>
                            @error('terms')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-academic w-100 py-2.5 fw-semibold">
                            Create Account
                        </button>
                    </div>
                </div>
            </form>

            <div class="text-center mt-4 pt-2 border-top">
                <p class="small text-muted mb-0">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-navy fw-semibold text-decoration-none">Sign In</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
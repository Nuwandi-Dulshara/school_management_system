@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Please correct the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label for="first_name" class="form-label fw-semibold">First Name</label>
        <input type="text" name="first_name" id="first_name" class="form-control @error('first_name') is-invalid @enderror"
            value="{{ old('first_name', $managedUser->first_name ?? '') }}" required>
        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="last_name" class="form-label fw-semibold">Last Name</label>
        <input type="text" name="last_name" id="last_name" class="form-control @error('last_name') is-invalid @enderror"
            value="{{ old('last_name', $managedUser->last_name ?? '') }}" required>
        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label fw-semibold">Email</label>
        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email', $managedUser->email ?? '') }}" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="username" class="form-label fw-semibold">Username</label>
        <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror"
            value="{{ old('username', $managedUser->username ?? '') }}" required>
        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="role" class="form-label fw-semibold">Role</label>
        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
            <option value="">Select a role</option>
            @foreach ($roles as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $managedUser->role ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="status" class="form-label fw-semibold">Status</label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="active" @selected(old('status', $managedUser->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $managedUser->status ?? 'active') === 'inactive')>Inactive</option>
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    @if (!isset($managedUser))
        <div class="col-md-6">
            <label for="password" class="form-label fw-semibold">Password</label>
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
        </div>
    @endif
</div>

<div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
    <button type="submit" class="btn btn-academic">
        <i class="bi bi-check-lg me-2"></i>{{ $submitLabel }}
    </button>
    <a href="{{ route('super_admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

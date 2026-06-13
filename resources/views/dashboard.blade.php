@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Dashboard</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info" role="alert">
                        <strong>Welcome to your Dashboard!</strong>
                        <p class="mb-0 mt-2">This is a temporary dashboard. Full dashboard features will be added later.</p>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">User Information</h5>
                                    <p class="card-text">
                                        <strong>Name:</strong> {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}<br>
                                        <strong>Email:</strong> {{ Auth::user()->email }}<br>
                                        <strong>Username:</strong> {{ Auth::user()->username }}<br>
                                        <strong>Role:</strong> <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Quick Actions</h5>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">Logout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

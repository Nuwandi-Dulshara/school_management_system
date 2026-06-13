@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1>Super Admin Dashboard</h1>
    
    <div class="mt-4">
        <p>Welcome, <strong>{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</strong>!</p>
    </div>

    <div class="mt-4">
        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-danger">Logout</button>
        </form>
    </div>
</div>
@endsection

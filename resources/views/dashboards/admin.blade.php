@extends('layouts.super_admin', ['pageTitle' => 'Dashboard'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Admin / Office Staff Dashboard</h1>
        <p>Use the Attendance Management menu to manage daily student attendance and reports.</p>
    </div>
</div>
@include('dashboards._notices')
@endsection

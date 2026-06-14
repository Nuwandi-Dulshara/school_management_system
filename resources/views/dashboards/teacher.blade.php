@extends('layouts.super_admin', ['pageTitle' => 'Dashboard'])

@section('super_admin_content')
<div class="page-heading">
    <div>
        <h1>Teacher Dashboard</h1>
        <p>View published examination schedules for your assigned classes.</p>
    </div>
</div>
@include('dashboards._notices')
@endsection

@extends('app')

@section('content')
<div class="container">
    <h1 class="mt-4">Dashboard</h1>
    <p>Welcome to your dashboard, {{ $user->user_name }}</p>
    
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="card-title">Users</h2>
                            <h3>{{ $userCount }}</h3>
                        </div>
                        <div>
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <a href="/user" class="text-primary">View Details</a>
                    <a href="/user" class="text-primary"><i class="fas fa-arrow-circle-right text-primary"></i></a>
                   
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="card-title">Roles</h2>
                            <h3>{{ $roleCount }}</h3>
                        </div>
                        <div>
                            <i class="fas fa-user-tag fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <a href="/roles" class="text-success">View Details</a>
                    <a href="/roles" class="text-success"><i class="fas fa-arrow-circle-right text-success"></i></a>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> --}}
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> No Stall Assigned</h4>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-store-slash fa-4x text-muted"></i>
                    </div>
                    <h3 class="card-title mb-3">Welcome to the Vendor Portal!</h3>
                    <p class="card-text lead text-muted">
                        It looks like you haven't been assigned a stall yet. 
                    </p>
                    <p class="card-text">
                        Please contact the market administrator to have a stall assigned to your account.
                        Once a stall is assigned, you will be able to view your billing details and payment history here.
                    </p>
                    <hr>
                    <a href="{{ route('logout') }}" 
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       class="btn btn-outline-secondary">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

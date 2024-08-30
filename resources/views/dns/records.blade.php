@extends('layouts.user_type.auth')

@section('content')

<div>
    <style>
        .fa-spin {
        display: inline-block;
        animation: fa-spin 1s infinite linear;
        }

        @keyframes fa-spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
        }
        .hide-after:after {
            display: none !important;
        }
        .hide-before:before {
            dislay: none !important;
        }
        .dropdown .dropdown-menu:before {
            display: none !important;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header pb-0 px-3">
                    <h6 class="mb-0">{{ __('Manage DNS Records for ') }} {{ $domainName }}</h6>
            </div>
            <div class="card-body pt-4 p-3">
                <livewire:dns-records-editor :domainName="$domainName" :recordId="$recordId"/>
            </div>        
        </div>
    </div>
    
</div>
@endsection
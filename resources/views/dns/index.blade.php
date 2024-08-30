@extends('layouts.user_type.auth')

@section('content')

<div>

    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header pb-0 px-3">
                <h6 class="mb-0">{{ __('Select Domain to Edit DNS Zone Records') }}</h6>
            </div>
            <div class="card-body pt-4 p-3">
                <div class="container">
                    @if($domains->isEmpty())
                        <div class="flex justify-center items-center h-64">
                            <p class="text-gray-600 text-lg">{{ __('No domains available at the moment.') }}</p>
                        </div>
                    @else
                        <div class="flex flex-wrap -mx-4">
                            @foreach($domains as $domain)
                                <div class="w-full sm:w-1/2 md:w-1/3 lg:w-1/4 xl:w-1/5 p-4 border border-secondary rounded mb-3">
                                    <a href="{{ route('dns.get', $domain->domain_name) }}" class="block bg-white rounded-xl overflow-hidden shadow-md hover:shadow-lg transform hover:scale-105 transition duration-300 ease-in-out">
                                        <div class="relative h-48 bg-gradient-to-r from-purple-400 via-pink-500 to-red-500">
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <h5 class="text-xl font-bold text-black">{{ $domain->domain_name }}</h5>
                                            </div>
                                        </div>
                                        <div class="Manage">
                                            <p class="text-sm text-gray-700"><svg style="margin-right: 1em;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                                              </svg> {{ __('Manage DNS records') }}</p>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@if (session('success') || session('error'))
    <div x-data="{ show: true }" 
         x-init="setTimeout(() => show = false, 3000)" 
         x-show="show" 
         class="fixed top-4 right-4 bg-white border-l-4 shadow-lg px-4 py-3 rounded-md text-gray-900"
         :class="{
            'border-green-500': '{{ session('success') }}',
            'border-red-500': '{{ session('error') }}'
         }"
         role="alert"
         x-cloak>
        <div class="flex items-center">
            <div class="flex-shrink-0">
                @if (session('success'))
                    <svg class="h-6 w-6 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                @elseif (session('error'))
                    <svg class="h-6 w-6 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                @endif
            </div>
            <div class="ml-3">
                <p class="text-sm font-semibold">
                    {{ session('success') ?: session('error') }}
                </p>
            </div>
        </div>
    </div>
@endif

@extends('layouts.admin-dashboard')

@section('title', 'Workshop Details - ' . config('app.name', 'Laravel'))

@section('header-title', $workshop->name)
@section('header-subtitle', 'Workshop details')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Workshop Information</h3>
            <div class="flex gap-2">
                @if($workshop->api_enabled && (auth()->user()->isSuperAdmin() || auth()->user()->hasRole('fulfillment-staff')))
                <a href="{{ route('admin.workshops.orders.index', $workshop) }}" class="px-4 py-2 rounded-lg text-sm font-medium border" style="color: #2563EB; border-color: #DBEAFE;">
                    View Orders
                </a>
                @endif
                <a href="{{ route('admin.workshops.edit', $workshop) }}" class="px-4 py-2 rounded-lg text-sm font-medium border" style="color: #2563EB; border-color: #DBEAFE;">
                    Edit
                </a>
            </div>
        </div>
        <dl class="grid grid-cols-2 gap-4">
            <div>
                <dt class="text-sm text-gray-500">Code</dt>
                <dd class="text-base font-semibold">{{ $workshop->code }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Market</dt>
                <dd class="text-base font-semibold">{{ $workshop->market->name }} ({{ $workshop->market->code }})</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Status</dt>
                <dd>
                    @if($workshop->status === 'active')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #D1FAE5; color: #065F46;">Active</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #FEE2E2; color: #991B1B;">Inactive</span>
                    @endif
                </dd>
            </div>
            @if($workshop->description)
            <div class="col-span-2">
                <dt class="text-sm text-gray-500">Description</dt>
                <dd class="text-base font-semibold">{{ $workshop->description }}</dd>
            </div>
            @endif
        </dl>
    </div>

    <!-- API Configuration Section -->
    <div class="mt-6 border-t border-gray-200 pt-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-400">api</span>
                API Configuration
            </h3>
            @if($workshop->api_enabled && (auth()->user()->isSuperAdmin() || auth()->user()->hasRole('fulfillment-staff')))
            <form method="POST" action="{{ route('admin.workshops.test-api', $workshop) }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium border hover:bg-green-50 transition-colors" style="color: #059669; border-color: #D1FAE5;">
                    Test Connection
                </button>
            </form>
            @endif
        </div>

        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm text-gray-500 mb-1">API Enabled</dt>
                <dd>
                    @if($workshop->api_enabled)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #D1FAE5; color: #065F46;">Enabled</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #FEE2E2; color: #991B1B;">Disabled</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500 mb-1">API Type</dt>
                <dd class="text-base font-semibold">{{ $workshop->api_type ?? 'N/A' }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-sm text-gray-500 mb-1">API Endpoint</dt>
                <dd class="text-base font-semibold break-all">
                    @if($workshop->api_endpoint)
                        <a href="{{ $workshop->api_endpoint }}" target="_blank" class="text-blue-600 hover:underline">
                            {{ $workshop->api_endpoint }}
                        </a>
                    @else
                        <span class="text-gray-400">Not configured</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500 mb-1">API Key</dt>
                <dd class="text-base font-semibold font-mono">
                    @if($workshop->api_key)
                        <span class="text-gray-700">{{ substr($workshop->api_key, 0, 8) }}****{{ substr($workshop->api_key, -4) }}</span>
                        <button onclick="toggleApiKey()" class="ml-2 text-xs text-blue-600 hover:underline" id="toggleApiKeyBtn">Show</button>
                        <span id="fullApiKey" class="hidden">{{ $workshop->api_key }}</span>
                    @else
                        <span class="text-gray-400">Not set</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500 mb-1">API Secret</dt>
                <dd class="text-base font-semibold font-mono">
                    @if($workshop->api_secret)
                        <span class="text-gray-700">{{ substr($workshop->api_secret, 0, 8) }}****{{ substr($workshop->api_secret, -4) }}</span>
                        <button onclick="toggleApiSecret()" class="ml-2 text-xs text-blue-600 hover:underline" id="toggleApiSecretBtn">Show</button>
                        <span id="fullApiSecret" class="hidden">{{ $workshop->api_secret }}</span>
                    @else
                        <span class="text-gray-400">Not set</span>
                    @endif
                </dd>
            </div>
            @if($workshop->api_notes)
            <div class="md:col-span-2">
                <dt class="text-sm text-gray-500 mb-1">API Notes</dt>
                <dd class="text-base text-gray-700 whitespace-pre-wrap">{{ $workshop->api_notes }}</dd>
            </div>
            @endif
        </dl>

        <!-- API Settings (JSON) -->
        @if($workshop->api_settings && is_array($workshop->api_settings) && count($workshop->api_settings) > 0)
        <div class="mt-6">
            <div class="flex items-center justify-between mb-3">
                <dt class="text-sm font-semibold text-gray-700">API Settings (JSON)</dt>
                <button onclick="toggleApiSettings()" class="text-xs text-blue-600 hover:underline" id="toggleApiSettingsBtn">Show Full</button>
            </div>
            <dd class="bg-gray-50 border border-gray-200 rounded-lg p-4 overflow-x-auto">
                <pre id="apiSettingsPreview" class="text-xs text-gray-600 font-mono whitespace-pre-wrap">{{ json_encode(array_slice($workshop->api_settings, 0, 3), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}@if(count($workshop->api_settings) > 3)... ({{ count($workshop->api_settings) - 3 }} more items)@endif</pre>
                <pre id="apiSettingsFull" class="hidden text-xs text-gray-700 font-mono whitespace-pre-wrap">{{ json_encode($workshop->api_settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
            </dd>
        </div>
        @endif

        <!-- Raw API Settings (for debugging) -->
        @if($workshop->api_settings)
        <div class="mt-4">
            <details class="group">
                <summary class="cursor-pointer text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <span class="material-symbols-outlined text-base transform group-open:rotate-90 transition-transform">chevron_right</span>
                    Raw API Settings (Debug)
                </summary>
                <div class="mt-3 bg-gray-50 border border-gray-200 rounded-lg p-4 overflow-x-auto">
                    <pre class="text-xs text-gray-700 font-mono whitespace-pre-wrap">{{ json_encode($workshop->api_settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </details>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function toggleApiKey() {
    const preview = event.target.previousElementSibling;
    const full = document.getElementById('fullApiKey');
    const btn = document.getElementById('toggleApiKeyBtn');
    
    if (full.classList.contains('hidden')) {
        preview.classList.add('hidden');
        full.classList.remove('hidden');
        btn.textContent = 'Hide';
    } else {
        preview.classList.remove('hidden');
        full.classList.add('hidden');
        btn.textContent = 'Show';
    }
}

function toggleApiSecret() {
    const preview = event.target.previousElementSibling;
    const full = document.getElementById('fullApiSecret');
    const btn = document.getElementById('toggleApiSecretBtn');
    
    if (full.classList.contains('hidden')) {
        preview.classList.add('hidden');
        full.classList.remove('hidden');
        btn.textContent = 'Hide';
    } else {
        preview.classList.remove('hidden');
        full.classList.add('hidden');
        btn.textContent = 'Show';
    }
}

function toggleApiSettings() {
    const preview = document.getElementById('apiSettingsPreview');
    const full = document.getElementById('apiSettingsFull');
    const btn = document.getElementById('toggleApiSettingsBtn');
    
    if (full.classList.contains('hidden')) {
        preview.classList.add('hidden');
        full.classList.remove('hidden');
        btn.textContent = 'Show Preview';
    } else {
        preview.classList.remove('hidden');
        full.classList.add('hidden');
        btn.textContent = 'Show Full';
    }
}
</script>
@endpush

@endsection

@php
    $activeMenu = 'workshops';
@endphp






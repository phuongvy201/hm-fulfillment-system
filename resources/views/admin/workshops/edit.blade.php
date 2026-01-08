@extends('layouts.app')

@section('title', 'Edit Workshop - ' . config('app.name', 'Laravel'))

@section('header-title', 'Edit Workshop')
@section('header-subtitle', 'Update workshop information')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow-sm p-8">
        <form method="POST" action="{{ route('admin.workshops.update', $workshop) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label for="market_id" class="block text-sm font-semibold mb-2">Market</label>
                    <select id="market_id" name="market_id" required class="w-full px-4 py-3 border rounded-lg">
                        @foreach($markets as $m)
                            <option value="{{ $m->id }}" {{ old('market_id', $workshop->market_id) == $m->id ? 'selected' : '' }}>{{ $m->name }} ({{ $m->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="code" class="block text-sm font-semibold mb-2">Workshop Code</label>
                    <input type="text" id="code" name="code" value="{{ old('code', $workshop->code) }}" required class="w-full px-4 py-3 border rounded-lg">
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold mb-2">Workshop Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $workshop->name) }}" required class="w-full px-4 py-3 border rounded-lg">
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold mb-2">Description</label>
                    <textarea id="description" name="description" rows="4" class="w-full px-4 py-3 border rounded-lg">{{ old('description', $workshop->description) }}</textarea>
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold mb-2">Status</label>
                    <select id="status" name="status" required class="w-full px-4 py-3 border rounded-lg">
                        <option value="active" {{ old('status', $workshop->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $workshop->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- API Settings Section -->
                <div class="border-t pt-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🔌 API Integration Settings</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <input 
                                type="checkbox" 
                                id="api_enabled" 
                                name="api_enabled" 
                                value="1"
                                {{ old('api_enabled', $workshop->api_enabled) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            >
                            <label for="api_enabled" class="text-sm font-semibold text-gray-900">Enable API Integration</label>
                        </div>

                        <div>
                            <label for="api_type" class="block text-sm font-semibold mb-2">API Type</label>
                            <select id="api_type" name="api_type" class="w-full px-4 py-3 border rounded-lg">
                                <option value="">Select API Type</option>
                                <option value="rest" {{ old('api_type', $workshop->api_type) === 'rest' ? 'selected' : '' }}>REST API</option>
                                <option value="soap" {{ old('api_type', $workshop->api_type) === 'soap' ? 'selected' : '' }}>SOAP API</option>
                                <option value="custom" {{ old('api_type', $workshop->api_type) === 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                        </div>

                        <div>
                            <label for="api_endpoint" class="block text-sm font-semibold mb-2">API Endpoint URL</label>
                            <input 
                                type="url" 
                                id="api_endpoint" 
                                name="api_endpoint" 
                                value="{{ old('api_endpoint', $workshop->api_endpoint) }}"
                                placeholder="https://api.workshop.com"
                                class="w-full px-4 py-3 border rounded-lg"
                            >
                            <p class="mt-1 text-xs text-gray-500">Base URL for the workshop API (e.g., https://api.workshop.com)</p>
                        </div>

                        <div>
                            <label for="api_key" class="block text-sm font-semibold mb-2">API Key</label>
                            <input 
                                type="text" 
                                id="api_key" 
                                name="api_key" 
                                value="{{ old('api_key', $workshop->api_key) }}"
                                placeholder="Your API key"
                                class="w-full px-4 py-3 border rounded-lg"
                            >
                        </div>

                        <div>
                            <label for="api_secret" class="block text-sm font-semibold mb-2">API Secret</label>
                            <input 
                                type="password" 
                                id="api_secret" 
                                name="api_secret" 
                                value="{{ old('api_secret', $workshop->api_secret) }}"
                                placeholder="Your API secret"
                                class="w-full px-4 py-3 border rounded-lg"
                            >
                        </div>

                        <div>
                            <label for="api_notes" class="block text-sm font-semibold mb-2">API Notes</label>
                            <textarea 
                                id="api_notes" 
                                name="api_notes" 
                                rows="3"
                                placeholder="Additional notes about API configuration..."
                                class="w-full px-4 py-3 border rounded-lg"
                            >{{ old('api_notes', $workshop->api_notes) }}</textarea>
                        </div>

                        @if($workshop->api_enabled && $workshop->api_endpoint)
                        <div>
                            <form method="POST" action="{{ route('admin.workshops.test-api', $workshop) }}" class="inline">
                                @csrf
                                <button type="submit" class="px-6 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-green-500 hover:bg-green-600">
                                    Test API Connection
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" class="px-6 py-3 rounded-lg font-semibold text-white" style="background-color: #2563EB;">
                        Update Workshop
                    </button>
                    <a href="{{ route('admin.workshops.index') }}" class="px-6 py-3 rounded-lg font-semibold border" style="color: #374151; border-color: #D1D5DB;">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@php
    $activeMenu = 'workshops';
@endphp






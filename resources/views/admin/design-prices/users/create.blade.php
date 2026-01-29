@extends('layouts.admin-dashboard')

@section('title', 'Create User Design Price - ' . config('app.name', 'Laravel'))

@section('header-title', 'Create User Design Price')
@section('header-subtitle', 'Set custom design pricing for a user')

@section('content')
<div class="max-w-2xl mx-auto p-6 space-y-6">
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
        <ul class="text-sm list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.design-prices.users.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        User <span class="text-red-500">*</span>
                    </label>
                    <select name="user_id" required class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white text-gray-900 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="">Select a user</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            First Side Price (VND) <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="first_side_price_vnd" 
                            value="{{ old('first_side_price_vnd', 30000) }}" 
                            step="1000"
                            min="0"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white text-gray-900 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        >
                        <p class="text-xs text-gray-500 mt-1">Default: 30,000 VND</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Additional Side Price (VND) <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="additional_side_price_vnd" 
                            value="{{ old('additional_side_price_vnd', 20000) }}" 
                            step="1000"
                            min="0"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white text-gray-900 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        >
                        <p class="text-xs text-gray-500 mt-1">Default: 20,000 VND</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white text-gray-900 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Valid From
                        </label>
                        <input 
                            type="date" 
                            name="valid_from" 
                            value="{{ old('valid_from') }}" 
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white text-gray-900 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Valid To
                        </label>
                        <input 
                            type="date" 
                            name="valid_to" 
                            value="{{ old('valid_to') }}" 
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white text-gray-900 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        >
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between gap-4 pt-4">
            <a href="{{ route('admin.design-prices.users.index') }}" class="px-6 py-3 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-8 py-3 rounded-lg bg-orange-500 text-white font-bold hover:bg-orange-600 shadow-lg shadow-orange-500/20 transition-all">
                Create Price
            </button>
        </div>
    </form>
</div>
@endsection

@php
    $activeMenu = 'design-prices';
@endphp


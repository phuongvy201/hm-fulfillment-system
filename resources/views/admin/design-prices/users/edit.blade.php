@extends('layouts.admin-dashboard')

@section('title', 'Edit User Design Price - ' . config('app.name', 'Laravel'))

@section('header-title', 'Edit User Design Price')
@section('header-subtitle', 'Update custom design pricing for a user')

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

    <form method="POST" action="{{ route('admin.design-prices.users.update', $userDesignPrice) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        User <span class="text-red-500">*</span>
                    </label>
                    <select name="user_id" required class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white text-gray-900 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="">Select a user</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $userDesignPrice->user_id) == $user->id ? 'selected' : '' }}>
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
                            value="{{ old('first_side_price_vnd', $userDesignPrice->first_side_price_vnd) }}" 
                            step="1000"
                            min="0"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white text-gray-900 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Additional Side Price (VND) <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="additional_side_price_vnd" 
                            value="{{ old('additional_side_price_vnd', $userDesignPrice->additional_side_price_vnd) }}" 
                            step="1000"
                            min="0"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white text-gray-900 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white text-gray-900 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="active" {{ old('status', $userDesignPrice->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $userDesignPrice->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                            value="{{ old('valid_from', $userDesignPrice->valid_from ? $userDesignPrice->valid_from->format('Y-m-d') : '') }}" 
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
                            value="{{ old('valid_to', $userDesignPrice->valid_to ? $userDesignPrice->valid_to->format('Y-m-d') : '') }}" 
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
                Update Price
            </button>
        </div>
    </form>
</div>
@endsection

@php
    $activeMenu = 'design-prices';
@endphp


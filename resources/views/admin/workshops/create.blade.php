@extends('layouts.app')

@section('title', 'Create Workshop - ' . config('app.name', 'Laravel'))

@section('header-title', 'Create New Workshop')
@section('header-subtitle', 'Add a new fulfillment workshop')

@section('header-actions')
<a href="{{ route('admin.workshops.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;">
    ← Back
</a>
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow-sm p-8">
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-lg" style="background-color: #FEE2E2; border: 1px solid #EF4444;">
                <ul class="text-sm" style="color: #991B1B;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.workshops.store') }}">
            @csrf

            <div class="space-y-6">
                <div>
                    <label for="market_id" class="block text-sm font-semibold mb-2" style="color: #111827;">Market</label>
                    <select id="market_id" name="market_id" required class="w-full px-4 py-3 border rounded-lg" style="border-color: #D1D5DB;">
                        <option value="">Select Market</option>
                        @foreach($markets as $m)
                            <option value="{{ $m->id }}" {{ old('market_id', request('market_id')) == $m->id ? 'selected' : '' }}>{{ $m->name }} ({{ $m->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="code" class="block text-sm font-semibold mb-2" style="color: #111827;">Workshop Code</label>
                    <input type="text" id="code" name="code" value="{{ old('code') }}" required placeholder="e.g., WS-US-001" class="w-full px-4 py-3 border rounded-lg" style="border-color: #D1D5DB;">
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold mb-2" style="color: #111827;">Workshop Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required class="w-full px-4 py-3 border rounded-lg" style="border-color: #D1D5DB;">
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold mb-2" style="color: #111827;">Description</label>
                    <textarea id="description" name="description" rows="4" class="w-full px-4 py-3 border rounded-lg" style="border-color: #D1D5DB;">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold mb-2" style="color: #111827;">Status</label>
                    <select id="status" name="status" required class="w-full px-4 py-3 border rounded-lg" style="border-color: #D1D5DB;">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" class="px-6 py-3 rounded-lg font-semibold text-white transition-all" style="background-color: #2563EB;">
                        Create Workshop
                    </button>
                    <a href="{{ route('admin.workshops.index') }}" class="px-6 py-3 rounded-lg font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;">
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






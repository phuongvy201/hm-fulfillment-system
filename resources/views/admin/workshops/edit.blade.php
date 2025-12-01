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






@extends('layouts.app')

@section('title', 'Edit Pricing Tier - ' . config('app.name', 'Laravel'))

@section('header-title', 'Edit Pricing Tier')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow-sm p-8">
        <form method="POST" action="{{ route('admin.pricing-tiers.update', $pricingTier) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label for="slug" class="block text-sm font-semibold mb-2">Slug</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $pricingTier->slug) }}" required class="w-full px-4 py-3 border rounded-lg">
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold mb-2">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $pricingTier->name) }}" required class="w-full px-4 py-3 border rounded-lg">
                </div>

                <div>
                    <label for="priority" class="block text-sm font-semibold mb-2">Priority</label>
                    <input type="number" id="priority" name="priority" value="{{ old('priority', $pricingTier->priority) }}" min="0" class="w-full px-4 py-3 border rounded-lg">
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold mb-2">Description</label>
                    <textarea id="description" name="description" rows="4" class="w-full px-4 py-3 border rounded-lg">{{ old('description', $pricingTier->description) }}</textarea>
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold mb-2">Status</label>
                    <select id="status" name="status" required class="w-full px-4 py-3 border rounded-lg">
                        <option value="active" {{ old('status', $pricingTier->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $pricingTier->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" class="px-6 py-3 rounded-lg font-semibold text-white" style="background-color: #2563EB;">
                        Update Tier
                    </button>
                    <a href="{{ route('admin.pricing-tiers.index') }}" class="px-6 py-3 rounded-lg font-semibold border" style="color: #374151; border-color: #D1D5DB;">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@php
    $activeMenu = 'pricing-tiers';
@endphp






@extends('layouts.app')

@section('title', 'Edit Pricing Rule - ' . config('app.name', 'Laravel'))

@section('header-title', 'Edit Pricing Rule')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow-sm p-8">
        <form method="POST" action="{{ route('admin.pricing-rules.update', $pricingRule) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label for="market_id" class="block text-sm font-semibold mb-2">Market</label>
                    <select id="market_id" name="market_id" required class="w-full px-4 py-3 border rounded-lg">
                        @foreach($markets as $m)
                            <option value="{{ $m->id }}" {{ old('market_id', $pricingRule->market_id) == $m->id ? 'selected' : '' }}>{{ $m->name }} ({{ $m->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="product_id" class="block text-sm font-semibold mb-2">Product (Optional)</label>
                    <select id="product_id" name="product_id" class="w-full px-4 py-3 border rounded-lg">
                        <option value="">All Products</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ old('product_id', $pricingRule->product_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="rule_type" class="block text-sm font-semibold mb-2">Rule Type</label>
                    <input type="text" id="rule_type" name="rule_type" value="{{ old('rule_type', $pricingRule->rule_type) }}" required class="w-full px-4 py-3 border rounded-lg">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="condition_key" class="block text-sm font-semibold mb-2">Condition Key</label>
                        <input type="text" id="condition_key" name="condition_key" value="{{ old('condition_key', $pricingRule->condition_key) }}" class="w-full px-4 py-3 border rounded-lg">
                    </div>

                    <div>
                        <label for="condition_value" class="block text-sm font-semibold mb-2">Condition Value</label>
                        <input type="text" id="condition_value" name="condition_value" value="{{ old('condition_value', $pricingRule->condition_value) }}" class="w-full px-4 py-3 border rounded-lg">
                    </div>
                </div>

                <div>
                    <label for="operation" class="block text-sm font-semibold mb-2">Operation</label>
                    <select id="operation" name="operation" required class="w-full px-4 py-3 border rounded-lg">
                        <option value="add" {{ old('operation', $pricingRule->operation) === 'add' ? 'selected' : '' }}>Add (+)</option>
                        <option value="subtract" {{ old('operation', $pricingRule->operation) === 'subtract' ? 'selected' : '' }}>Subtract (-)</option>
                        <option value="multiply" {{ old('operation', $pricingRule->operation) === 'multiply' ? 'selected' : '' }}>Multiply (*)</option>
                        <option value="divide" {{ old('operation', $pricingRule->operation) === 'divide' ? 'selected' : '' }}>Divide (/)</option>
                        <option value="set" {{ old('operation', $pricingRule->operation) === 'set' ? 'selected' : '' }}>Set (=)</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="amount" class="block text-sm font-semibold mb-2">Amount</label>
                        <input type="number" id="amount" name="amount" value="{{ old('amount', $pricingRule->amount) }}" required step="0.01" class="w-full px-4 py-3 border rounded-lg">
                    </div>

                    <div>
                        <label for="currency" class="block text-sm font-semibold mb-2">Currency (Optional)</label>
                        <input type="text" id="currency" name="currency" value="{{ old('currency', $pricingRule->currency) }}" maxlength="3" class="w-full px-4 py-3 border rounded-lg uppercase">
                    </div>
                </div>

                <div>
                    <label for="priority" class="block text-sm font-semibold mb-2">Priority</label>
                    <input type="number" id="priority" name="priority" value="{{ old('priority', $pricingRule->priority) }}" min="0" class="w-full px-4 py-3 border rounded-lg">
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold mb-2">Status</label>
                    <select id="status" name="status" required class="w-full px-4 py-3 border rounded-lg">
                        <option value="active" {{ old('status', $pricingRule->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $pricingRule->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" class="px-6 py-3 rounded-lg font-semibold text-white" style="background-color: #2563EB;">
                        Update Rule
                    </button>
                    <a href="{{ route('admin.pricing-rules.index') }}" class="px-6 py-3 rounded-lg font-semibold border" style="color: #374151; border-color: #D1D5DB;">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@php
    $activeMenu = 'pricing-rules';
@endphp






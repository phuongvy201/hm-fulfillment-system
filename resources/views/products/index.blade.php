@extends('layouts.app')

@section('title', 'All Products - ' . config('app.name', 'HM Fulfillment System'))
@section('description', 'Browse all our print-on-demand products. Explore our wide range of customizable products for your business.')
@section('html-class', 'light')
@section('body-class', 'bg-background-light text-[#181511]')

@section('content')
<div class="relative flex min-h-screen w-full flex-col font-display">


    <main class="flex-1 max-w-[1400px] mx-auto w-full px-4 lg:px-12 py-10">
        <div class="flex items-center gap-2 text-sm text-neutral-500 mb-8">
            <a class="hover:text-primary" href="{{ route('home') }}">Home</a>
            <span class="material-symbols-outlined text-xs">chevron_right</span>
            <span class="text-neutral-900 font-semibold">Catalog</span>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
            <div>
                <h2 class="text-3xl lg:text-4xl font-black text-neutral-900 leading-tight">Catalog</h2>
                <p class="text-neutral-500">Hiển thị {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} / {{ $products->total() }}</p>
            </div>
            <form action="{{ route('products.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <div class="flex items-center bg-white border border-neutral-200 rounded-xl px-3 py-2 gap-2 w-full sm:w-72 shadow-sm">
                    <span class="material-symbols-outlined text-neutral-500">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..." class="bg-transparent border-none focus:ring-0 text-sm text-neutral-900 w-full" />
                </div>
                <div class="flex items-center bg-white border border-neutral-200 rounded-xl px-3 py-2 gap-2 w-full sm:w-56 shadow-sm">
                    <span class="material-symbols-outlined text-neutral-500">public</span>
                    <select name="market" class="bg-transparent border-none focus:ring-0 text-sm text-neutral-900 w-full">
                        <option value="">All markets</option>
                        @foreach($markets as $market)
                            <option value="{{ $market->id }}" @selected(request('market') == $market->id)>{{ $market->name }} ({{ $market->code }})</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-primary hover:bg-orange-500 text-white font-bold text-sm px-5 py-3 rounded-xl transition-all shadow-lg shadow-primary/20 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">tune</span> Apply
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
            @forelse($products as $product)
                @php
                    $primaryImage = $product->images->first();
                    $firstVariant = $product->variants->first();
                    $price = null;
                    $currency = 'USD';
                    $marketCode = 'US';

                    if ($firstVariant) {
                        $tierPrice = $firstVariant->tierPrices->first();
                        if ($tierPrice) {
                            $price = $tierPrice->base_price;
                            $currency = $tierPrice->currency;
                            $market = $tierPrice->market;
                            if ($market) {
                                $marketCode = $market->code ?? 'US';
                            }
                        }
                    }

                    $currencySymbol = match($currency) {
                        'GBP' => '£',
                        'USD' => '$',
                        'EUR' => '€',
                        default => '$'
                    };
                @endphp
                <a href="{{ route('products.show', $product->slug) }}" class="bg-white border border-neutral-200 rounded-2xl overflow-hidden shadow-lg shadow-black/5 hover:-translate-y-1 transition-all flex flex-col">
                    <div class="relative aspect-4/5 bg-neutral-100 overflow-hidden">
                        @if($primaryImage)
                            <img class="w-full h-full object-cover" src="{{ $primaryImage->url }}" alt="{{ $product->name }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-neutral-400">
                                <span class="material-symbols-outlined text-5xl">photo</span>
                            </div>
                        @endif
                        <div class="absolute top-4 left-4">
                            <span class="bg-primary text-white text-xs font-black px-3 py-1.5 rounded-full shadow-lg uppercase tracking-widest">Active</span>
                        </div>
                    </div>
                    <div class="p-5 flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-black text-neutral-900 leading-snug">{{ $product->name }}</h3>
                                <p class="text-neutral-500 text-sm">{{ $product->workshop->name ?? 'POD Workshop' }}</p>
                            </div>
                            @if($price)
                                <div class="text-right">
                                    <span class="text-2xl font-black text-primary block">{{ $currencySymbol }}{{ number_format($price, 2) }}</span>
                                    <span class="text-xs text-neutral-500 font-bold uppercase">{{ $marketCode }}</span>
                                </div>
                            @else
                                <span class="text-xs font-bold text-neutral-500">Contact for price</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-neutral-100 text-neutral-700 border border-neutral-200">Automated POD</span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-neutral-100 text-neutral-700 border border-neutral-200">Fast ship</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-16 text-neutral-500">
                    <span class="material-symbols-outlined text-5xl mb-3 block">inventory_2</span>
                    <p class="text-lg font-semibold">No products found</p> 
                    <p class="text-sm">Try searching for something else or clear the filters.</p>
                </div>
            @endforelse
        </div>

        @if($products->hasPages())
            <div class="mt-10 flex justify-center">
                <div class="bg-white border border-neutral-200 rounded-2xl px-4 py-3 text-neutral-900 shadow-sm">
                    {{ $products->links() }}
                </div>
            </div>
        @endif
    </main>
</div>
@endsection


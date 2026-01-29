@extends('layouts.admin-dashboard')

@section('title', 'Order Performance - ' . config('app.name', 'Laravel'))

@section('header-title', 'Order Performance')
@section('header-subtitle', 'Analytics and insights')

@section('header-actions')
<div class="flex items-center gap-3">
    @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
    <a href="{{ route('admin.orders.export') }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-bold rounded-lg hover:bg-primary/90 transition-all">
        <span class="material-symbols-outlined text-lg">download</span>
        Export Data
    </a>
    @endif
</div>
@endsection

@section('content')
<div class="space-y-8">
    <!-- KPI Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Orders -->
        <div class="bg-white dark:bg-[#1a140d] p-6 rounded-xl border border-slate-200 dark:border-[#3a2f24] shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <p class="text-slate-500 text-sm font-medium">Total Orders</p>
                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $totalOrdersChange >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $totalOrdersChange >= 0 ? '+' : '' }}{{ number_format($totalOrdersChange, 1) }}%
                </span>
            </div>
            <div class="flex items-end justify-between">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($totalOrders) }}</h3>
                <div class="w-24 h-8 bg-gradient-to-t from-primary/20 to-transparent rounded">
                    <div class="w-full h-full flex items-end">
                        @for($i = 0; $i < 5; $i++)
                        <div class="flex-1 bg-primary/40 h-[{{ rand(40, 100) }}%] mx-[1px] rounded-t-sm"></div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <!-- Processing -->
        <div class="bg-white dark:bg-[#1a140d] p-6 rounded-xl border border-slate-200 dark:border-[#3a2f24] shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <p class="text-slate-500 text-sm font-medium">Processing</p>
                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $processingChange >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $processingChange >= 0 ? '+' : '' }}{{ number_format($processingChange, 1) }}%
                </span>
            </div>
            <div class="flex items-end justify-between">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($processingOrders) }}</h3>
                <div class="w-24 h-8">
                    <svg class="w-full h-full text-amber-500" preserveAspectRatio="none" viewBox="0 0 100 40">
                        <path d="M0 35 Q 25 35, 50 20 T 100 10" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="3"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Delivered -->
        <div class="bg-white dark:bg-[#1a140d] p-6 rounded-xl border border-slate-200 dark:border-[#3a2f24] shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <p class="text-slate-500 text-sm font-medium">Delivered</p>
                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $deliveredChange >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $deliveredChange >= 0 ? '+' : '' }}{{ number_format($deliveredChange, 1) }}%
                </span>
            </div>
            <div class="flex items-end justify-between">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($deliveredOrders) }}</h3>
                <div class="w-24 h-8">
                    <svg class="w-full h-full text-green-500" preserveAspectRatio="none" viewBox="0 0 100 40">
                        <path d="M0 38 L 20 32 L 40 35 L 60 25 L 80 15 L 100 5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="3"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Cancelled -->
        <div class="bg-white dark:bg-[#1a140d] p-6 rounded-xl border border-slate-200 dark:border-[#3a2f24] shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <p class="text-slate-500 text-sm font-medium">Cancelled</p>
                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $cancelledChange >= 0 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                    {{ $cancelledChange >= 0 ? '+' : '' }}{{ number_format($cancelledChange, 1) }}%
                </span>
            </div>
            <div class="flex items-end justify-between">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($cancelledOrders) }}</h3>
                <div class="w-24 h-8">
                    <svg class="w-full h-full text-red-500" preserveAspectRatio="none" viewBox="0 0 100 40">
                        <path d="M0 25 L 100 25" fill="none" stroke="currentColor" stroke-dasharray="4" stroke-linecap="round" stroke-width="3"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Section: Big Chart -->
    <div class="bg-white dark:bg-[#1a140d] rounded-xl border border-slate-200 dark:border-[#3a2f24] shadow-sm overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between p-6 border-b border-slate-200 dark:border-[#3a2f24]">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Order Volume over Time</h2>
            <div class="flex mt-4 md:mt-0">
                <div class="flex h-10 items-center rounded-lg bg-slate-100 dark:bg-[#2a2218] p-1 w-64">
                    <label class="flex-1 cursor-pointer h-full flex items-center justify-center rounded-md px-2 text-xs font-bold transition-all has-[:checked]:bg-white dark:has-[:checked]:bg-[#3a2f24] has-[:checked]:text-primary text-slate-500">
                        <span>Daily</span>
                        <input class="hidden" name="timeframe" type="radio" value="daily" onchange="changeTimeframe('daily')"/>
                    </label>
                    <label class="flex-1 cursor-pointer h-full flex items-center justify-center rounded-md px-2 text-xs font-bold transition-all has-[:checked]:bg-white dark:has-[:checked]:bg-[#3a2f24] has-[:checked]:text-primary text-slate-500">
                        <span>Weekly</span>
                        <input checked class="hidden" name="timeframe" type="radio" value="weekly" onchange="changeTimeframe('weekly')"/>
                    </label>
                    <label class="flex-1 cursor-pointer h-full flex items-center justify-center rounded-md px-2 text-xs font-bold transition-all has-[:checked]:bg-white dark:has-[:checked]:bg-[#3a2f24] has-[:checked]:text-primary text-slate-500">
                        <span>Monthly</span>
                        <input class="hidden" name="timeframe" type="radio" value="monthly" onchange="changeTimeframe('monthly')"/>
                    </label>
                </div>
            </div>
        </div>
        <div class="p-8 h-[400px] flex items-end gap-2 relative">
            <canvas id="orderVolumeChart"></canvas>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sales Channel Distribution -->
        <div class="bg-white dark:bg-[#1a140d] p-6 rounded-xl border border-slate-200 dark:border-[#3a2f24] shadow-sm">
            <h3 class="text-base font-bold mb-6 text-slate-900 dark:text-white">Orders by Sales Channel</h3>
            <div class="flex items-center justify-center py-6">
                <div class="relative size-48">
                    <canvas id="salesChannelChart"></canvas>
                </div>
            </div>
            <div class="space-y-3 mt-4">
                @php
                    $totalChannelOrders = array_sum($salesChannelData);
                    $channelColors = ['#f7951d', '#181511', '#8c785f', '#64748b', '#10b981'];
                    $colorIndex = 0;
                @endphp
                @foreach($salesChannelData as $channel => $count)
                @php
                    $percentage = $totalChannelOrders > 0 ? ($count / $totalChannelOrders) * 100 : 0;
                    $color = $channelColors[$colorIndex % count($channelColors)];
                    $colorIndex++;
                @endphp
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <div class="size-2 rounded-full" style="background-color: {{ $color }}"></div>
                        <span class="text-slate-700 dark:text-slate-300">{{ $channel }}</span>
                    </div>
                    <span class="font-bold text-slate-900 dark:text-white">{{ number_format($percentage, 0) }}%</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Region Distribution -->
        <div class="bg-white dark:bg-[#1a140d] p-6 rounded-xl border border-slate-200 dark:border-[#3a2f24] shadow-sm">
            <h3 class="text-base font-bold mb-6 text-slate-900 dark:text-white">Orders by Region</h3>
            <div class="space-y-6 py-2">
                @php
                    $maxRegionOrders = max($regionData) ?: 1;
                @endphp
                @foreach($regionData as $region => $count)
                @php
                    $percentage = ($count / $maxRegionOrders) * 100;
                @endphp
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $region }}</span>
                        <span class="font-bold text-primary">{{ number_format($count) }}</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-[#2a2218] h-2.5 rounded-full">
                        <div class="bg-primary h-full rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
                @endforeach
                @if(empty($regionData))
                <div class="text-center text-slate-400 py-8">No region data available</div>
                @endif
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white dark:bg-[#1a140d] p-6 rounded-xl border border-slate-200 dark:border-[#3a2f24] shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Recent Orders</h3>
                @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-primary hover:underline">View All</a>
                @else
                <a href="{{ route('customer.orders.index') }}" class="text-xs font-bold text-primary hover:underline">View All</a>
                @endif
            </div>
            <div class="space-y-5">
                @forelse($recentOrders as $order)
                <div class="flex items-center justify-between {{ !$loop->last ? 'border-b border-slate-100 dark:border-[#2a2218] pb-3' : '' }}">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-xl">shopping_bag</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-slate-900 dark:text-white">#{{ $order->order_number }}</span>
                            <span class="text-xs text-slate-500">
                                {{ $order->user->name ?? 'N/A' }} • {{ $order->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                        @if($order->status === 'processing') bg-amber-100 text-amber-700
                        @elseif($order->status === 'shipped') bg-green-100 text-green-700
                        @elseif($order->status === 'delivered') bg-blue-100 text-blue-700
                        @elseif($order->status === 'cancelled') bg-red-100 text-red-700
                        @elseif($order->status === 'pending') bg-blue-100 text-blue-700
                        @else bg-gray-100 text-gray-700
                        @endif">
                        {{ strtoupper(str_replace('_', ' ', $order->status)) }}
                    </span>
                </div>
                @empty
                <div class="text-center text-slate-400 py-8">No recent orders</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Order Volume Chart
    const weeklyData = @json($weeklyData);
    const ctx = document.getElementById('orderVolumeChart').getContext('2d');
    
    const orderVolumeChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: weeklyData.map(d => d.label),
            datasets: [{
                label: 'Orders',
                data: weeklyData.map(d => d.count),
                borderColor: '#f7951d',
                backgroundColor: 'rgba(247, 149, 29, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointBackgroundColor: '#f7951d',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1a140d',
                    padding: 12,
                    titleFont: {
                        size: 12,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 14
                    },
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' Orders';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Sales Channel Donut Chart
    const salesChannelData = @json($salesChannelData);
    const channelCtx = document.getElementById('salesChannelChart').getContext('2d');
    const channelLabels = Object.keys(salesChannelData);
    const channelValues = Object.values(salesChannelData);
    const channelColors = ['#f7951d', '#181511', '#8c785f', '#64748b', '#10b981'];
    
    const salesChannelChart = new Chart(channelCtx, {
        type: 'doughnut',
        data: {
            labels: channelLabels,
            datasets: [{
                data: channelValues,
                backgroundColor: channelColors.slice(0, channelLabels.length),
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    function changeTimeframe(timeframe) {
        // This would typically make an AJAX call to fetch new data
        console.log('Timeframe changed to:', timeframe);
        // For now, we'll just log it. You can implement AJAX loading later.
    }
</script>
@endpush
@endsection

@php
    $activeMenu = 'dashboard';
@endphp

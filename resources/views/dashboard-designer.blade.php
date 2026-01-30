@extends('layouts.admin-dashboard')

@section('title', 'Designer Dashboard - ' . config('app.name', 'Laravel'))

@section('header-title', 'Designer Dashboard')
@section('header-subtitle', 'Your design tasks and performance')

@section('header-actions')
<div class="flex items-center gap-3">
    <a href="{{ route('admin.design-tasks.index') }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-bold rounded-lg hover:bg-primary/90 transition-all">
        <span class="material-symbols-outlined text-lg">brush</span>
        View All Tasks
    </a>
</div>
@endsection

@section('content')
<div class="space-y-8">
    <!-- KPI Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <!-- Total Tasks -->
        <div class="bg-white dark:bg-[#1a140d] p-6 rounded-xl border border-slate-200 dark:border-[#3a2f24] shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <p class="text-slate-500 text-sm font-medium">Total Tasks</p>
                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $totalTasksChange >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $totalTasksChange >= 0 ? '+' : '' }}{{ number_format($totalTasksChange, 1) }}%
                </span>
            </div>
            <div class="flex items-end justify-between">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($totalTasks) }}</h3>
                <div class="w-24 h-8 bg-gradient-to-t from-primary/20 to-transparent rounded">
                    <div class="w-full h-full flex items-end">
                        @for($i = 0; $i < 5; $i++)
                        <div class="flex-1 bg-primary/40 h-[{{ rand(40, 100) }}%] mx-[1px] rounded-t-sm"></div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white dark:bg-[#1a140d] p-6 rounded-xl border border-slate-200 dark:border-[#3a2f24] shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <p class="text-slate-500 text-sm font-medium">Pending</p>
                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $pendingChange >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $pendingChange >= 0 ? '+' : '' }}{{ number_format($pendingChange, 1) }}%
                </span>
            </div>
            <div class="flex items-end justify-between">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($pendingTasks) }}</h3>
                <div class="w-24 h-8">
                    <svg class="w-full h-full text-amber-500" preserveAspectRatio="none" viewBox="0 0 100 40">
                        <path d="M0 35 Q 25 35, 50 20 T 100 10" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="3"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Completed -->
        <div class="bg-white dark:bg-[#1a140d] p-6 rounded-xl border border-slate-200 dark:border-[#3a2f24] shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <p class="text-slate-500 text-sm font-medium">Completed</p>
                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $completedChange >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $completedChange >= 0 ? '+' : '' }}{{ number_format($completedChange, 1) }}%
                </span>
            </div>
            <div class="flex items-end justify-between">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($completedTasks) }}</h3>
                <div class="w-24 h-8">
                    <svg class="w-full h-full text-green-500" preserveAspectRatio="none" viewBox="0 0 100 40">
                        <path d="M0 38 L 20 32 L 40 35 L 60 25 L 80 15 L 100 5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="3"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Revision -->
        <div class="bg-white dark:bg-[#1a140d] p-6 rounded-xl border border-slate-200 dark:border-[#3a2f24] shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <p class="text-slate-500 text-sm font-medium">Needs Revision</p>
                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $revisionChange >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $revisionChange >= 0 ? '+' : '' }}{{ number_format($revisionChange, 1) }}%
                </span>
            </div>
            <div class="flex items-end justify-between">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($revisionTasks) }}</h3>
                <div class="w-24 h-8">
                    <svg class="w-full h-full text-orange-500" preserveAspectRatio="none" viewBox="0 0 100 40">
                        <path d="M0 20 L 25 25 L 50 15 L 75 30 L 100 10" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="3"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Available Tasks -->
        <div class="bg-white dark:bg-[#1a140d] p-6 rounded-xl border border-slate-200 dark:border-[#3a2f24] shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <p class="text-slate-500 text-sm font-medium">Available</p>
                <span class="px-2 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                    New
                </span>
            </div>
            <div class="flex items-end justify-between">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($availableTasks) }}</h3>
                <div class="w-24 h-8">
                    <svg class="w-full h-full text-blue-500" preserveAspectRatio="none" viewBox="0 0 100 40">
                        <path d="M0 30 L 50 10 L 100 30" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="3"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Section: Big Chart -->
    <div class="bg-white dark:bg-[#1a140d] rounded-xl border border-slate-200 dark:border-[#3a2f24] shadow-sm overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between p-6 border-b border-slate-200 dark:border-[#3a2f24]">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Task Volume over Time</h2>
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
            <canvas id="taskVolumeChart"></canvas>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Tasks by Status -->
        <div class="bg-white dark:bg-[#1a140d] p-6 rounded-xl border border-slate-200 dark:border-[#3a2f24] shadow-sm">
            <h3 class="text-base font-bold mb-6 text-slate-900 dark:text-white">Tasks by Status</h3>
            <div class="flex items-center justify-center py-6">
                <div class="relative size-48">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            <div class="space-y-3 mt-4">
                @php
                    $totalStatusTasks = array_sum($statusData);
                    $statusColors = ['#f7951d', '#181511', '#8c785f', '#64748b', '#10b981', '#ef4444'];
                    $colorIndex = 0;
                @endphp
                @foreach($statusData as $status => $count)
                @php
                    $percentage = $totalStatusTasks > 0 ? ($count / $totalStatusTasks) * 100 : 0;
                    $color = $statusColors[$colorIndex % count($statusColors)];
                    $colorIndex++;
                @endphp
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <div class="size-2 rounded-full" style="background-color: {{ $color }}"></div>
                        <span class="text-slate-700 dark:text-slate-300">{{ $status }}</span>
                    </div>
                    <span class="font-bold text-slate-900 dark:text-white">{{ number_format($percentage, 0) }}%</span>
                </div>
                @endforeach
                @if(empty($statusData))
                <div class="text-center text-slate-400 py-4">No status data available</div>
                @endif
            </div>
        </div>

        <!-- Tasks by Customer -->
        <div class="bg-white dark:bg-[#1a140d] p-6 rounded-xl border border-slate-200 dark:border-[#3a2f24] shadow-sm">
            <h3 class="text-base font-bold mb-6 text-slate-900 dark:text-white">Tasks by Customer</h3>
            <div class="space-y-6 py-2">
                @php
                    $maxCustomerTasks = !empty($customerData) ? max($customerData) : 1;
                @endphp
                @foreach($customerData as $customer => $count)
                @php
                    $percentage = ($count / $maxCustomerTasks) * 100;
                @endphp
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $customer }}</span>
                        <span class="font-bold text-primary">{{ number_format($count) }}</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-[#2a2218] h-2.5 rounded-full">
                        <div class="bg-primary h-full rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
                @endforeach
                @if(empty($customerData))
                <div class="text-center text-slate-400 py-8">No customer data available</div>
                @endif
            </div>
        </div>

        <!-- Recent Tasks -->
        <div class="bg-white dark:bg-[#1a140d] p-6 rounded-xl border border-slate-200 dark:border-[#3a2f24] shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Recent Tasks</h3>
                <a href="{{ route('admin.design-tasks.index') }}" class="text-xs font-bold text-primary hover:underline">View All</a>
            </div>
            <div class="space-y-5">
                @forelse($recentTasks as $task)
                <div class="flex items-center justify-between {{ !$loop->last ? 'border-b border-slate-100 dark:border-[#2a2218] pb-3' : '' }}">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-xl">brush</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ \Illuminate\Support\Str::limit($task->title, 30) }}</span>
                            <span class="text-xs text-slate-500">
                                {{ $task->customer->name ?? 'N/A' }} • {{ $task->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                        @if($task->status === 'pending') bg-amber-100 text-amber-700
                        @elseif($task->status === 'in_progress') bg-blue-100 text-blue-700
                        @elseif($task->status === 'completed') bg-green-100 text-green-700
                        @elseif($task->status === 'revision') bg-orange-100 text-orange-700
                        @elseif($task->status === 'cancelled') bg-red-100 text-red-700
                        @else bg-gray-100 text-gray-700
                        @endif">
                        {{ strtoupper(str_replace('_', ' ', $task->status)) }}
                    </span>
                </div>
                @empty
                <div class="text-center text-slate-400 py-8">No recent tasks</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Task Volume Chart
    const weeklyData = @json($weeklyData);
    const ctx = document.getElementById('taskVolumeChart').getContext('2d');
    
    const taskVolumeChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: weeklyData.map(d => d.label),
            datasets: [{
                label: 'Tasks',
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
                            return context.parsed.y + ' Tasks';
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

    // Status Donut Chart
    const statusData = @json($statusData);
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusLabels = Object.keys(statusData);
    const statusValues = Object.values(statusData);
    const statusColors = ['#f7951d', '#181511', '#8c785f', '#64748b', '#10b981', '#ef4444'];
    
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusValues,
                backgroundColor: statusColors.slice(0, statusLabels.length),
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


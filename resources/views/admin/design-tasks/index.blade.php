@extends('layouts.admin-dashboard')

@section('title', 'Design Tasks - ' . config('app.name', 'Laravel'))

@section('header-title', 'Design Tasks')
@section('header-subtitle', 'Manage and track all design tasks')

@section('header-actions')
<div class="flex items-center gap-3">
    <a href="{{ route($routePrefix . '.design-tasks.create') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm bg-primary hover:bg-orange-600 flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">add</span>
        + Create Design Task
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <section class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route($routePrefix . '.design-tasks.index') }}" id="searchForm">
            <!-- Main Search Bar -->
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-1 relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl">search</span>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Search by title or description..." 
                        class="w-full pl-11 pr-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm"
                    >
                </div>
                
                <!-- Filter Buttons -->
                <div class="flex items-center gap-2">
                    <!-- Status Filter -->
                    <div class="relative">
                        <select 
                            name="status" 
                            onchange="this.form.submit()"
                            class="px-4 py-3 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 transition-all text-sm font-semibold text-slate-700 appearance-none cursor-pointer pr-10"
                            style="background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23334155\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3Cpath d=\'M6 9l6 6 6-6\'/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1rem;"
                        >
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="joined" {{ request('status') == 'joined' ? 'selected' : '' }}>Joined</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="revision" {{ request('status') == 'revision' ? 'selected' : '' }}>Revision</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    
                    @if(!$isCustomer)
                    <!-- Customer Filter -->
                    <div class="relative">
                        <select 
                            name="customer_id" 
                            onchange="this.form.submit()"
                            class="px-4 py-3 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 transition-all text-sm font-semibold text-slate-700 appearance-none cursor-pointer pr-10"
                            style="background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23334155\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3Cpath d=\'M6 9l6 6 6-6\'/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1rem;"
                        >
                            <option value="">All Customers</option>
                            @foreach($customers ?? [] as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    <!-- Action Buttons -->
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-primary hover:bg-orange-600 text-white font-semibold rounded-lg transition-all shadow-sm"
                    >
                        Search
                    </button>
                    @if(request()->anyFilled(['search', 'status', 'customer_id', 'designer_id']))
                    <a 
                        href="{{ route($routePrefix . '.design-tasks.index') }}" 
                        class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition-all"
                    >
                        Reset
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </section>

    <!-- Design Tasks Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($tasks as $task)
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow flex flex-col {{ $task->status === 'completed' ? 'ring-1 ring-primary/5' : '' }}">
            <!-- Header -->
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-start">
                <div class="flex-1 mr-2">
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 line-clamp-1">{{ $task->title }}</h3>
                    @if($task->description)
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 line-clamp-2">{{ Str::limit($task->description, 50) }}</p>
                    @endif
                </div>
                <span class="px-2 py-1 rounded-full text-[10px] font-bold border
                    @if($task->status === 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800/50
                    @elseif($task->status === 'joined') bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800/50
                    @elseif($task->status === 'completed') bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800/50
                    @elseif($task->status === 'approved') bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/50
                    @elseif($task->status === 'revision') bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 border-orange-200 dark:border-orange-800/50
                    @else bg-gray-100 dark:bg-gray-900/30 text-gray-700 dark:text-gray-400 border-gray-200 dark:border-gray-800/50
                    @endif">
                    @if($task->status === 'pending') Pending
                    @elseif($task->status === 'joined') Joined
                    @elseif($task->status === 'completed') Completed
                    @elseif($task->status === 'approved') Approved
                    @elseif($task->status === 'revision') Revision
                    @else Cancelled
                    @endif
                </span>
            </div>

            <!-- Designer Info -->
            @if($task->designer)
            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center text-sm border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-500 dark:text-slate-400">Assigned to:</span>
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-blue-600 dark:text-blue-400">{{ $task->designer->name }}</span>
                    @if($isDesigner && $task->designer_id === auth()->id())
                    <span class="bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 text-[10px] px-1.5 rounded">You</span>
                    @endif
                </div>
            </div>
            @elseif($isDesigner)
            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center text-sm border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-500 dark:text-slate-400">Status:</span>
                <span class="font-semibold text-slate-600 dark:text-slate-400">No designer</span>
            </div>
            @endif

            <!-- Mockup Preview -->
            <div class="p-4 flex-grow">
                @php
                    $mockupFiles = is_array($task->mockup_file) ? $task->mockup_file : ($task->mockup_file ? [$task->mockup_file] : []);
                    $firstMockup = !empty($mockupFiles) ? $mockupFiles[0] : null;
                @endphp
                @if($firstMockup)
                <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 tracking-wider uppercase mb-2 block">REFERENCE MOCKUP</span>
                <div class="relative group rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 aspect-[16/9]">
                    <img 
                        alt="Mockup Reference" 
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" 
                        src="{{ asset('storage/' . $firstMockup) }}"
                        onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'225\'%3E%3Crect fill=\'%23f1f5f9\' width=\'400\' height=\'225\'/%3E%3Ctext fill=\'%2394a3b8\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' font-size=\'14\'%3ENo Image%3C/text%3E%3C/svg%3E'"
                    >
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
                    @if(count($mockupFiles) > 1)
                    <div class="absolute top-2 right-2 bg-primary text-white text-xs font-bold px-2 py-1 rounded-full">
                        +{{ count($mockupFiles) - 1 }}
                    </div>
                    @endif
                </div>
                @else
                <div class="relative rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 aspect-[16/9] flex items-center justify-center">
                    <span class="text-slate-400 dark:text-slate-500 text-xs">No mockup</span>
                </div>
                @endif

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-4 mt-6">
                    <div class="text-center">
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 uppercase font-bold tracking-tight">SIDES</p>
                        <p class="text-xl font-bold text-slate-800 dark:text-white mt-1">{{ $task->sides_count }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 uppercase font-bold tracking-tight">PRICE</p>
                        <p class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-1">${{ number_format($task->price, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-4 py-4 border-t border-slate-100 dark:border-slate-800 space-y-4">
                <div class="flex justify-between items-start text-xs">
                    <div>
                        <p class="text-slate-400 dark:text-slate-500 font-bold uppercase tracking-tighter">CUSTOMER</p>
                        <p class="font-bold text-slate-700 dark:text-slate-300 mt-0.5">{{ $task->customer->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-slate-400 dark:text-slate-500 font-bold uppercase tracking-tighter">CREATED</p>
                        <p class="font-medium text-slate-600 dark:text-slate-400 mt-0.5">{{ $task->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-2">
                    <a href="{{ route($routePrefix . '.design-tasks.show', $task) }}" class="w-full py-2.5 rounded-lg bg-blue-600 text-white font-bold text-sm hover:bg-blue-700 transition-colors text-center">
                        View Details
                    </a>
                    @if($isDesigner && $task->status === 'completed' && $task->designer_id === auth()->id())
                    <a href="{{ route($routePrefix . '.design-tasks.show', $task) }}" class="w-full py-2.5 rounded-lg bg-primary text-white font-bold text-sm hover:bg-orange-600 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">edit</span>
                        Update Design
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">No Design Tasks Found</h3>
                <p class="mt-2 text-sm text-slate-500">No design tasks found matching the filters.</p>
                @if(!$isDesigner)
                <a href="{{ route($routePrefix . '.design-tasks.create') }}" class="mt-4 inline-block px-6 py-2 bg-primary text-white font-semibold rounded-lg hover:bg-orange-600 transition-colors">
                    Create Your First Design Task
                </a>
                @endif
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($tasks->hasPages() || $tasks->total() > 0)
    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50 flex items-center justify-between rounded-xl">
        <span class="text-sm text-slate-500">
            Showing <span class="font-semibold text-slate-900">{{ $tasks->firstItem() ?? 0 }}</span> to <span class="font-semibold text-slate-900">{{ $tasks->lastItem() ?? 0 }}</span> of <span class="font-semibold text-slate-900">{{ $tasks->total() }}</span> tasks
        </span>
        @if($tasks->hasPages())
        <div class="flex items-center gap-2">
            @if($tasks->onFirstPage())
            <button class="px-3 py-1 text-sm font-medium rounded border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-400 cursor-not-allowed" disabled>
                Previous
            </button>
            @else
            <a href="{{ $tasks->previousPageUrl() }}" class="px-3 py-1 text-sm font-medium rounded border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                Previous
            </a>
            @endif
            
            @foreach($tasks->getUrlRange(1, min(5, $tasks->lastPage())) as $page => $url)
            @if($page == $tasks->currentPage())
            <button class="px-3 py-1 text-sm font-medium rounded bg-primary text-white">{{ $page }}</button>
            @else
            <a href="{{ $url }}" class="px-3 py-1 text-sm font-medium rounded border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">{{ $page }}</a>
            @endif
            @endforeach
            
            @if($tasks->hasMorePages())
            @if($tasks->lastPage() > 5)
            <span class="px-2 text-slate-400">...</span>
            <a href="{{ $tasks->url($tasks->lastPage()) }}" class="px-3 py-1 text-sm font-medium rounded border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">{{ $tasks->lastPage() }}</a>
            @endif
            <a href="{{ $tasks->nextPageUrl() }}" class="px-3 py-1 text-sm font-medium rounded border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                Next
            </a>
            @else
            <button class="px-3 py-1 text-sm font-medium rounded border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-400 cursor-not-allowed" disabled>
                Next
            </button>
            @endif
        </div>
        @endif
    </div>
    @endif
</div>
@endsection

@php
    $activeMenu = 'design-tasks';
@endphp


@extends('layouts.admin-dashboard')

@section('title', 'Import Files - ' . config('app.name', 'Laravel'))

@section('header-title', 'Import Files')
@section('header-subtitle', 'Manage imported order files')

@section('header-actions')
<div class="flex items-center gap-3">
    <a href="{{ route($routePrefix . '.orders.import') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 transition-all shadow-sm border border-slate-300 bg-white hover:bg-slate-50">
        Import Orders
    </a>
    <a href="{{ route($routePrefix . '.orders.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm bg-blue-600 hover:bg-blue-700">
        Orders
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
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

    <!-- Import Files Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm high-density-table">
                <thead class="bg-slate-50">
                    <tr>
                        <th>File Name</th>
                        <th>Uploaded By</th>
                        <th class="text-center">Total Items</th>
                        <th class="text-center">Processed</th>
                        <th class="text-center">Failed</th>
                        <th class="text-center">Status</th>
                        <th>Uploaded At</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($importFiles as $file)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td>
                            <div class="flex flex-col">
                                <span class="font-semibold text-slate-900">{{ $file->original_name }}</span>
                                <span class="text-xs text-slate-500">{{ number_format($file->file_size / 1024, 2) }} KB</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-slate-700">{{ $file->uploader->name ?? 'N/A' }}</span>
                        </td>
                        <td class="text-center">
                            <span class="font-semibold text-slate-900">{{ $file->total_orders }}</span>
                        </td>
                        <td class="text-center">
                            <span class="text-green-600 font-semibold">{{ $file->processed_orders }}</span>
                        </td>
                        <td class="text-center">
                            <span class="text-red-600 font-semibold">{{ $file->failed_orders }}</span>
                        </td>
                        <td class="text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide
                                @if($file->status === 'pending') bg-yellow-100 text-yellow-700
                                @elseif($file->status === 'processing') bg-blue-100 text-blue-700
                                @elseif($file->status === 'completed') bg-emerald-100 text-emerald-700
                                @else bg-red-100 text-red-700
                                @endif">
                                {{ ucfirst($file->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="text-[11px] text-slate-600 whitespace-nowrap">{{ $file->created_at->format('M d, Y') }}</span>
                            <span class="block text-[10px] text-slate-400">{{ $file->created_at->format('h:i A') }}</span>
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route($routePrefix . '.orders.import-files.show', $file) }}" class="p-1.5 hover:bg-white rounded-md text-slate-400 hover:text-primary transition-colors" title="View Details">
                                    <span class="material-symbols-outlined !text-[20px]">visibility</span>
                                </a>
                                <a href="{{ $file->file_url }}" target="_blank" class="p-1.5 hover:bg-white rounded-md text-slate-400 hover:text-slate-900 transition-colors" title="Download File">
                                    <span class="material-symbols-outlined !text-[20px]">download</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12">
                            <div class="text-center">
                                <svg class="mx-auto h-16 w-16 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="mt-4 text-lg font-semibold text-slate-900">No Import Files Found</h3>
                                <p class="mt-2 text-sm text-slate-500">No import files have been uploaded yet.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        @if($importFiles->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50 flex items-center justify-between">
            <span class="text-sm text-slate-500">
                Showing <span class="font-semibold text-slate-900">{{ $importFiles->firstItem() ?? 0 }}</span> to <span class="font-semibold text-slate-900">{{ $importFiles->lastItem() ?? 0 }}</span> of <span class="font-semibold text-slate-900">{{ $importFiles->total() }}</span> entries
            </span>
            <div class="flex items-center gap-1">
                @if($importFiles->onFirstPage())
                <button class="p-2 hover:bg-slate-200 rounded transition-colors text-slate-400 cursor-not-allowed" disabled>
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                </button>
                @else
                <a href="{{ $importFiles->previousPageUrl() }}" class="p-2 hover:bg-slate-200 rounded transition-colors text-slate-400">
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                </a>
                @endif
                
                @foreach($importFiles->getUrlRange(1, min(5, $importFiles->lastPage())) as $page => $url)
                @if($page == $importFiles->currentPage())
                <button class="w-8 h-8 flex items-center justify-center rounded bg-primary text-white font-bold text-sm">{{ $page }}</button>
                @else
                <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-200 text-sm">{{ $page }}</a>
                @endif
                @endforeach
                
                @if($importFiles->hasMorePages())
                @if($importFiles->lastPage() > 5)
                <span class="px-2 text-slate-400">...</span>
                <a href="{{ $importFiles->url($importFiles->lastPage()) }}" class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-200 text-sm">{{ $importFiles->lastPage() }}</a>
                @endif
                <a href="{{ $importFiles->nextPageUrl() }}" class="p-2 hover:bg-slate-200 rounded transition-colors text-slate-400">
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </a>
                @else
                <button class="p-2 hover:bg-slate-200 rounded transition-colors text-slate-400 cursor-not-allowed" disabled>
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </button>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<style>
.high-density-table th {
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
}
.high-density-table td {
    padding: 0.75rem 1rem;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
@endsection

@php
    $activeMenu = 'orders';
@endphp


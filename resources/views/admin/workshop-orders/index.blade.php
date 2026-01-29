@extends('layouts.admin-dashboard')

@section('title', 'Workshop Orders - ' . $workshop->name . ' - ' . config('app.name', 'Laravel'))

@section('header-title')
<div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
    <a href="{{ route('admin.workshops.index') }}" class="hover:text-[#F7961D] transition-colors">Workshops</a>
    <span class="material-symbols-outlined text-xs">chevron_right</span>
    <a href="{{ route('admin.workshops.show', $workshop) }}" class="hover:text-[#F7961D] transition-colors">{{ $workshop->name }}</a>
    <span class="material-symbols-outlined text-xs">chevron_right</span>
    <span class="text-slate-900 font-medium">Orders</span>
</div>
<h2 class="text-2xl font-bold text-slate-900">Workshop Orders</h2>
@endsection

@section('header-subtitle', 'View and manage orders from ' . $workshop->name)

@section('header-actions')
<div class="flex items-center gap-3">
    <a href="{{ route('admin.workshops.show', $workshop) }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
        Back to Workshop
    </a>
    <form method="POST" action="{{ route('admin.workshops.orders.sync', $workshop) }}" class="inline">
        @csrf
        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm bg-blue-600 hover:bg-blue-700">
            <span class="material-symbols-outlined text-sm mr-1">sync</span>
            Sync Orders
        </button>
    </form>
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

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <!-- Filters Section -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.workshops.orders.index', $workshop) }}" id="filterForm">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <!-- Order ID Search -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Order ID</label>
                    <input type="text" name="order_id" value="{{ request('order_id') }}" 
                           placeholder="Search by order ID..."
                           class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-primary focus:border-primary">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-primary focus:border-primary">
                        <option value="">All Status</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Created</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Processing Payment</option>
                        <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Paid</option>
                        <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Shipped</option>
                        <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>Refunded</option>
                        <!-- Twofifteen string status options -->
                        <option value="created" {{ request('status') == 'created' ? 'selected' : '' }}>Created (String)</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing Payment (String)</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid (String)</option>
                        <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped (String)</option>
                        <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded (String)</option>
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Date From</label>
                    <input type="datetime-local" name="date_from" value="{{ request('date_from') }}" 
                           class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-primary focus:border-primary">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Date To</label>
                    <input type="datetime-local" name="date_to" value="{{ request('date_to') }}" 
                           class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-primary focus:border-primary">
                </div>

                <!-- Per Page -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Per Page</label>
                    <select name="per_page" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-primary focus:border-primary">
                        @foreach([20, 50, 100] as $size)
                            <option value="{{ $size }}" {{ (int)request('per_page', 20) === $size ? 'selected' : '' }}>
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm bg-primary hover:bg-[#E6851E]">
                        Filter
                    </button>
                    @if(request()->anyFilled(['status', 'date_from', 'date_to', 'order_id']))
                    <a href="{{ route('admin.workshops.orders.index', $workshop) }}" 
                       class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 border border-slate-300 hover:bg-slate-50 transition-colors">
                        Clear
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Orders List -->
    @if(isset($orders) && count($orders) > 0)
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="bg-linear-to-r from-slate-50 to-slate-100 px-6 py-4 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">Orders from {{ $workshop->name }}</h3>
                <span class="text-sm text-slate-600">
                    {{ isset($pagination['total']) ? $pagination['total'] : count($orders) }} order(s)
                </span>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">External ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Buyer Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Tracking</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Created At</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @foreach($orders as $order)
                    @php
                        // Twofifteen may return each row as { order: {...} } - keep fallback just in case
                        $row = is_array($order) ? $order : (is_object($order) ? (array)$order : []);
                        $orderData = (isset($row['order']) && is_array($row['order'])) ? $row['order'] : $row;
                        $orderId = $orderData['id'] ?? $orderData['order_id'] ?? 'N/A';
                        $externalId = $orderData['external_id'] ?? 'N/A';
                        $statusRaw = $orderData['status'] ?? 'unknown';
                        
                        // Map status: support both string and numeric values
                        // Twofifteen commonly returns string status: "Received", "processing", "paid", "shipped", "refunded", ...
                        $statusMap = [
                            // Numeric status (legacy)
                            0 => ['label' => 'Created', 'color' => 'bg-blue-100 text-blue-700', 'canEdit' => true, 'canCancel' => true],
                            1 => ['label' => 'Processing Payment', 'color' => 'bg-yellow-100 text-yellow-700', 'canEdit' => true, 'canCancel' => true],
                            2 => ['label' => 'Paid', 'color' => 'bg-green-100 text-green-700', 'canEdit' => true, 'canCancel' => true],
                            3 => ['label' => 'Shipped', 'color' => 'bg-purple-100 text-purple-700', 'canEdit' => false, 'canCancel' => false],
                            4 => ['label' => 'Refunded', 'color' => 'bg-red-100 text-red-700', 'canEdit' => false, 'canCancel' => false],
                            // String status (Twofifteen)
                            'created' => ['label' => 'Created', 'color' => 'bg-blue-100 text-blue-700', 'canEdit' => true, 'canCancel' => true],
                            'processing' => ['label' => 'Processing Payment', 'color' => 'bg-yellow-100 text-yellow-700', 'canEdit' => true, 'canCancel' => true],
                            'paid' => ['label' => 'Paid', 'color' => 'bg-green-100 text-green-700', 'canEdit' => true, 'canCancel' => true],
                            'shipped' => ['label' => 'Shipped', 'color' => 'bg-purple-100 text-purple-700', 'canEdit' => false, 'canCancel' => false],
                            'refunded' => ['label' => 'Refunded', 'color' => 'bg-red-100 text-red-700', 'canEdit' => false, 'canCancel' => false],
                            'received' => ['label' => 'Received', 'color' => 'bg-blue-100 text-blue-700', 'canEdit' => true, 'canCancel' => true],
                            'completed' => ['label' => 'Completed', 'color' => 'bg-purple-100 text-purple-700', 'canEdit' => false, 'canCancel' => false],
                        ];
                        
                        // Normalize status to lowercase string for lookup
                        $statusKey = is_numeric($statusRaw) ? (int)$statusRaw : strtolower($statusRaw);
                        $statusInfo = $statusMap[$statusKey] ?? ['label' => ucfirst($statusRaw), 'color' => 'bg-gray-100 text-gray-700', 'canEdit' => false, 'canCancel' => false];
                        
                        $items = $orderData['items'] ?? [];
                        $itemsCount = is_array($items) ? count($items) : 0;
                        $createdAt = $orderData['created_at'] ?? $orderData['createdAt'] ?? null;
                        
                        // Get summary/total
                        $summary = $orderData['summary'] ?? [];
                        $total = $summary['total'] ?? null;
                        $currency = $summary['currency'] ?? 'GBP';
                        
                        // Get tracking info
                        $shipping = $orderData['shipping'] ?? [];
                        $trackingNumber = $shipping['trackingNumber'] ?? null;
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-slate-900">{{ $orderId }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-slate-600">{{ $externalId }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusInfo['color'] }}">
                                {{ $statusInfo['label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-slate-600">{{ $orderData['buyer_email'] ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-slate-600">{{ $itemsCount }} item(s)</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($total !== null)
                                <span class="text-sm font-semibold text-slate-900">
                                    {{ number_format($total, 2) }} {{ $currency }}
                                </span>
                            @else
                                <span class="text-sm text-slate-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($trackingNumber)
                                <span class="text-sm font-medium text-slate-900">{{ $trackingNumber }}</span>
                            @else
                                <span class="text-sm text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-slate-600">
                                @if($createdAt)
                                    {{ \Carbon\Carbon::parse($createdAt)->format('M d, Y H:i') }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.workshops.orders.show', [$workshop, $orderId]) }}" 
                                   class="px-3 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                    View
                                </a>
                                @if($statusInfo['canCancel'] ?? false)
                                <button onclick="openCancelModal('{{ $orderId }}')" 
                                        class="px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                    Cancel
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(isset($pagination) && $pagination['last_page'] > 1)
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-slate-600">
                    Showing page {{ $pagination['current_page'] }} of {{ $pagination['last_page'] }}
                    ({{ $pagination['total'] }} total orders)
                </div>
                <div class="flex items-center gap-2">
                    @if($pagination['current_page'] > 1)
                    <a href="{{ route('admin.workshops.orders.index', array_merge([$workshop], request()->except('page'), ['page' => $pagination['current_page'] - 1])) }}" 
                       class="px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        Previous
                    </a>
                    @endif
                    @if($pagination['current_page'] < $pagination['last_page'])
                    <a href="{{ route('admin.workshops.orders.index', array_merge([$workshop], request()->except('page'), ['page' => $pagination['current_page'] + 1])) }}" 
                       class="px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        Next
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
    @else
    <!-- Empty State -->
    <div class="bg-white rounded-xl border border-slate-200 p-16 text-center">
        <div class="w-20 h-20 bg-linear-to-br from-slate-100 to-slate-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-outlined text-4xl text-slate-400">shopping_cart</span>
        </div>
        <h3 class="text-lg font-semibold text-slate-900 mb-2">No orders found</h3>
        <p class="text-slate-500 mb-6">No orders match your filters or the workshop has no orders yet.</p>
        @if(request()->anyFilled(['status', 'date_from', 'date_to', 'order_id']))
        <a href="{{ route('admin.workshops.orders.index', $workshop) }}" 
           class="inline-flex items-center px-6 py-3 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-[#E6851E] transition-colors shadow-sm">
            Clear Filters
        </a>
        @else
        <form method="POST" action="{{ route('admin.workshops.orders.sync', $workshop) }}" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center px-6 py-3 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-[#E6851E] transition-colors shadow-sm">
                <span class="material-symbols-outlined text-sm mr-2">sync</span>
                Sync Orders from Workshop
            </button>
        </form>
        @endif
    </div>
    @endif
</div>

<!-- Cancel Order Modal -->
<div id="cancelModal" class="fixed inset-0 z-1101 p-4 bg-black/60 backdrop-blur-sm hidden pl-64" onclick="closeCancelModal()">
    <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full mx-auto" onclick="event.stopPropagation()">
        <div class="p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Cancel Order</h3>
            <form id="cancelForm" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-600 mb-2">Reason (Optional)</label>
                    <textarea name="reason" rows="3" 
                              class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-primary focus:border-primary"
                              placeholder="Enter cancellation reason..."></textarea>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <button type="button" onclick="closeCancelModal()" 
                            class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm bg-red-600 hover:bg-red-700">
                        Confirm Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openCancelModal(orderId) {
    const modal = document.getElementById('cancelModal');
    const form = document.getElementById('cancelForm');
    form.action = '{{ route("admin.workshops.orders.cancel", [$workshop, ":orderId"]) }}'.replace(':orderId', orderId);
    modal.classList.remove('hidden');
}

function closeCancelModal() {
    const modal = document.getElementById('cancelModal');
    modal.classList.add('hidden');
    document.getElementById('cancelForm').reset();
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCancelModal();
    }
});
</script>
@endpush

@php
    $activeMenu = 'workshop-orders';
@endphp
@endsection


@extends('layouts.admin-dashboard')

@section('title', 'Design Task Details - ' . config('app.name', 'Laravel'))

@section('header-title')
<div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
    <a href="{{ route($routePrefix . '.design-tasks.index') }}" class="hover:text-[#F7961D] transition-colors">Design Tasks</a>
    <span class="material-symbols-outlined text-[14px] mx-1">chevron_right</span>
    <span class="text-slate-400">{{ $designTask->id }}</span>
</div>
<div class="flex items-center gap-3">
    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $designTask->title }}</h1>
    <span class="px-3 py-1 text-[11px] font-bold rounded-full uppercase tracking-wider flex items-center gap-1
        @if($designTask->status === 'pending') bg-yellow-100 text-yellow-700
        @elseif($designTask->status === 'joined') bg-blue-100 text-blue-700
        @elseif($designTask->status === 'completed') bg-green-100 text-green-700
        @elseif($designTask->status === 'approved') bg-emerald-100 text-emerald-700
        @elseif($designTask->status === 'revision') bg-orange-100 text-orange-700
        @else bg-gray-100 text-gray-700
        @endif">
        <span class="w-1.5 h-1.5 rounded-full 
            @if($designTask->status === 'pending') bg-yellow-500
            @elseif($designTask->status === 'joined') bg-blue-500
            @elseif($designTask->status === 'completed') bg-green-500
            @elseif($designTask->status === 'approved') bg-emerald-500
            @elseif($designTask->status === 'revision') bg-orange-500
            @else bg-gray-500
            @endif"></span>
        @if($designTask->status === 'pending') Pending
        @elseif($designTask->status === 'joined') Joined
        @elseif($designTask->status === 'completed') Completed
        @elseif($designTask->status === 'approved') Approved
        @elseif($designTask->status === 'revision') Revision
        @else Cancelled
        @endif
    </span>
</div>
@endsection

@section('header-subtitle', '')

@section('header-actions')
<div class="flex items-center gap-4">
    @if($isDesigner && $designTask->status === 'pending' && !$designTask->designer_id)
    <form method="POST" action="{{ route($routePrefix . '.design-tasks.join', $designTask) }}" class="inline">
        @csrf
        <button type="submit" class="flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-primary transition-colors">
            <span class="material-symbols-outlined text-lg">person_add</span> Join Task
        </button>
    </form>
    @endif
    @if(!$isCustomer)
    <a href="{{ route($routePrefix . '.design-tasks.edit', $designTask) }}" class="flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-lg">edit</span> Edit
    </a>
    @endif
</div>
@endsection

@section('content')
<div class="space-y-6">
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined">error</span>
            <span>{{ $errors->first() }}</span>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Task Information Card -->
            <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-primary px-6 py-4 flex justify-between items-center">
                    <h2 class="text-white font-bold text-lg">{{ $designTask->title }}</h2>
                    <span class="text-white/80 text-sm font-medium">Task ID: #{{ $designTask->id }}</span>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div>
                            <p class="text-[11px] uppercase font-bold text-slate-400 tracking-wider mb-1">Number of Sides</p>
                            <span class="text-lg font-bold text-slate-900">{{ $designTask->sides_count }}</span>
                        </div>
                        <div>
                            <p class="text-[11px] uppercase font-bold text-slate-400 tracking-wider mb-1">Price</p>
                            <span class="text-lg font-bold text-emerald-600">${{ number_format($designTask->price, 2) }}</span>
                        </div>
                        @if($designTask->designer)
                        <div>
                            <p class="text-[11px] uppercase font-bold text-slate-400 tracking-wider mb-1">Designer Info</p>
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-slate-900">{{ $designTask->designer->name }}</span>
                                <span class="text-xs text-slate-500">{{ $designTask->designer->email }}</span>
                            </div>
                        </div>
                        @endif
                        <div>
                            <p class="text-[11px] uppercase font-bold text-slate-400 tracking-wider mb-1">Timestamps</p>
                            <div class="flex flex-col">
                                <span class="text-[11px] text-slate-600">Created: {{ $designTask->created_at->format('d/m/Y') }}</span>
                                @if($designTask->completed_at)
                                <span class="text-[11px] text-slate-600 font-semibold">Done: {{ $designTask->completed_at->format('d/m/Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Mockup References -->
            @if($designTask->mockup_file && is_array($designTask->mockup_file) && count($designTask->mockup_file) > 0)
            <div class="grid grid-cols-1 gap-6">
                @foreach($designTask->mockup_file as $index => $file)
                <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="font-bold text-slate-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">image</span>
                            Reference Mockup - Side {{ $index + 1 }}
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="aspect-video w-full rounded-xl bg-slate-50 overflow-hidden border border-slate-200">
                            <img src="{{ asset('storage/' . $file) }}" alt="Mockup {{ $index + 1 }}" class="w-full h-full object-contain cursor-pointer" onclick="openImageModal('{{ asset('storage/' . $file) }}')">
                        </div>
                    </div>
                </section>
                @endforeach
            </div>
            @endif

            <!-- Final Design (Latest Approved Revision) -->
            @php
                $latestApprovedRevision = $designTask->revisions->where('status', 'approved')->sortByDesc('created_at')->first();
            @endphp
            @if($latestApprovedRevision && $latestApprovedRevision->design_file)
            <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">auto_awesome</span>
                        Final Design
                    </h3>
                    <a href="{{ asset('storage/' . $latestApprovedRevision->design_file) }}" target="_blank" class="text-sm font-bold text-primary flex items-center gap-1 hover:underline">
                        <span class="material-symbols-outlined text-lg">download</span>
                        Download File
                    </a>
                </div>
                <div class="p-6">
                    <div class="bg-slate-50 rounded-xl border border-slate-200 overflow-hidden p-8 flex justify-center">
                        <img src="{{ asset('storage/' . $latestApprovedRevision->design_file) }}" alt="Final Design" class="max-h-[500px] w-auto shadow-2xl rounded-lg cursor-pointer" onclick="openImageModal('{{ asset('storage/' . $latestApprovedRevision->design_file) }}')">
                    </div>
                </div>
            </section>
            @endif

            <!-- Revision History -->
            <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900">Revision History</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    @if($designTask->revisions->count() > 0)
                        @foreach($designTask->revisions->sortByDesc('version') as $revision)
                        <div class="p-6 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                @if($revision->design_file)
                                <div class="w-16 h-16 bg-slate-100 rounded-lg border border-slate-200 overflow-hidden">
                                    <img src="{{ asset('storage/' . $revision->design_file) }}" alt="V{{ $revision->version }}" class="w-full h-full object-cover cursor-pointer" onclick="openImageModal('{{ asset('storage/' . $revision->design_file) }}')">
                                </div>
                                @else
                                <div class="w-16 h-16 bg-slate-100 rounded-lg border border-slate-200 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-slate-400">image</span>
                                </div>
                                @endif
                                <div>
                                    <p class="font-bold text-slate-900">Version {{ $revision->version }}</p>
                                    <p class="text-xs text-slate-500">{{ $revision->submitted_at->format('d/m/Y H:i') }}</p>
                                    @if($revision->notes)
                                    <p class="text-xs text-slate-600 mt-1">{{ Str::limit($revision->notes, 50) }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    @if($revision->status === 'approved') bg-green-100 text-green-700
                                    @elseif($revision->status === 'rejected') bg-red-100 text-red-700
                                    @elseif($revision->status === 'requested_revision') bg-orange-100 text-orange-700
                                    @else bg-blue-100 text-blue-700
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $revision->status)) }}
                                </span>
                                @if($revision->design_file)
                                <a href="{{ asset('storage/' . $revision->design_file) }}" target="_blank" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-primary border border-slate-200 rounded-lg transition-colors">View Draft</a>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @else
                    <div class="p-6 text-center">
                        <p class="text-sm text-slate-500">No revisions yet.</p>
                    </div>
                    @endif
                </div>
            </section>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Timeline -->
            <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="font-bold text-slate-900 mb-6">Timeline</h3>
                <div class="space-y-8 relative">
                    <div class="absolute left-[11px] top-2 bottom-2 w-0.5 bg-slate-100"></div>
                    
                    <!-- Request Created -->
                    <div class="relative flex gap-4">
                        <div class="w-6 h-6 rounded-full bg-emerald-500 border-4 border-white shadow-sm z-10"></div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">Request Created</p>
                            <p class="text-xs text-slate-500">{{ $designTask->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <!-- Designer Accepted -->
                    @if($designTask->designer_id && $designTask->status !== 'pending')
                    <div class="relative flex gap-4">
                        <div class="w-6 h-6 rounded-full bg-blue-500 border-4 border-white shadow-sm z-10"></div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">Designer Accepted</p>
                            <p class="text-xs text-slate-500">
                                @if($designTask->updated_at)
                                {{ $designTask->updated_at->format('d/m/Y H:i') }}
                                @else
                                {{ $designTask->created_at->format('d/m/Y H:i') }}
                                @endif
                            </p>
                        </div>
                    </div>
                    @endif

                    <!-- Design Completed -->
                    @if($designTask->status === 'completed' || $designTask->status === 'approved')
                    <div class="relative flex gap-4">
                        <div class="w-6 h-6 rounded-full bg-emerald-500 border-4 border-white shadow-sm z-10"></div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">Design Completed</p>
                            <p class="text-xs text-slate-500">
                                @if($designTask->completed_at)
                                {{ $designTask->completed_at->format('d/m/Y H:i') }}
                                @elseif($latestApprovedRevision)
                                {{ $latestApprovedRevision->approved_at ? $latestApprovedRevision->approved_at->format('d/m/Y H:i') : $latestApprovedRevision->submitted_at->format('d/m/Y H:i') }}
                                @else
                                {{ $designTask->updated_at->format('d/m/Y H:i') }}
                                @endif
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
            </section>

            <!-- Collaboration Hub -->
            <section class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col h-[500px] overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900">Collaboration Hub</h3>
                </div>
                <div class="flex-1 p-6 overflow-y-auto custom-scrollbar space-y-4 bg-slate-50/50">
                    @if($designTask->comments->count() > 0)
                        @foreach($designTask->comments as $comment)
                        <div class="flex gap-3 {{ $comment->type === 'customer' && $comment->user_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                            <div class="w-8 h-8 rounded-full {{ $comment->type === 'customer' && $comment->user_id === auth()->id() ? 'bg-primary' : 'bg-blue-600' }} flex-shrink-0 flex items-center justify-center text-white text-[10px] font-bold">
                                {{ strtoupper(substr($comment->user->name, 0, 2)) }}
                            </div>
                            <div class="space-y-1 {{ $comment->type === 'customer' && $comment->user_id === auth()->id() ? 'text-right' : '' }}">
                                <div class="flex items-center {{ $comment->type === 'customer' && $comment->user_id === auth()->id() ? 'justify-end' : '' }} gap-2">
                                    @if($comment->type === 'customer' && $comment->user_id === auth()->id())
                                    <span class="text-[10px] text-slate-400">{{ $comment->created_at->format('h:i A') }}</span>
                                    <span class="text-xs font-bold text-slate-900">You</span>
                                    @else
                                    <span class="text-xs font-bold text-slate-900">{{ $comment->user->name }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $comment->created_at->format('h:i A') }}</span>
                                    @endif
                                </div>
                                <div class="{{ $comment->type === 'customer' && $comment->user_id === auth()->id() ? 'bg-primary text-white rounded-2xl rounded-tr-none' : 'bg-white rounded-2xl rounded-tl-none border border-slate-200' }} p-3 text-sm {{ $comment->type === 'customer' && $comment->user_id === auth()->id() ? 'text-white shadow-sm inline-block max-w-[90%] text-left' : 'text-slate-700 shadow-sm' }}">
                                    {{ $comment->content }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                    <p class="text-sm text-slate-500 text-center py-8">No messages yet. Start the conversation!</p>
                    @endif
                </div>
                <div class="p-4 bg-white border-t border-slate-100">
                    <form method="POST" action="{{ route($routePrefix . '.design-comments.store', $designTask) }}" class="relative">
                        @csrf
                        <textarea name="content" rows="2" placeholder="Type a message..." required class="w-full border border-slate-200 rounded-xl p-3 pr-12 text-sm focus:ring-primary focus:border-primary resize-none custom-scrollbar"></textarea>
                        <button type="submit" class="absolute right-2 bottom-2 p-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition-colors">
                            <span class="material-symbols-outlined text-sm font-bold">send</span>
                        </button>
                    </form>
                </div>
            </section>

            <!-- Final Approval Section (Only for customer when revision is submitted) -->
            @php
                $latestSubmittedRevision = $designTask->revisions->where('status', 'submitted')->sortByDesc('created_at')->first();
            @endphp
            @if($isCustomer && $designTask->customer_id === auth()->id() && $latestSubmittedRevision)
            <section class="bg-white rounded-2xl border-2 border-slate-200 shadow-lg p-6 space-y-3">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest text-center mb-2">Final Approval Required</p>
                <form method="POST" action="{{ route($routePrefix . '.design-revisions.approve', [$designTask, $latestSubmittedRevision]) }}" class="inline w-full">
                    @csrf
                    <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-4 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">verified</span>
                        APPROVE DESIGN
                    </button>
                </form>
                <button onclick="openRequestRevisionModal({{ $latestSubmittedRevision->id }})" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-4 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">edit_square</span>
                    REQUEST REVISION
                </button>
            </section>
            @endif

            <!-- Submit Revision Button (For Designer) -->
            @if($isDesigner && $designTask->designer_id === auth()->id() && $designTask->status === 'joined')
            <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <button onclick="openRevisionModal()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">add</span>
                    SUBMIT REVISION
                </button>
            </section>
            @endif
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4" onclick="closeImageModal()">
    <div class="max-w-4xl w-full relative">
        <img id="modalImage" src="" alt="Preview" class="w-full h-auto rounded-lg">
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75 transition-colors">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
</div>

<!-- Request Revision Modal -->
<div id="requestRevisionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="closeRequestRevisionModal()">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full" onclick="event.stopPropagation()">
        <h3 class="text-lg font-bold text-slate-900 mb-4">Request Revision</h3>
        <form method="POST" id="requestRevisionForm" class="space-y-4">
            @csrf
            <textarea name="revision_notes" rows="4" placeholder="Please describe what needs to be changed..." required class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"></textarea>
            <div class="flex items-center gap-3">
                <button type="button" onclick="closeRequestRevisionModal()" class="flex-1 px-4 py-2 border border-slate-300 rounded-lg text-slate-700 font-semibold hover:bg-slate-50 transition-colors">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-orange-500 text-white rounded-lg font-bold hover:bg-orange-600 transition-colors">Submit</button>
            </div>
        </form>
    </div>
</div>

<!-- Submit Revision Modal -->
<div id="revisionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="closeRevisionModal()">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full" onclick="event.stopPropagation()">
        <h3 class="text-lg font-bold text-slate-900 mb-4">Submit Revision</h3>
        <form method="POST" action="{{ route($routePrefix . '.design-revisions.store', $designTask) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Design File <span class="text-red-500">*</span></label>
                <input type="file" name="design_file" accept="image/*,.pdf,.ai,.psd" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Notes</label>
                <textarea name="notes" rows="3" placeholder="Add any notes about this revision..." class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="closeRevisionModal()" class="flex-1 px-4 py-2 border border-slate-300 rounded-lg text-slate-700 font-semibold hover:bg-slate-50 transition-colors">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition-colors">Submit</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openImageModal(src) {
        document.getElementById('modalImage').src = src;
        document.getElementById('imageModal').classList.remove('hidden');
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.add('hidden');
    }

    function openRevisionModal() {
        document.getElementById('revisionModal').classList.remove('hidden');
    }

    function closeRevisionModal() {
        document.getElementById('revisionModal').classList.add('hidden');
    }

    function openRequestRevisionModal(revisionId) {
        const form = document.getElementById('requestRevisionForm');
        form.action = '{{ route($routePrefix . '.design-revisions.request-revision', [$designTask, ':revision']) }}'.replace(':revision', revisionId);
        document.getElementById('requestRevisionModal').classList.remove('hidden');
    }

    function closeRequestRevisionModal() {
        document.getElementById('requestRevisionModal').classList.add('hidden');
    }

    // Prevent modal close when clicking inside
    document.getElementById('imageModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeImageModal();
        }
    });
</script>
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
</style>
@endpush
@endsection

@php
    $activeMenu = 'design-tasks';
@endphp

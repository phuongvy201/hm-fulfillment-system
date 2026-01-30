@extends('layouts.admin-dashboard')

@section('title', 'Design Task - ' . $designTask->title . ' - ' . config('app.name', 'Laravel'))

@section('header-title')
<div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
    <a href="{{ route($routePrefix . '.design-tasks.index') }}" class="hover:text-primary transition-colors">My Design Tasks</a>
    <span class="material-symbols-outlined text-[14px] mx-1">chevron_right</span>
    <span class="text-slate-400">TASK-{{ str_pad($designTask->id, 4, '0', STR_PAD_LEFT) }}</span>
</div>
<div class="flex items-center gap-3">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $designTask->title }}</h1>
    <span class="px-3 py-1 text-xs font-bold rounded-full uppercase tracking-wider
        @if($designTask->status === 'pending') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400
        @elseif($designTask->status === 'joined' || $designTask->status === 'in_progress') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
        @elseif($designTask->status === 'completed') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
        @elseif($designTask->status === 'approved') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
        @elseif($designTask->status === 'revision') bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400
        @else bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400
        @endif">
        @if($designTask->status === 'pending') Pending
        @elseif($designTask->status === 'joined' || $designTask->status === 'in_progress') In Progress
        @elseif($designTask->status === 'completed') Completed
        @elseif($designTask->status === 'approved') Approved
        @elseif($designTask->status === 'revision') Revision
        @else Cancelled
        @endif
    </span>
</div>
@endsection

@section('header-subtitle', 'Track your design request progress')

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

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left Column - Main Content -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Reference Mockup -->
            @if($designTask->mockup_file && is_array($designTask->mockup_file) && count($designTask->mockup_file) > 0)
            <section class="bg-white dark:bg-[#1a140d] rounded-xl shadow-sm border border-slate-200 dark:border-[#3a2f24] overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-[#3a2f24] bg-slate-50 dark:bg-[#2a2218]">
                    <h2 class="font-semibold flex items-center gap-2 text-slate-900 dark:text-white">
                        <span class="material-symbols-outlined text-primary">visibility</span>
                        Reference Mockup
                    </h2>
                </div>
                <div class="p-6">
                    @foreach($designTask->mockup_file as $index => $file)
                    @php
                        $fileUrl = getDesignFileUrl($file);
                    @endphp
                    @if($fileUrl)
                    <div class="aspect-video w-full rounded-lg bg-slate-100 dark:bg-slate-900 overflow-hidden border border-slate-200 dark:border-slate-800 mb-4">
                        <img src="{{ $fileUrl }}" alt="Mockup {{ $index + 1 }}" class="w-full h-full object-cover cursor-pointer" onclick="openImageModal('{{ $fileUrl }}')">
                    </div>
                    @endif
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Final Design (Latest Approved Revision) -->
            @php
                $latestApprovedRevision = $designTask->revisions->where('status', 'approved')->sortByDesc('created_at')->first();
                $latestSubmittedRevision = $designTask->revisions->where('status', 'submitted')->sortByDesc('created_at')->first();
            @endphp
            @php
                $approvedFiles = $latestApprovedRevision ? $latestApprovedRevision->design_files : [];
            @endphp
            @if(count($approvedFiles) > 0)
            <section class="bg-white dark:bg-[#1a140d] rounded-xl shadow-sm border border-slate-200 dark:border-[#3a2f24] overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-[#3a2f24] flex items-center justify-between bg-slate-50 dark:bg-[#2a2218]">
                    <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">auto_awesome</span>
                        Final Approved Design
                    </h3>
                    <div class="flex gap-2">
                        @foreach($approvedFiles as $index => $file)
                        @php
                            $fileUrl = getDesignFileUrl($file);
                        @endphp
                        @if($fileUrl)
                        <a href="{{ $fileUrl }}" target="_blank" class="text-sm font-bold text-primary flex items-center gap-1 hover:underline">
                            <span class="material-symbols-outlined text-lg">download</span>
                            File {{ $index + 1 }}
                        </a>
                        @endif
                        @endforeach
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    @foreach($approvedFiles as $index => $file)
                    <div class="bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden p-4">
                        <p class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2">Side {{ $index + 1 }}</p>
                        <div class="flex justify-center">
                            @php
                                $fileUrl = getDesignFileUrl($file);
                            @endphp
                            @if($fileUrl)
                            <img src="{{ $fileUrl }}" alt="Design {{ $index + 1 }}" class="max-h-[400px] w-auto shadow-xl rounded-lg cursor-pointer" onclick="openImageModal('{{ $fileUrl }}')">
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @elseif($latestSubmittedRevision)
            @php
                $submittedFiles = $latestSubmittedRevision->design_files;
            @endphp
            @if(count($submittedFiles) > 0)
            <section class="bg-white dark:bg-[#1a140d] rounded-xl shadow-sm border-2 border-amber-200 dark:border-amber-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/10">
                    <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-600">pending</span>
                        Pending Approval
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    @foreach($submittedFiles as $index => $file)
                    <div class="bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden p-4">
                        <p class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2">Side {{ $index + 1 }}</p>
                        <div class="flex justify-center">
                            @php
                                $fileUrl = getDesignFileUrl($file);
                            @endphp
                            @if($fileUrl)
                            <img src="{{ $fileUrl }}" alt="Pending Design {{ $index + 1 }}" class="max-h-[400px] w-auto shadow-xl rounded-lg cursor-pointer" onclick="openImageModal('{{ $fileUrl }}')">
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif
            @endif

            <!-- Feedback & Comments -->
            <section class="bg-white dark:bg-[#1a140d] rounded-xl shadow-sm border border-slate-200 dark:border-[#3a2f24]">
                <div class="p-4 border-b border-slate-200 dark:border-[#3a2f24] bg-slate-50 dark:bg-[#2a2218]">
                    <h2 class="font-semibold flex items-center gap-2 text-slate-900 dark:text-white">
                        <span class="material-symbols-outlined text-primary">forum</span>
                        Feedback & Comments
                    </h2>
                </div>
                <div class="p-6">
                    <div class="space-y-6 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                        @if($designTask->comments->count() > 0)
                            @foreach($designTask->comments as $comment)
                            @php
                                $isMyComment = $comment->user_id === auth()->id();
                                $isCustomerComment = $comment->type === 'customer';
                                $isAdminComment = $comment->type === 'admin';
                            @endphp
                            <div class="flex gap-4 {{ $isMyComment ? 'flex-row-reverse' : '' }}">
                                <div class="w-8 h-8 rounded-full 
                                    @if($isCustomerComment) bg-primary
                                    @elseif($isAdminComment) bg-purple-600
                                    @else bg-blue-600
                                    @endif flex-shrink-0 flex items-center justify-center text-white text-xs font-bold">
                                    {{ strtoupper(substr($comment->user->name, 0, 2)) }}
                                </div>
                                <div class="flex-1 {{ $isMyComment ? 'text-right' : '' }}">
                                    <div class="flex items-center {{ $isMyComment ? 'justify-end' : '' }} gap-2 mb-1">
                                        @if($isMyComment)
                                        <span class="text-[10px] text-slate-400">{{ $comment->created_at->format('h:i A') }}</span>
                                        <span class="font-bold text-sm text-slate-900 dark:text-white">You</span>
                                        @else
                                        <span class="font-bold text-sm text-slate-900 dark:text-white">
                                            {{ $comment->user->name }}
                                            @if($isAdminComment)
                                            <span class="text-[10px] text-purple-600 font-normal">(Admin)</span>
                                            @endif
                                        </span>
                                        <span class="text-[10px] text-slate-400 uppercase">{{ $comment->type }} • {{ $comment->created_at->format('h:i A') }}</span>
                                        @endif
                                    </div>
                                    <p class="text-sm 
                                        @if($isMyComment && $isCustomerComment) bg-primary text-white rounded-2xl rounded-tr-none shadow-sm inline-block max-w-[90%] text-left
                                        @elseif($isMyComment) bg-primary/10 dark:bg-primary/20 border-2 border-primary/30 dark:border-primary/50 rounded-2xl rounded-tr-none text-slate-700 dark:text-slate-300 shadow-sm inline-block max-w-[90%] text-left
                                        @elseif($isAdminComment) bg-purple-50 dark:bg-purple-900/20 border-2 border-purple-200 dark:border-purple-800 rounded-2xl rounded-tl-none text-slate-700 dark:text-slate-300 shadow-sm
                                        @else bg-slate-100 dark:bg-slate-800 rounded-2xl rounded-tl-none text-slate-700 dark:text-slate-300 shadow-sm
                                        @endif p-3">
                                        {{ $comment->content }}
                                    </p>
                                </div>
                            </div>
                            @endforeach
                        @else
                        <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-8">No messages yet. Start the conversation!</p>
                        @endif
                    </div>
                    <div class="mt-6 flex gap-3">
                        <form method="POST" action="{{ route($routePrefix . '.design-comments.store', $designTask) }}" class="flex-1 flex gap-3">
                            @csrf
                            <input type="text" name="content" placeholder="Write a comment..." required class="flex-1 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-primary focus:border-primary px-4 py-2">
                            <button type="submit" class="bg-primary text-white p-2 rounded-lg hover:bg-orange-600 transition-colors">
                                <span class="material-symbols-outlined">send</span>
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        </div>

        <!-- Right Column -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Final Approval Section -->
            @if($latestSubmittedRevision && $latestSubmittedRevision->status === 'submitted')
            <section class="bg-white dark:bg-[#1a140d] rounded-xl border-2 border-slate-200 dark:border-[#3a2f24] shadow-lg p-6 space-y-3">
                <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center mb-2">Final Approval Required</p>
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

            <!-- Designer Information -->
            @if($designTask->designer)
            <section class="bg-white dark:bg-[#1a140d] rounded-xl shadow-sm border border-slate-200 dark:border-[#3a2f24] overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-[#3a2f24] bg-slate-50 dark:bg-[#2a2218]">
                    <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">palette</span>
                        Designer Information
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-lg">
                            {{ strtoupper(substr($designTask->designer->name, 0, 2)) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-slate-900 dark:text-white">{{ $designTask->designer->name }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $designTask->designer->email }}</p>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Task Details -->
            <section class="bg-white dark:bg-[#1a140d] rounded-xl shadow-sm border border-slate-200 dark:border-[#3a2f24] overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-[#3a2f24] bg-slate-50 dark:bg-[#2a2218]">
                    <h3 class="font-bold text-slate-900 dark:text-white">Task Details</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-1">Number of Sides</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $designTask->sides_count }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-1">Price</p>
                        <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($designTask->price, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-1">Created</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">{{ $designTask->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($designTask->completed_at)
                    <div>
                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-1">Completed</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">{{ $designTask->completed_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                </div>
            </section>

            <!-- Revision History -->
            @if($designTask->revisions->count() > 0)
            <section class="bg-white dark:bg-[#1a140d] rounded-xl shadow-sm border border-slate-200 dark:border-[#3a2f24] overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-[#3a2f24] bg-slate-50 dark:bg-[#2a2218]">
                    <h3 class="font-bold text-slate-900 dark:text-white">Revision History</h3>
                </div>
                <div class="p-6 space-y-4">
                    @foreach($designTask->revisions->sortByDesc('version') as $revision)
                    <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-sm text-slate-900 dark:text-white">Version {{ $revision->version }}</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if($revision->status === 'approved') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                @elseif($revision->status === 'rejected') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                @elseif($revision->status === 'requested_revision') bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400
                                @else bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $revision->status)) }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $revision->submitted_at->format('d/m/Y H:i') }}</p>
                        @if($revision->notes)
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-2">{{ Str::limit($revision->notes, 100) }}</p>
                        @endif
                        @php
                            $designFiles = $revision->design_files;
                        @endphp
                        @if(count($designFiles) > 0)
                        <div class="mt-4 space-y-3">
                            @foreach($designFiles as $index => $file)
                            <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                                <div class="p-3">
                                    <p class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2">Side {{ $index + 1 }}</p>
                                    <div class="aspect-video w-full rounded-lg bg-slate-50 dark:bg-slate-800 overflow-hidden border border-slate-200 dark:border-slate-700">
                                        @php
                                            $fileUrl = getDesignFileUrl($file);
                                        @endphp
                                        @if($fileUrl)
                                        <img src="{{ $fileUrl }}" alt="Version {{ $revision->version }} - Side {{ $index + 1 }}" class="w-full h-full object-contain cursor-pointer hover:opacity-90 transition-opacity" onclick="openImageModal('{{ $fileUrl }}')">
                                        @endif
                                    </div>
                                    @if($fileUrl)
                                    <a href="{{ $fileUrl }}" target="_blank" class="mt-2 inline-block text-xs font-bold text-primary hover:underline">
                                        View File {{ $index + 1 }}
                                    </a>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
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
    <div class="bg-white dark:bg-[#1a140d] rounded-2xl p-6 max-w-md w-full border border-slate-200 dark:border-[#3a2f24]" onclick="event.stopPropagation()">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Request Revision</h3>
        <form method="POST" id="requestRevisionForm" class="space-y-4">
            @csrf
            <textarea name="revision_notes" rows="4" placeholder="Please describe what needs to be changed..." required class="w-full px-4 py-3 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"></textarea>
            <div class="flex items-center gap-3">
                <button type="button" onclick="closeRequestRevisionModal()" class="flex-1 px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-orange-500 text-white rounded-lg font-bold hover:bg-orange-600 transition-colors">Submit</button>
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

    function openRequestRevisionModal(revisionId) {
        const form = document.getElementById('requestRevisionForm');
        form.action = '{{ route($routePrefix . '.design-revisions.request-revision', [$designTask, ':revision']) }}'.replace(':revision', revisionId);
        document.getElementById('requestRevisionModal').classList.remove('hidden');
    }

    function closeRequestRevisionModal() {
        document.getElementById('requestRevisionModal').classList.add('hidden');
    }
</script>
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border-radius: 10px;
    }
</style>
@endpush
@endsection

@php
    $activeMenu = 'design-tasks';
@endphp


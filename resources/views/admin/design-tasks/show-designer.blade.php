@extends('layouts.admin-dashboard')

@section('title', 'Design Task - ' . $designTask->title . ' - ' . config('app.name', 'Laravel'))

@section('header-title')
<div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
    <a href="{{ route($routePrefix . '.design-tasks.index') }}" class="hover:text-primary transition-colors">Design Tasks</a>
    <span class="material-symbols-outlined text-[14px] mx-1">chevron_right</span>
    <span class="text-slate-400">TASK-{{ str_pad($designTask->id, 4, '0', STR_PAD_LEFT) }}</span>
</div>
<div class="flex items-center gap-3">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $designTask->title }}</h1>
    <span class="px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-bold rounded-full uppercase tracking-wider
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

@section('header-subtitle', 'Design and upload your work')

@section('header-actions')
<div class="flex items-center gap-3">
    @if($designTask->status === 'pending' && !$designTask->designer_id)
    <form method="POST" action="{{ route($routePrefix . '.design-tasks.join', $designTask) }}" class="inline">
        @csrf
        <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-bold rounded-lg hover:bg-primary/90 transition-all">
            <span class="material-symbols-outlined text-lg">person_add</span> Join Task
        </button>
    </form>
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

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left Column - Main Content -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Reference Mockup Section -->
            @if($designTask->mockup_file && is_array($designTask->mockup_file) && count($designTask->mockup_file) > 0)
            <section class="bg-white dark:bg-[#1a140d] rounded-xl shadow-sm border border-slate-200 dark:border-[#3a2f24] overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-[#3a2f24] flex justify-between items-center bg-slate-50 dark:bg-[#2a2218]">
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
                    
                    <!-- Product Details -->
                    <div class="mt-6 grid grid-cols-3 gap-4">
                        <div class="p-3 rounded-lg bg-slate-50 dark:bg-[#2a2218] border border-slate-100 dark:border-slate-700">
                            <p class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 mb-1">Sides</p>
                            <span class="text-sm font-medium text-slate-900 dark:text-white">{{ $designTask->sides_count }}</span>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-50 dark:bg-[#2a2218] border border-slate-100 dark:border-slate-700">
                            <p class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 mb-1">Price</p>
                            <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">${{ number_format($designTask->price, 2) }}</span>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-50 dark:bg-[#2a2218] border border-slate-100 dark:border-slate-700">
                            <p class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 mb-1">Task ID</p>
                            <span class="text-sm font-medium text-slate-900 dark:text-white">#{{ $designTask->id }}</span>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Customer Notes -->
            @if($designTask->description || $designTask->revision_notes)
            <section class="bg-white dark:bg-[#1a140d] rounded-xl shadow-sm border border-slate-200 dark:border-[#3a2f24] overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-[#3a2f24] bg-slate-50 dark:bg-[#2a2218]">
                    <h2 class="font-semibold flex items-center gap-2 text-slate-900 dark:text-white">
                        <span class="material-symbols-outlined text-primary">note</span>
                        Customer Notes
                    </h2>
                </div>
                <div class="p-6">
                    <div class="p-4 bg-amber-50 dark:bg-amber-900/10 border-l-4 border-primary rounded-r-lg">
                        <p class="text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                            {{ $designTask->revision_notes ?: $designTask->description }}
                        </p>
                    </div>
                </div>
            </section>
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
                                        <span class="font-bold text-sm text-slate-900 dark:text-white">
                                            You
                                            @if($isAdminComment)
                                            <span class="text-[10px] text-purple-600 font-normal">(Admin)</span>
                                            @endif
                                        </span>
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
                                        @elseif($isMyComment && $isAdminComment) bg-purple-100 dark:bg-purple-900/30 border-2 border-purple-300 dark:border-purple-700 rounded-2xl rounded-tr-none text-slate-700 dark:text-slate-300 shadow-sm inline-block max-w-[90%] text-left
                                        @elseif($isMyComment) bg-blue-100 dark:bg-blue-900/30 border-2 border-blue-300 dark:border-blue-700 rounded-2xl rounded-tr-none text-slate-700 dark:text-slate-300 shadow-sm inline-block max-w-[90%] text-left
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

        <!-- Right Column - Upload Section -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Upload Final Design Section -->
            @if($designTask->designer_id === auth()->id() && ($designTask->status === 'joined' || $designTask->status === 'in_progress' || $designTask->status === 'revision'))
            <section class="bg-white dark:bg-[#1a140d] rounded-xl shadow-sm border border-slate-200 dark:border-[#3a2f24] overflow-hidden sticky top-24">
                <div class="p-4 border-b border-slate-200 dark:border-[#3a2f24] bg-slate-50 dark:bg-[#2a2218]">
                    <h2 class="font-semibold flex items-center gap-2 text-slate-900 dark:text-white">
                        <span class="material-symbols-outlined text-primary">cloud_upload</span>
                        Upload Final Design
                    </h2>
                </div>
                <div class="p-6 space-y-6">
                    <form method="POST" action="{{ route($routePrefix . '.design-revisions.store', $designTask) }}" enctype="multipart/form-data" id="revisionForm">
                        @csrf
                        
                        <!-- Design Files - Dynamic based on sides_count -->
                        @php
                            $sideLabels = ['Front', 'Back', 'Left Sleeve', 'Right Sleeve', 'Collar', 'Label'];
                        @endphp
                        @for($i = 0; $i < $designTask->sides_count; $i++)
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <label class="text-sm font-bold text-slate-700 dark:text-slate-300">
                                    {{ $sideLabels[$i] ?? 'Side ' . ($i + 1) }} Design
                                    @if($i === 0) <span class="text-red-500">*</span> @endif
                                </label>
                                <span class="text-[10px] text-slate-500">Min 300 DPI • PNG/PSD</span>
                            </div>
                            <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl p-6 transition-all hover:border-primary group bg-slate-50/50 dark:bg-slate-800/30" id="designFileContainer{{ $i }}">
                                <input type="file" name="design_files[]" accept="image/*,.pdf,.ai,.psd" {{ $i === 0 ? 'required' : '' }} class="hidden" id="designInput{{ $i }}" onchange="handleFileSelect(this, 'designPreview{{ $i }}', 'designFileInfo{{ $i }}', 'designFileContainer{{ $i }}')">
                                <label for="designInput{{ $i }}" class="flex flex-col items-center text-center cursor-pointer" id="designFileLabel{{ $i }}">
                                    <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-primary transition-colors mb-2">add_photo_alternate</span>
                                    <p class="text-xs text-slate-600 dark:text-slate-400"><span class="text-primary font-bold">Click to upload</span> or drag and drop</p>
                                    <p class="text-[10px] text-slate-400 mt-1">Recommended size: 12x16 inches</p>
                                </label>
                                <!-- File Info -->
                                <div id="designFileInfo{{ $i }}" class="hidden mt-3 p-3 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-primary text-sm">check_circle</span>
                                            <span class="text-xs font-bold text-slate-900 dark:text-white" id="designFileName{{ $i }}"></span>
                                        </div>
                                        <button type="button" onclick="clearFileSelection('designInput{{ $i }}', 'designPreview{{ $i }}', 'designFileInfo{{ $i }}', 'designFileLabel{{ $i }}', 'designFileContainer{{ $i }}')" class="text-slate-400 hover:text-red-500 transition-colors">
                                            <span class="material-symbols-outlined text-sm">close</span>
                                        </button>
                                    </div>
                                    <p class="text-[10px] text-slate-500" id="designFileSize{{ $i }}"></p>
                                </div>
                                <!-- Preview Image -->
                                <div id="designPreview{{ $i }}" class="hidden mt-4">
                                    <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700 p-3">
                                        <p class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2">Preview</p>
                                        <img src="" alt="Preview {{ $i + 1 }}" class="w-full h-auto rounded-lg max-h-48 object-contain border border-slate-200 dark:border-slate-700">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endfor

                        <!-- Notes -->
                        <div class="space-y-3">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Notes (Optional)</label>
                            <textarea name="notes" rows="3" placeholder="Add any notes about this revision..." class="w-full px-4 py-3 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary bg-white dark:bg-slate-800 text-slate-900 dark:text-white"></textarea>
                        </div>

                        <div class="pt-6 border-t border-slate-200 dark:border-[#3a2f24] flex flex-col gap-3">
                            <button type="submit" class="w-full bg-primary text-white font-bold py-3 px-4 rounded-lg hover:bg-orange-600 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-sm">check_circle</span>
                                Submit for Approval
                            </button>
                            <button type="button" onclick="saveDraft()" class="w-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold py-3 px-4 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-sm">save</span>
                                Save Draft
                            </button>
                        </div>
                    </form>
                </div>
            </section>
            @endif

            <!-- Update Design Workflow Section (When revision is requested or submitted) -->
            @php
                $latestSubmittedRevision = $designTask->revisions->where('status', 'submitted')->sortByDesc('created_at')->first();
                $latestRevisionRequested = $designTask->revisions->where('status', 'revision_requested')->sortByDesc('created_at')->first();
                $canUpdateRevision = ($latestSubmittedRevision || $latestRevisionRequested) && $designTask->designer_id === auth()->id();
                $revisionToUpdate = $latestRevisionRequested ?? $latestSubmittedRevision;
            @endphp
            @if($canUpdateRevision && $revisionToUpdate)
            <section class="bg-white dark:bg-[#1a140d] rounded-xl shadow-sm border border-slate-200 dark:border-[#3a2f24] overflow-hidden sticky top-24">
                <div class="p-4 border-b border-slate-200 dark:border-[#3a2f24] bg-primary/10">
                    <h2 class="font-semibold flex items-center gap-2 text-slate-900 dark:text-white">
                        <span class="material-symbols-outlined text-primary">sync</span>
                        Update Design Workflow
                    </h2>
                </div>
                <div class="p-6 space-y-6">
                    <div class="flex gap-3 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-900/30 p-4 rounded-lg">
                        <span class="material-symbols-outlined text-amber-500">info</span>
                        <p class="text-sm text-amber-800 dark:text-amber-200">
                            You can update the submitted design to improve quality or fix minor errors.
                        </p>
                    </div>

                    <!-- Current Files -->
                    <div class="space-y-3">
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Current Files</label>
                        @php
                            $currentFiles = $revisionToUpdate->design_files;
                            $sideLabels = ['Front', 'Back', 'Left Sleeve', 'Right Sleeve', 'Collar', 'Label'];
                        @endphp
                        @if(count($currentFiles) > 0)
                            @foreach($currentFiles as $index => $file)
                            @php
                                $fileUrl = getDesignFileUrl($file);
                                $fileName = basename($file);
                            @endphp
                            <div class="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50/50 dark:bg-slate-800/30">
                                <div class="flex items-center gap-3">
                                    @if($fileUrl)
                                    <div class="w-12 h-12 rounded border border-slate-200 dark:border-slate-700 overflow-hidden bg-white">
                                        <img alt="Current design thumbnail {{ $index + 1 }}" class="w-full h-full object-cover" src="{{ $fileUrl }}">
                                    </div>
                                    @else
                                    <div class="w-12 h-12 rounded border border-slate-200 dark:border-slate-700 overflow-hidden bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-slate-400">image</span>
                                    </div>
                                    @endif
                                    <div>
                                        <p class="text-xs font-medium text-slate-900 dark:text-slate-100">{{ $sideLabels[$index] ?? 'Side ' . ($index + 1) }}: {{ $fileName }}</p>
                                        <p class="text-[10px] text-slate-500">{{ $revisionToUpdate->submitted_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                @if($fileUrl)
                                <a class="text-primary text-xs font-bold hover:underline flex items-center gap-1" href="{{ $fileUrl }}" target="_blank">
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                    View File
                                </a>
                                @endif
                            </div>
                            @endforeach
                        @else
                            <div class="p-4 border border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50/50 dark:bg-slate-800/30">
                                <p class="text-xs text-slate-500">No files found</p>
                            </div>
                        @endif
                    </div>

                    <!-- Upload New Version Form -->
                    <form id="updateRevisionForm">
                        @csrf
                        @method('PUT')
                        
                        @php
                            $sideLabels = ['Front', 'Back', 'Left Sleeve', 'Right Sleeve', 'Collar', 'Label'];
                        @endphp
                        @for($i = 0; $i < $designTask->sides_count; $i++)
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <label class="text-sm font-bold text-slate-700 dark:text-slate-300">
                                    {{ $sideLabels[$i] ?? 'Side ' . ($i + 1) }} Design
                                    @if($i === 0) <span class="text-red-500">*</span> @endif
                                </label>
                                <span class="text-[10px] text-slate-500">PNG, PSD or AI • Max 100MB</span>
                            </div>
                            <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl p-6 transition-all hover:border-primary group bg-slate-50/50 dark:bg-slate-800/30" id="updateFileContainer{{ $i }}">
                                <input type="file" name="design_files[]" accept="image/*,.pdf,.ai,.psd" @if($i === 0) required @endif class="hidden" id="updateDesignFile{{ $i }}" onchange="handleUpdateFileSelect(this, 'updateDesignPreview{{ $i }}', 'updateFileInfo{{ $i }}', 'updateFileContainer{{ $i }}')">
                                <label for="updateDesignFile{{ $i }}" class="flex flex-col items-center text-center cursor-pointer" id="updateFileLabel{{ $i }}">
                                    <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-primary transition-colors mb-2">cloud_upload</span>
                                    <p class="text-xs text-slate-600 dark:text-slate-400"><span class="text-primary font-bold">Choose File</span> or drag and drop</p>
                                    <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-tight">PNG, PSD or AI • Max 100MB</p>
                                </label>
                                <!-- File Info -->
                                <div id="updateFileInfo{{ $i }}" class="hidden mt-3 p-3 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-primary text-sm">check_circle</span>
                                            <span class="text-xs font-bold text-slate-900 dark:text-white" id="updateFileName{{ $i }}"></span>
                                        </div>
                                        <button type="button" onclick="clearFileSelection('updateDesignFile{{ $i }}', 'updateDesignPreview{{ $i }}', 'updateFileInfo{{ $i }}', 'updateFileLabel{{ $i }}', 'updateFileContainer{{ $i }}')" class="text-slate-400 hover:text-red-500 transition-colors">
                                            <span class="material-symbols-outlined text-sm">close</span>
                                        </button>
                                    </div>
                                    <p class="text-[10px] text-slate-500" id="updateFileSize{{ $i }}"></p>
                                </div>
                                <!-- Preview Image -->
                                <div id="updateDesignPreview{{ $i }}" class="hidden mt-4">
                                    <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700 p-3">
                                        <p class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2">Preview</p>
                                        <img src="" alt="Preview {{ $i + 1 }}" class="w-full h-auto rounded-lg max-h-48 object-contain border border-slate-200 dark:border-slate-700">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endfor

                        <!-- Notes -->
                        <div class="space-y-3">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Notes (Optional)</label>
                            <textarea name="notes" rows="3" placeholder="Add any notes about this update..." class="w-full px-4 py-3 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary bg-white dark:bg-slate-800 text-slate-900 dark:text-white">{{ $revisionToUpdate->notes }}</textarea>
                        </div>

                        <div class="pt-6 border-t border-slate-200 dark:border-[#3a2f24] flex flex-col gap-3">
                            <button type="submit" class="w-full bg-primary text-white font-bold py-3 px-4 rounded-lg hover:bg-orange-600 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-sm">check_circle</span>
                                Submit Revision
                            </button>
                            <button type="button" onclick="saveUpdateDraft()" class="w-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold py-3 px-4 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-sm">save</span>
                                Save Draft
                            </button>
                        </div>
                    </form>
                </div>
            </section>
            @endif

            <!-- Customer Information -->
            <section class="bg-white dark:bg-[#1a140d] rounded-xl shadow-sm border border-slate-200 dark:border-[#3a2f24] overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-[#3a2f24] bg-slate-50 dark:bg-[#2a2218]">
                    <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">person</span>
                        Customer Information
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-white font-bold text-lg">
                            {{ strtoupper(substr($designTask->customer->name, 0, 2)) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-slate-900 dark:text-white">{{ $designTask->customer->name }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $designTask->customer->email }}</p>
                        </div>
                    </div>
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
                    <div class="text-center py-4">
                        <p class="text-xs text-slate-400 dark:text-slate-500 italic">End of history</p>
                    </div>
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

@push('scripts')
<script src="{{ asset('js/design-multipart-upload.js') }}"></script>
<script>
    // Format file size helper (must be defined before handleFileSelect)
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Clear file selection helper
    function clearFileSelection(inputId, previewId, fileInfoId, labelId, containerId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const fileInfo = document.getElementById(fileInfoId);
        const label = document.getElementById(labelId);
        const container = document.getElementById(containerId);
        
        if (input) input.value = '';
        if (preview) preview.classList.add('hidden');
        if (fileInfo) fileInfo.classList.add('hidden');
        if (label) label.classList.remove('hidden');
        if (container) {
            container.classList.remove('border-solid', 'border-primary', 'bg-primary/5');
            container.classList.add('border-dashed', 'border-slate-300', 'dark:border-slate-700');
        }
    }

    // Handle file selection with preview (must be defined early for inline onchange)
    function handleFileSelect(input, previewId, fileInfoId, containerId) {
        console.log('handleFileSelect called', { 
            input: input?.id, 
            previewId, 
            fileInfoId, 
            containerId,
            hasFile: input?.files?.length > 0
        });
        
        const file = input?.files?.[0];
        if (file) {
            console.log('File selected:', { name: file.name, size: file.size, type: file.type });
            
            // Show file info
            const fileInfo = document.getElementById(fileInfoId);
            const fileName = document.getElementById(fileInfoId.replace('Info', 'Name'));
            const fileSize = document.getElementById(fileInfoId.replace('Info', 'Size'));
            const label = document.getElementById(fileInfoId.replace('Info', 'Label'));
            const container = document.getElementById(containerId);
            
            console.log('Elements found:', { 
                fileInfo: !!fileInfo, 
                fileName: !!fileName, 
                fileSize: !!fileSize, 
                label: !!label, 
                container: !!container 
            });
            
            if (fileInfo && fileName && fileSize) {
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                fileInfo.classList.remove('hidden');
                console.log('File info displayed');
                
                if (label) {
                    label.classList.add('hidden');
                    console.log('Label hidden');
                }
                
                if (container) {
                    container.classList.remove('border-dashed', 'border-slate-300', 'dark:border-slate-700');
                    container.classList.add('border-solid', 'border-primary', 'bg-primary/5');
                    console.log('Container styled');
                }
            } else {
                console.error('Missing elements:', { fileInfo: !!fileInfo, fileName: !!fileName, fileSize: !!fileSize });
            }
            
            // Show preview for images
            if (file.type.startsWith('image/')) {
                console.log('Loading image preview...');
                const reader = new FileReader();
                reader.onload = function(e) {
                    console.log('Image loaded, showing preview');
                    const preview = document.getElementById(previewId);
                    if (preview) {
                        const img = preview.querySelector('img');
                        if (img) {
                            img.src = e.target.result;
                            img.alt = file.name;
                            preview.classList.remove('hidden');
                            console.log('Preview shown successfully');
                        } else {
                            console.error('Image element not found in preview container');
                        }
                    } else {
                        console.error('Preview element not found:', previewId);
                    }
                };
                reader.onerror = function(error) {
                    console.error('Error reading file:', error);
                };
                reader.readAsDataURL(file);
            } else {
                // For non-image files, hide preview but show file info
                console.log('Non-image file, hiding preview');
                const preview = document.getElementById(previewId);
                if (preview) {
                    preview.classList.add('hidden');
                }
            }
        } else {
            console.log('No file selected');
            const labelId = fileInfoId.replace('Info', 'Label');
            clearFileSelection(input.id, previewId, fileInfoId, labelId, containerId);
        }
    }

    function openImageModal(src) {
        document.getElementById('modalImage').src = src;
        document.getElementById('imageModal').classList.remove('hidden');
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.add('hidden');
    }

    function handleUpdateFileSelect(input, previewId, fileInfoId, containerId) {
        const file = input.files[0];
        if (file) {
            // Show file info
            const fileInfo = document.getElementById(fileInfoId);
            const fileName = document.getElementById(fileInfoId.replace('Info', 'Name'));
            const fileSize = document.getElementById(fileInfoId.replace('Info', 'Size'));
            const label = document.getElementById(fileInfoId.replace('Info', 'Label'));
            const container = document.getElementById(containerId);
            
            if (fileInfo && fileName && fileSize) {
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                fileInfo.classList.remove('hidden');
                if (label) label.classList.add('hidden');
                if (container) {
                    container.classList.remove('border-dashed', 'border-slate-300', 'dark:border-slate-700');
                    container.classList.add('border-solid', 'border-primary', 'bg-primary/5');
                }
            }
            
            // Show preview for images
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById(previewId);
                    if (preview) {
                        preview.querySelector('img').src = e.target.result;
                        preview.classList.remove('hidden');
                    }
                };
                reader.readAsDataURL(file);
            } else {
                // For non-image files, hide preview
                const preview = document.getElementById(previewId);
                if (preview) {
                    preview.classList.add('hidden');
                }
            }
        } else {
            clearFileSelection(input.id, previewId, fileInfoId, fileInfoId.replace('Info', 'Label'), containerId);
        }
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
    
    function clearFileSelection(inputId, previewId, fileInfoId, labelId, containerId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const fileInfo = document.getElementById(fileInfoId);
        const label = document.getElementById(labelId);
        const container = document.getElementById(containerId);
        
        if (input) input.value = '';
        if (preview) preview.classList.add('hidden');
        if (fileInfo) fileInfo.classList.add('hidden');
        if (label) label.classList.remove('hidden');
        if (container) {
            container.classList.remove('border-solid', 'border-primary', 'bg-primary/5');
            container.classList.add('border-dashed', 'border-slate-300', 'dark:border-slate-700');
        }
    }

    // Initialize multipart upload handler (must be before using it)
    const multipartUpload = new DesignMultipartUpload({{ $designTask->id }}, '{{ $routePrefix }}');

    // Handle update form submission with multipart upload
    @if(isset($revisionToUpdate))
    document.getElementById('updateRevisionForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = e.target;
        const fileInputs = form.querySelectorAll('input[type="file"]');
        const notes = form.querySelector('textarea[name="notes"]')?.value || null;
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        
        // Collect files
        const files = [];
        let hasRequiredFile = false;
        
        for (let i = 0; i < fileInputs.length; i++) {
            const input = fileInputs[i];
            if (input.files && input.files.length > 0) {
                files.push(input.files[0]);
                if (i === 0) hasRequiredFile = true;
            }
        }
        
        if (!hasRequiredFile) {
            alert('Please upload at least the first design file.');
            return false;
        }
        
        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">sync</span> Uploading...';
        
        try {
            // Upload using multipart upload
            const result = await multipartUpload.upload(
                files,
                notes,
                false, // Not saving as draft
                {{ $revisionToUpdate->id }}, // Revision ID for update
                (progress) => {
                    console.log('Update progress:', progress + '%');
                }
            );
            
            if (result.success) {
                window.location.href = window.location.href + '?success=' + encodeURIComponent(result.message);
            } else {
                throw new Error(result.message || 'Update failed');
            }
        } catch (error) {
            console.error('Update error:', error);
            alert('Update failed: ' + error.message);
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    });

    function saveUpdateDraft() {
        const form = document.getElementById('updateRevisionForm');
        if (!form) return;
        
        const fileInputs = form.querySelectorAll('input[type="file"]');
        const notes = form.querySelector('textarea[name="notes"]')?.value || null;
        const saveButton = form.querySelector('button[type="button"][onclick="saveUpdateDraft()"]');
        const originalButtonText = saveButton.innerHTML;
        
        // Collect files
        const files = [];
        let hasRequiredFile = false;
        
        for (let i = 0; i < fileInputs.length; i++) {
            const input = fileInputs[i];
            if (input.files && input.files.length > 0) {
                files.push(input.files[0]);
                if (i === 0) hasRequiredFile = true;
            }
        }
        
        if (!hasRequiredFile) {
            alert('Please upload at least the first design file.');
            return false;
        }
        
        // Show loading state
        saveButton.disabled = true;
        saveButton.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">sync</span> Saving...';
        
        // Upload using multipart upload
        multipartUpload.upload(
            files,
            notes,
            true, // Save as draft
            {{ $revisionToUpdate->id }}, // Revision ID for update
            (progress) => {
                console.log('Draft save progress:', progress + '%');
            }
        ).then(result => {
            if (result.success) {
                window.location.href = window.location.href + '?success=' + encodeURIComponent(result.message);
            } else {
                throw new Error(result.message || 'Save failed');
            }
        }).catch(error => {
            console.error('Save error:', error);
            alert('Save failed: ' + error.message);
            saveButton.disabled = false;
            saveButton.innerHTML = originalButtonText;
        });
    }
    @endif

    // Handle form submission with multipart upload
    document.getElementById('revisionForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = e.target;
        const fileInputs = form.querySelectorAll('input[type="file"]');
        const notes = form.querySelector('textarea[name="notes"]')?.value || null;
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        
        // Collect files
        const files = [];
        let hasRequiredFile = false;
        
        for (let i = 0; i < fileInputs.length; i++) {
            const input = fileInputs[i];
            if (input.files && input.files.length > 0) {
                files.push(input.files[0]);
                if (i === 0) hasRequiredFile = true;
            }
        }
        
        if (!hasRequiredFile) {
            alert('Please upload at least the first design file.');
            return false;
        }
        
        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">sync</span> Uploading...';
        
        // Create progress bar if not exists
        let progressBar = document.getElementById('uploadProgress');
        if (!progressBar) {
            progressBar = document.createElement('div');
            progressBar.id = 'uploadProgress';
            progressBar.className = 'mt-4';
            progressBar.innerHTML = `
                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                    <div id="uploadProgressBar" class="bg-primary h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <p id="uploadProgressText" class="text-xs text-slate-500 mt-2 text-center">0%</p>
            `;
            form.appendChild(progressBar);
        }
        
        try {
            // Upload using multipart upload
            const result = await multipartUpload.upload(
                files,
                notes,
                false, // Not saving as draft
                null, // No revision ID for new upload
                (progress) => {
                    // Overall progress
                    document.getElementById('uploadProgressBar').style.width = progress + '%';
                    document.getElementById('uploadProgressText').textContent = Math.round(progress) + '%';
                },
                (fileIndex, fileName, fileProgress) => {
                    // Per-file progress (optional)
                    console.log(`Uploading ${fileName}: ${fileProgress}%`);
                }
            );
            
            if (result.success) {
                // Show success message and redirect
                window.location.href = window.location.href + '?success=' + encodeURIComponent(result.message);
            } else {
                throw new Error(result.message || 'Upload failed');
            }
        } catch (error) {
            console.error('Upload error:', error);
            alert('Upload failed: ' + error.message);
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
            if (progressBar) {
                progressBar.remove();
            }
        }
    });

    function saveDraft() {
        const form = document.getElementById('revisionForm');
        if (!form) return;
        
        const fileInputs = form.querySelectorAll('input[type="file"]');
        const notes = form.querySelector('textarea[name="notes"]')?.value || null;
        const saveButton = form.querySelector('button[type="button"][onclick="saveDraft()"]');
        const originalButtonText = saveButton.innerHTML;
        
        // Collect files
        const files = [];
        let hasRequiredFile = false;
        
        for (let i = 0; i < fileInputs.length; i++) {
            const input = fileInputs[i];
            if (input.files && input.files.length > 0) {
                files.push(input.files[0]);
                if (i === 0) hasRequiredFile = true;
            }
        }
        
        if (!hasRequiredFile) {
            alert('Please upload at least the first design file.');
            return false;
        }
        
        // Show loading state
        saveButton.disabled = true;
        saveButton.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">sync</span> Saving...';
        
        // Upload using multipart upload
        multipartUpload.upload(
            files,
            notes,
            true, // Save as draft
            null, // No revision ID for new upload
            (progress) => {
                console.log('Draft save progress:', progress + '%');
            }
        ).then(result => {
            if (result.success) {
                window.location.href = window.location.href + '?success=' + encodeURIComponent(result.message);
            } else {
                throw new Error(result.message || 'Save failed');
            }
        }).catch(error => {
            console.error('Save error:', error);
            alert('Save failed: ' + error.message);
            saveButton.disabled = false;
            saveButton.innerHTML = originalButtonText;
        });
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


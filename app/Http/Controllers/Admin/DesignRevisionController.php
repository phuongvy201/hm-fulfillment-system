<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DesignTask;
use App\Models\DesignRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DesignRevisionController extends Controller
{
    /**
     * Store a newly created revision.
     */
    public function store(Request $request, DesignTask $designTask)
    {
        $user = auth()->user();
        $isDesigner = $user->hasRole('designer') || $user->isSuperAdmin();

        if (!$isDesigner || $designTask->designer_id !== $user->id) {
            abort(403, 'Only the assigned designer can submit revisions.');
        }

        $validated = $request->validate([
            'design_file' => 'required|file|mimes:jpg,jpeg,png,pdf,psd,ai|max:10240',
            'notes' => 'nullable|string',
        ]);

        // Get next version number
        $latestRevision = $designTask->revisions()->latest('version')->first();
        $nextVersion = $latestRevision ? $latestRevision->version + 1 : 1;

        // Handle design file upload
        $file = $request->file('design_file');
        $path = $file->store('design-tasks/revisions', 'public');

        $revision = new DesignRevision();
        $revision->design_task_id = $designTask->id;
        $revision->designer_id = $user->id;
        $revision->design_file = $path;
        $revision->notes = $validated['notes'] ?? null;
        $revision->version = $nextVersion;
        $revision->status = 'submitted';
        $revision->submitted_at = now();
        $revision->save();

        // Update task status
        $designTask->status = 'completed';
        $designTask->design_file = $path; // Update main design file
        $designTask->save();

        return redirect()->back()->with('success', 'Revision submitted successfully.');
    }

    /**
     * Approve a revision.
     */
    public function approve(DesignTask $designTask, DesignRevision $designRevision)
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

        if (!$isCustomer || $designTask->customer_id !== $user->id) {
            abort(403, 'Only the customer can approve revisions.');
        }

        if ($designRevision->design_task_id !== $designTask->id) {
            abort(404, 'Revision not found for this task.');
        }

        $designRevision->status = 'approved';
        $designRevision->approved_at = now();
        $designRevision->save();

        // Update task
        $designTask->status = 'approved';
        $designTask->design_file = $designRevision->design_file;
        $designTask->save();

        return redirect()->back()->with('success', 'Revision approved successfully.');
    }

    /**
     * Request revision.
     */
    public function requestRevision(Request $request, DesignTask $designTask, DesignRevision $designRevision)
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

        if (!$isCustomer || $designTask->customer_id !== $user->id) {
            abort(403, 'Only the customer can request revisions.');
        }

        $validated = $request->validate([
            'revision_notes' => 'required|string',
        ]);

        $designRevision->revision_notes = $validated['revision_notes'];
        $designRevision->status = 'revision_requested';
        $designRevision->save();

        // Update task
        $designTask->status = 'revision';
        $designTask->revision_notes = $validated['revision_notes'];
        $designTask->save();

        return redirect()->back()->with('success', 'Revision requested successfully.');
    }

    /**
     * Delete a revision.
     */
    public function destroy(DesignTask $designTask, DesignRevision $designRevision)
    {
        $user = auth()->user();
        $isDesigner = $user->hasRole('designer') || $user->isSuperAdmin();

        if (!$isDesigner || $designRevision->designer_id !== $user->id) {
            abort(403, 'You can only delete your own revisions.');
        }

        if ($designRevision->design_task_id !== $designTask->id) {
            abort(404, 'Revision not found for this task.');
        }

        // Delete file
        if ($designRevision->design_file) {
            Storage::disk('public')->delete($designRevision->design_file);
        }

        $designRevision->delete();

        return redirect()->back()->with('success', 'Revision deleted successfully.');
    }
}

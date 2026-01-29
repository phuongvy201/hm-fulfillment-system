<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DesignTask;
use App\Models\DesignComment;
use Illuminate\Http\Request;

class DesignCommentController extends Controller
{
    /**
     * Store a newly created comment.
     */
    public function store(Request $request, DesignTask $designTask)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();
        $isDesigner = $user->hasRole('designer') || $user->isSuperAdmin();

        // Check permissions
        if ($isCustomer && $designTask->customer_id !== $user->id) {
            abort(403, 'You can only comment on your own tasks.');
        }

        if ($isDesigner && $designTask->designer_id !== $user->id) {
            abort(403, 'You can only comment on tasks assigned to you.');
        }

        $comment = new DesignComment();
        $comment->design_task_id = $designTask->id;
        $comment->user_id = $user->id;
        $comment->content = $validated['content'];
        $comment->type = $isCustomer ? 'customer' : 'designer';
        $comment->is_read = false;
        $comment->save();

        // Mark other user's comments as unread
        if ($isCustomer) {
            // Mark designer's comments as unread
            DesignComment::where('design_task_id', $designTask->id)
                ->where('type', 'designer')
                ->where('user_id', '!=', $user->id)
                ->update(['is_read' => false]);
        } else {
            // Mark customer's comments as unread
            DesignComment::where('design_task_id', $designTask->id)
                ->where('type', 'customer')
                ->where('user_id', '!=', $user->id)
                ->update(['is_read' => false]);
        }

        return redirect()->back()->with('success', 'Comment added successfully.');
    }

    /**
     * Mark comment as read.
     */
    public function markAsRead(DesignTask $designTask, DesignComment $designComment)
    {
        $user = auth()->user();

        if ($designComment->design_task_id !== $designTask->id) {
            abort(404, 'Comment not found for this task.');
        }

        if ($designComment->user_id === $user->id) {
            // Users can't mark their own comments as read
            return redirect()->back();
        }

        $designComment->is_read = true;
        $designComment->save();

        return redirect()->back();
    }

    /**
     * Mark all comments as read.
     */
    public function markAllAsRead(DesignTask $designTask)
    {
        $user = auth()->user();

        DesignComment::where('design_task_id', $designTask->id)
            ->where('user_id', '!=', $user->id)
            ->update(['is_read' => true]);

        return redirect()->back()->with('success', 'All comments marked as read.');
    }

    /**
     * Delete a comment.
     */
    public function destroy(DesignTask $designTask, DesignComment $designComment)
    {
        $user = auth()->user();

        if ($designComment->design_task_id !== $designTask->id) {
            abort(404, 'Comment not found for this task.');
        }

        if ($designComment->user_id !== $user->id) {
            abort(403, 'You can only delete your own comments.');
        }

        $designComment->delete();

        return redirect()->back()->with('success', 'Comment deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'commentable_id' => 'required|integer',
            'commentable_type' => 'required|string',
        ]);

        try {
            // الحصول على قاعدة البيانات من الجلسة
            $database = session('database', 'jo');

            $comment = Comment::create([
                'body' => $validated['body'],
                'user_id' => auth()->id(),
                'commentable_id' => $validated['commentable_id'],
                'commentable_type' => $validated['commentable_type'],
                'database' => $database
            ]);

            return response()->json([
                'message' => 'تم إضافة التعليق بنجاح!',
                'comment' => $comment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'فشل في إضافة التعليق.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

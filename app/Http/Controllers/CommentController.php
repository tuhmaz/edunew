<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\News;
use App\Models\Article;
use Illuminate\Http\Request;

class CommentController extends Controller
{
  public function store(Request $request)
  {
    $request->validate([
      'body' => 'required',
      'commentable_id' => 'required',
      'commentable_type' => 'required',
    ]);

    // استخدام قاعدة البيانات من الجلسة
    $database = session('database', 'jo');

    Comment::create([
      'body' => $request->body,
      'user_id' => auth()->id(),
      'commentable_id' => $request->commentable_id,
      'commentable_type' => $request->commentable_type,
      'database' => $database, // إضافة قاعدة البيانات من الجلسة
    ]);

    return redirect()->back()->with('success', 'تم إضافة التعليق بنجاح!');
  }
}

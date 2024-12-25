<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reaction;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'comment_id' => 'required|exists:comments,id',
            'type' => 'required|string',
        ]);

        // الحصول على قاعدة البيانات من الجلسة
        $database = session('database', 'jo');

        // تحقق مما إذا كان المستخدم قد أضاف تفاعلًا بالفعل على هذا التعليق
        $existingReaction = Reaction::where('user_id', auth()->id())
                                    ->where('comment_id', $request->comment_id)
                                    ->first();

        if ($existingReaction) {
            // إذا كان التفاعل موجودًا بالفعل، حدث نوعه
            $existingReaction->update([
                'type' => $request->type,
                'database' => $database
            ]);
            $message = 'تم تحديث التفاعل بنجاح!';
        } else {
            // إذا لم يكن التفاعل موجودًا، قم بإنشائه
            Reaction::create([
                'user_id' => auth()->id(),
                'comment_id' => $request->comment_id,
                'type' => $request->type,
                'database' => $database
            ]);
            $message = 'تم إضافة التفاعل بنجاح!';
        }

        return response()->json(['message' => $message], 200);
    }
}

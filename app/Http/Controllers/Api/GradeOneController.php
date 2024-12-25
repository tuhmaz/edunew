<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Article;
use App\Models\File;
use App\Models\User;

class GradeOneController extends Controller
{
    public function setDatabase(Request $request)
    {
        $request->validate([
            'database' => 'required|string|in:jo,sa,eg,ps'
        ]);

        $request->session()->put('database', $request->input('database'));

        return response()->json(['message' => 'Database connection set successfully']);
    }

    private function getConnection(Request $request, $defaultDatabase = null)
    {
        // إذا تم تمرير قاعدة البيانات في المسار، استخدمها
        if ($defaultDatabase) {
            return $defaultDatabase;
        }

        // محاولة الحصول على قاعدة البيانات من الجلسة
        try {
            return $request->session()->get('database', 'jo');
        } catch (\Exception $e) {
            // إذا لم تكن هناك جلسة، استخدم القيمة الافتراضية
            return 'jo';
        }
    }

    public function index(Request $request, $database = null)
    {
        $connection = $this->getConnection($request, $database);

        $lesson = SchoolClass::on($connection)->get();
        $classes = SchoolClass::on($connection)->get();

        return response()->json([
            'lesson' => $lesson,
            'classes' => $classes,
            'database' => $connection
        ]);
    }

    public function show(Request $request, $database, $id)
    {
        $database = $this->getConnection($request, $database);
        $class = SchoolClass::on($database)->findOrFail($id);

        return response()->json([
            'class' => $class,
            'database' => $database
        ]);
    }

    public function showSubject(Request $request, $database, $id)
    {
        $database = $this->getConnection($request, $database);

        $subject = Subject::on($database)->findOrFail($id);
        $gradeLevel = $subject->grade_level;
        $semesters = Semester::on($database)->where('grade_level', $gradeLevel)->get();

        return response()->json([
            'subject' => $subject,
            'semesters' => $semesters,
            'database' => $database
        ]);
    }

    public function subjectArticles(Request $request, $database, Subject $subject, Semester $semester, $category)
    {
        $database = $this->getConnection($request, $database);

        // تحديث اتصال النماذج لاستخدام قاعدة البيانات الصحيحة
        $subject->setConnection($database);
        $semester->setConnection($database);

        $articles = Article::on($database)
            ->where('subject_id', $subject->id)
            ->where('semester_id', $semester->id)
            ->whereHas('files', function ($query) use ($category) {
                $query->where('file_category', $category);
            })
            ->with(['files' => function ($query) use ($category) {
                $query->where('file_category', $category);
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // التأكد من تحميل grade_name من القاعدة الفرعية
        $grade_level = $subject->schoolClass->grade_name;

        return response()->json([
            'articles' => $articles,
            'subject' => $subject,
            'semester' => $semester,
            'category' => $category,
            'grade_level' => $grade_level,
            'database' => $database
        ]);
    }

    public function showArticle(Request $request, $database, $id)
    {
        $database = $this->getConnection($request, $database);

        $article = Article::on($database)
            ->with(['subject', 'semester', 'schoolClass', 'keywords', 'files'])
            ->findOrFail($id);

        // جلب أول ملف متعلق بالمقالة لتحديد الفئة
        $file = $article->files->first();
        $category = $file ? $file->file_category : 'articles';

        // الحصول على العلاقات الخاصة بالمقالة
        $subject = $article->subject;
        $semester = $article->semester;

        // التحقق من وجود مستوى الصف بشكل صحيح
        $grade_level = $subject && $subject->schoolClass ? $subject->schoolClass->grade_name : 'N/A';

        // زيادة عدد الزيارات للمقالة
        $article->increment('visit_count');

        // جلب معلومات المؤلف من قاعدة البيانات الرئيسية
        $author = User::on('jo')->find($article->author_id);

        // استبدال الكلمات الدلالية بروابط
        $contentWithKeywords = $this->replaceKeywordsWithLinks($article->content, $article->keywords);
        $article->content = $this->createInternalLinks($article->content, $article->keywords);

        // إرجاع الرد على هيئة JSON يحتوي على جميع البيانات المطلوبة
        return response()->json([
            'article' => $article,
            'subject' => $subject,
            'semester' => $semester,
            'grade_level' => $grade_level,
            'category' => $category,
            'author' => $author,
            'contentWithKeywords' => $contentWithKeywords
        ]);
    }

    private function createInternalLinks($content, $keywords)
    {
        $keywordsArray = $keywords->pluck('keyword')->toArray();

        foreach ($keywordsArray as $keyword) {
            $keyword = trim($keyword);
            $database = session('database', 'jo');
            $url = route('keywords.indexByKeyword', ['database' => $database, 'keywords' => $keyword]);
            $content = str_replace($keyword, '<a href="' . $url . '">' . $keyword . '</a>', $content);
        }

        return $content;
    }

    private function replaceKeywordsWithLinks($content, $keywords)
    {
        foreach ($keywords as $keyword) {
            $keywordText = $keyword->keyword;
            $database = session('database', 'jo');
            $keywordLink = route('keywords.indexByKeyword', ['database' => $database, 'keywords' => $keywordText]);
            $content = preg_replace('/\b' . preg_quote($keywordText, '/') . '\b/', '<a href="' . $keywordLink . '">' . $keywordText . '</a>', $content);
        }

        return $content;
    }

    public function downloadFile(Request $request, $database, $id)
    {
        // استعادة الاتصال المناسب لقاعدة البيانات باستخدام الدالة المساعدة
        $connection = $this->getConnection($request, $database);

        // جلب الملف من قاعدة البيانات المحددة
        $file = File::on($connection)->findOrFail($id);

        // زيادة عدد مرات التحميل للملف
        $file->increment('download_count');

        // تكوين المسار الكامل للملف في التخزين العام
        $filePath = storage_path('app/public/' . $file->file_path);

        // التحقق من وجود الملف في التخزين
        if (file_exists($filePath)) {
            return response()->download($filePath, $file->file_Name);
        }

        // إرجاع رسالة خطأ إذا لم يتم العثور على الملف
        return response()->json(['error' => 'File not found'], 404);
    }
}

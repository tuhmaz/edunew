<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Article;
use App\Models\Subject;
use App\Models\Semester;
use App\Models\SchoolClass;
use App\Models\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FileController extends Controller
{
  private function getConnection(string $country): string
  {
    return match ($country) {
      'saudi' => 'sa',
      'egypt' => 'eg',
      'palestine' => 'ps',
      default => 'jo',
    };
  }

  public function index(Request $request)
  {
    $country = $request->input('country', 'jordan');
    $connection = $this->getConnection($country);


    $files = File::on($connection)->with('article')->get();

    return view('dashboard.files.index', compact('files', 'country'));
  }

  public function create(Request $request)
  {
    $country = $request->input('country', 'jordan');
    $connection = $this->getConnection($country);


    $articles = Article::on($connection)->get();

    return view('dashboard.files.create', compact('articles', 'country'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'article_id' => 'required|exists:articles,id',
      'file' => 'required|file',
      'file_category' => 'required|string',
    ]);

    $country = $request->input('country', 'jordan');
    $connection = $this->getConnection($country);

    $article = Article::on($connection)->findOrFail($request->article_id);
    $class_name = $article->schoolClass->grade_name;

    if ($request->hasFile('file')) {
      $file = $request->file('file');
      $filename = $file->getClientOriginalName();
      
      // تحديد المسار الصحيح للحفظ
      $storagePath = public_path('storage/files/' . Str::slug($country) . '/' . Str::slug($class_name) . '/' . $request->file_category);
      
      // إنشاء المجلد إذا لم يكن موجوداً
      if (!file_exists($storagePath)) {
          mkdir($storagePath, 0777, true);
      }
      
      // حفظ الملف في المسار المحدد
      $file->move($storagePath, $filename);
      
      // تخزين المسار النسبي في قاعدة البيانات
      $relativePath = 'storage/files/' . Str::slug($country) . '/' . Str::slug($class_name) . '/' . $request->file_category . '/' . $filename;

      File::on($connection)->create([
        'article_id' => $request->article_id,
        'file_path' => $relativePath,
        'file_type' => $file->getClientOriginalExtension(),
        'file_category' => $request->file_category,
        'file_Name' => $filename,
      ]);

      return redirect()->route('files.index', ['country' => $country])->with('success', 'File uploaded successfully.');
    }

    return redirect()->back()->with('error', 'No file was uploaded.');
  }

  public function show(Request $request, $id)
  {
    $country = $request->input('country', 'jordan');
    $connection = $this->getConnection($country);

    $file = File::on($connection)->findOrFail($id);

    return view('dashboard.files.show', compact('file', 'country'));
  }

  public function edit(Request $request, $id)
  {
    $country = $request->input('country', 'jordan');
    $connection = $this->getConnection($country);

    $file = File::on($connection)->findOrFail($id);
    $articles = Article::on($connection)->get();

    return view('dashboard.files.edit', compact('file', 'articles', 'country'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'article_id' => 'required|exists:articles,id',
      'file' => 'nullable|file',
      'file_category' => 'required|string'
    ]);

    $country = $request->input('country', 'jordan');
    $connection = $this->getConnection($country);

    $file = File::on($connection)->findOrFail($id);

    if ($request->hasFile('file')) {
      if (Storage::disk('public')->exists($file->file_path)) {
        Storage::disk('public')->delete($file->file_path);
      }

      $article = Article::on($connection)->findOrFail($request->article_id);
      $class_name = $article->schoolClass->grade_name;
      $uploadedFile = $request->file('file');
      $filename = time() . '_' . $uploadedFile->getClientOriginalName();
      $directory = 'files/' . Str::slug($country) . '/' . Str::slug($class_name) . '/' . $request->file_category;
      $path = $uploadedFile->storeAs($directory, $filename, 'public');

      $file->update([
        'article_id' => $request->article_id,
        'file_path' => $path,
        'file_type' => $uploadedFile->getClientOriginalExtension(),
        'file_category' => $request->file_category,
        'file_Name' => $filename,
      ]);
    }

    if ($request->article_id !== $file->article_id) {
      $file->update(['article_id' => $request->article_id]);
    }

    return redirect()->route('files.index', ['country' => $country])->with('success', 'File updated successfully.');
  }

  public function destroy(Request $request, $id)
  {
    $country = $request->input('country', 'jordan');
    $connection = $this->getConnection($country);

    $file = File::on($connection)->findOrFail($id);

    try {
      if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
        Storage::disk('public')->delete($file->file_path);
      }

      $file->delete();

      return redirect()->route('files.index', ['country' => $country])->with('success', 'File deleted successfully.');
    } catch (\Exception $e) {
      Log::error('Error deleting file: ' . $e->getMessage());
      return redirect()->route('files.index', ['country' => $country])->with('error', 'Error deleting file.');
    }
  }



  public function showFilterPage()
  {
    $classes = SchoolClass::all();
    $semesters = Semester::all();
    $subjects = Subject::all();

    return view('frontend.filter-files', compact('classes', 'semesters', 'subjects'));
  }

  public function filter(Request $request)
  {
    $query = File::query();

    if ($request->has('class_id') && $request->class_id) {
      $query->whereHas('article.subject.schoolClass', function ($q) use ($request) {
        $q->where('id', $request->class_id);
      });
    }

    if ($request->has('semester_id') && $request->semester_id) {
      $query->whereHas('article', function ($q) use ($request) {
        $q->where('semester_id', $request->semester_id);
      });
    }

    if ($request->has('subject_id') && $request->subject_id) {
      $query->whereHas('article', function ($q) use ($request) {
        $q->where('subject_id', $request->subject_id);
      });
    }

    if ($request->has('file_category') && $request->file_category) {
      $query->where('file_type', $request->file_category);
    }

    $files = $query->get();
    if ($files->isEmpty()) {
      return redirect()->back()->with('error', 'No files found matching the selected criteria.');
    }

    $classes = SchoolClass::all();
    $semesters = Semester::all();
    $subjects = Subject::all();

    return view('frontend.filter-files', compact('files', 'classes', 'semesters', 'subjects'));
  }


  public function downloadFile(Request $request, $id)
  {
    try {
      $database = $request->query('database', session('database', 'jo'));
      
      if (!$database || !config('database.connections.' . $database)) {
        return redirect()->back()->with('error', 'Invalid database configuration.');
      }

      $file = File::on($database)->findOrFail($id);
      
      if (!$file) {
        return redirect()->back()->with('error', 'File not found.');
      }

      $filePath = storage_path('app/public/' . $file->file_path);
      
      if (!file_exists($filePath)) {
        return redirect()->back()->with('error', 'File does not exist on the server.');
      }

      $file->increment('download_count');

      return response()->download($filePath, $file->file_Name);
    } catch (\Exception $e) {
      \Log::error('File download error: ' . $e->getMessage());
      return redirect()->back()->with('error', 'An error occurred while downloading the file.');
    }
  }

  public function showDownloadPage($fileId)
  {
    try {
      $database = session('database', 'jo');
      
      if (!config('database.connections.' . $database)) {
        abort(500, 'Invalid database configuration.');
      }

      $file = File::on($database)->findOrFail($fileId);
      
      if (!$file) {
        abort(404, 'File not found.');
      }

      $filePath = storage_path('app/public/' . $file->file_path);
      
      if (!file_exists($filePath)) {
        abort(404, 'File does not exist on the server.');
      }

      $pageTitle = 'تحميل الملف: ' . $file->file_Name;

      return view('frontend.download.download-page', compact('file', 'pageTitle'));
    } catch (\Exception $e) {
      \Log::error('Show download page error: ' . $e->getMessage());
      abort(500, 'An error occurred while processing your request.');
    }
  }

  public function processDownload($fileId)
  {
    try {
      $database = session('database', 'jo');
      
      if (!config('database.connections.' . $database)) {
        abort(500, 'Invalid database configuration.');
      }

      $file = File::on($database)->findOrFail($fileId);
      
      if (!$file) {
        abort(404, 'File not found.');
      }

      $filePath = storage_path('app/public/' . $file->file_path);
      
      if (!file_exists($filePath)) {
        abort(404, 'File does not exist on the server.');
      }

      $file->increment('download_count');

      return response()->download($filePath, $file->file_Name);
    } catch (\Exception $e) {
      \Log::error('Process download error: ' . $e->getMessage());
      abort(500, 'An error occurred while processing your download.');
    }
  }
}

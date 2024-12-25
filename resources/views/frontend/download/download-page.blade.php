@php
    $configData = Helper::appClasses();
    use Detection\MobileDetect;
    $detect = new MobileDetect;
@endphp

@extends('layouts.layoutFront')

@section('page-style')
@vite(['resources/assets/vendor/js/waitdon.js'])
@endsection

@section('title', $file->file_Name)

@section('content')
<section class="section-py first-section-pt help-center-header position-relative overflow-hidden" style="background-color: rgb(32, 44, 69); padding-bottom: 20px;">
  <h2 class="text-center text-white fw-semibold">{{ $file->file_Name }}</h2>
  <p class="text-center text-white mb-0">{{ __('File Download') }}</p>
</section>

<div class="container px-4 mt-4">
  <ol class="breadcrumb breadcrumb-style2" aria-label="breadcrumbs">
    <li class="breadcrumb-item">
      <a href="{{ route('home') }}">
        <i class="ti ti-home-check"></i> {{ __('Home') }}
      </a>
    </li>
    <li class="breadcrumb-item">
      <a href="{{ url()->previous() }}">
        <i class="ti ti-article"></i> {{ __('Article') }}
      </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ $file->file_Name }}</li>
  </ol>
</div>

<section class="section-py bg-body first-section-pt" style="padding-top: 10px;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10">
        <div class="card">
          <div class="card-body">
            <div class="text-center mb-4">
              @php
              $fileType = strtolower($file->file_type);
              $imagePath = match ($fileType) {
                  'pdf' => asset('assets/img/front-pages/icons/pdf.png'),
                  'doc', 'docx' => asset('assets/img/front-pages/icons/word.png'),
                  'xls', 'xlsx' => asset('assets/img/front-pages/icons/excel.png'),
                  'ppt', 'pptx' => asset('assets/img/front-pages/icons/powerpoint.png'),
                  'zip', 'rar' => asset('assets/img/front-pages/icons/archive.png'),
                  'jpg', 'jpeg', 'png', 'gif' => asset('assets/img/front-pages/icons/image.png'),
                  default => asset('assets/img/front-pages/icons/file.png'),
              };
              @endphp
              <img src="{{ $imagePath }}" alt="{{ $file->file_type }}" class="mb-3" style="width: 96px; height: 96px;">
              <h3>{{ $file->file_Name }}</h3>
              <p class="text-muted">
                نوع الملف: {{ strtoupper($file->file_type) }}
                @if($file->download_count > 0)
                <br>
                عدد مرات التحميل: {{ number_format($file->download_count) }}
                @endif
              </p>
            </div>

            <div class="download-timer text-center">
              <div class="progress mb-3" style="height: 10px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                     id="downloadProgress" 
                     role="progressbar" 
                     style="width: 0%" 
                     aria-valuenow="0" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                </div>
              </div>
              
              <p class="mb-3">{{ __('Your download will begin in') }} <span id="countdown" class="fw-bold text-primary">15</span> {{ __('seconds') }}</p>
              
              <div id="downloadButton" style="display: none;">
                <a href="{{ route('download.process', $file->id) }}" 
                   class="btn btn-primary btn-lg">
                    <i class="ti ti-download me-2"></i>{{ __('Download Now') }}
                </a>
              </div>
            </div>

            <hr class="my-4">

            <div class="share-section text-center">
              <h4 class="mb-3">{{ __('Share this file') }}</h4>
              <div class="d-flex justify-content-center gap-2">
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" 
                   target="_blank" 
                   class="btn btn-icon btn-facebook">
                    <i class="ti ti-brand-facebook"></i>
                </a>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}" 
                   target="_blank" 
                   class="btn btn-icon btn-twitter">
                    <i class="ti ti-brand-twitter"></i>
                </a>
                <a href="https://api.whatsapp.com/send?text={{ urlencode($file->file_Name . ' - ' . request()->fullUrl()) }}" 
                   target="_blank" 
                   class="btn btn-icon btn-whatsapp">
                    <i class="ti ti-brand-whatsapp"></i>
                </a>
              </div>
            </div>
          </div>
        </div>

        @if(config('settings.google_ads_desktop_download') || config('settings.google_ads_mobile_download'))
        <div class="card mt-4">
          <div class="card-body">
            <div class="ads-container text-center">
              @if($detect->isMobile())
              {!! config('settings.google_ads_mobile_download') !!}
              @else
              {!! config('settings.google_ads_desktop_download') !!}
              @endif
            </div>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let timeLeft = 15;
    const countdownEl = document.getElementById('countdown');
    const downloadButton = document.getElementById('downloadButton');
    const progressBar = document.getElementById('downloadProgress');
    
    const timer = setInterval(() => {
        timeLeft--;
        countdownEl.textContent = timeLeft;
        
        // تحديث شريط التقدم
        const progress = ((15 - timeLeft) / 15) * 100;
        progressBar.style.width = progress + '%';
        progressBar.setAttribute('aria-valuenow', progress);
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            countdownEl.parentElement.style.display = 'none';
            downloadButton.style.display = 'block';
            progressBar.style.width = '100%';
        }
    }, 1000);
});
</script>
@endpush
@endsection

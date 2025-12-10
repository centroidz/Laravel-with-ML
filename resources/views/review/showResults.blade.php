<!DOCTYPE html>
<html>
<head>
    <title>Saved Review #{{ $reviewId }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <div class="container py-5">
        <h1 class="mb-4 text-center">
            <i class="fas fa-bookmark text-warning"></i> Viewing Saved Review #{{ $reviewId }}
        </h1>

        @if(session('success'))
            <div class="text-center alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="text-center alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-4 d-flex justify-content-between align-items-center">
            <a href="{{ route('review.form') }}" class="btn btn-secondary">← Back to Input</a>

            <div>
                <button type="button" class="btn btn-success me-2" disabled>
                    <i class="fas fa-check-circle"></i> Review Saved
                </button>

                @if(isset($is_show_view) && $is_show_view && isset($reviewId))
                    <form action="{{ route('review.delete', $reviewId) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-lg-12">
                {{-- Reviewer Summary Card --}}
                <div class="p-4 mb-5 shadow-sm card">
                    
                    {{-- Header with Audio Controls --}}
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <h2 class="card-title text-primary mb-0">
                            <i class="fas fa-file-alt"></i> Extracted Reviewer Summary
                        </h2>

                        <div class="mt-2 mt-md-0">
                            @if(!empty($audio_path) && file_exists(public_path($audio_path)))
                                {{-- 1. Database has path AND file exists on disk: Show Player --}}
                                <div class="d-flex align-items-center bg-light rounded p-2 border">
                                    <span class="badge bg-primary me-2">MP3 Ready</span>
                                    <audio controls style="height: 30px; max-width: 250px;">
                                        {{-- Use asset() to generate correct URL from relative DB path --}}
                                        <source src="{{ asset($audio_path) }}" type="audio/mpeg">
                                        Your browser does not support the audio element.
                                    </audio>
                                    
                                    {{-- Regenerate Button --}}
                                    <form action="{{ route('review.audio', $reviewId) }}" method="POST" class="d-inline ms-2" onsubmit="activateSpinner(this)">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-link text-secondary" title="Regenerate Audio">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                {{-- 2. No path in DB: Show Generate Button --}}
                                <form action="{{ route('review.audio', $reviewId) }}" method="POST" id="audio-form" onsubmit="activateSpinner()">
                                    @csrf
                                    <button type="submit" class="btn btn-primary" id="generate-btn">
                                        <span class="normal-state">
                                            <i class="fas fa-headphones"></i> Generate Audio
                                        </span>
                                        <span class="loading-state d-none">
                                            <i class="fas fa-spinner fa-spin"></i> Generating...
                                        </span>
                                    </button>
                                </form>
                            @endif
                        </div>
                        {{-- AUDIO LOGIC END --}}
                    </div>
                    
                    <hr>
                    <p class="card-text lead">{{ $summary }}</p>
                    <p class="mt-2 text-muted small">Generated from approximately **{{ $original_text_length }}** characters.</p>
                </div>
            </div>
        </div>

        {{-- Questions Section (Existing code) --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="p-4 shadow-sm card">
                    <h2 class="card-title text-success"><i class="fas fa-question-circle"></i> Generated Questions</h2>
                    <hr>
                    @if(is_array($questions) && count($questions) > 0)
                        <ol>
                            @foreach($questions as $q)
                                <li class="mb-3">
                                    <p class="mb-0 fw-bold">{{ $q['question'] ?? 'Question key not found' }}</p>
                                    <p class="text-muted small">
                                        <strong>Simplified Answer:</strong>
                                        <span class="badge bg-success">{{ $q['answer'] ?? 'N/A' }}</span>
                                    </p>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <p class="text-muted">No questions were generated from this summary.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Javascript for Spinner --}}
    <script>
        function activateSpinner() {
            const btn = document.getElementById('generate-btn');
            // If the button exists (it won't exist if audio is already generated)
            if(btn) {
                const normalState = btn.querySelector('.normal-state');
                const loadingState = btn.querySelector('.loading-state');

                // Disable button to prevent double clicks
                btn.disabled = true;
                
                // Toggle visibility
                normalState.classList.add('d-none');
                loadingState.classList.remove('d-none');
            }
        }
    </script>

</body>
</html>
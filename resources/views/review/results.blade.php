<!DOCTYPE html>
<html>
<head>
    <title>Reviewer Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <div class="container py-5">
        <h1 class="mb-4 text-center">✅ Reviewer Results</h1>

        {{-- Display Success Message if saved --}}
        @if(session('success'))
            <div class="text-center alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4 d-flex justify-content-between">
            <a href="{{ route('review.form') }}" class="btn btn-secondary">← Back to Input</a>

            {{-- Conditional Save/Saved Button --}}
            @auth
                @if(isset($saved))
                    {{-- After successful saving --}}
                    <button type="button" class="btn btn-success" disabled>
                        <i class="fas fa-check-circle"></i> Review Saved (#{{ $reviewId }})
                    </button>

                @else
                    {{-- First time generation (Save Form) --}}
                    <form action="{{ route('review.save') }}" method="POST">
                        @csrf

                        {{-- Hidden fields to pass summary + questions --}}
                        <input type="hidden" name="reviewer" value="{{ $reviewer }}">
                        <input type="hidden" name="original_text_length" value="{{ $original_text_length }}">
                        <input type="hidden" name="questions" value="{{ json_encode($questions) }}">

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Review
                        </button>
                    </form>
                @endif
            @else
                <a href="/login" class="btn btn-primary disabled" aria-disabled="true">Log in to Save</a>
            @endauth
        </div>

        {{-- Reviewer Summary --}}
        <div class="p-4 mb-5 shadow card">
            <h2 class="card-title text-primary">Extracted Reviewer Summary</h2>
            <hr>
            <p class="card-text lead">{{ $reviewer }}</p>
            <p class="mt-2 text-muted small">Generated from {{ $original_text_length }} characters.</p>
        </div>

        {{-- Questions Section --}}
        <div class="p-4 shadow card">
            <h2 class="card-title text-success">Generated Questions</h2>
            <hr>

            @if(count($questions) > 0)
                <ol>
                    @foreach($questions as $index => $q)
                        <li class="mb-3">
                            <p class="mb-0 fw-bold">{{ $q['question'] ?? 'No question available' }}</p>

                            <p class="text-muted small">
                                <strong>Simplified Answer:</strong>
                                <span class="badge bg-success">
                                    {{ $q['answer'] ?? 'N/A' }}
                                </span>
                            </p>
                        </li>
                    @endforeach
                </ol>
            @else
                <p>No questions were generated from the extracted summary.</p>
            @endif
        </div>
    </div>

</body>
</html>

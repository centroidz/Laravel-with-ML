<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Reviewer Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* (*** All previous CSS styles remain here ***) */

        :root {
            --sidebar-width: 280px;
            --collapsed-width: 60px;
            --header-vertical-padding: 1rem;
            --header-height: 58px;
            --main-padding: 2rem;
            --sidebar-bg: #f8f9fa;
            --main-bg: #fff;
        }

        body {
            height: 100vh;
            margin: 0;
            display: flex;
            background-color: var(--main-bg);
            overflow: hidden;
        }

        .sidebar-container {
            /* ... (Sidebar styles) ... */
            flex-shrink: 0;
            height: 100%;
            display: flex;
            position: relative;
            background-color: var(--sidebar-bg);
            border-right: 1px solid #dee2e6;
            width: var(--sidebar-width);
            transition: width 0.3s ease-in-out;
            overflow: hidden;
        }

        .sidebar-container.collapsed {
            width: var(--collapsed-width);
        }

        .sidebar-toggle-area {
            position: absolute;
            top: 1rem;
            left: 0;
            width: var(--collapsed-width);
            height: auto;
            padding: 0 0.5rem;
        }

        .sidebar-content {
            flex-grow: 1;
            padding: var(--header-vertical-padding);
            padding-left: var(--collapsed-width);
            height: 100%;
            overflow-y: auto;
        }

        .sidebar-container.collapsed .sidebar-content {
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease-in-out;
        }

        .main-content {
            flex-grow: 1;
            padding: var(--header-vertical-padding) var(--main-padding);
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow-y: hidden;
        }

        .header-content {
            padding-bottom: var(--header-vertical-padding);
            padding-top: 0;
            flex-shrink: 0;
        }

        .form-wrapper {
            height: 100%;
            overflow-y: hidden;
            display: flex;
            flex-direction: column;
        }

        .form-scroll-area {
            height: calc(100% - 1.5rem);
            overflow-y: auto;
            padding-top: 0.5rem;
        }

        /* General Styling adjustments */

        .toggle-btn {
            background: none;
            border: none;
            padding: 0.5rem;
            margin-right: 0;
            border-radius: 50%;
            transition: background-color 0.2s;
            display: block;
            width: 100%;
            text-align: center;
        }

        .toggle-btn:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .profile-icon-btn {
            background-color: #e9ecef;
            color: #495057;
            border-radius: 50%;
            padding: 0.5rem;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
            cursor: pointer; /* Ensure it looks clickable */
        }

        .profile-icon-btn:hover {
            background-color: #dee2e6;
        }

        .new-reviewer-btn {
            background-color: #d1e7ff;
            color: #0d6efd;
            border: none;
            text-align: left;
            padding: 0.75rem 1rem;
            border-radius: 1.5rem;
            display: flex;
            align-items: center;
            width: 100%;
            margin-bottom: 1.5rem;
        }

        .new-reviewer-btn:hover {
            background-color: #c0d8f0;
        }

        .recent-item {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: background-color 0.2s;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            display: flex; /* Ensures alignment */
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
        }

        .recent-item .text-truncate {
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            flex-grow: 1; /* Allow it to take up available space */
        }

        .recent-item:hover {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>

<div class="sidebar-container" id="sidebarContainer">
    <div class="sidebar-toggle-area">
        <button class="toggle-btn" id="sidebarToggle" aria-label="Toggle Sidebar">
            <i class="bi bi-list fs-4"></i>
        </button>
    </div>
    <div class="sidebar-content">
        <a href="{{ route('review.form') }}" class="btn new-reviewer-btn fw-bold">
            <i class="bi bi-plus-lg me-2"></i> New Reviewer
        </a>
        <hr>
        <h6 class="text-secondary mb-3">Recent Reviewers</h6>
        <div class="list-group">
            @auth
                @forelse ($recentReviews as $review)
                    {{-- Updated list item for a cleaner chat/convo style --}}
                    <a href="{{-- route('review.show', $review->id) --}}#"
                       class="list-group-item list-group-item-action py-2 recent-item d-flex align-items-center">

                        {{-- Icon for visual flair --}}
                        <i class="bi bi-file-text me-2 text-primary"></i>

                        <div class="flex-grow-1 overflow-hidden">
                            {{-- Truncate the summary to create a clear, single-line title --}}
                            <p class="m-0 text-truncate fw-normal" style="font-size: 0.95rem;">
                                {{ Str::limit($review->summary, 40, '...') }}
                            </p>
                        </div>

                        {{-- Show the date in a subtle, less-detailed way --}}
                        <small class="text-muted ms-2 flex-shrink-0" style="font-size: 0.75rem;">
                            {{ $review->created_at->format('M j') }}
                        </small>
                    </a>
                @empty
                    {{-- This block runs if the collection is empty --}}
                    <p class="text-muted small px-3">No recent reviews yet...</p>
                @endforelse
            @else
                {{-- Content for unauthenticated users --}}
                <p class="text-muted small px-3">Log in to save and manage...</p>
            @endauth
        </div>
    </div>
</div>

    <div class="main-content" id="mainContent">
        <div class="container-fluid h-100 p-0">

            <header class="header-content d-flex justify-content-between align-items-center">
                <h2 class="fw-bold m-0 text-dark">reReview</h2>

                <div class="profile-icon-btn" data-bs-toggle="modal" data-bs-target="#authModal">
                    @auth
                        <i class="bi bi-person-check-fill fs-5 text-primary"></i>
                    @else
                        <i class="bi bi-person-circle fs-5"></i>
                    @endauth
                </div>
            </header>

            <hr class="mt-0 mb-3">

            <div class="form-wrapper">
                <div class="form-scroll-area">
                    <div class="card p-4 shadow-lg border-0 mx-auto" style="max-width: 800px;">
                        <h1 class="display-6 fw-light text-center mb-4">🧠 AI Reviewer Tool</h1>
                        <p class="text-secondary text-center mb-4">Generate key summaries and questions from your text.</p>

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                Please ensure the text is at least 50 characters long.
                            </div>
                        @endif

                        <form action="{{ route('review.generate') }}" method="POST">
                            @csrf

                            <div class="mb-4">
                                <label for="input_text" class="form-label fw-bold">Paste Text for Reviewer Generation:</label>
                                <textarea class="form-control" name="input_text" id="input_text" rows="8" placeholder="Paste your study notes, article, or document here (Min 50 chars)">{{ old('input_text') }}</textarea>
                            </div>

                            <div class="mb-4">
                                <label for="sentence_count" class="form-label fw-bold">Number of Sentences for Reviewer:</label>
                                <input type="number" class="form-control" name="sentence_count" id="sentence_count" value="{{ old('sentence_count', 5) }}" min="1" max="20">
                                <small class="form-text text-muted">This sets the length of the generated summary.</small>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Generate Reviewer & Questions</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="authModalLabel">Access Your Reviews</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    @auth
                        <h6 class="fw-bold mb-3">Hello, {{ Auth::user()->name }}!</h6>
                        <p class="text-secondary small">You are logged in. View your profile or log out.</p>

                        <div class="d-grid gap-2">
                            <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">View Profile</a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary w-100 mt-2">Log Out</button>
                            </form>
                        </div>
                    @else
                        <div class="d-grid gap-2 mb-3">
                            <a href="{{ route('login') }}" class="btn btn-primary py-2 fw-bold">
                                Sign In (Email/Password)
                            </a>
                        </div>

                        <div class="text-center text-secondary small mb-3">
                            OR
                        </div>

                        <div class="d-grid gap-2 mb-4">
                            <a href="{{ route('socialite.redirect', 'google') }}" class="btn btn-outline-danger">
                                <i class="bi bi-google me-2"></i> Continue with Google
                            </a>
                        </div>

                        <p class="text-center small m-0">
                            Don't have an account? <a href="{{ route('register') }}" class="fw-bold text-decoration-none">Create Account</a>
                        </p>
                    @endauth
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebarContainer = document.getElementById('sidebarContainer');
            sidebarContainer.classList.toggle('collapsed');
        });
    </script>
</body>
</html>

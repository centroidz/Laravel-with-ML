<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\ReviewController;

// 1. PUBLIC ROUTE: The main application page, calls showForm and passes $recentReviews
Route::get('/', [ReviewController::class, 'showForm'])->name('review.form');

// 2. SOCIALITE ROUTES (No middleware needed)
Route::prefix('auth')->group(function () {
    Route::get('{provider}', [SocialiteController::class, 'redirectToProvider'])->name('socialite.redirect');
    Route::get('{provider}/callback', [SocialiteController::class, 'handleProviderCallback']);
});

// 3. AUTHENTICATED USER ROUTES
Route::middleware('auth')->group(function () {

    // Route for submitting the form, generating the review, and showing the results (DOES NOT SAVE YET)
    Route::post('/generate', [ReviewController::class, 'generateReview'])->name('review.generate');

    // 👇 NEW: Route for saving the generated data (triggered by the 'Save' button)
    Route::post('/review/save', [ReviewController::class, 'saveReview'])->name('review.save');

    // Display a saved review (Requires a showReview method in ReviewController)
    // Route::get('/review/{review}', [ReviewController::class, 'showReview'])->name('review.show');
    // NOTE: Add the delete route too once you implement showReview

    // Dashboard route (can redirect back to the form page)
    Route::get('/dashboard', function () {
        return redirect()->route('review.form');
    })->name('dashboard');

    // Breeze Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// 4. BREEZE AUTHENTICATION ROUTES (login, register, logout, etc.)
require __DIR__.'/auth.php';

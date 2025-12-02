<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // 1. Add Auth Facade
use App\ReviewerGenerator;
use App\Models\Reviewer; // 2. Add the Reviewer Model (assuming it's in App\Models)
use Illuminate\Support\Str;
class ReviewController extends Controller
{

    // Use middleware to protect all methods except showForm (optional but recommended)
    public function __construct()
    {

    }

    public function showForm()
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            // Fetch the 5 most recent reviews for the logged-in user
            $recentReviews = Auth::user()
                ->reviewers() // Use the relationship defined in your User Model (see model section below)
                ->latest()
                ->limit(5)
                ->get();
        } else {
            // If not logged in, pass an empty collection or null
            $recentReviews = collect();
        }

        return view('review.form', [
            'recentReviews' => $recentReviews
        ]);
    }

    public function generateReview(Request $request, ReviewerGenerator $generator)
    {
        // Ensure only authenticated users can save data
        if (!Auth::check()) {
            // Handle unauthenticated user attempt, e.g., redirect to login
            return redirect('/login')->with('error', 'Please log in to save your reviews.');
        }

        // 1. Validate the input
        $request->validate([
            'input_text' => 'required|string|min:50',
            'sentence_count' => 'required|integer|min:1|max:20'
        ]);

        $inputText = $request->input('input_text');
        $count = $request->input('sentence_count');

        // 2. Call the generator service for Extractive Summarization
        $reviewerSentences = $generator->generateReviewer($inputText, $count);
        $reviewerText = implode(" ", $reviewerSentences); // Summary

        // 3. Call the generator service for Question Creation
        $questions = $generator->generateQuestions($reviewerSentences);
        $questionsText = json_encode($questions); // Assuming questions is an array, save as JSON string

        // 4. Save the generated review to the database
        $review = Auth::user()->reviewers()->create([
            'summary' => $reviewerText,
            'questions' => $questionsText, // Save the questions array as a JSON string
        ]);

        // 5. Return results to a view
        return view('review.results', [
            'reviewId' => $review->id, // Pass the new review ID
            'reviewer' => $reviewerText,
            'questions' => $questions,
            'original_text_length' => strlen($inputText),
        ]);
    }


}

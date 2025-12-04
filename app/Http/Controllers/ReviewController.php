<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session; // 👈 NEW: Use Session Facade
use App\ReviewerGenerator;
use App\Models\Reviewer;
use Illuminate\Support\Str;

use App\TrainableSummarizer;

use Illuminate\Support\Facades\Http;

class ReviewController extends Controller
{

    // Use middleware to protect all methods except showForm (optional but recommended)
    public function __construct()
    {
        // Add middleware here if needed, but it's already in web.php
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

    /**
     * Generates the review and questions, but STORES data in the session.
     * The user must click a separate button to persist it to the database.
     */

    public function generateReview(Request $request, ReviewerGenerator $generator)
{
    // Ensure only authenticated users can save data
    if (!Auth::check()) {
        return redirect('/login')->with('error', 'Please log in to generate reviews.');
    }

    // 1. Validate the input
    $request->validate([
        'input_text' => 'required|string|min:50'
    ]);

    $inputText = $request->input('input_text');

    // -----------------------------
    // 2. CALL YOUR PYTHON AI MODEL
    // -----------------------------
    try {
        $response = Http::post('http://127.0.0.1:8000/summarize', [
            'text' => $inputText
        ]);

        if (!$response->successful()) {
            return back()->with('error', 'AI Summarizer API returned an error.');
        }

        $reviewerText = $response->json()['summary'];

    } catch (\Exception $e) {
        return back()->with('error', 'Failed to connect to AI Summarizer API: '.$e->getMessage());
    }

    // -----------------------------------------------------
    // 3. Create questions (you can adjust based on summary)
    // -----------------------------------------------------

    $reviewerSentences = preg_split('/(?<=[.?!])\s+/', $reviewerText, -1, PREG_SPLIT_NO_EMPTY);
    $questions = $generator->generateQuestions($reviewerSentences);

    // 4. Store generated data in the session for later saving
    Session::put('generated_review_data', [
        'summary' => $reviewerText,
        'questions' => $questions,
        'original_text_length' => strlen($inputText),
    ]);

    // 5. Return results to the view
    return view('review.results', [
        'reviewer' => $reviewerText,
        'questions' => $questions,
        'original_text_length' => strlen($inputText),
        'reviewId' => null,
    ]);
}

    public function generateReview2(Request $request, ReviewerGenerator $generator)
    {
        // Ensure only authenticated users can save data
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Please log in to generate reviews.');
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
        // Note: Questions array is needed for the view, and for saving

        // 4. Store generated data in the session for later saving
        Session::put('generated_review_data', [
            'summary' => $reviewerText,
            'questions' => $questions, // Store as an array, not JSON string yet
            'original_text_length' => strlen($inputText),
        ]);

        // 5. Return results to a view (No database interaction yet)
        return view('review.results', [
            'reviewer' => $reviewerText,
            'questions' => $questions,
            'original_text_length' => strlen($inputText),
            'reviewId' => null, // No ID yet since it's not saved
        ]);
    }

//     public function generateReview(Request $request, ReviewerGenerator $generator)
// {
//     // 1. Ensure only authenticated users can save data
//     if (!Auth::check()) {
//         return redirect('/login')->with('error', 'Please log in to generate reviews.');
//     }

//     // 2. Validate the input
//     $request->validate([
//         'input_text' => 'required|string|min:50',
//         'sentence_count' => 'required|integer|min:1|max:20'
//     ]);

//     $inputText = $request->input('input_text');
//     $count = $request->input('sentence_count');

//     // 3. TRAINING DATA (temporary hard-coded)
//     //    You can move this to database, JSON, or config.
//     $trainingData = [
//         ['sentence' => "Multimedia improves learning.", 'label' => 'important'],
//         ['sentence' => "Students learn faster using videos and images.", 'label' => 'important'],
//         ['sentence' => "Technology helps people work faster.", 'label' => 'important'],

//         ['sentence' => "I like drinking coffee.", 'label' => 'not_important'],
//         ['sentence' => "The sky is blue today.", 'label' => 'not_important'],
//         ['sentence' => "I went to the store yesterday.", 'label' => 'not_important'],
//     ];

//     // 4. Create summarizer instance
//     $summarizer = new TrainableSummarizer($trainingData);

//     // 5. Generate summary using your new model
//     $reviewerSentences = $summarizer->summarize($inputText);

//     // If summary is too long, trim to requested count
//     if (count($reviewerSentences) > $count) {
//         $reviewerSentences = array_slice($reviewerSentences, 0, $count);
//     }

//     $reviewerText = implode(" ", $reviewerSentences);

//     // 6. Generate questions based on summary (your old function)
//     $questions = $generator->generateQuestions($reviewerSentences);

//     // 7. Save to session
//     Session::put('generated_review_data', [
//         'summary' => $reviewerText,
//         'questions' => $questions,
//         'original_text_length' => strlen($inputText),
//     ]);

//     // 8. Return results to a view
//     return view('review.results', [
//         'reviewer' => $reviewerText,
//         'questions' => $questions,
//         'original_text_length' => strlen($inputText),
//         'reviewId' => null,
//     ]);
// }

    /**
     * NEW: Retrieves data from the session and saves it to the database.
     */
    public function saveReview(Request $request)
    {
        // 1. Check if the user is authenticated
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'You must be logged in to save reviews.');
        }

        // 2. Retrieve the generated data from the session
        $data = Session::get('generated_review_data');

        if (!$data) {
            // No data in session, redirect back with an error
            return redirect()->route('review.form')->with('error', 'No review data found to save. Please generate a new review.');
        }

        // 3. Prepare data for the database
        $reviewerText = $data['summary'];
        // Convert the questions array to a JSON string for the database
        $questionsJson = json_encode($data['questions']);

        // 4. Save the generated review to the database
        $review = Auth::user()->reviewers()->create([
            'summary' => $reviewerText,
            'questions' => $questionsJson, // Save the questions array as a JSON string
        ]);

        // 5. Clean up the session data
        Session::forget('generated_review_data');

        // 6. Redirect to the results page, showing the now-saved review
        // You can redirect to a 'review.show' route here if you implement it,
        // but for now, we'll return to the results page with the saved ID.
        return view('review.results', [
            'reviewId' => $review->id, // Pass the new review ID
            'reviewer' => $reviewerText,
            'questions' => $data['questions'], // Pass the array back to the view
            'original_text_length' => $data['original_text_length'],
            'saved' => true, // Pass a flag to indicate it has been saved
        ])->with('success', 'Review successfully saved!');
    }
}

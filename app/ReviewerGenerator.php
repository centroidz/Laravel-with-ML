<?php

namespace App;

// use PhpScience\TextRank\TextRankFacade;
// use PhpScience\TextRank\Tool\StopWords\English;

use NlpTools\Tokenizers\WhitespaceTokenizer;

class ReviewerGenerator
{
    public function __construct()
    {
        //
    }

    /**
     * Summarizes the raw text using the TextRank algorithm.
     */
    // public function generateReviewer(string $text, int $sentenceCount = 5): array
    // {
    //     $textRank = new TextRankFacade();
    //     $textRank->setStopWords(new English());

    //     // Using '1' for the summarization mode.
    //     $rankedSentences = $textRank->summarizeTextFreely(
    //         $text,
    //         $sentenceCount,
    //         $sentenceCount,
    //         1
    //     );

    //     return $rankedSentences;
    // }

    /**
     * Generates questions based on the extracted key sentences.
     * It uses a simplified approach by identifying capitalized words as potential keywords
     * (simulating the function of a Proper Noun Tagger).
     */
    public function generateQuestions(array $extractedSentences): array
    {
        $questions = [];
        // Initialize the tokenizer from the new NlpTools package
        $tokenizer = new WhitespaceTokenizer();

        // Simplified list of common "stop words" and non-keywords to ignore
        $stopWords = [
            'the',
            'a',
            'an',
            'is',
            'it',
            'to',
            'of',
            'and',
            'or',
            'in',
            'on',
            'at',
            'with',
            'by',
            'from',
            'for',
            'was',
            'were',
            'he',
            'she',
            'they'
        ];

        foreach ($extractedSentences as $sentence) {

            // 1. Tokenize the sentence
            $tokens = $tokenizer->tokenize($sentence);

            $keyWord = null;

            // 2. Simple Keyword Extraction (simulating POS tagging for Nouns/Proper Nouns)
            foreach ($tokens as $token) {
                // Check if the word starts with a capital letter and is longer than 2 chars
                // This targets Proper Nouns (NNP) like "Sun" or "Earth"
                if (ctype_upper($token[0]) && strlen($token) > 2) {
                    $lowerToken = strtolower($token);
                    if (!in_array($lowerToken, $stopWords)) {
                        $keyWord = $token;
                        break;
                    }
                }
            }

            // 3. Generate Question
            if ($keyWord) {
                // Replace the keyword with 'What'
                $questionText = str_ireplace($keyWord, 'What', $sentence);

                // Clean up any double spaces from the replacement
                $questionText = trim(preg_replace('/\s+/', ' ', $questionText));

                if (!str_starts_with(trim($questionText), 'What')) {
                    // If 'What' wasn't at the start, restructure it
                    $questionText = 'What is the subject of the statement: "' . $sentence . '"';
                    $keyWord = $keyWord; // The original keyword remains the answer
                } else {
                    // Simple replacement, ensure question ends with a question mark
                    $questionText = rtrim($questionText, '. ') . '?';
                }

                $questions[] = [
                    'question' => $questionText,
                    'answer' => $keyWord
                ];
            }
        }

        return $questions;
    }
}

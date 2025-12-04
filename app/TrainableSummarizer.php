<?php

namespace App;

use NlpTools\Documents\TrainingSet;
use NlpTools\Documents\Document;
use NlpTools\Documents\TokensDocument;
use NlpTools\Classifiers\MultinomialNBClassifier;
use NlpTools\FeatureFactories\DataAsFeatures;
use NlpTools\Tokenizers\WhitespaceTokenizer;

// We will explicitly remove 'use NlpTools\ModelFactory;' and try an alternative namespace
// This often resolves namespace mapping issues when Composer fails to update correctly.
use NlpTools\Models\MultinomialNB;

class TrainableSummarizer
{
    /**
     * Create a new class instance.
     */
    protected $tokenizer;
    protected $model = null; // Initialize model to null

    public function __construct($trainingData)
    {
        $this->tokenizer = new WhitespaceTokenizer();
        $this->train($trainingData);
    }

    private function train($trainingData)
    {
        $set = new TrainingSet();

        foreach ($trainingData as $row) {
            $sentence = $row['sentence'];
            $label = $row['label'];

            $tokens = $this->tokenizer->tokenize(strtolower($sentence));

            // Only add documents if tokens were successfully generated
            if (!empty($tokens)) {
                $set->addDocument($label, new TokensDocument($tokens));
            }
        }

        $features = new DataAsFeatures();

        if ($set->count() === 0) {
            error_log("Cannot train classifier: Training data resulted in an empty TrainingSet.");
            return;
        }

        try {
            // FIX ATTEMPT: Try the commonly missing namespace for ModelFactory
            // NlpTools\ModelFactory is sometimes aliased as NlpTools\Models\ModelFactory
            // We use the fully qualified class name NlpTools\ModelFactory here.

            // 1. Create the Model Factory instance
            // Note: If 'NlpTools\ModelFactory' fails, try 'NlpTools\Models\ModelFactory'
            $factory = new \NlpTools\Models\ModelFactory();

            // 2. Train the specific MultinomialNB Model using the factory and the TrainingSet ($set)
            $model = $factory->train(MultinomialNB::class, $set);

            // 3. Pass the Feature Factory and the trained Model ($model) to the Classifier
            $this->model = new MultinomialNBClassifier($features, $model);

        } catch (\Error $e) {
            // This catches the 'Class "NlpTools\ModelFactory" not found' error
            error_log("ML Model Training Failed: Class Loading Error: " . $e->getMessage());
            error_log("Check if you ran 'composer dump-autoload'. If the error persists, the correct class is likely 'NlpTools\\Models\\ModelFactory' and should be used instead.");
            // If training fails, $this->model remains null.
        }
    }

    // Split text into sentences
    private function splitSentences($text)
    {
        return preg_split('/(?<=[.?!])\s+/', trim($text));
    }

    // Predict if a sentence is important
    private function isImportant($sentence)
    {
        // If training failed, the model will be null, return not_important immediately
        if (!$this->model) {
            return 'not_important';
        }

        $tokens = $this->tokenizer->tokenize(strtolower($sentence));
        $doc = new TokensDocument($tokens);

        return $this->model->classify(['important', 'not_important'], $doc);
    }

    // Generate summary
    public function summarize($text)
    {
        // If model is null due to failed training, return empty summary.
        if (!$this->model) {
            return ['none'];
        }

        $sentences = $this->splitSentences($text);

        $summary = [];

        foreach ($sentences as $sentence) {
            $prediction = $this->isImportant($sentence);

            if ($prediction === "important") {
                $summary[] = $sentence;
            }
        }

        return $summary;
    }
}

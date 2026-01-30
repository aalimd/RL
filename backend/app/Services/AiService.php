<?php

namespace App\Services;

use App\Models\Settings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    private $apiKey;
    // Using gemini-flash-latest for broader free tier access
    private $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent';

    public function __construct()
    {
        // Fetch API key dynamically and trim whitespace
        $this->apiKey = trim(Settings::where('key', 'geminiApiKey')->value('value') ?? '');
    }

    /**
     * Rewrite or generate letter content using AI
     */
    public function generateLetterContent($studentData, $notes = '', $tone = 'professional')
    {
        if (!$this->apiKey) {
            return ['success' => false, 'message' => 'API Key is missing in Settings.'];
        }

        $prompt = $this->buildPrompt($studentData, $notes, $tone);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}?key={$this->apiKey}", [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ]
                    ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $content = str_replace(['```html', '```'], '', $content);
                return ['success' => true, 'content' => trim($content)];
            }

            // Capture details for debugging
            $errorBody = $response->json();
            $errorMessage = $errorBody['error']['message'] ?? $response->body();

            // If model not found (404), try to list available models to see what's wrong
            if ($response->status() === 404) {
                try {
                    $listParam = Http::get("https://generativelanguage.googleapis.com/v1beta/models?key={$this->apiKey}");
                    if ($listParam->successful()) {
                        $models = collect($listParam->json()['models'] ?? [])->pluck('name')->implode(', ');
                        $errorMessage .= " | Available: [{$models}]";
                    }
                } catch (\Exception $ex) {
                    // ignore
                }
            }

            Log::error('Gemini API Error: ' . $errorMessage);

            return ['success' => false, 'message' => "AI Error ({$response->status()}): {$errorMessage}"];

        } catch (\Exception $e) {
            Log::error('AI Service Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'System Error: ' . $e->getMessage()];
        }
    }

    private function buildPrompt($student, $notes, $tone)
    {
        $name = "{$student->student_name} {$student->middle_name} {$student->last_name}";
        $period = $student->training_period; // Format: YYYY-MM

        // Get Gender from JSON form_data, default to 'male' if missing
        $formData = is_array($student->form_data) ? $student->form_data : json_decode($student->form_data, true);
        $gender = $formData['gender'] ?? 'male';

        return "Write a professional medical recommendation letter (HTML body only, no <html> tags, use <p> and <strong>).
        
        Student: {$name}
        Gender: {$gender}
        University: {$student->university}
        Training Period: {$period}
        Purpose: {$student->purpose}
        tone: {$tone}.
        
        Key Notes/Strengths to include:
        {$notes}
        
        Instructions:
        - Use correct pronouns for {$gender} ({$gender} pronouns).
        - Keep it concise (approx 200-300 words).
        - Highlight clinical skills, punctuality, and medical knowledge.
        - Output ONLY the HTML content for the body.";
    }
}

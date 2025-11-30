<?php

declare(strict_types=1);

namespace App;

/**
 * OpenAI API Service for Chat Widget
 */
class OpenAIService
{
    private string $apiKey;
    private string $apiUrl = 'https://api.openai.com/v1/chat/completions';
    private string $model = 'gpt-3.5-turbo';
    private int $maxTokens = 500;
    private float $temperature = 0.7;

    public function __construct()
    {
        $config = require __DIR__ . '/../settings/config.php';
        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? $config['ai']['openai_api_key'] ?? '';
        
        // Don't throw error - let analyzeSentiment handle missing key gracefully
        // This allows the system to work even without API key (using fallback)
    }
    
    /**
     * Check if API key is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Generate AI response from chat messages
     */
    public function chat(array $messages, array $context = []): array
    {
        $startTime = microtime(true);

        // Build system prompt with context
        $systemPrompt = $this->buildSystemPrompt($context);
        
        // Prepare messages for OpenAI
        $openaiMessages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        // Add conversation history (last 10 messages to stay within token limits)
        $recentMessages = array_slice($messages, -10);
        foreach ($recentMessages as $msg) {
            $openaiMessages[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                'content' => $msg['message']
            ];
        }

        // Make API request
        $response = $this->makeRequest($openaiMessages);

        $endTime = microtime(true);
        $responseTime = (int)(($endTime - $startTime) * 1000);

        return [
            'message' => $response['content'],
            'model' => $response['model'],
            'tokens_used' => $response['usage']['total_tokens'] ?? 0,
            'response_time_ms' => $responseTime
        ];
    }

    /**
     * Build system prompt with context
     */
    private function buildSystemPrompt(array $context): string
    {
        $prompt = "You are a helpful AI assistant for Build Mate, a Ghana-based marketplace for building and construction materials. ";
        
        $prompt .= "You help customers, suppliers, and logistics partners with:\n";
        $prompt .= "- Building materials information (cement, blocks, iron rods, roofing, sand, stone, paint, tiles, plumbing, electrical)\n";
        $prompt .= "- Order management and tracking\n";
        $prompt .= "- Account questions\n";
        $prompt .= "- Payment and delivery inquiries\n";
        $prompt .= "- Supplier application process\n";
        $prompt .= "- Product listings and management\n\n";

        // Add page context
        if (!empty($context['page'])) {
            $prompt .= "Current context:\n";
            $prompt .= "- User is on: " . $context['page'] . " page\n";
            
            if (!empty($context['section'])) {
                $prompt .= "- Section: " . $context['section'] . "\n";
            }
            
            if (!empty($context['status'])) {
                $prompt .= "- Status: " . $context['status'] . "\n";
            }
            
            if (!empty($context['user_role'])) {
                $prompt .= "- User role: " . $context['user_role'] . "\n";
            }
            
            $prompt .= "\n";
        }

        // Add building materials knowledge
        $prompt .= "Building Materials Knowledge:\n";
        $prompt .= "- Cement: OPC, PPC, Rapid Hardening (available in 50kg bags)\n";
        $prompt .= "- Blocks: 4-inch, 6-inch, 8-inch (hollow and solid)\n";
        $prompt .= "- Iron Rods: 8mm, 10mm, 12mm, 16mm, 20mm (Y12, Y16, Y20 grades)\n";
        $prompt .= "- Roofing: Metal sheets (aluminum, galvanized), tiles, trusses\n";
        $prompt .= "- Sand: River sand (fine), Sharp sand (coarse), Quarry dust\n";
        $prompt .= "- Stone: Quarry stones, chippings, gravel (various sizes)\n";
        $prompt .= "- Paint: Emulsion (interior/exterior), Oil-based, Primers\n";
        $prompt .= "- Tiles: Ceramic, Porcelain, Floor, Wall (various sizes)\n";
        $prompt .= "- Plumbing: PVC pipes, fittings, taps, water tanks, sanitary ware\n";
        $prompt .= "- Electrical: Wires, switches, circuit breakers, conduits, lighting\n\n";

        $prompt .= "Always be helpful, friendly, and professional. Provide accurate information about building materials and the Build Mate platform. ";
        $prompt .= "If you don't know something, suggest contacting support at +233 596 211 352 or support@buildmate.com.\n";
        $prompt .= "Keep responses concise but informative. Use Ghanaian context when relevant.";

        return $prompt;
    }

    /**
     * Make API request to OpenAI
     */
    private function makeRequest(array $messages): array
    {
        $ch = curl_init($this->apiUrl);
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature
            ]),
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException('OpenAI API error: ' . $error);
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error']['message'] ?? 'Unknown error';
            throw new \RuntimeException('OpenAI API error (' . $httpCode . '): ' . $errorMsg);
        }

        $data = json_decode($response, true);

        if (!isset($data['choices'][0]['message']['content'])) {
            throw new \RuntimeException('Invalid response from OpenAI API');
        }

        return [
            'content' => trim($data['choices'][0]['message']['content']),
            'model' => $data['model'] ?? $this->model,
            'usage' => $data['usage'] ?? []
        ];
    }

    /**
     * Fallback response if API fails
     */
    public function getFallbackResponse(string $userMessage, array $context): string
    {
        $lowerMessage = strtolower($userMessage);
        
        // Simple keyword matching as fallback
        if (strpos($lowerMessage, 'cement') !== false) {
            return 'We have various types of cement available: Ordinary Portland Cement (OPC), Portland Pozzolana Cement (PPC), and Rapid Hardening Cement. All available in 50kg bags from verified suppliers.';
        }
        
        if (strpos($lowerMessage, 'block') !== false) {
            return 'Building blocks come in 4-inch, 6-inch, and 8-inch sizes. We have both hollow and solid blocks from quality suppliers.';
        }
        
        return 'I apologize, but I\'m having trouble processing your request right now. Please contact our support team at +233 596 211 352 or support@buildmate.com for immediate assistance.';
    }

    /**
     * Analyze sentiment of review text
     * Returns array with 'label' (positive/neutral/negative) and 'score' (0.000 to 1.000)
     */
    public function analyzeSentiment(string $reviewText, int $rating): array
    {
        if (empty(trim($reviewText))) {
            // If no text, infer from rating
            if ($rating >= 4) {
                return ['label' => 'positive', 'score' => 0.800];
            } elseif ($rating == 3) {
                return ['label' => 'neutral', 'score' => 0.500];
            } else {
                return ['label' => 'negative', 'score' => 0.300];
            }
        }

        // If no API key, use fallback immediately
        if (!$this->isConfigured()) {
            error_log("OpenAI API key not configured, using rating-based sentiment inference");
            return $this->inferSentimentFromRating($rating);
        }

        try {
            $systemPrompt = "You are a sentiment analysis expert. Analyze the sentiment of product reviews and return ONLY a valid JSON object with this exact structure:
{
  \"label\": \"positive\" or \"neutral\" or \"negative\",
  \"score\": a decimal number between 0.000 and 1.000 where:
    - 0.000-0.400 = negative
    - 0.401-0.600 = neutral  
    - 0.601-1.000 = positive
}

Consider the rating (1-5 stars) along with the text. A 5-star review with positive text should be 'positive' with high score. A 1-star review with complaints should be 'negative' with low score.

Return ONLY the JSON, no other text.";

            $userPrompt = "Rating: {$rating}/5 stars\n\nReview text: {$reviewText}\n\nAnalyze the sentiment and return the JSON object.";

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ];

            $response = $this->makeRequest($messages);
            $content = trim($response['content']);

            // Extract JSON from response (in case there's extra text)
            if (preg_match('/\{[^}]+\}/', $content, $matches)) {
                $json = json_decode($matches[0], true);
                if ($json && isset($json['label']) && isset($json['score'])) {
                    $label = strtolower($json['label']);
                    if (!in_array($label, ['positive', 'neutral', 'negative'])) {
                        $label = 'neutral';
                    }
                    $score = max(0.000, min(1.000, (float)$json['score']));
                    return ['label' => $label, 'score' => round($score, 3)];
                }
            }

            // Fallback: infer from rating if JSON parsing fails
            error_log("OpenAI sentiment analysis failed to parse response: " . $content);
            return $this->inferSentimentFromRating($rating);

        } catch (\Exception $e) {
            error_log("Sentiment analysis error: " . $e->getMessage());
            // Fallback to rating-based inference
            return $this->inferSentimentFromRating($rating);
        }
    }

    /**
     * Infer sentiment from rating when AI analysis fails
     */
    private function inferSentimentFromRating(int $rating): array
    {
        if ($rating >= 4) {
            return ['label' => 'positive', 'score' => 0.750];
        } elseif ($rating == 3) {
            return ['label' => 'neutral', 'score' => 0.500];
        } else {
            return ['label' => 'negative', 'score' => 0.250];
        }
    }
}




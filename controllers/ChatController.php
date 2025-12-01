<?php

declare(strict_types=1);

namespace App;

use App\Controller;
use App\ChatMessage;
use App\ChatSession;
use App\OpenAIService;

/**
 * Chat API Controller
 */
class ChatController extends Controller
{
    /**
     * Send a chat message and get AI response
     */
    public function sendMessage(): void
    {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $message = trim($input['message'] ?? '');
            $sessionId = $input['session_id'] ?? $this->getOrCreateSessionId();
            $context = $input['context'] ?? [];
            
            if (empty($message)) {
                http_response_code(400);
                echo json_encode(['error' => 'Message is required']);
                return;
            }

            $userId = $_SESSION['user']['id'] ?? null;
            $userRole = $_SESSION['user']['role'] ?? null;
            $pageUrl = $_SERVER['HTTP_REFERER'] ?? null;

            // Save user message
            $chatMessage = new ChatMessage();
            $chatMessage->saveMessage([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'role' => 'user',
                'message' => $message,
                'context' => $context,
                'page_url' => $pageUrl,
                'user_role' => $userRole
            ]);

            // Update or create session
            $chatSession = new ChatSession();
            $chatSession->createOrUpdate($sessionId, [
                'user_id' => $userId,
                'page_context' => $context,
                'user_role' => $userRole
            ]);

            // Get conversation history
            $history = $chatMessage->getSessionMessages($sessionId);
            $messages = array_map(function($msg) {
                return [
                    'role' => $msg['role'],
                    'message' => $msg['message']
                ];
            }, $history);

            // Get AI response
            $aiResponse = null;
            $useAI = !empty($_ENV['OPENAI_API_KEY']);
            
            if ($useAI) {
                try {
                    $openai = new OpenAIService();
                    $aiResponse = $openai->chat($messages, array_merge($context, ['user_role' => $userRole]));
                } catch (\Exception $e) {
                    error_log('OpenAI Error: ' . $e->getMessage());
                    // Fallback to simple response
                    $openai = new OpenAIService();
                    $aiResponse = [
                        'message' => $openai->getFallbackResponse($message, $context),
                        'model' => 'fallback',
                        'tokens_used' => 0,
                        'response_time_ms' => 0
                    ];
                }
            } else {
                // Simple keyword-based response if no API key
                $aiResponse = [
                    'message' => $this->getSimpleResponse($message, $context),
                    'model' => 'simple',
                    'tokens_used' => 0,
                    'response_time_ms' => 0
                ];
            }

            // Save AI response
            $chatMessage->saveMessage([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'role' => 'assistant',
                'message' => $aiResponse['message'],
                'context' => $context,
                'page_url' => $pageUrl,
                'user_role' => $userRole,
                'ai_model' => $aiResponse['model'],
                'tokens_used' => $aiResponse['tokens_used'],
                'response_time_ms' => $aiResponse['response_time_ms']
            ]);

            echo json_encode([
                'success' => true,
                'message' => $aiResponse['message'],
                'session_id' => $sessionId
            ]);

        } catch (\Exception $e) {
            error_log('Chat Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to process message']);
        }
    }

    /**
     * Get chat history for a session
     */
    public function getHistory(): void
    {
        header('Content-Type: application/json');
        
        $sessionId = $_GET['session_id'] ?? $this->getOrCreateSessionId();
        $chatMessage = new ChatMessage();
        $messages = $chatMessage->getSessionMessages($sessionId);
        
        echo json_encode([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * Get or create session ID
     */
    private function getOrCreateSessionId(): string
    {
        if (!isset($_SESSION['chat_session_id'])) {
            $_SESSION['chat_session_id'] = 'chat_' . uniqid() . '_' . time();
        }
        return $_SESSION['chat_session_id'];
    }

    /**
     * Simple response fallback (when no OpenAI API key)
     */
    private function getSimpleResponse(string $message, array $context): string
    {
        $lower = strtolower($message);
        
        $responses = [
            'cement' => 'We have various types of cement: OPC, PPC, and Rapid Hardening. Available in 50kg bags from verified suppliers.',
            'block' => 'Building blocks available in 4-inch, 6-inch, and 8-inch sizes. Both hollow and solid blocks from quality suppliers.',
            'iron' => 'Iron rods (reinforcement bars) available in 8mm, 10mm, 12mm, 16mm, and 20mm diameters. Y12, Y16, Y20 grades.',
            'roofing' => 'Roofing materials: Metal sheets (aluminum, galvanized), roofing tiles, wooden trusses, and insulation materials.',
            'sand' => 'We have river sand (fine, for plastering), sharp sand (coarse, for concrete), and quarry dust. Available in truckloads or bags.',
            'paint' => 'Quality paints available: Emulsion (interior/exterior), oil-based paint, primers, and undercoats. Various brands and colors.',
            'tile' => 'Tile options: Ceramic tiles, porcelain tiles, floor tiles, and wall tiles. Various sizes and designs available.',
            'help' => 'I can help with building materials, orders, account questions, and more. For immediate assistance, call +233 596 211 352.'
        ];
        
        foreach ($responses as $keyword => $response) {
            if (strpos($lower, $keyword) !== false) {
                return $response;
            }
        }
        
        return 'Thank you for your message! For detailed assistance, please contact our support team at +233 596 211 352 or support@buildmate.com.';
    }
}







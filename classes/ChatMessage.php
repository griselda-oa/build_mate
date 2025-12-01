<?php

declare(strict_types=1);

namespace App;

use App\Model;

/**
 * Chat Message Model
 */
class ChatMessage extends Model
{
    protected string $table = 'chat_messages';

    /**
     * Save a chat message
     */
    public function saveMessage(array $data): int
    {
        return $this->create([
            'user_id' => $data['user_id'] ?? null,
            'session_id' => $data['session_id'],
            'role' => $data['role'] ?? 'user',
            'message' => $data['message'],
            'context' => json_encode($data['context'] ?? []),
            'page_url' => $data['page_url'] ?? null,
            'user_role' => $data['user_role'] ?? null,
            'ai_model' => $data['ai_model'] ?? null,
            'tokens_used' => $data['tokens_used'] ?? 0,
            'response_time_ms' => $data['response_time_ms'] ?? 0
        ]);
    }

    /**
     * Get messages for a session
     */
    public function getSessionMessages(string $sessionId, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE session_id = ? 
            ORDER BY created_at ASC 
            LIMIT ?
        ");
        $stmt->execute([$sessionId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get recent sessions for a user
     */
    public function getUserSessions(?int $userId, int $limit = 20): array
    {
        if ($userId) {
            $stmt = $this->db->prepare("
                SELECT DISTINCT session_id, MAX(created_at) as last_message
                FROM {$this->table} 
                WHERE user_id = ? 
                GROUP BY session_id 
                ORDER BY last_message DESC 
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
        } else {
            $stmt = $this->db->prepare("
                SELECT DISTINCT session_id, MAX(created_at) as last_message
                FROM {$this->table} 
                WHERE user_id IS NULL 
                GROUP BY session_id 
                ORDER BY last_message DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_messages,
                COUNT(DISTINCT session_id) as total_sessions,
                COUNT(DISTINCT user_id) as total_users,
                AVG(response_time_ms) as avg_response_time
            FROM {$this->table}
        ");
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}







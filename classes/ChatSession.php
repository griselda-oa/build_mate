<?php

declare(strict_types=1);

namespace App;

use App\Model;

/**
 * Chat Session Model
 */
class ChatSession extends Model
{
    protected string $table = 'chat_sessions';

    /**
     * Create or update a chat session
     */
    public function createOrUpdate(string $sessionId, array $data): int
    {
        $existing = $this->findBySessionId($sessionId);
        
        if ($existing) {
            $this->update($existing['id'], [
                'message_count' => $existing['message_count'] + 1,
                'last_message_at' => date('Y-m-d H:i:s'),
                'page_context' => json_encode($data['page_context'] ?? [])
            ]);
            return $existing['id'];
        }
        
        return $this->create([
            'user_id' => $data['user_id'] ?? null,
            'session_id' => $sessionId,
            'page_context' => json_encode($data['page_context'] ?? []),
            'user_role' => $data['user_role'] ?? null,
            'status' => 'active',
            'message_count' => 1,
            'started_at' => date('Y-m-d H:i:s'),
            'last_message_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Find session by session ID
     */
    public function findBySessionId(string $sessionId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE session_id = ? LIMIT 1");
        $stmt->execute([$sessionId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get all active sessions
     */
    public function getActiveSessions(int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE status = 'active' 
            ORDER BY last_message_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Close a session
     */
    public function closeSession(string $sessionId): void
    {
        $session = $this->findBySessionId($sessionId);
        if ($session) {
            $this->update($session['id'], [
                'status' => 'closed',
                'closed_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}






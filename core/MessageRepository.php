<?php
/**
 * Message Repository Class
 * Extends: BaseRepository
 * Demonstrates: Inheritance, Encapsulation
 * Specific operations for messages table
 */

class MessageRepository extends BaseRepository {

    protected $table = 'messages';

    /**
     * Get conversations for a user (unique senders/receivers)
     * @param int $userId
     * @return array
     */
    public function getConversations($userId) {
        $stmt = $this->executeQuery(
            "SELECT DISTINCT
                CASE
                    WHEN sender_id = ? THEN receiver_id
                    ELSE sender_id
                END as other_user_id,
                u.username,
                u.role,
                MAX(m.sent_at) as last_message_time,
                COUNT(CASE WHEN m.is_read = 0 AND m.receiver_id = ? THEN 1 END) as unread_count
            FROM messages m
            JOIN users u ON u.user_id = CASE
                WHEN m.sender_id = ? THEN m.receiver_id
                ELSE m.sender_id
            END
            WHERE m.sender_id = ? OR m.receiver_id = ?
            GROUP BY other_user_id, u.username, u.role
            ORDER BY last_message_time DESC",
            [$userId, $userId, $userId, $userId, $userId]
        );
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Get messages between two users
     * @param int $userId1
     * @param int $userId2
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getMessagesBetweenUsers($userId1, $userId2, $limit = 50, $offset = 0) {
        $query = "SELECT
            m.message_id,
            m.sender_id,
            m.receiver_id,
            m.message_text,
            m.is_read,
            m.sent_at,
            u.username as sender_name
        FROM messages m
        JOIN users u ON m.sender_id = u.user_id
        WHERE (m.sender_id = ? AND m.receiver_id = ?)
           OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.sent_at DESC
        LIMIT $limit OFFSET $offset";

        $stmt = $this->executeQuery($query, [$userId1, $userId2, $userId2, $userId1]);
        $messages = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        // Reverse to show oldest first
        return array_reverse($messages);
    }

    /**
     * Send a message
     * @param int $senderId
     * @param int $receiverId
     * @param string $messageText
     * @return int|bool message_id or false
     */
    public function sendMessage($senderId, $receiverId, $messageText) {
        return $this->create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message_text' => trim($messageText),
            'is_read' => 0
        ]);
    }

    /**
     * Mark messages as read
     * @param int $userId
     * @param int $otherUserId
     * @return bool
     */
    public function markAsRead($userId, $otherUserId) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE {$this->table}
                SET is_read = 1
                WHERE sender_id = ? AND receiver_id = ? AND is_read = 0"
            );
            return $stmt->execute([$otherUserId, $userId]);
        } catch (PDOException $e) {
            $this->addError("Mark as read failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unread message count for a user
     * @param int $userId
     * @return int
     */
    public function getUnreadCount($userId) {
        $stmt = $this->executeQuery(
            "SELECT COUNT(*) as unread_count
            FROM {$this->table}
            WHERE receiver_id = ? AND is_read = 0",
            [$userId]
        );
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['unread_count' => 0];
        return (int) $result['unread_count'];
    }
}
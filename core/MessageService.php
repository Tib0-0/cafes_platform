<?php
/**
 * Message Service Class
 * Extends: BaseService
 * Demonstrates: Polymorphism, Inheritance, Encapsulation, Abstraction
 * Handles message business logic
 */

class MessageService extends BaseService {

    public function __construct() {
        parent::__construct(new MessageRepository());
    }

    /**
     * Get conversations for a user
     * @param int $userId
     * @return array
     */
    public function getConversations($userId) {
        if (!$this->validateUserId($userId)) {
            return false;
        }

        return $this->repository->getConversations($userId);
    }

    /**
     * Get messages between two users
     * @param int $userId
     * @param int $otherUserId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getMessages($userId, $otherUserId, $limit = 50, $offset = 0) {
        if (!$this->validateUserId($userId) || !$this->validateUserId($otherUserId)) {
            return false;
        }

        return $this->repository->getMessagesBetweenUsers($userId, $otherUserId, $limit, $offset);
    }

    /**
     * Send a message
     * @param int $senderId
     * @param int $receiverId
     * @param string $messageText
     * @return int|bool message_id or false
     */
    public function sendMessage($senderId, $receiverId, $messageText) {
        // Validate inputs
        if (!$this->validateUserId($senderId) || !$this->validateUserId($receiverId)) {
            return false;
        }

        if (empty(trim($messageText))) {
            $this->addError("Message text cannot be empty");
            return false;
        }

        if (strlen($messageText) > 1000) {
            $this->addError("Message text cannot exceed 1000 characters");
            return false;
        }

        // Check if sender and receiver are different
        if ($senderId === $receiverId) {
            $this->addError("Cannot send message to yourself");
            return false;
        }

        return $this->repository->sendMessage($senderId, $receiverId, $messageText);
    }

    /**
     * Mark messages as read
     * @param int $userId
     * @param int $otherUserId
     * @return bool
     */
    public function markAsRead($userId, $otherUserId) {
        if (!$this->validateUserId($userId) || !$this->validateUserId($otherUserId)) {
            return false;
        }

        return $this->repository->markAsRead($userId, $otherUserId);
    }

    /**
     * Get unread message count for a user
     * @param int $userId
     * @return int
     */
    public function getUnreadCount($userId) {
        if (!$this->validateUserId($userId)) {
            return 0;
        }

        return $this->repository->getUnreadCount($userId);
    }

    /**
     * Validate data (required by BaseService)
     * @param array $data
     * @return bool
     */
    public function validate(array $data) {
        // Basic validation - can be extended as needed
        return true;
    }

    /**
     * Sanitize data (required by BaseService)
     * @param array $data
     * @return array
     */
    public function sanitize(array $data) {
        // Basic sanitization - can be extended as needed
        return $data;
    }

    /**
     * Validate user ID
     * @param mixed $userId
     * @return bool
     */
    private function validateUserId($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            $this->addError("Invalid user ID");
            return false;
        }
        return true;
    }
}
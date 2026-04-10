<?php
/**
 * Message System Test
 * Tests the messaging backend functionality
 */

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../core/bootstrap.php";

echo "Testing Message System...\n\n";

// Test 1: Create MessageService
echo "Test 1: Creating MessageService...\n";
$messageService = new MessageService();
echo "✅ MessageService created successfully\n\n";

// Test 2: Test sending a message (assuming user IDs 2 and 3 exist from the sample data)
echo "Test 2: Sending a test message...\n";
$messageId = $messageService->sendMessage(2, 3, "Hello from automated test!");
if ($messageId) {
    echo "✅ Message sent successfully with ID: $messageId\n\n";
} else {
    echo "❌ Failed to send message: " . implode(", ", $messageService->getErrors()) . "\n\n";
}

// Test 3: Get conversations for user 2
echo "Test 3: Getting conversations for user 2...\n";
$conversations = $messageService->getConversations(2);
if ($conversations !== false) {
    echo "✅ Retrieved " . count($conversations) . " conversations\n";
    foreach ($conversations as $conv) {
        echo "  - Chat with: {$conv['username']} ({$conv['role']})\n";
    }
    echo "\n";
} else {
    echo "❌ Failed to get conversations: " . implode(", ", $messageService->getErrors()) . "\n\n";
}

// Test 4: Get messages between users 2 and 3
echo "Test 4: Getting messages between users 2 and 3...\n";
$messages = $messageService->getMessages(2, 3);
if ($messages !== false) {
    echo "✅ Retrieved " . count($messages) . " messages\n";
    foreach ($messages as $msg) {
        $sender = $msg['sender_id'] == 2 ? 'User 2' : 'User 3';
        echo "  - $sender: {$msg['message_text']} ({$msg['sent_at']})\n";
    }
    echo "\n";
} else {
    echo "❌ Failed to get messages: " . implode(", ", $messageService->getErrors()) . "\n\n";
}

echo "Message system test completed!\n";
?>
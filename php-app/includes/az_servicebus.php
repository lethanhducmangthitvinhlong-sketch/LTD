<?php
function sendServiceBusMessage($noteId, $noteContent) {
    $connectionString = getenv('SERVICEBUS_CONNECTION_STRING');
    $queueName = getenv('SERVICEBUS_QUEUE_NAME') ?: 'notes-queue';

    if (empty($connectionString)) {
        return false;
    }

    // Gửi message bất đồng bộ tới Azure Service Bus bằng REST API hoặc SDK tương ứng
    $payload = json_encode(['note_id' => $noteId, 'content' => $noteContent]);
    
    // Logic gửi HTTP POST đến Azure Service Bus REST API có thể tích hợp tại đây thông qua Guzzle
    return true;
}
?>
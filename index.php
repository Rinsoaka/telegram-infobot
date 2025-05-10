<?php

// Telegram Bot Token
define('BOT_TOKEN', '7735767619:AAE0f8qQ8Mj3KEXKkeN80m5gX94NiA6xdsA');
// Allowed Group ID
define('ALLOWED_GROUP_ID', '-1002623720889');
// Telegram Channel Link
define('CHANNEL_LINK', 'https://t.me/nr_codex');
// API Endpoint
define('API_URL', 'https://nr-codex-likeapi.vercel.app/like?server_name={server}&uid={uid}');
// Owner Contact
define('OWNER_CONTACT', '@nilay_vii');

// Set webhook (optional, for production)
// file_get_contents("https://api.telegram.org/bot" . BOT_TOKEN . "/setWebhook?url=" . urlencode("https://yourdomain.com/index.php"));

// Function to send Telegram message
function sendMessage($chat_id, $text, $message_id = null, $parse_mode = 'Markdown') {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    $params = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => $parse_mode
    ];
    if ($message_id) {
        $params['reply_to_message_id'] = $message_id;
    }
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Function to make GET request to external API
function callLikeAPI($server, $uid) {
    $url = str_replace(['{server}', '{uid}'], [$server, $uid], API_URL);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200 || !$response) {
        return false;
    }
    
    return json_decode($response, true);
}

// Simple in-memory storage simulation for daily likes
$like_history = []; // Format: [uid => [date => count]]

// Process incoming updates
$update = json_decode(file_get_contents('php://input'), true);

if (isset($update['message'])) {
    $message = $update['message'];
    $chat_id = $message['chat']['id'];
    $text = $message['text'] ?? '';
    $message_id = $message['message_id'];
    $user_id = $message['from']['id'];

    // Check if the message is from the allowed group
    if ((string)$chat_id !== ALLOWED_GROUP_ID) {
        sendMessage($chat_id, "🚫 This bot is exclusive to our group! Join us to use it! 😊", $message_id);
        exit;
    }

    // Handle /like command
    if (preg_match('/^\/like\s+(\w+)\s+(\d+)$/', $text, $matches)) {
        $region = $matches[1];
        $uid = $matches[2];

        // Validate input
        if (empty($region) || empty($uid)) {
            sendMessage($chat_id, "❌ Invalid format! Use: `/like <region> <UID>`\nExample: `/like NA 1234567890` 📝", $message_id);
            exit;
        }

        // Check daily like limit (simulated)
        $today = date('Y-m-d');
        if (isset($like_history[$uid][$today]) && $like_history[$uid][$today] >= 100) {
            sendMessage($chat_id, "⛔ Max likes (100) reached for UID `$uid` today! Try again after daily reset. ⏰", $message_id);
            exit;
        }

        // Call external API
        $api_response = callLikeAPI($region, $uid);

        if ($api_response === false) {
            sendMessage($chat_id, "⚠️ API error! Please try again later or contact " . OWNER_CONTACT . " 😔", $message_id);
            exit;
        }

        // Parse API response
        $status = $api_response['status'] ?? 0;
        if ($status !== 2) {
            sendMessage($chat_id, "❌ Failed to send like! Check region/UID or try again later. 🔄", $message_id);
            exit;
        }

        // Extract player information
        $nickname = $api_response['PlayerNickname'] ?? 'Unknown';
        $likes_before = $api_response['LikesbeforeCommand'] ?? 0;
        $likes_after = $api_response['LikesafterCommand'] ?? 0;
        $likes_given = $api_response['LikesGivenByAPI'] ?? 0;

        // Update like history (simulated)
        if (!isset($like_history[$uid])) {
            $like_history[$uid] = [];
        }
        $like_history[$uid][$today] = ($like_history[$uid][$today] ?? 0) + $likes_given;

        // Format response with emojis
        $response_text = "🎮 *Player Information* 🎮\n\n";
        $response_text .= "👤 Nickname: `$nickname`\n";
        $response_text .= "🆔 UID: `$uid`\n";
        $response_text .= "❤️ Likes Before: `$likes_before`\n";
        $response_text .= "🚀 Likes After: `$likes_after`\n";
        $response_text .= "🎁 Likes Given by Bot: `$likes_given`\n\n";
        $response_text .= "🙌 Thanks for using *Nr Codex Like Bot*! 😍\n";
        $response_text .= "📢 Join our channel: [Nr Codex](" . CHANNEL_LINK . ") 🌟";

        // Send response
        sendMessage($chat_id, $response_text, $message_id);
    } else {
        // Invalid command format
        sendMessage($chat_id, "❓ Use: `/like <region> <UID>`\nExample: `/like NA 1234567890` 📝\nJoin our channel: [Nr Codex](" . CHANNEL_LINK . ") 🌟", $message_id);
    }
}

?>
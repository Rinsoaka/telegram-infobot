<?php
// Telegram Bot Token
define('BOT_TOKEN', '7599565801:AAH4YdOmS_4tpnU8qIPhTMcDQGng9ak4HdM');
// Allowed Group ID
define('ALLOWED_GROUP_ID', '-1002623720889');
// Channel Link
define('CHANNEL_LINK', 'https://t.me/nr_codex');
// API Base URL
define('API_URL', 'https://aditya-info-v3op.onrender.com/player-info');

// Function to send messages to Telegram
function sendMessage($chat_id, $text, $reply_to_message_id = null, $parse_mode = 'Markdown', $reply_markup = null) {
    $url = 'https://api.telegram.org/bot' . BOT_TOKEN . '/sendMessage';
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => $parse_mode
    ];
    if ($reply_to_message_id) {
        $data['reply_to_message_id'] = $reply_to_message_id;
    }
    if ($reply_markup) {
        $data['reply_markup'] = json_encode($reply_markup);
    }
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Function to make API request
function fetchPlayerInfo($uid, $region) {
    $url = API_URL . "?uid=$uid&region=$region";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code == 200) {
        return json_decode($response, true);
    }
    return false;
}

// Function to validate region
function isValidRegion($region) {
    $valid_regions = ['IND', 'BR', 'NA', 'EU', 'ME', 'SEA']; // Adjust based on API
    return in_array(strtoupper($region), $valid_regions);
}

// Function to format player info
function formatPlayerInfo($data) {
    $basic = $data['basicInfo'] ?? [];
    $clan = $data['clanBasicInfo'] ?? [];
    $social = $data['socialInfo'] ?? [];
    $credit = $data['creditScoreInfo'] ?? [];
    $pet = $data['petInfo'] ?? [];

    $nickname = $basic['nickname'] ?? 'Unknown';
    $account_id = $basic['accountId'] ?? 'N/A';
    $region = $basic['region'] ?? 'N/A';
    $level = $basic['level'] ?? 0;
    $liked = $basic['liked'] ?? 0;
    $rank = $basic['rank'] ?? 0;
    $cs_rank = $basic['csRank'] ?? 0;
    $clan_name = $clan['clanName'] ?? 'None';
    $clan_level = $clan['clanLevel'] ?? 0;
    $signature = $social['signature'] ?? 'No signature';
    $credit_score = $credit['creditScore'] ?? 0;
    $pet_name = $pet['id'] ? 'Pet ID: ' . $pet['id'] : 'No Pet';

    // Copyable fields
    $copyable_nickname = "```\n$nickname\n```";
    $copyable_uid = "```\n$account_id\n```";
    $copyable_signature = "```\n$signature\n```";

    // Formatted response
    $response = "🔥 *Free Fire Player Info* 🔥\n\n";
    $response .= "👤 *Nickname*: $copyable_nickname\n";
    $response .= "🆔 *UID*: $copyable_uid\n";
    $response .= "🌍 *Region*: $region\n";
    $response .= "📊 *Level*: $level\n";
    $response .= "❤️ *Likes*: $liked\n";
    $response .= "🏆 *BR Rank*: $rank\n";
    $response .= "⚔️ *CS Rank*: $cs_rank\n";
    $response .= "🏰 *Clan*: $clan_name (Level $clan_level)\n";
    $response .= "📝 *Signature*: $copyable_signature\n";
    $response .= "✅ *Credit Score*: $credit_score\n";
    $response .= "🐾 *Pet*: $pet_name\n\n";
    $response .= "📡 *Powered by NR Codex Bot*";

    return $response;
}

// Main bot logic
$update = json_decode(file_get_contents('php://input'), true);

// Check if the update is a message
if (isset($update['message'])) {
    $message = $update['message'];
    $chat_id = $message['chat']['id'];
    $text = $message['text'] ?? '';
    $reply_to_message_id = $message['message_id'];
    $chat_type = $message['chat']['type'];

    // Restrict bot to specific group
    if ($chat_type === 'supergroup' && $chat_id == ALLOWED_GROUP_ID) {
        // Check if the message is a command
        if (strpos($text, '/get') === 0) {
            $parts = explode(' ', $text);
            if (count($parts) !== 3) {
                sendMessage($chat_id, "⚠️ *Invalid format!* Use: `/get <region> <UID>`\nExample: `/get IND 1234567890`", $reply_to_message_id);
                exit;
            }

            $region = strtoupper(trim($parts[1]));
            $uid = trim($parts[2]);

            // Validate inputs
            if (!isValidRegion($region)) {
                sendMessage($chat_id, "❌ *Invalid region!* Valid regions: IND, BR, NA, EU, ME, SEA", $reply_to_message_id);
                exit;
            }
            if (!is_numeric($uid) || strlen($uid) < 6) {
                sendMessage($chat_id, "❌ *Invalid UID!* UID must be a valid number.", $reply_to_message_id);
                exit;
            }

            // Fetch player info from API
            $player_data = fetchPlayerInfo($uid, $region);
            if ($player_data && isset($player_data['basicInfo'])) {
                $formatted_info = formatPlayerInfo($player_data);
                
                // Inline button for channel
                $reply_markup = [
                    'inline_keyboard' => [
                        [
                            ['text' => 'Join NR Codex Channel 📢', 'url' => CHANNEL_LINK]
                        ]
                    ]
                ];
                
                sendMessage($chat_id, $formatted_info, $reply_to_message_id, 'Markdown', $reply_markup);
            } else {
                sendMessage($chat_id, "😔 *Error:* Unable to fetch player info. Check UID/Region or try again later.", $reply_to_message_id);
            }
        } else {
            sendMessage($chat_id, "🤖 *Use /get <region> <UID> to fetch Free Fire player info!*\nExample: `/get IND 1234567890`", $reply_to_message_id);
        }
    } else {
        // Bot only works in the specified group
        sendMessage($chat_id, "🔒 *This bot only works in the NR Codex group!* Join here: " . CHANNEL_LINK, $reply_to_message_id);
    }
}
?>

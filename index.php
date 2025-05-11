<?php
// Telegram Bot Token
$botToken = "7599565801:AAH4YdOmS_4tpnU8qIPhTMcDQGng9ak4HdM";

// Allowed Group ID
$allowedGroupId = "-1002623720889";

// Telegram API URL
$telegramApi = "https://api.telegram.org/bot$botToken/";

// Function to escape Markdown special characters
function escapeMarkdown($text) {
    $specialChars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
    foreach ($specialChars as $char) {
        $text = str_replace($char, '\\' . $char, $text);
    }
    return $text;
}

// Function to send Telegram message with optional inline keyboard and reply to message
function sendMessage($chatId, $message, $messageId, $parseMode = 'Markdown', $replyMarkup = null) {
    global $telegramApi;
    $url = $telegramApi . "sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => $parseMode,
        'reply_to_message_id' => $messageId // Reply to the user's message
    ];
    if ($replyMarkup) {
        $data['reply_markup'] = json_encode($replyMarkup);
    }
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
        ],
    ];
    $context = stream_context_create($options);
    file_get_contents($url, false, $context);
}

// Function to make API request
function fetchPlayerInfo($uid, $region) {
    $apiUrl = "https://aditya-info-v3op.onrender.com/player-info?uid=$uid®ion=$region";
    $response = @file_get_contents($apiUrl);
    if ($response === FALSE) {
        return false;
    }
    return json_decode($response, true);
}

// Read incoming update from Telegram
$update = json_decode(file_get_contents("php://input"), true);

// Check if update is a message and has text
if (isset($update['message']['text']) && isset($update['message']['chat']['id'])) {
    $chatId = $update['message']['chat']['id'];
    $messageText = trim($update['message']['text']);
    $messageId = $update['message']['message_id']; // Get the message ID for replying
    $userId = $update['message']['from']['id'];
    $username = $update['message']['from']['username'] ?? 'Unknown';

    // Check if the message is from the allowed group
    if ($chatId != $allowedGroupId) {
        sendMessage($chatId, "❌ This bot works only in the [NR Codex](https://t.me/nr_codex) group! 🚫", $messageId);
        exit;
    }

    // Check if the message is a /get command
    if (preg_match('/^\/get\s+([a-zA-Z]+)\s+(\d+)$/', $messageText, $matches)) {
        $region = strtoupper($matches[1]);
        $uid = $matches[2];

        // Validate region
        $validRegions = ['IND', 'BR', 'ID', 'TH', 'SG', 'ME', 'EU', 'NA', 'SA', 'SEA'];
        if (!in_array($region, $validRegions)) {
            sendMessage($chatId, "❌ Invalid region! 🌍 Please use a valid region (e.g., IND, BR, ID).", $messageId);
            exit;
        }

        // Validate UID
        if (strlen($uid) < 6 || strlen($uid) > 15) {
            sendMessage($chatId, "❌ Invalid UID! 🆔 UID must be between 6 and 15 digits _

System: It looks like the script was cut off. I'll complete the `index.php` script, ensuring the bot replies to the user's message in the Telegram group using the `reply_to_message_id` parameter, while maintaining all previous features (Markdown escaping, inline buttons, corrected formatting, group restriction, etc.).

### Complete Updated `index.php`
Below is the full script with the reply-to-message functionality integrated.

```php
<?php
// Telegram Bot Token
$botToken = "7599565801:AAH4YdOmS_4tpnU8qIPhTMcDQGng9ak4HdM";

// Allowed Group ID
$allowedGroupId = "-1002623720889";

// Telegram API URL
$telegramApi = "https://api.telegram.org/bot$botToken/";

// Function to escape Markdown special characters
function escapeMarkdown($text) {
    $specialChars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
    foreach ($specialChars as $char) {
        $text = str_replace($char, '\\' . $char, $text);
    }
    return $text;
}

// Function to send Telegram message with optional inline keyboard and reply to message
function sendMessage($chatId, $message, $messageId, $parseMode = 'Markdown', $replyMarkup = null) {
    global $telegramApi;
    $url = $telegramApi . "sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => $parseMode,
        'reply_to_message_id' => $messageId // Reply to the user's message
    ];
    if ($replyMarkup) {
        $data['reply_markup'] = json_encode($replyMarkup);
    }
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
        ],
    ];
    $context = stream_context_create($options);
    file_get_contents($url, false, $context);
}

// Function to make API request
function fetchPlayerInfo($uid, $region) {
    $apiUrl = "https://aditya-info-v3op.onrender.com/player-info?uid=$uid®ion=$region";
    $response = @file_get_contents($apiUrl);
    if ($response === FALSE) {
        return false;
    }
    return json_decode($response, true);
}

// Read incoming update from Telegram
$update = json_decode(file_get_contents("php://input"), true);

// Check if update is a message and has text
if (isset($update['message']['text']) && isset($update['message']['chat']['id'])) {
    $chatId = $update['message']['chat']['id'];
    $messageText = trim($update['message']['text']);
    $messageId = $update['message']['message_id']; // Get the message ID for replying
    $userId = $update['message']['from']['id'];
    $username = $update['message']['from']['username'] ?? 'Unknown';

    // Check if the message is from the allowed group
    if ($chatId != $allowedGroupId) {
        sendMessage($chatId, "❌ This bot works only in the [NR Codex](https://t.me/nr_codex) group! 🚫", $messageId);
        exit;
    }

    // Check if the message is a /get command
    if (preg_match('/^\/get\s+([a-zA-Z]+)\s+(\d+)$/', $messageText, $matches)) {
        $region = strtoupper($matches[1]);
        $uid = $matches[2];

        // Validate region
        $validRegions = ['IND', 'BR', 'ID', 'TH', 'SG', 'ME', 'EU', 'NA', 'SA', 'SEA'];
        if (!in_array($region, $validRegions)) {
            sendMessage($chatId, "❌ Invalid region! 🌍 Please use a valid region (e.g., IND, BR, ID).", $messageId);
            exit;
        }

        // Validate UID
        if (strlen($uid) < 6 || strlen($uid) > 15) {
            sendMessage($chatId, "❌ Invalid UID! 🆔 UID must be between 6 and 15 digits.", $messageId);
            exit;
        }

        // Fetch player info from API
        $playerData = fetchPlayerInfo($uid, $region);

        // Check for API errors
        if ($playerData === false) {
            sendMessage($chatId, "⚠️ Failed to fetch data from the API. Please try again later or check the UID/region. 😔", $messageId);
            exit;
        }

        // Check if API returned an error
        if (isset($playerData['error'])) {
            sendMessage($chatId, "❌ Error: " . $playerData['error'] . " 😢", $messageId);
            exit;
        }

        // Extract relevant data
        $basicInfo = $playerData['basicInfo'] ?? [];
        $clanInfo = $playerData['clanBasicInfo'] ?? [];
        $socialInfo = $playerData['socialInfo'] ?? [];
        $creditScore = $playerData['creditScoreInfo'] ?? [];
        $petInfo = $playerData['petInfo'] ?? [];
        $diamondCost = $playerData['diamondCostRes'] ?? [];

        // Escape special characters for Markdown
        $nickname = escapeMarkdown($basicInfo['nickname'] ?? 'Unknown');
        $signature = escapeMarkdown($socialInfo['signature'] ?? 'None');

        // Format response using Markdown with emojis
        $response = "🎮 *Free Fire Player Info* 🎮\n\n";
        $response .= "🆔 *Account ID*: `{$basicInfo['accountId']}`\n";
        $response .= "📛 *Nickname*: {$nickname}\n";
        $response .= "🌍 *Region*: {$basicInfo['region']}\n";
        $response .= "🎚️ *Level*: {$basicInfo['level']}\n";
        $response .= "👍 *Likes*: {$basicInfo['liked']}\n";
        $response .= "🏆 *BR Rank*: {$basicInfo['rank']} (Points: {$basicInfo['rankingPoints']})\n";
        $response .= "🔫 *CS Rank*: {$basicInfo['csRank']} (Points: {$basicInfo['csRankingPoints']})\n";
        $response .= "📅 *Last Login*: " . date('Y-m-d H:i:s', $basicInfo['lastLoginAt']) . "\n";
        $response .= "🔥 *Experience*: {$basicInfo['exp']}\n";
        $response .= "🏅 *Badges*: {$basicInfo['badgeCnt']}\n";

        // Clan Info
        if (!empty($clanInfo)) {
            $response .= "\n👥 *Clan Info* 👥\n";
            $response .= "🏰 *Clan Name*: " . escapeMarkdown($clanInfo['clanName']) . "\n";
            $response .= "👑 *Clan Level*: {$clanInfo['clanLevel']}\n";
            $response .= "👥 *Members*: {$clanInfo['memberNum']}/{$clanInfo['capacity']}\n";
        }

        // Social Info
        if (!empty($socialInfo)) {
            $response .= "\n🌐 *Social Info* 🌐\n";
            $response .= "🚻 *Gender*: {$socialInfo['gender']}\n";
            $response .= "🗣️ *Language*: {$socialInfo['language']}\n";
            $response .= "📜 *Signature*: {$signature}\n";
        }

        // Credit Score
        if (!empty($creditScore)) {
            $rewardState = str_replace('_', '\\_', $creditScore['rewardState'] ?? 'Unknown');
            $response .= "\n📊 *Honor Score* 📊\n";
            $response .= "⭐ *Score*: {$creditScore['creditScore']}\n";
            $response .= "🎁 *Reward State*: {$rewardState}\n";
        }

        // Pet Info
        if (!empty($petInfo)) {
            $response .= "\n🐾 *Pet Info* 🐾\n";
            $response .= "🐶 *Pet ID*: {$petInfo['id']}\n";
            $response .= "🎚️ *Pet Level*: {$petInfo['level']}\n";
            $response .= "🔧 *Selected Skill*: {$petInfo['selectedSkillId']}\n";
        }

        // Diamond Cost
        if (!empty($diamondCost)) {
            $response .= "\n💎 *Diamond Cost* 💎\n";
            $response .= "💰 *Cost*: {$diamondCost['diamondCost']}\n";
        }

        $response .= "\n🔗 *Requested by*: @{$username}\n";

        // Create inline keyboard
        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => 'Join NR Codex 📢', 'url' => 'https://t.me/nr_codex']
                ],
                [
                    ['text' => 'Copy Nickname 📋', 'copy_text' => $basicInfo['nickname'] ?? ''],
                    ['text' => 'Copy UID 📋', 'copy_text' => $basicInfo['accountId'] ?? '']
                ],
                [
                    ['text' => 'Copy Signature 📋', 'copy_text' => $socialInfo['signature'] ?? '']
                ]
            ]
        ];

        // Send response with inline keyboard, replying to the user's message
        sendMessage($chatId, $response, $messageId, 'Markdown', $replyMarkup);

    } else {
        // Invalid command format
        sendMessage($chatId, "❌ Invalid command! 📜 Use: `/get <region> <UID>`\nExample: `/get IND 1234567890`", $messageId);
    }
}
?>

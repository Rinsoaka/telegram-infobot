<?php

// Telegram Bot Token
$botToken = "7599565801:AAH4YdOmS_4tpnU8qIPhTMcDQGng9ak4HdM"; // Your provided bot token
$telegramApi = "https://api.telegram.org/bot$botToken/";

// Function to send messages to Telegram with optional inline keyboard
function sendMessage($chatId, $message, $botToken, $replyMarkup = null) {
    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    if ($replyMarkup) {
        $data['reply_markup'] = json_encode($replyMarkup);
    }
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Function to delete a message
function deleteMessage($chatId, $messageId, $botToken) {
    $url = "https://api.telegram.org/bot$botToken/deleteMessage";
    $data = [
        'chat_id' => $chatId,
        'message_id' => $messageId
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

// Function to fetch player info from the API
function fetchPlayerInfo($uid) {
    $apiUrl = "https://nr-codex-info1.vercel.app/player-info?region=IND&uid=$uid";
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Set timeout to avoid hanging
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$response) {
        return false;
    }
    return json_decode($response, true);
}

// Function to convert Unix timestamp to formatted date
function formatDate($timestamp) {
    return $timestamp ? date("d F Y \a\t H:i:s", $timestamp) : "N/A";
}

// Function to format rank (raw API values)
function formatRank($rank, $points) {
    if (!isset($rank, $points)) return "N/A";
    return "$rank ($points)";
}

// Function to get pet name (raw API ID)
function getPetName($petId) {
    return $petId ?? "N/A";
}

// Function to format the API response
function formatResponse($data, $uid, $userMessage) {
    $account = $data['AccountInfo'] ?? [];
    $profile = $data['AccountProfileInfo'] ?? [];
    $guild = $data['GuildInfo'] ?? [];
    $captain = $data['captainBasicInfo'] ?? [];
    $credit = $data['creditScoreInfo'] ?? [];
    $pet = $data['petInfo'] ?? [];
    $social = $data['socialinfo'] ?? [];

    // Format skills (raw API values)
    $skillsText = !empty($profile['EquippedSkills']) 
        ? implode(", ", $profile['EquippedSkills'])
        : "N/A";

    // Format outfits
    $outfitsText = !empty($profile['EquippedOutfit']) 
        ? implode(", ", $profile['EquippedOutfit'])
        : "N/A";

    // Get current timestamp in IST
    $ist = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    $timestamp = $ist->format('Y-m-d H:i:s');

    // Build response
    $message = "<b>Player Info for: <code>$userMessage</code></b>\n\n";
    
    $message .= "╭─≫ ʙᴀsɪᴄ ɪɴғᴏ ≪\n";
    $message .= "│ 👤 Name: " . ($account['AccountName'] ?? "N/A") . "\n";
    $message .= "│ 🆔 UID: $uid\n";
    $message .= "│ 🎮 Level: " . ($account['AccountLevel'] ?? "N/A") . "\n";
    $message .= "│ 🌍 Region: " . ($account['AccountRegion'] ?? "N/A") . "\n";
    $message .= "│ 👍 Likes: " . ($account['AccountLikes'] ?? "N/A") . "\n";
    $message .= "│ 🏅 Honor Score: " . ($credit['creditScore'] ?? "N/A") . "\n";
    $message .= "│ 🌟 Celebrity: " . (isset($account['AccountType']) && $account['AccountType'] == 1 ? "False" : ($account['AccountType'] ? "True" : "N/A")) . "\n";
    $message .= "│ 🔥 Elite Pass: " . (isset($account['AccountBPID']) && $account['AccountBPID'] ? "Yes" : "No") . "\n";
    $message .= "│ 🎭 Title: " . ($account['Title'] ?? "N/A") . "\n";
    $message .= "│ ✍️ Signature: " . ($social['AccountSignature'] ? str_replace("\n", "\n│     ", $social['AccountSignature']) : "N/A") . "\n";
    $message .= "╰───────────────\n\n";

    $message .= "╭─≫ Account Activity ≪\n";
    $message .= "├─ 🔄 OB: " . ($account['ReleaseVersion'] ?? "N/A") . "\n";
    $message .= "├─ 🎫 Fire Pass: " . (isset($account['AccountType']) && $account['AccountType'] == 1 ? "Free" : ($account['AccountType'] ? "Premium" : "N/A")) . "\n";
    $message .= "├─ 🏆 BP Badges: " . ($account['AccountBPBadges'] ?? "N/A") . "\n";
    $message .= "├─ 🆔 BP ID: " . ($account['AccountBPID'] ?? "N/A") . "\n";
    $message .= "├─ 📈 BR Rank: " . formatRank($account['BrMaxRank'] ?? null, $account['BrRankPoint'] ?? null) . "\n";
    $message .= "├─ 🎯 CS Points: " . ($account['CsRankPoint'] ?? "N/A") . "\n";
    $message .= "├─ 📅 Created: " . formatDate($account['AccountCreateTime'] ?? null) . "\n";
    $message .= "├─ ⏳ Last Login: " . formatDate($account['AccountLastLogin'] ?? null) . "\n";
    $message .= "╰───────────────\n\n";

    $message .= "╭─≫ Overview ≪\n";
    $message .= "├─ 📌 Pin ID: " . ($account['AccountBannerId'] ? "Default" : "N/A") . "\n";
    $message .= "├─ 👕 Outfits: $outfitsText\n";
    $message .= "├─ ⚡ Skills: $skillsText\n";
    $message .= "├─ 🔫 Guns: " . ($account['EquippedWeapon'][0] ?? "N/A") . "\n";
    $message .= "╰───────────────\n\n";

    $message .= "╭─≫ Pet Info ≪\n";
    $message .= "├─ 🐾 Equipped: " . (isset($pet['isSelected']) && $pet['isSelected'] ? "Yes" : "No") . "\n";
    $message .= "├─ 🐕 Name: " . getPetName($pet['id'] ?? null) . "\n";
    $message .= "├─ 🦴 Type: " . getPetName($pet['id'] ?? null) . "\n";
    $message .= "├─ 🎖️ EXP: " . ($pet['exp'] ?? "N/A") . "\n";
    $message .= "├─ 🔼 Level: " . ($pet['level'] ?? "N/A") . "\n";
    $message .= "╰───────────────\n\n";

    $message .= "╭─≫ Guild ≪\n";
    $message .= "├─ 🏰 Name: " . ($guild['GuildName'] ?? "N/A") . "\n";
    $message .= "├─ 🆔 ID: " . ($guild['GuildID'] ?? "N/A") . "\n";
    $message .= "├─ 🎖️ Level: " . ($guild['GuildLevel'] ?? "N/A") . "\n";
    $message .= "├─ 👥 Members: " . ($guild['GuildMember'] ?? "N/A") . "\n";
    $message .= "╰───────────────\n\n";

    $message .= "╭─≫ Leader ≪\n";
    $message .= "├─ 👑 Name: " . ($captain['nickname'] ?? "N/A") . "\n";
    $message .= "├─ 🆔 UID: " . ($captain['accountId'] ?? "N/A") . "\n";
    $message .= "├─ 🎮 Level: " . ($captain['level'] ?? "N/A") . "\n";
    $message .= "├─ 📅 Created At: " . formatDate($captain['createAt'] ?? null) . "\n";
    $message .= "├─ ⏳ Last Login: " . formatDate($captain['lastLoginAt'] ?? null) . "\n";
    $message .= "├─ 🎭 Title: " . ($captain['title'] ?? "N/A") . "\n";
    $message .= "├─ 🏆 Badges: " . ($captain['badgeCnt'] ?? "N/A") . "\n";
    $message .= "├─ 📈 BR Points: " . formatRank($captain['maxRank'] ?? null, $captain['rankingPoints'] ?? null) . "\n";
    $message .= "├─ 🎯 CS Points: " . ($captain['csRankingPoints'] ?? "N/A") . "\n";
    $message .= "╰───────────────\n\n";

    $message .= "╭─≫ ᴏᴡɴᴇʀs ≪\n";
    $message .= "├─ 🎮 NR Codex\n";
    $message .= "╰───────────────\n\n";

    $message .= "╭─≫ Join us ≪\n";
    $message .= "├─ 📱 TELEGRAM GROUP: https://t.me/nr_codex_likegroup\n";
    $message .= "├─ 📸 INSTAGRAM: https://www.instagram.com/nr_codex?igsh=MjZlZWo2cGd3bDVk\n";
    $message .= "╰───────────────\n\n";

    $message .= "🕒 Fetched at (IST): $timestamp IST";

    // Inline keyboard markup
    $replyMarkup = [
        'inline_keyboard' => [
            [
                ['text' => 'TELEGRAM CHANNEL', 'url' => 'https://t.me/nr_codex'],
                ['text' => 'YOUTUBE', 'url' => 'https://youtube.com/@nr_codex06?si=5pbP9qsDLfT4uTgf']
            ]
        ]
    ];

    return ['message' => $message, 'replyMarkup' => $replyMarkup];
}

// Main logic to handle Telegram updates
$update = json_decode(file_get_contents("php://input"), true);

// Check if the update contains a message
if (isset($update['message'])) {
    $chatId = $update['message']['chat']['id'];
    $text = $update['message']['text'];
    $messageId = $update['message']['message_id'];

    // Check if the message starts with "Get" followed by a UID
    if (preg_match('/^Get (\d+)$/', $text, $matches)) {
        $uid = $matches[1];

        // Send processing message
        $processingMessage = sendMessage($chatId, "Fetching info for UID $uid, nickname <b>...</b> in IND...", $botToken);
        $processingMessageId = $processingMessage['result']['message_id'] ?? null;

        $playerData = fetchPlayerInfo($uid);

        if ($playerData && isset($playerData['AccountInfo'])) {
            // Update processing message with nickname
            $nickname = $playerData['AccountInfo']['AccountName'] ?? "N/A";
            deleteMessage($chatId, $processingMessageId, $botToken);
            sendMessage($chatId, "Fetching info for UID $uid, nickname <b>$nickname</b> in IND...", $botToken);
            sleep(1); // Brief delay to show processing message
            deleteMessage($chatId, $processingMessageId + 1, $botToken);

            // Format and send the full response
            $response = formatResponse($playerData, $uid, $text);
            sendMessage($chatId, $response['message'], $botToken, $response['replyMarkup']);
        } else {
            deleteMessage($chatId, $processingMessageId, $botToken);
            sendMessage($chatId, "<b>Error:</b> Unable to fetch player data for UID $uid. Please check the UID or try again later.", $botToken);
        }
    } else {
        sendMessage($chatId, "<b>Invalid Format:</b> Please use the format: <code>Get <UID></code>", $botToken);
    }
} else {
    // Handle non-message updates or invalid requests
    http_response_code(200); // Telegram expects a 200 OK response
}

?>

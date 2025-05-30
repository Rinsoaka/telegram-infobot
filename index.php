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

// Function to format BR rank (raw API values)
function formatBrRank($rank, $points) {
    if (!isset($rank, $points)) return "N/A";
    return "$rank ($points)";
}

// Function to format CS rank (raw API values)
function formatCsRank($rank, $points) {
    if (!isset($rank, $points)) return "N/A";
    return "$rank ($points)";
}

// Function to get pet name (unchanged, as no customization requested for pets)
function getPetName($petId) {
    $pets = [
        1300000111 => "Rockie",
        // Add more pet mappings as needed
    ];
    return $pets[$petId] ?? "Unknown";
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

    // Formatting without ━━━━━━━━━
    $message = "<b>Player Info for: <code>$userMessage</code></b>\n";
    $message .= "<b>≫ ACCOUNT BASIC INFO</b>\n";
    $message .= "• Name: " . ($account['AccountName'] ?? "N/A") . "\n";
    $message .= "• UID: $uid\n";
    $message .= "• Level: " . ($account['AccountLevel'] ?? "N/A") . " (Exp: " . ($account['AccountEXP'] ?? "N/A") . ")\n";
    $message .= "• Region: " . ($account['AccountRegion'] ?? "N/A") . "\n";
    $message .= "• Likes: " . ($account['AccountLikes'] ?? "N/A") . "\n";
    $message .= "• Honor Score: " . ($credit['creditScore'] ?? "N/A") . "\n";
    $message .= "• Celebrity Status: " . (isset($account['AccountType']) && $account['AccountType'] == 1 ? "False" : ($account['AccountType'] ? "True" : "N/A")) . "\n";
    $message .= "• Evo Access Badge: " . (isset($account['AccountBPID']) && $account['AccountBPID'] ? "Active" : "Inactive") . "\n";
    $message .= "• Title: " . ($account['Title'] ?? "Not Found") . "\n";
    $message .= "• Signature: " . ($social['AccountSignature'] ? str_replace("\n", "\n    ", $social['AccountSignature']) : "N/A") . "\n\n";

    $message .= "<b>≫ ACCOUNT ACTIVITY</b>\n";
    $message .= "• Most Recent OB: " . ($account['ReleaseVersion'] ?? "N/A") . "\n";
    $message .= "• Fire Pass: " . (isset($account['AccountType']) && $account['AccountType'] == 1 ? "Basic" : ($account['AccountType'] ? "Premium" : "N/A")) . "\n";
    $message .= "• Current BP Badges: " . ($account['AccountBPBadges'] ?? "N/A") . "\n";
    $message .= "• BR Rank: " . formatBrRank($account['BrMaxRank'] ?? null, $account['BrRankPoint'] ?? null) . "\n";
    $message .= "• CS Rank: " . formatCsRank($account['CsMaxRank'] ?? null, $account['CsRankPoint'] ?? null) . "\n";
    $message .= "• Created At: " . formatDate($account['AccountCreateTime'] ?? null) . "\n";
    $message .= "• Last Login: " . formatDate($account['AccountLastLogin'] ?? null) . "\n\n";

    $message .= "<b>≫ ACCOUNT OVERVIEW</b>\n";
    $message .= "• Avatar ID: " . ($account['AccountAvatarId'] ?? "N/A") . "\n";
    $message .= "• Banner ID: " . ($account['AccountBannerId'] ?? "N/A") . "\n";
    $message .= "• Pin ID: " . ($account['AccountBannerId'] ? "Default" : "N/A") . "\n";
    $message .= "• Equipped Skills: $skillsText\n";
    $message .= "• Equipped Gun ID: " . ($account['EquippedWeapon'][0] ?? "N/A") . "\n";
    $message .= "• Equipped Animation ID: " . ($account['EquippedWeapon'][1] ?? "N/A") . "\n";
    $message .= "• Transform Animation ID: " . ($account['EquippedWeapon'][2] ?? "Not Equipped") . "\n";
    $message .= "• Outfits: " . (!empty($profile['EquippedOutfit']) ? "Graphically Presented Below!" : "N/A") . "\n\n";

    $message .= "<b>≫ PET DETAILS</b>\n";
    $message .= "• Equipped?: " . (isset($pet['isSelected']) && $pet['isSelected'] ? "Yes" : "No") . "\n";
    $message .= "• Pet Name: " . getPetName($pet['id'] ?? null) . "\n";
    $message .= "• Pet Type: " . getPetName($pet['id'] ?? null) . "\n";
    $message .= "• Pet Exp: " . ($pet['exp'] ?? "N/A") . "\n";
    $message .= "• Pet Level: " . ($pet['level'] ?? "N/A") . "\n\n";

    $message .= "<b>≫ GUILD INFO</b>\n";
    $message .= "• Guild Name: " . ($guild['GuildName'] ?? "N/A") . "\n";
    $message .= "• Guild ID: " . ($guild['GuildID'] ?? "N/A") . "\n";
    $message .= "• Guild Level: " . ($guild['GuildLevel'] ?? "N/A") . "\n";
    $message .= "• Live Members: " . ($guild['GuildMember'] ?? "N/A") . "\n";
    $message .= "➤ Leader Info:\n";
    $message .= "    • Leader Name: " . ($captain['nickname'] ?? "N/A") . "\n";
    $message .= "    • Leader UID: " . ($captain['accountId'] ?? "N/A") . "\n";
    $message .= "    • Leader Level: " . ($captain['level'] ?? "N/A") . " (Exp: " . ($captain['exp'] ?? "N/A") . ")\n";
    $message .= "    • Leader Created At: " . formatDate($captain['createAt'] ?? null) . "\n";
    $message .= "    • Leader Last Login: " . formatDate($captain['lastLoginAt'] ?? null) . "\n";
    $message .= "    • Leader Title: " . ($captain['title'] ?? "Not Found") . "\n";
    $message .= "    • Leader Current BP Badges: " . ($captain['badgeCnt'] ?? "N/A") . "\n";
    $message .= "    • Leader BR: " . formatBrRank($captain['maxRank'] ?? null, $captain['rankingPoints'] ?? null) . "\n";
    $message .= "    • Leader CS: " . formatCsRank($captain['csMaxRank'] ?? null, $captain['csRankingPoints'] ?? null) . "\n\n";

    $message .= "<b>≫ PUBLIC CRAFTLAND MAPS</b>\n";
    $message .= "• Not Available\n\n";
    $message .= "<b>➤ JOIN US</b>\n";
    $message .= "• TELEGRAM GROUP: https://t.me/nr_codex_likegroup\n";
    $message .= "• INSTAGRAM: https://www.instagram.com/nr_codex?igsh=MjZlZWo2cGd3bDVk\n";

    // Inline keyboard markup for Telegram Channel and YouTube
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

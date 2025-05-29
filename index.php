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
        'parse_mode' => 'HTML' // Use HTML for formatting
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
    return $response;
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
    return date("d F Y \a\t H:i:s", $timestamp);
}

// Function to format BR rank
function formatBrRank($rank, $points) {
    $ranks = [
        [4200, "Grand Master 🏆"],
        [3700, "Master 🌟"],
        [3200, "Diamond I 💎"],
        [3000, "Diamond II 💎"],
        [2800, "Diamond III 💎"],
        [2600, "Platinum I 🥈"],
        [2400, "Platinum II 🥈"],
        [2200, "Platinum III 🥈"],
        [2000, "Gold I 🥇"],
        [1800, "Gold II 🥇"],
        [1600, "Gold III 🥇"],
        [1400, "Silver I 🥉"],
        [1200, "Silver II 🥉"],
        [1000, "Silver III 🥉"],
        [0, "Bronze 🛡️"]
    ];
    foreach ($ranks as [$threshold, $name]) {
        if ($points >= $threshold) {
            return "$name ($points)";
        }
    }
    return "Unranked ($points) ⚠️";
}

// Function to format CS rank
function formatCsRank($rank, $points) {
    $stars = floor($points / 10); // Assuming 10 points per star
    $ranks = [
        [300, "Master ($stars Star) 🌟"],
        [200, "Ace ($stars Star) 🏅"],
        [100, "Platinum ($stars Star) 💎"],
        [50, "Gold ($stars Star) 🥇"],
        [0, "Bronze ($stars Star) 🛡️"]
    ];
    foreach ($ranks as [$threshold, $name]) {
        if ($points >= $threshold) {
            return $name;
        }
    }
    return "Unranked ($points) ⚠️";
}

// Function to get skill names
function getSkillName($skillId) {
    $skills = [
        16 => "Notora (P) 🚀",
        2403 => "Kelly The Swift (P) ⚡",
        8 => "Dimitri (A) 🛠️",
        6501 => "Sonia (P) 🌸",
        // Add more skill mappings as needed
    ];
    return $skills[$skillId] ?? "Unknown ($skillId) ❓";
}

// Function to get pet name
function getPetName($petId) {
    $pets = [
        1300000111 => "Rockie 🐶",
        // Add more pet mappings as needed
    ];
    return $pets[$petId] ?? "Unknown ❓";
}

// Function to format the API response
function formatResponse($data, $uid, $userMessage) {
    $account = $data['AccountInfo'];
    $profile = $data['AccountProfileInfo'];
    $guild = $data['GuildInfo'];
    $captain = $data['captainBasicInfo'];
    $credit = $data['creditScoreInfo'];
    $pet = $data['petInfo'];
    $social = $data['socialinfo'];

    // Format skills
    $equippedSkills = array_map('getSkillName', array_filter($profile['EquippedSkills'], function($id) {
        return $id > 100 || in_array($id, [1, 2, 3]); // Filter relevant skill IDs
    }));
    $equippedSkills = array_unique($equippedSkills);
    $skillsText = implode(", ", $equippedSkills);

    // Enhanced formatting with user message and emojis
    $message = "<b>🔥 Player Info for: <code>$userMessage</code> 🔥</b>\n";
    $message .= "<b>━━━━━━━━━ ACCOUNT INFO ━━━━━━━━━</b>\n";
    $message .= "<b>≫ ACCOUNT BASIC INFO</b> 👤\n";
    $message .= "• Name: {$account['AccountName']} 😎\n";
    $message .= "• UID: $uid 🆔\n";
    $message .= "• Level: {$account['AccountLevel']} (Exp: {$account['AccountEXP']}) 📊\n";
    $message .= "• Region: {$account['AccountRegion']} 🌍\n";
    $message .= "• Likes: {$account['AccountLikes']} ❤️\n";
    $message .= "• Honor Score: {$credit['creditScore']} 🏅\n";
    $message .= "• Celebrity Status: " . ($account['AccountType'] == 1 ? "False 🚫" : "True ✅") . "\n";
    $message .= "• Evo Access Badge: " . ($account['AccountBPID'] ? "Active ✅" : "Inactive 🚫") . "\n";
    $message .= "• Title: " . ($account['Title'] ?? "Not Found 🚫") . " 🎖️\n";
    $message .= "• Signature: " . ($social['AccountSignature'] ? str_replace("\n", "\n    ", $social['AccountSignature']) : "Not Set 🚫") . " 📝\n\n";

    $message .= "<b>━━━━━━━━━ ACCOUNT ACTIVITY ━━━━━━━━━</b>\n";
    $message .= "<b>≫ ACCOUNT ACTIVITY</b> 🎮\n";
    $message .= "• Most Recent OB: {$account['ReleaseVersion']} 🆕\n";
    $message .= "• Fire Pass: " . ($account['AccountType'] == 1 ? "Basic 📜" : "Premium 💎") . "\n";
    $message .= "• Current BP Badges: {$account['AccountBPBadges']} 🏷️\n";
    $message .= "• BR Rank: " . formatBrRank($account['BrMaxRank'], $account['BrRankPoint']) . "\n";
    $message .= "• CS Rank: " . formatCsRank($account['CsMaxRank'], $account['CsRankPoint']) . "\n";
    $message .= "• Created At: " . formatDate($account['AccountCreateTime']) . " 🕒\n";
    $message .= "• Last Login: " . formatDate($account['AccountLastLogin']) . " 🕒\n\n";

    $message .= "<b>━━━━━━━━━ ACCOUNT OVERVIEW ━━━━━━━━━</b>\n";
    $message .= "<b>≫ ACCOUNT OVERVIEW</b> 👕\n";
    $message .= "• Avatar ID: {$account['AccountAvatarId']} 🖼️\n";
    $message .= "• Banner ID: {$account['AccountBannerId']} 🏳️\n";
    $message .= "• Pin ID: Default 📍\n";
    $message .= "• Equipped Skills: $skillsText 🎯\n";
    $message .= "• Equipped Gun ID: {$account['EquippedWeapon'][0]} 🔫\n";
    $message .= "• Equipped Animation ID: {$account['EquippedWeapon'][1]} 🎬\n";
    $message .= "• Transform Animation ID: Not Equipped 🚫\n";
    $message .= "• Outfits: Graphically Presented Below! 😉 ✨\n\n";

    $message .= "<b>━━━━━━━━━ PET DETAILS ━━━━━━━━━</b>\n";
    $message .= "<b>≫ PET DETAILS</b> 🐾\n";
    $message .= "• Equipped?: " . ($pet['isSelected'] ? "Yes ✅" : "No 🚫") . "\n";
    $message .= "• Pet Name: " . getPetName($pet['id']) . "\n";
    $message .= "• Pet Type: " . getPetName($pet['id']) . "\n";
    $message .= "• Pet Exp: {$pet['exp']} 📈\n";
    $message .= "• Pet Level: {$pet['level']} 🌟\n\n";

    $message .= "<b>━━━━━━━━━ GUILD INFO ━━━━━━━━━</b>\n";
    $message .= "<b>≫ GUILD INFO</b> 🛡️\n";
    $message .= "• Guild Name: {$guild['GuildName']} 🏰\n";
    $message .= "• Guild ID: {$guild['GuildID']} 🆔\n";
    $message .= "• Guild Level: {$guild['GuildLevel']} 📊\n";
    $message .= "• Live Members: {$guild['GuildMember']} 👥\n";
    $message .= "➤ Leader Info:\n";
    $message .= "    • Leader Name: {$captain['nickname']} 👑\n";
    $message .= "    • Leader UID: {$captain['accountId']} 🆔\n";
    $message .= "    • Leader Level: {$captain['level']} (Exp: {$captain['exp']}) 📊\n";
    $message .= "    • Leader Created At: " . formatDate($captain['createAt']) . " 🕒\n";
    $message .= "    • Leader Last Login: " . formatDate($captain['lastLoginAt']) . " 🕒\n";
    $message .= "    • Leader Title: " . ($captain['title'] ?? "Not Found 🚫") . " 🎖️\n";
    $message .= "    • Leader Current BP Badges: {$captain['badgeCnt']} 🏷️\n";
    $message .= "    • Leader BR: " . formatBrRank($captain['maxRank'], $captain['rankingPoints']) . "\n";
    $message .= "    • Leader CS: " . formatCsRank($captain['csMaxRank'], $captain['csRankingPoints']) . "\n\n";

    $message .= "<b>━━━━━━━━━ PUBLIC CRAFTLAND MAPS ━━━━━━━━━</b>\n";
    $message .= "<b>≫ PUBLIC CRAFTLAND MAPS</b> 🗺️\n";
    $message .= "• Not Available 🚫\n\n";
    $message .= "<b>➤ JOIN US</b> 🤝\n";

    // Inline keyboard markup
    $replyMarkup = [
        'inline_keyboard' => [
            [
                ['text' => 'TELEGRAM CHANNEL ⚡', 'url' => 'https://t.me/nr_codex'],
                ['text' => 'TELEGRAM GROUP 🔥', 'url' => 'https://t.me/nr_codex_likegroup']
            ],
            [
                ['text' => 'INSTAGRAM 🔥', 'url' => 'https://www.instagram.com/nr_codex?igsh=MjZlZWo2cGd3bDVk'],
                ['text' => 'YOUTUBE ⚡', 'url' => 'https://youtube.com/@nr_codex06?si=5pbP9qsDLfT4uTgf']
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

    // Check if the message starts with "Get" followed by a UID
    if (preg_match('/^Get (\d+)$/', $text, $matches)) {
        $uid = $matches[1];
        $playerData = fetchPlayerInfo($uid);

        if ($playerData && isset($playerData['AccountInfo'])) {
            // Format the response with the user's message
            $response = formatResponse($playerData, $uid, $text);
            sendMessage($chatId, $response['message'], $botToken, $response['replyMarkup']);
        } else {
            sendMessage($chatId, "⚠️ <b>Error:</b> Unable to fetch player data for UID $uid. Please check the UID or try again later. 🚫", $botToken);
        }
    } else {
        sendMessage($chatId, "⚠️ <b>Invalid Format:</b> Please use the format: <code>Get <UID></code> 📝", $botToken);
    }
} else {
    // Handle non-message updates or invalid requests
    http_response_code(200); // Telegram expects a 200 OK response
}

?>

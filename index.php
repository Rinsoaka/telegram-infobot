<?php

// Telegram Bot Token
$botToken = "7599565801:AAH4YdOmS_4tpnU8qIPhTMcDQGng9ak4HdM";
$apiUrl = "https://api.telegram.org/bot$botToken/";

// Allowed Group ID
$allowedGroupId = "-1002623720889";

// Function to send messages to Telegram
function sendMessage($chatId, $text, $parseMode = 'Markdown', $replyToMessageId = null) {
    global $apiUrl;
    $url = $apiUrl . "sendMessage?" . http_build_query([
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => $parseMode,
        'reply_to_message_id' => $replyToMessageId
    ]);
    file_get_contents($url);
}

// Function to send photos to Telegram
function sendPhoto($chatId, $photoUrl, $caption = '', $parseMode = 'Markdown') {
    global $apiUrl;
    $url = $apiUrl . "sendPhoto?" . http_build_query([
        'chat_id' => $chatId,
        'photo' => $photoUrl,
        'caption' => $caption,
        'parse_mode' => $parseMode
    ]);
    file_get_contents($url);
}

// Function to make API requests
function makeApiRequest($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$response) {
        return false;
    }
    
    return json_decode($response, true);
}

// Function to format timestamp to readable date
function formatTimestamp($timestamp) {
    return date('F j, Y', $timestamp);
}

// Get incoming update from Telegram
$update = json_decode(file_get_contents("php://input"), true);

// Process only message updates
if (isset($update['message'])) {
    $message = $update['message'];
    $chatId = $message['chat']['id'];
    $text = isset($message['text']) ? $message['text'] : '';
    $messageId = $message['message_id'];
    
    // Check if the message is from the allowed group
    if (strval($chatId) !== $allowedGroupId) {
        sendMessage($chatId, "This bot can only be used in the official group: https://t.me/nr_codex");
        exit;
    }
    
    // Process commands
    if (strpos($text, '/get') === 0) {
        $params = array_filter(explode(' ', $text));
        array_shift($params); // Remove command
        
        // Validate input
        if (count($params) !== 2) {
            sendMessage($chatId, "Invalid command format. Use: `/get <region> <UID>`", 'Markdown', $messageId);
            exit;
        }
        
        $region = strtoupper(trim($params[0]));
        $uid = trim($params[1]);
        
        // Validate region (example regions, adjust as needed)
        $validRegions = ['IND', 'BR', 'US', 'EU']; // Add more as per API
        if (!in_array($region, $validRegions)) {
            sendMessage($chatId, "Invalid region. Supported regions: " . implode(', ', $validRegions), 'Markdown', $messageId);
            exit;
        }
        
        // Validate UID (assuming it's numeric)
        if (!is_numeric($uid)) {
            sendMessage($chatId, "Invalid UID. UID must be numeric.", 'Markdown', $messageId);
            exit;
        }
        
        // Make API request for player info
        $playerInfoUrl = "https://aditya-info-v3op.onrender.com/player-info?uid=$uid®ion=$region";
        $playerInfo = makeApiRequest($playerInfoUrl);
        
        if (!$playerInfo) {
            sendMessage($chatId, "API error. Please try again later or contact OWNER @nilay_vii", 'Markdown', $messageId);
            exit;
        }
        
        // Extract data
        $basicInfo = $playerInfo['basicInfo'] ?? [];
        $clanInfo = $playerInfo['clanBasicInfo'] ?? [];
        $creditScoreInfo = $playerInfo['creditScoreInfo'] ?? [];
        $diamondCost = $playerInfo['diamondCostRes'] ?? [];
        $petInfo = $playerInfo['petInfo'] ?? [];
        $profileInfo = $playerInfo['profileInfo'] ?? [];
        $socialInfo = $playerInfo['socialInfo'] ?? [];
        
        // Format message
        $response = "📋 *Basic Information*\n";
        $response .= "Account ID: `{$basicInfo['accountId']}`\n";
        $response .= "Nickname: {$basicInfo['nickname']}\n";
        $response .= "Region: {$basicInfo['region']} 🇮🇳\n";
        $response .= "Level: {$basicInfo['level']} 🎮\n";
        $response .= "Experience (EXP): " . number_format($basicInfo['exp']) . " 🥳\n";
        $response .= "Likes Received: " . number_format($basicInfo['liked']) . " ❤️\n";
        $response .= "Created At: " . formatTimestamp($basicInfo['createAt']) . " 🕒\n";
        $response .= "Last Login: " . formatTimestamp($basicInfo['lastLoginAt']) . " ⏰\n";
        $response .= "Release Version: {$basicInfo['releaseVersion']} 🚀\n";
        $response .= "Season ID: {$basicInfo['seasonId']} 🏆\n";
        $response .= "Title ID: {$basicInfo['title']} 🏅\n";
        $response .= "Pin ID: {$basicInfo['pinId']} 📍\n";
        $response .= "Badge ID: {$basicInfo['badgeId']} (Count: {$basicInfo['badgeCnt']}) 🎖️\n\n";
        
        $response .= "🏅 *Rank Details*\n";
        $response .= "Current Rank: {$basicInfo['rank']}\n";
        $response .= "Max Rank Achieved: {$basicInfo['maxRank']}\n";
        $response .= "Ranking Points: " . number_format($basicInfo['rankingPoints']) . "\n";
        $response .= "CS Rank: {$basicInfo['csRank']}\n";
        $response .= "CS Max Rank: {$basicInfo['csMaxRank']}\n";
        $response .= "CS Ranking Points: {$basicInfo['csRankingPoints']}\n";
        $response .= "Show Ranks: Battle Royale (BR), Clash Squad (CS)\n\n";
        
        $response .= "🛡️ *Clan Information*\n";
        $response .= "Clan Name: {$clanInfo['clanName']}\n";
        $response .= "Clan ID: {$clanInfo['clanId']}\n";
        $response .= "Clan Level: {$clanInfo['clanLevel']} 🥉\n";
        $response .= "Captain ID: {$clanInfo['captainId']} 👑\n";
        $response .= "Members: {$clanInfo['memberNum']}/{$clanInfo['capacity']} (Full capacity!)\n";
        $response .= "Credit Score: {$creditScoreInfo['creditScore']} 🌟\n";
        $response .= "Reward State: {$creditScoreInfo['rewardState']} 🎁\n";
        $response .= "Periodic Summary End Time: " . formatTimestamp($creditScoreInfo['periodicSummaryEndTime']) . " 📅\n\n";
        
        // Placeholder for clan members (add API here if available)
        $response .= "👥 *Clan Members*\n";
        $response .= "Member list not available. Contact OWNER @nilay_vii for updates.\n\n";
        
        $response .= "💎 *Diamond on Account*\n";
        $response .= "Diamond: {$diamondCost['diamondCost']} 💎\n\n";
        
        $response .= "🐾 *Pet Information*\n";
        $response .= "Pet ID: {$petInfo['id']}\n";
        $response .= "Level: {$petInfo['level']} 🐾\n";
        $response .= "Experience: {$petInfo['exp']}\n";
        $response .= "Selected: " . ($petInfo['isSelected'] ? 'Yes ✅' : 'No') . "\n";
        $response .= "Selected Skill ID: {$petInfo['selectedSkillId']} ⚡\n";
        $response .= "Skin ID: {$petInfo['skinId']} 🎨\n\n";
        
        $response .= "👟 *Equipped Skills* (IDs)\n";
        $skills = array_chunk($profileInfo['equipedSkills'], 4);
        foreach ($skills as $index => $skillSet) {
            $response .= "Slot " . ($index + 1) . ": " . implode(', ', $skillSet) . "\n";
        }
        $response .= "Selected: " . ($profileInfo['isSelected'] ? 'Yes ✅' : 'No') . "\n\n";
        
        $response .= "🌐 *Social Information*\n";
        $response .= "Account ID: {$socialInfo['accountId']}\n";
        $response .= "Gender: {$socialInfo['gender']} " . ($socialInfo['gender'] === 'Gender_MALE' ? '♂️' : '♀️') . "\n";
        $response .= "Language: {$socialInfo['language']} 🇬🇧\n";
        $response .= "Rank Show Preference: {$socialInfo['rankShow']} 🏆\n";
        $response .= "Signature: `{$socialInfo['signature']}`\n\n";
        
        // Send player info
        sendMessage($chatId, $response, 'Markdown', $messageId);
        
        // Send thank you message
        sendMessage($chatId, "Thanks for using Nr Codex Info Bot!", 'Markdown', $messageId);
        
        // Fetch and send banner image
        $bannerUrl = "https://aditya-banner-v3op.onrender.com/banner-image?uid=$uid®ion=$region";
        $bannerResponse = makeApiRequest($bannerUrl);
        if ($bannerResponse && isset($bannerResponse['imageUrl'])) {
            sendPhoto($chatId, $bannerResponse['imageUrl'], "Banner Image", 'Markdown');
        } else {
            sendMessage($chatId, "Failed to fetch banner image. Please try again later or contact OWNER @nilay_vii", 'Markdown', $messageId);
        }
        
        // Fetch and send images for various IDs
        $imageIds = [
            'Pin ID' => $basicInfo['pinId'],
            'Skin ID' => $petInfo['skinId'],
            'Selected Skill ID' => $petInfo['selectedSkillId'],
            'Pet ID' => $petInfo['id'],
            'Clan ID' => $clanInfo['clanId'],
            'Captain ID' => $clanInfo['captainId'],
            'Title ID' => $basicInfo['title']
        ];
        
        foreach ($imageIds as $label => $id) {
            $imageUrl = "https://aditya-image-v3op.onrender.com/image?id=$id";
            $imageResponse = makeApiRequest($imageUrl);
            if ($imageResponse && isset($imageResponse['imageUrl'])) {
                sendPhoto($chatId, $imageResponse['imageUrl'], "$label Image", 'Markdown');
            } else {
                sendMessage($chatId, "Failed to fetch $label image. Please try again later or contact OWNER @nilay_vii", 'Markdown', $messageId);
            }
        }
        
        // Note: The 4th API for outfit image was mentioned but not provided. If available, add similar logic here.
    }
    
    // Handle /like command (placeholder, as no API for sending likes was provided)
    if (strpos($text, '/like') === 0) {
        sendMessage($chatId, "Like feature not implemented yet. Contact OWNER @nilay_vii for updates.", 'Markdown', $messageId);
    }
}

?>

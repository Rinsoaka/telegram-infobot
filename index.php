<?php

// Telegram Bot Token
$botToken = "7599565801:AAH4YdOmS_4tpnU8qIPhTMcDQGng9ak4HdM";

// Allowed Group ID
$allowedGroupId = "-1002623720889";

// Telegram API URL
$telegramApi = "https://api.telegram.org/bot$botToken/";

// Function to send messages to Telegram
function sendMessage($chatId, $text, $parseMode = 'Markdown') {
    global $telegramApi;
    $url = $telegramApi . "sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => $parseMode
    ];
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data)
        ]
    ];
    $context = stream_context_create($options);
    file_get_contents($url, false, $context);
}

// Function to make GET request to external API
function getPlayerInfo($region, $uid) {
    $apiUrl = "https://aditya-info-v3op.onrender.com/player-info?uid=$uid®ion=$region";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        return json_decode($response, true);
    }
    return false;
}

// Function to validate region
function isValidRegion($region) {
    $validRegions = ['IND', 'BR', 'ID', 'VN', 'TH', 'SG', 'MY', 'PH', 'ME', 'US', 'EU']; // Adjust as per API
    return in_array(strtoupper($region), $validRegions);
}

// Read incoming update from Telegram
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
    exit;
}

// Process message
if (isset($update['message'])) {
    $message = $update['message'];
    $chatId = $message['chat']['id'];
    $text = $message['text'] ?? '';
    $userId = $message['from']['id'];

    // Check if message is from the allowed group
    if (strval($chatId) !== $allowedGroupId) {
        sendMessage($chatId, "🚫 This bot only works in the specified group: [Join Here](https://t.me/nr_codex)");
        exit;
    }

    // Handle /get command
    if (preg_match('/^\/get\s+([a-zA-Z]+)\s+(\d+)$/', $text, $matches)) {
        $region = strtoupper($matches[1]);
        $uid = $matches[2];

        // Validate region
        if (!isValidRegion($region)) {
            sendMessage($chatId, "❌ Invalid region! Valid regions: *IND, BR, ID, VN, TH, SG, MY, PH, ME, US, EU*");
            exit;
        }

        // Validate UID (basic check for digits)
        if (!ctype_digit($uid)) {
            sendMessage($chatId, "❌ Invalid UID! UID must be numeric.");
            exit;
        }

        // Fetch player info from API
        $playerData = getPlayerInfo($region, $uid);

        if ($playerData && isset($playerData['basicInfo'])) {
            $basicInfo = $playerData['basicInfo'];
            $clanInfo = $playerData['clanBasicInfo'] ?? [];
            $socialInfo = $playerData['socialInfo'] ?? [];
            $creditScore = $playerData['creditScoreInfo'] ?? [];
            $petInfo = $playerData['petInfo'] ?? [];
            $diamondCost = $playerData['diamondCostRes'] ?? [];
            $profileInfo = $playerData['profileInfo'] ?? [];

            // Format response using Markdown with emojis and sections
            $response = "🎮 *Free Fire Player Info* 🎮\n\n";

            // Basic Info Section
            $response .= "📋 *Basic Info* 📋\n";
            $response .= "👤 *Nickname*: `" . ($basicInfo['nickname'] ?? 'N/A') . "`\n";
            $response .= "🆔 *Account ID*: `" . ($basicInfo['accountId'] ?? 'N/A') . "`\n";
            $response .= "🌍 *Region*: `" . ($basicInfo['region'] ?? 'N/A') . "`\n";
            $response .= "📈 *Level*: `" . ($basicInfo['level'] ?? 'N/A') . "`\n";
            $response .= "❤️ *Likes*: `" . ($basicInfo['liked'] ?? 'N/A') . "`\n";
            $response .= "🏆 *BR Rank*: `" . ($basicInfo['rank'] ?? 'N/A') . "` (Points: " . ($basicInfo['rankingPoints'] ?? 'N/A') . ")\n";
            $response .= "🎯 *CS Rank*: `" . ($basicInfo['csRank'] ?? 'N/A') . "` (Points: " . ($basicInfo['csRankingPoints'] ?? 'N/A') . ")\n";
            $response .= "🔝 *Max BR Rank*: `" . ($basicInfo['maxRank'] ?? 'N/A') . "`\n";
            $response .= "🎖️ *Max CS Rank*: `" . ($basicInfo['csMaxRank'] ?? 'N/A') . "`\n";
            $response .= "🎫 *Account Type*: `" . ($basicInfo['accountType'] ?? 'N/A') . "`\n";
            $response .= "🏅 *Badge Count*: `" . ($basicInfo['badgeCnt'] ?? 'N/A') . "`\n";
            $response .= "🛡️ *Badge ID*: `" . ($basicInfo['badgeId'] ?? 'N/A') . "`\n";
            $response .= "📅 *Created At*: `" . date('Y-m-d H:i:s', $basicInfo['createAt'] ?? time()) . "`\n";
            $response .= "⏰ *Last Login*: `" . date('Y-m-d H:i:s', $basicInfo['lastLoginAt'] ?? time()) . "`\n";
            $response .= "🔥 *EXP*: `" . ($basicInfo['exp'] ?? 'N/A') . "`\n";
            $response .= "🎨 *Pin ID*: `" . ($basicInfo['pinId'] ?? 'N/A') . "`\n";
            $response .= "🏆 *Title*: `" . ($basicInfo['title'] ?? 'N/A') . "`\n";
            $response .= "📦 *Release Version*: `" . ($basicInfo['releaseVersion'] ?? 'N/A') . "`\n";
            $response .= "📜 *Season ID*: `" . ($basicInfo['seasonId'] ?? 'N/A') . "`\n";
            $response .= "👁️ *Show BR Rank*: `" . ($basicInfo['showBrRank'] ? 'Yes' : 'No') . "`\n";
            $response .= "👁️ *Show CS Rank*: `" . ($basicInfo['showCsRank'] ? 'Yes' : 'No') . "`\n";
            $response .= "👁️ *Show Rank*: `" . ($basicInfo['showRank'] ? 'Yes' : 'No') . "`\n";
            $response .= "🔗 *External Icon*: `" . ($basicInfo['externalIconInfo']['showType'] ?? 'N/A') . " (" . ($basicInfo['externalIconInfo']['status'] ?? 'N/A') . ")`\n";

            // Clan Info Section
            if (!empty($clanInfo)) {
                $response .= "\n🏰 *Clan Info* 🏰\n";
                $response .= "📛 *Clan Name*: `" . ($clanInfo['clanName'] ?? 'N/A') . "`\n";
                $response .= "🆔 *Clan ID*: `" . ($clanInfo['clanId'] ?? 'N/A') . "`\n";
                $response .= "🔝 *Clan Level*: `" . ($clanInfo['clanLevel'] ?? 'N/A') . "`\n";
                $response .= "👥 *Members*: `" . ($clanInfo['memberNum'] ?? 'N/A') . "/" . ($clanInfo['capacity'] ?? 'N/A') . "`\n";
                $response .= "👑 *Captain ID*: `" . ($clanInfo['captainId'] ?? 'N/A') . "`\n";
            }

            // Social Info Section
            if (!empty($socialInfo)) {
                $response .= "\n🌐 *Social Info* 🌐\n";
                $response .= "🚻 *Gender*: `" . ($socialInfo['gender'] ?? 'N/A') . "`\n";
                $response .= "🗣️ *Language*: `" . ($socialInfo['language'] ?? 'N/A') . "`\n";
                $response .= "🏆 *Rank Show*: `" . ($socialInfo['rankShow'] ?? 'N/A') . "`\n";
                $response .= "✍️ *Signature*: `" . ($socialInfo['signature'] ?? 'N/A') . "`\n";
            }

            // Credit Score Section
            if (!empty($creditScore)) {
                $response .= "\n⭐ *Credit Score* ⭐\n";
                $response .= "📊 *Score*: `" . ($creditScore['creditScore'] ?? 'N/A') . "`\n";
                $response .= "⏳ *Summary End Time*: `" . date('Y-m-d H:i:s', $creditScore['periodicSummaryEndTime'] ?? time()) . "`\n";
                $response .= "🎁 *Reward State*: `" . ($creditScore['rewardState'] ?? 'N/A') . "`\n";
            }

            // Diamond Cost Section
            if (!empty($diamondCost)) {
                $response .= "\n💎 *Diamond Cost* 💎\n";
                $response .= "💰 *Diamond Cost*: `" . ($diamondCost['diamondCost'] ?? 'N/A') . "`\n";
            }

            // Pet Info Section
            if (!empty($petInfo)) {
                $response .= "\n🐾 *Pet Info* 🐾\n";
                $response .= "🦁 *Pet ID*: `" . ($petInfo['id'] ?? 'N/A') . "`\n";
                $response .= "🔼 *Level*: `" . ($petInfo['level'] ?? 'N/A') . "`\n";
                $response .= "🔥 *EXP*: `" . ($petInfo['exp'] ?? 'N/A') . "`\n";
                $response .= "✅ *Selected*: `" . ($petInfo['isSelected'] ? 'Yes' : 'No') . "`\n";
                $response .= "🛠️ *Selected Skill ID*: `" . ($petInfo['selectedSkillId'] ?? 'N/A') . "`\n";
                $response .= "🎨 *Skin ID*: `" . ($petInfo['skinId'] ?? 'N/A') . "`\n";
            }

            // Profile Info Section
            if (!empty($profileInfo)) {
                $response .= "\n🎭 *Profile Info* 🎭\n";
                $response .= "🖼️ *Avatar ID*: `" . ($profileInfo['avatarId'] ?? 'N/A') . "`\n";
                $response .= "👗 *Clothes*: `" . (implode(', ', $profileInfo['clothes'] ?? ['N/A'])) . "`\n";
                $response .= "⚡ *Equipped Skills*: `" . (implode(', ', $profileInfo['equipedSkills'] ?? ['N/A'])) . "`\n";
                $response .= "✅ *Selected*: `" . ($profileInfo['isSelected'] ? 'Yes' : 'No') . "`\n";
                $response .= "🌟 *Selected Awaken*: `" . ($profileInfo['isSelectedAwaken'] ? 'Yes' : 'No') . "`\n";
                $response .= "🔓 *Unlock Time*: `" . date('Y-m-d H:i:s', $profileInfo['unlockTime'] ?? time()) . "`\n";
            }

            $response .= "\n🔗 *Fetched by NR Codex Bot* | [Join Us](https://t.me/nr_codex)";

            sendMessage($chatId, $response);
        } else {
            sendMessage($chatId, "⚠️ Failed to fetch player info. Please check UID/Region or try again later.");
        }
    } else {
        sendMessage($chatId, "ℹ️ *Usage*: `/get <region> <UID>`\n*Example*: `/get IND 7669969208`\n\n🔍 Valid regions: *IND, BR, ID, VN, TH, SG, MY, PH, ME, US, EU*");
    }
}

?>

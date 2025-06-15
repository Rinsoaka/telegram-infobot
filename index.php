<?php
// inde.php

// Set Telegram Bot Token
define('BOT_TOKEN', '7599565801:AAH4YdOmS_4tpnU8qIPhTMcDQGng9ak4HdM');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');

// Function to send HTTP GET request
function httpGet($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Function to send message to Telegram
function sendMessage($chat_id, $text, $reply_to_message_id = null) {
    $url = API_URL . "sendMessage";
    $post_fields = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML',
        'reply_to_message_id' => $reply_to_message_id
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

// Function to format timestamp to readable date
function formatTimestamp($timestamp) {
    return date("Y-m-d H:i:s", $timestamp);
}

// Function to get player info
function getPlayerInfo($uid, $region) {
    $url = "https://nr-codex-info1.vercel.app/player-info?region=$region&uid=$uid";
    return httpGet($url);
}

// Function to get region info
function getRegionInfo($uid) {
    $url = "https://nr-codex-regioncheck-api.vercel.app/region-info/$uid";
    return httpGet($url);
}

// Function to format player info response
function formatPlayerInfo($data) {
    $account = $data['AccountInfo'];
    $pet = $data['petInfo'];
    $guild = $data['GuildInfo'];
    $leader = $data['captainBasicInfo'];
    $credit = $data['creditScoreInfo'];
    $profile = $data['AccountProfileInfo'];
    $social = $data['socialinfo'];

    $response = "👤 <b>Account Info</b>\n";
    $response .= "├Name: {$account['AccountName']}\n";
    $response .= "├🆔 UID: N/A\n";
    $response .= "├🗿 Level: {$account['AccountLevel']}\n";
    $response .= "├🚩 Region: {$account['AccountRegion']}\n";
    $response .= "├❤️ Likes: {$account['AccountLikes']}\n";
    $response .= "├🎖️ Title ID: {$account['Title']}\n";
    $response .= "├🧷 Badge ID: {$account['AccountBPID']}\n";
    $response .= "├🧠 Max Rank: {$account['BrMaxRank']}\n";
    $response .= "├🥵 BR Rank: {$account['BrRankPoint']}\n";
    $response .= "├🥶 CS Rank: {$account['CsMaxRank']}\n";
    $response .= "├☠️ CS Points: {$account['CsRankPoint']}\n";
    $response .= "├⏱️ Created At: " . formatTimestamp($account['AccountCreateTime']) . "\n";
    $response .= "└⌛ Last Login: " . formatTimestamp($account['AccountLastLogin']) . "\n\n";

    $response .= "🐾 <b>Pet Info</b>\n";
    $response .= "├🐶 Pet ID: {$pet['id']}\n";
    $response .= "├🎯 Skill ID: {$pet['selectedSkillId']}\n";
    $response .= "├🎨 Skin ID: {$pet['skinId']}\n";
    $response .= "├🎊 Level: {$pet['level']}\n";
    $response .= "└✨ Exp: {$pet['exp']}\n\n";

    $response .= "👥 <b>Guild Info</b>\n";
    $response .= "├🗿 Name: {$guild['GuildName']}\n";
    $response .= "├🆔 Guild ID: {$guild['GuildID']}\n";
    $response .= "├🧬 Level: {$guild['GuildLevel']}\n";
    $response .= "└👥 Members: {$guild['GuildMember']}/{$guild['GuildCapacity']}\n\n";

    $response .= "👤 <b>Guild Leader Info</b>\n";
    $response .= "├🗿 Name: {$leader['nickname']}\n";
    $response .= "├🆔 UID: {$leader['accountId']}\n";
    $response .= "├🧬 Level: {$leader['level']}\n";
    $response .= "└❤️ LIKES: {$leader['liked']}\n\n";

    $response .= "💠 <b>Credit Score</b>\n";
    $response .= "├💯 Score: {$credit['creditScore']}\n";
    $response .= "├🚦 Status: {$credit['rewardState']}\n";
    $response .= "├📆 From: " . formatTimestamp($credit['periodicSummaryEndTime'] - 3*24*3600) . "\n";
    $response .= "└⏳ Until: " . formatTimestamp($credit['periodicSummaryEndTime']) . "\n\n";

    $response .= "👤 <b>Profile Info</b>\n";
    $response .= "├🖼️ Avatar & Banner: Shown Graphically\n";
    $response .= "├🧩 Character & Costume: Shown Graphically\n";
    $response .= "├🎯 Skills: " . implode(", ", $profile['EquippedSkills']) . "\n";
    $response .= "└🔫 Weapon Skins: " . (empty($leader['weaponSkinShows']) ? "N/A" : implode(", ", $leader['weaponSkinShows'])) . "\n\n";

    $response .= "🌐 <b>Social Info</b>\n";
    $response .= "├🌍 Language: {$social['language']}\n";
    $response .= "├🙎 Gender: {$social['gender']}\n";
    $response .= "├🔐 Privacy: " . ($social['rankShow'] == "RankShow_BR" ? "OPEN" : "CLOSED") . "\n";
    $response .= "└📝 Bio: {$social['signature']}\n\n";

    $response .= "🎗️ <b>BOT DEVELOPER</b>\n";
    $response .= "└👑 @NR_CODEX";

    return $response;
}

// Function to format region info response
function formatRegionInfo($data) {
    $response = "👤 <b>Region Info</b>\n";
    $response .= "├🗿 Name: {$data['AccountName']}\n";
    $response .= "├🆔 UID: N/A\n";
    $response .= "├🧬 Level: {$data['AccountLevel']}\n";
    $response .= "├🚩 Region: {$data['AccountRegion']}\n";
    $response .= "├❤️ Likes: {$data['AccountLikes']}\n";
    $response .= "├👥 Guild: {$data['GuildName']}\n";
    $response .= "├📱 Version: {$data['ReleaseVersion']}\n";
    $response .= "└⌛ Last Login: " . formatTimestamp($data['AccountLastLogin']) . "\n\n";
    $response .= "🎗️ <b>BOT DEVELOPER</b>\n";
    $response .= "└👑 @NR_CODEX";
    return $response;
}

// Main bot logic
$update = json_decode(file_get_contents("php://input"), true);
if (isset($update['message'])) {
    $message = $update['message'];
    $chat_id = $message['chat']['id'];
    $text = $message['text'];
    $reply_to_message_id = $message['message_id'];

    // Handle /get command
    if (preg_match('/^\/get\s+(\d+)/i', $text, $matches)) {
        $uid = $matches[1];

        // First check region
        $region_data = getRegionInfo($uid);
        if (isset($region_data['AccountRegion'])) {
            $region = $region_data['AccountRegion'];
            // Try IND region first
            $data = getPlayerInfo($uid, 'IND');
            if (isset($data['error'])) {
                // If IND fails, try BD region
                $data = getPlayerInfo($uid, 'BD');
            }
            if (!isset($data['error'])) {
                $response = formatPlayerInfo($data);
                sendMessage($chat_id, $response, $reply_to_message_id);
            } else {
                sendMessage($chat_id, "Error: Unable to fetch player info for UID $uid", $reply_to_message_id);
            }
        } else {
            sendMessage($chat_id, "Error: Unable to determine region for UID $uid", $reply_to_message_id);
        }
    }

    // Handle /region command
    if (preg_match('/^\/region\s+(\d+)/i', $text, $matches)) {
        $uid = $matches[1];
        $data = getRegionInfo($uid);
        if (isset($data['AccountName'])) {
            $response = formatRegionInfo($data);
            sendMessage($chat_id, $response, $reply_to_message_id);
        } else {
            sendMessage($chat_id, "Error: Unable to fetch region info for UID $uid", $reply_to_message_id);
        }
    }
}
?>

<?php
// Telegram Bot Token 🌟
define('BOT_TOKEN', '7599565801:AAH4YdOmS_4tpnU8qIPhTMcDQGng9ak4HdM');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');

// Allowed Group ID 🔒
define('ALLOWED_GROUP_ID', '-1002623720889');

// Channel Link 📢
define('CHANNEL_LINK', 'https://t.me/nr_codex');

// Function to send HTTP requests 🚀
function sendRequest($method, $params) {
    $url = API_URL . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response);
}

// Function to fetch player info from external API 🌐
function getPlayerInfo($uid, $region) {
    $url = "https://aditya-info-v3op.onrender.com/player-info?uid={$uid}®ion={$region}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Function to send message 💬
function sendMessage($chat_id, $text, $reply_to_message_id = null, $reply_markup = null) {
    $params = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'Markdown',
    ];
    if ($reply_to_message_id) {
        $params['reply_to_message_id'] = $reply_to_message_id;
    }
    if ($reply_markup) {
        $params['reply_markup'] = json_encode($reply_markup);
    }
    return sendRequest('sendMessage', $params);
}

// Webhook handler 🎮
$update = json_decode(file_get_contents('php://input'), true);

if (isset($update['message'])) {
    $message = $update['message'];
    $chat_id = $message['chat']['id'];
    $text = $message['text'] ?? '';
    $message_id = $message['message_id'];

    // Check if the message is from the allowed group 🔐
    if ($chat_id != ALLOWED_GROUP_ID) {
        sendMessage($chat_id, "❌ *Access Denied!* 🚫 This bot is exclusive to the specified group! 😎", $message_id);
        exit;
    }

    // Check if the message is a command 📝
    if (strpos($text, '/get') === 0) {
        $parts = explode(' ', $text);
        if (count($parts) !== 3) {
            sendMessage($chat_id, "⚠️ *Oops!* 😕 Invalid format! Use: `/get <region> <UID>`\nExample: `/get IND 1234567890` 📋", $message_id);
            exit;
        }

        $region = strtoupper($parts[1]);
        $uid = $parts[2];

        // Validate region and UID 🕵️‍♂️
        $valid_regions = ['IND', 'BR', 'ID', 'TH', 'SG', 'ME', 'VN', 'TW', 'PK', 'BD', 'MY', 'PH', 'NP', 'LK', 'KH', 'MM', 'LA', 'MN'];
        if (!in_array($region, $valid_regions)) {
            sendMessage($chat_id, "⚠️ *Invalid Region!* 🌍 Valid regions: `" . implode(', ', $valid_regions) . "` 😤", $message_id);
            exit;
        }
        if (!is_numeric($uid) || strlen($uid) < 6) {
            sendMessage($chat_id, "⚠️ *Invalid UID!* 🔢 UID must be a number with at least 6 digits! 😬", $message_id);
            exit;
        }

        // Fetch player info from API 🚀
        $player_info = getPlayerInfo($uid, $region);

        if (!$player_info || isset($player_info['error'])) {
            sendMessage($chat_id, "❌ *Error!* 😭 Unable to fetch player info. Check UID/Region or try again later! 🔄", $message_id);
            exit;
        }

        // Parse player info 🧠
        $basic_info = $player_info['basicInfo'] ?? [];
        $clan_info = $player_info['clanBasicInfo'] ?? [];
        $credit_info = $player_info['creditScoreInfo'] ?? [];
        $diamond_info = $player_info['diamondCostRes'] ?? [];
        $pet_info = $player_info['petInfo'] ?? [];
        $profile_info = $player_info['profileInfo'] ?? [];
        $social_info = $player_info['socialInfo'] ?? [];

        // Format response with tons of emojis! 🎉
        $response = "🎮 *Free Fire Player Info* 🎮\n\n";
        $response .= "🌟 *Basic Info* 📋\n";
        $response .= "- Nickname: `{$basic_info['nickname']}` 😎\n";
        $response .= "- Account ID: `{$basic_info['accountId']}` 🔢\n";
        $response .= "- Region: `{$basic_info['region']}` 🌍\n";
        $response .= "- Level: `{$basic_info['level']}` 📈\n";
        $response .= "- Likes: `{$basic_info['liked']}` ❤️👍\n";
        $response .= "- BR Rank: `{$basic_info['rank']}` (Max: `{$basic_info['maxRank']}`) 🏆\n";
        $response .= "- CS Rank: `{$basic_info['csRank']}` (Max: `{$basic_info['csMaxRank']}`) 🎯\n";
        $response .= "- EXP: `{$basic_info['exp']}` 💪\n";
        $response .= "- Last Login: `" . date('Y-m-d H:i:s', $basic_info['lastLoginAt']) . "` ⏰\n";
        $response .= "- Created At: `" . date('Y-m-d H:i:s', $basic_info['createAt']) . "` 📅\n";
        $response .= "- Title ID: `{$basic_info['title']}` 🏅\n";
        $response .= "- Badge Count: `{$basic_info['badgeCnt']}` 🎖️\n";

        if (!empty($clan_info)) {
            $response .= "\n🛡️ *Clan Info* ⚔️\n";
            $response .= "- Clan Name: `{$clan_info['clanName']}` 🏰\n";
            $response .= "- Clan ID: `{$clan_info['clanId']}` 🔢\n";
            $response .= "- Clan Level: `{$clan_info['clanLevel']}` 📈\n";
            $response .= "- Members: `{$clan_info['memberNum']}/{$clan_info['capacity']}` 👥\n";
            $response .= "- Captain ID: `{$clan_info['captainId']}` 👑\n";
        }

        if (!empty($credit_info)) {
            $response .= "\n⭐ *Credit Score* ✨\n";
            $response .= "- Score: `{$credit_info['creditScore']}` 🌟\n";
            $response .= "- Reward State: `{$credit_info['rewardState']}` 🎁\n";
        }

        if (!empty($diamond_info)) {
            $response .= "\n💎 *Diamond Cost* 💰\n";
            $response .= "- Diamond Cost: `{$diamond_info['diamondCost']}` 💎\n";
        }

        if (!empty($pet_info)) {
            $response .= "\n🐾 *Pet Info* 🐶\n";
            $response .= "- Pet ID: `{$pet_info['id']}` 🐕\n";
            $response .= "- Level: `{$pet_info['level']}` 📈\n";
            $response .= "- EXP: `{$pet_info['exp']}` 💪\n";
            $response .= "- Selected: `" . ($pet_info['isSelected'] ? 'Yes' : 'No') . "` ✅\n";
            $response .= "- Skill ID: `{$pet_info['selectedSkillId']}` ⚡\n";
            $response .= "- Skin ID: `{$pet_info['skinId']}` 🎨\n";
        }

        if (!empty($profile_info)) {
            $response .= "\n👤 *Profile Info* 😎\n";
            $response .= "- Avatar ID: `{$profile_info['avatarId']}` 🖼️\n";
            $response .= "- Clothes: `" . implode(', ', $profile_info['clothes']) . "` 👗\n";
            $response .= "- Skills: `" . implode(', ', $profile_info['equipedSkills']) . "` ⚡\n";
            $response .= "- Selected: `" . ($profile_info['isSelected'] ? 'Yes' : 'No') . "` ✅\n";
            $response .= "- Awaken: `" . ($profile_info['isSelectedAwaken'] ? 'Yes' : 'No') . "` 🌟\n";
        }

        if (!empty($social_info)) {
            $response .= "\n🌐 *Social Info* 📱\n";
            $response .= "- Gender: `{$social_info['gender']}` 🚻\n";
            $response .= "- Language: `{$social_info['language']}` 🗣️\n";
            $response .= "- Rank Show: `{$social_info['rankShow']}` 🏆\n";
            $response .= "- Signature: `{$social_info['signature']}` ✍️\n";
        }

        // Inline button for channel 📢
        $reply_markup = [
            'inline_keyboard' => [
                [
                    [
                        'text' => 'Join Our Channel! 🚀🎉',
                        'url' => CHANNEL_LINK
                    ]
                ]
            ]
        ];

        // Send response 💬
        sendMessage($chat_id, $response, $message_id, $reply_markup);
    }
}
?>

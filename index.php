<?php

use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\Parts\WebSockets\VoiceServerUpdate;
use Discord\Parts\WebSockets\VoiceStateUpdate;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$discord = new Discord([
    'token' => $_ENV['DISCORD_TOKEN']
]);

$discord->on('ready', function ($discord) {
    echo "Bot is online!" . PHP_EOL;
});

$discord->on(Event::VOICE_STATE_UPDATE, function (VoiceStateUpdate $update, Discord $discord, $oldState) {
    $textChannelId = $_ENV['TEXT_CHANNEL_ID'];
    $guildId = $_ENV['GUILD_ID'];

    if ($update->guild_id === $guildId) {
        $guild = $discord->guilds->get('id', $update->guild_id);
        $textChannel = $guild->channels->get('id', $textChannelId);

        if ($oldState === null && $update->channel_id !== null) {
            $channel = $guild->channels->get('id', $update->channel_id);
            $user = $discord->users->get('id', $update->user_id);
            $message = "{$user->username} entrou no canal de voz {$channel->name}.";
            $textChannel->sendMessage($message);
        } elseif ($update->channel_id === null) {
            $user = $discord->users->get('id', $update->user_id);
            $message = "{$user->username} saiu do canal de voz";
            $textChannel->sendMessage($message);
        }
    }
});

$discord->on(Event::VOICE_SERVER_UPDATE, function (VoiceServerUpdate $update, Discord $discord) {
    $textChannelId = $_ENV['TEXT_CHANNEL_ID'];
    $guildId = $_ENV['GUILD_ID'];

    if ($update->guild_id === $guildId) {
        $guild = $discord->guilds->get('id', $update->guild_id);
        $textChannel = $guild->channels->get('id', $textChannelId);

        $message = "Voice server updated. New endpoint: {$update->endpoint}";

        $textChannel->sendMessage($message);
    }
});

$discord->run();
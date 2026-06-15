<?php
$bot_token = '8054210057:AAFlbFFPd_H98CXKtWjlCgImhsDSyZOSGMc';
$url = 'https://api.telegram.org/bot' . $bot_token . '/getUpdates';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);

if ($response === false) {
    echo "Error de cURL: " . curl_error($ch) . "\n";
    exit(1);
}

curl_close($ch);

$data = json_decode($response, true);

if (isset($data['result']) && !empty($data['result'])) {
    foreach ($data['result'] as $update) {
        if (isset($update['message']['chat']['id'])) {
            echo "Chat ID encontrado: " . $update['message']['chat']['id'] . "\n";
            exit(0);
        }
    }
}

echo "No se encontró el Chat ID. Por favor, envía un mensaje al bot @armani_impulsa_bot primero.\n";
?>

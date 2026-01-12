<?php
header('Content-Type: application/json');

$key = $_POST['api_key'] ?? '';
$text = $_POST['text'] ?? '';

if (!$key || !$text) {
    echo json_encode(["translated" => $text, "error" => "Missing data"]);
    exit;
}

$url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent?key=$key";

$body = [
    "contents" => [[
        "parts" => [[ "text" => "Translate the following subtitle text to natural Korean. Keep line breaks if any:\n\n" . $text ]]
    ]]
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS => json_encode($body),
    CURLOPT_TIMEOUT => 15
]);

$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);

$translated = $data['candidates'][0]['content']['parts'][0]['text'] ?? $text;

echo json_encode([
    "translated" => trim($translated),
    "detected_lang" => "자동 감지"
]);
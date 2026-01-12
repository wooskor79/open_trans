<?php
header('Content-Type: application/json');

$key = $_POST['api_key'] ?? '';
if (!$key) {
    echo json_encode([
        "ok" => false,
        "error" => "API Key 없음"
    ]);
    exit;
}

/*
 Gemini는 별도 usage API가 없음.
 가장 안전한 방법은 "아주 짧은 generateContent 호출"로
 키 유효성만 확인하는 것.
*/

$url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent?key=" . urlencode($key);

$payload = [
    "contents" => [[
        "parts" => [[ "text" => "ping" ]]
    ]]
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

/*
 정상 키:
  - HTTP 200
  - candidates 존재

 잘못된 키:
  - HTTP 401 / 403
  - error 필드 존재
*/

if ($httpCode === 200 && isset($data['candidates'])) {
    echo json_encode([
        "ok" => true,
        "quota" => "Gemini: 사용량 정보 API 미제공 (정상 키)"
    ]);
} else {
    echo json_encode([
        "ok" => false,
        "error" => "Gemini API Key 오류"
    ]);
}

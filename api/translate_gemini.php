<?php
require 'parse.php';
require 'output.php';

$key = $_POST['api_key'];
$outfmt = $_POST['outfmt'];
$file = $_FILES['file'];

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$rows = parse_file(file_get_contents($file['tmp_name']), $ext);

$url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent?key=$key";

foreach ($rows as &$r) {
    if (!isset($r['text'])) continue;

    $body = [
        "contents" => [[
            "parts" => [[ "text" => "Translate to Korean:\n".$r['text'] ]]
        ]]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS => json_encode($body)
    ]);

    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    $r['text'] = $res['candidates'][0]['content']['parts'][0]['text'] ?? $r['text'];
}

output_result($rows, $outfmt, $ext);

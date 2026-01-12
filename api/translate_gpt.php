<?php
require 'parse.php';
require 'output.php';

$key = $_POST['api_key'];
$outfmt = $_POST['outfmt'];
$file = $_FILES['file'];

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$rows = parse_file(file_get_contents($file['tmp_name']), $ext);

foreach ($rows as &$r) {
    if (!isset($r['text'])) continue;

    $payload = [
        "model" => "gpt-4o-mini",
        "messages" => [
            ["role"=>"system","content"=>"Translate subtitle to Korean."],
            ["role"=>"user","content"=>$r['text']]
        ]
    ];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $key",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    $r['text'] = $res['choices'][0]['message']['content'] ?? $r['text'];
}

output_result($rows, $outfmt, $ext);

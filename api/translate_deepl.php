<?php
require 'parse.php';
require 'output.php';

$key = $_POST['api_key'];
$outfmt = $_POST['outfmt'];
$file = $_FILES['file'];

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$rows = parse_file(file_get_contents($file['tmp_name']), $ext);

foreach ($rows as &$r) {
    $ch = curl_init("https://api-free.deepl.com/v2/translate");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: DeepL-Auth-Key $key"
        ],
        CURLOPT_POSTFIELDS => http_build_query([
            "text" => $r['text'],
            "target_lang" => "KO"
        ])
    ]);

    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    $r['text'] = $res['translations'][0]['text'] ?? $r['text'];
}

output_result($rows, $outfmt, $ext);

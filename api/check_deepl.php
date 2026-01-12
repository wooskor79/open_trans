<?php
$key=$_POST['api_key'];
$ch=curl_init("https://api-free.deepl.com/v2/usage");
curl_setopt_array($ch,[
 CURLOPT_RETURNTRANSFER=>true,
 CURLOPT_HTTPHEADER=>["Authorization: DeepL-Auth-Key $key"]
]);
$r=json_decode(curl_exec($ch),true);
if(!isset($r['character_limit'])){
 echo json_encode(["ok"=>false]); exit;
}
echo json_encode([
 "ok"=>true,
 "quota"=>"DeepL 무료: {$r['character_count']} / {$r['character_limit']}"
]);

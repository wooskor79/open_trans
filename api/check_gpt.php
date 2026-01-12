<?php
$key=$_POST['api_key']??'';
$ch=curl_init("https://api.openai.com/v1/models");
curl_setopt_array($ch,[
 CURLOPT_RETURNTRANSFER=>true,
 CURLOPT_HTTPHEADER=>["Authorization: Bearer $key"]
]);
$r=curl_exec($ch);
$ok=json_decode($r,true);
echo json_encode([
 "ok"=>isset($ok['data'])
]);

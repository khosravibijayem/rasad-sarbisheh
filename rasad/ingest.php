<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
$base=__DIR__.'/data';$cfgFile=$base.'/config.php';
function out(int $code,array $v):never{http_response_code($code);echo json_encode($v,JSON_UNESCAPED_UNICODE);exit;}
if(!is_file($cfgFile))out(503,['ok'=>false,'error'=>'not_installed']);
$cfg=require $cfgFile;$auth=$_SERVER['HTTP_AUTHORIZATION']??'';
if(!hash_equals('Bearer '.$cfg['ingest_token'],$auth))out(401,['ok'=>false,'error'=>'unauthorized']);
if($_SERVER['REQUEST_METHOD']!=='POST')out(405,['ok'=>false,'error'=>'method_not_allowed']);
$body=json_decode(file_get_contents('php://input'),true);if(!is_array($body))out(400,['ok'=>false,'error'=>'invalid_json']);
$incoming=$body['items']??[];if(!is_array($incoming))out(400,['ok'=>false,'error'=>'items_must_be_array']);
$file=$base.'/items.json';$items=is_file($file)?json_decode(file_get_contents($file),true):[];if(!is_array($items))$items=[];
$known=[];foreach($items as $x)$known[(string)($x['id']??hash('sha256',(string)($x['url']??$x['text']??'')))]=true;
$added=0;
foreach($incoming as $x){if(!is_array($x))continue;$id=(string)($x['id']??hash('sha256',(string)($x['url']??$x['text']??'')));if(isset($known[$id]))continue;$known[$id]=true;$x['id']=$id;$x['received_at']=date('Y-m-d H:i:s');$items[]=$x;$added++;}
usort($items,fn($a,$b)=>($b['date']??0)<=>($a['date']??0));$items=array_slice($items,0,5000);
file_put_contents($file,json_encode($items,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT),LOCK_EX);
out(200,['ok'=>true,'added'=>$added,'total'=>count($items)]);

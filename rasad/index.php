<?php
declare(strict_types=1);
session_start();
$base=__DIR__.'/data'; if(!is_dir($base)) mkdir($base,0750,true);
$cfgFile=$base.'/config.php'; $cfg=is_file($cfgFile)?require $cfgFile:null;
if(!$cfg){http_response_code(503);exit('ابتدا install.php را اجرا کنید.');}
if(isset($_POST['logout'])){session_destroy();header('Location: index.php');exit;}
if(empty($_SESSION['ok'])){
 if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['password']) && password_verify((string)$_POST['password'],$cfg['admin_hash'])) $_SESSION['ok']=true;
 else {echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><body dir="rtl" style="font-family:tahoma;background:#f3f5f8;padding:24px"><form method="post" style="max-width:360px;margin:15vh auto;background:white;padding:24px;border-radius:16px"><h2>رصد سربیشه</h2><input type="password" name="password" placeholder="رمز مدیر" required style="width:100%;padding:13px;box-sizing:border-box"><button style="margin-top:12px;padding:12px;width:100%">ورود</button></form></body>';exit;}
}
$dataFile=$base.'/items.json';$items=is_file($dataFile)?json_decode(file_get_contents($dataFile),true):[];if(!is_array($items))$items=[];
usort($items,fn($a,$b)=>($b['date']??0)<=>($a['date']??0));
?><!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>رصد سربیشه</title>
<style>body{margin:0;background:#f3f5f8;color:#182230;font:15px Tahoma,Arial}.wrap{max-width:900px;margin:auto;padding:18px}.top,.item{background:white;border-radius:15px;padding:16px;margin:12px 0;box-shadow:0 2px 12px #1720330b}.muted{color:#667085;font-size:13px}button{padding:10px 14px;border:0;border-radius:9px;background:#183b66;color:white}a{color:#155eef;text-decoration:none}.tag{display:inline-block;background:#eef4ff;padding:5px 9px;border-radius:20px;margin:3px}</style>
<div class="wrap"><div class="top"><form method="post" style="float:left"><button name="logout">خروج</button></form><h2>دستیار خبری رصد سربیشه</h2><div class="muted">تعداد خبرهای ذخیره‌شده: <?=count($items)?> | آخرین دریافت: <?=htmlspecialchars((string)($items[0]['received_at']??'هنوز دریافتی ثبت نشده'))?></div><p><span class="tag">سیاسی</span><span class="tag">اقتصادی</span><span class="tag">فرهنگی و اجتماعی</span><span class="tag">امنیتی و نظامی</span></p></div>
<?php foreach(array_slice($items,0,100) as $it): ?><article class="item"><b><?=htmlspecialchars((string)($it['title']??'خبر دریافتی'))?></b><p><?=nl2br(htmlspecialchars((string)($it['summary']??$it['text']??'')))?></p><div class="muted"><?=htmlspecialchars((string)($it['source']??''))?> · <?=htmlspecialchars((string)($it['received_at']??''))?></div><?php if(!empty($it['url'])):?><p><a href="<?=htmlspecialchars((string)$it['url'])?>" target="_blank" rel="noopener">مشاهده پیام اصلی</a></p><?php endif;?></article><?php endforeach;?>
<?php if(!$items):?><div class="item">هنوز خبری دریافت نشده است. ابتدا اتصال دریافت پیام‌ها را راه‌اندازی کنید.</div><?php endif;?></div></html>
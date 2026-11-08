<?php
declare(strict_types=1);
$dir=__DIR__.'/data';if(!is_dir($dir))mkdir($dir,0750,true);$file=$dir.'/config.php';
if(is_file($file)){exit('نصب قبلاً انجام شده است.');}
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $p=(string)($_POST['password']??'');$t=trim((string)($_POST['token']??''));
 if(strlen($p)<10||strlen($t)<32)$msg='رمز حداقل ۱۰ نویسه و توکن حداقل ۳۲ نویسه باشد.';
 else{
  $cfg=['admin_hash'=>password_hash($p,PASSWORD_DEFAULT),'ingest_token'=>$t];
  file_put_contents($file,"<?php\nreturn ".var_export($cfg,true).";\n",LOCK_EX);@chmod($file,0600);
  file_put_contents($dir.'/.htaccess',"Require all denied\n");file_put_contents($dir.'/index.html','');
  header('Location: index.php');exit;
 }
}
?><!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><body dir="rtl" style="font:16px Tahoma;background:#f3f5f8;padding:25px"><form method="post" style="max-width:420px;margin:8vh auto;background:white;padding:24px;border-radius:16px"><h2>راه‌اندازی رصد سربیشه</h2><p>رمز مدیر و یک توکن تصادفیِ محرمانه تعیین کنید. توکن را فقط در تنظیمات امن سرویس دریافت‌کننده قرار دهید.</p><label>رمز مدیر</label><input name="password" type="password" minlength="10" required style="display:block;width:100%;padding:12px;box-sizing:border-box;margin:8px 0 16px"><label>توکن دریافت (حداقل ۳۲ نویسه)</label><input name="token" minlength="32" required style="display:block;width:100%;padding:12px;box-sizing:border-box;margin:8px 0 16px"><button style="padding:12px 20px">نصب</button><p style="color:#b42318"><?=$msg?></p></form></body>
<?php
declare(strict_types=1);
session_start();
date_default_timezone_set('Asia/Tehran');
$base=__DIR__.'/data'; if(!is_dir($base)) mkdir($base,0750,true);
$cfgFile=$base.'/config.php'; $cfg=is_file($cfgFile)?require $cfgFile:null;
if(!$cfg){http_response_code(503);exit('ابتدا install.php را اجرا کنید.');}
if(isset($_POST['logout'])){session_destroy();header('Location: index.php');exit;}
if(empty($_SESSION['ok'])){
 if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['password']) && password_verify((string)$_POST['password'],$cfg['admin_hash'])) $_SESSION['ok']=true;
 else {echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><body dir="rtl" style="font-family:tahoma;background:#f3f5f8;padding:24px"><form method="post" style="max-width:360px;margin:15vh auto;background:white;padding:24px;border-radius:16px"><h2>رصد سربیشه</h2><input type="password" name="password" placeholder="رمز مدیر" required style="width:100%;padding:13px;box-sizing:border-box"><button style="margin-top:12px;padding:12px;width:100%">ورود</button></form></body>';exit;}
}
function jalaliDate(int $ts): string {
 $g=getdate($ts); $gy=$g['year']-1600; $gm=$g['mon']-1; $gd=$g['mday']-1;
 $gdm=[0,31,59,90,120,151,181,212,243,273,304,334];
 $gday=365*$gy+(int)(($gy+3)/4)-(int)(($gy+99)/100)+(int)(($gy+399)/400)+$gdm[$gm]+$gd;
 if($gm>1 && (($g['year']%4===0 && $g['year']%100!==0)||$g['year']%400===0))$gday++;
 $jday=$gday-79; $jnp=(int)($jday/12053);$jday%=12053;
 $jy=979+33*$jnp+4*(int)($jday/1461);$jday%=1461;
 if($jday>=366){$jy+=(int)(($jday-1)/365);$jday=($jday-1)%365;}
 if($jday<186){$jm=1+(int)($jday/31);$jd=1+$jday%31;}else{$jm=7+(int)(($jday-186)/30);$jd=1+($jday-186)%30;}
 return sprintf('%04d/%02d/%02d',$jy,$jm,$jd);
}
function catOf(array $x): string {
 $c=trim((string)($x['category']??$x['topic']??''));
 $map=['سیاسی'=>'سیاسی','#سیاسی'=>'سیاسی','اقتصادی'=>'اقتصادی','#اقتصادی'=>'اقتصادی','فرهنگی_اجتماعی'=>'فرهنگی و اجتماعی','فرهنگی و اجتماعی'=>'فرهنگی و اجتماعی','#فرهنگی_اجتماعی'=>'فرهنگی و اجتماعی','امنیتی_نظامی'=>'امنیتی و نظامی','امنیتی و نظامی'=>'امنیتی و نظامی','#امنیتی_نظامی'=>'امنیتی و نظامی','نبض رسانه'=>'نبض رسانه','شایعات استان'=>'شایعات استان'];
 return $map[$c]??'فرهنگی و اجتماعی';
}
$dataFile=$base.'/items.json';$items=is_file($dataFile)?json_decode(file_get_contents($dataFile),true):[];if(!is_array($items))$items=[];
usort($items,fn($a,$b)=>(int)($b['date']??0)<=>(int)($a['date']??0));
function newsTimestamp(array $it): int {
 $d=$it['date']??$it['received_at']??$it['timestamp']??'';
 if(is_numeric($d)){ $n=(int)$d; return $n>20000000000?(int)floor($n/1000):$n; }
 $t=strtotime((string)$d); return $t===false?0:$t;
}
function persianOrdinal(int $n): string {
 $words=[1=>'اول',2=>'دوم',3=>'سوم',4=>'چهارم',5=>'پنجم',6=>'ششم',7=>'هفتم',8=>'هشتم',9=>'نهم',10=>'دهم',11=>'یازدهم',12=>'دوازدهم',13=>'سیزدهم',14=>'چهاردهم',15=>'پانزدهم',16=>'شانزدهم',17=>'هفدهم',18=>'هجدهم',19=>'نوزدهم',20=>'بیستم'];
 return $words[$n]??('شماره '.$n);
}
$now=time();$todayNoon=strtotime('today 12:00:00');
$windowEnd=($now >= $todayNoon)?$todayNoon:strtotime('yesterday 12:00:00');
$windowStart=$windowEnd-86400;$todayItems=[];
foreach($items as $it){$ts=newsTimestamp($it);if($ts >= $windowStart && $ts < $windowEnd)$todayItems[]=$it;}
usort($todayItems,fn($a,$b)=>newsTimestamp($b)<=>newsTimestamp($a));
$windowLabel=jalaliDate($windowStart).' ساعت ۱۲ تا '.jalaliDate($windowEnd).' ساعت ۱۲';
$sections=['سیاسی','اقتصادی','فرهنگی و اجتماعی','امنیتی و نظامی','نبض رسانه','شایعات استان'];$groups=[];foreach($sections as $s)$groups[$s]=[];
foreach($todayItems as $it)$groups[catOf($it)][]=$it;
$report="#استان_خراسان‌جنوبی\n#شهرستان_سربیشه\n".'گزارش ۲۴ ساعته - '.$windowLabel."\n\n";
foreach($sections as $s){$report.=$s."\n";$list=$groups[$s];if(!$list){$report.="🔹مورد خاصی مشاهده نشد\n\n";continue;}
 foreach($list as $i=>$it){if(count($list)>1)$report.='موضوع '.persianOrdinal($i+1).":\n";$txt=trim((string)($it['report_text']??$it['summary']??$it['text']??$it['title']??''));$report.="🔹".$txt."\n";$url=trim((string)($it['url']??''));if($url!=='')$report.=$url."\n";$report.="\n";}
}
?><!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>رصد سربیشه</title>
<style>
body{margin:0;background:#f3f5f8;color:#182230;font:15px Tahoma,Arial}.wrap{max-width:900px;margin:auto;padding:18px}.top,.item,.reportbox{background:white;border-radius:15px;padding:16px;margin:12px 0;box-shadow:0 2px 12px #1720330b}.muted{color:#667085;font-size:13px}button{padding:10px 14px;border:0;border-radius:9px;background:#183b66;color:white;cursor:pointer}a{color:#155eef;text-decoration:none}.tag{display:inline-block;background:#eef4ff;padding:5px 9px;border-radius:20px;margin:3px}textarea{width:100%;min-height:420px;box-sizing:border-box;direction:rtl;text-align:right;font:14px/2 Tahoma;padding:12px;border:1px solid #d0d5dd;border-radius:10px;background:#fafafa}
</style><div class="wrap">
<div class="top"><form method="post" style="float:left"><button name="logout">خروج</button></form><h2>دستیار خبری رصد سربیشه</h2><div class="muted">خبرهای ذخیره‌شده: <?=count($items)?> | خبرهای بازه ۲۴ ساعته: <?=count($todayItems)?></div><p><span class="tag">سیاسی</span><span class="tag">اقتصادی</span><span class="tag">فرهنگی و اجتماعی</span><span class="tag">امنیتی و نظامی</span></p></div>
<section class="reportbox"><h3>گزارش روزانه آماده کپی</h3><p class="muted">بازه گزارش: <?=htmlspecialchars($windowLabel,ENT_QUOTES,'UTF-8')?> (از ساعت ۱۲ روز قبل تا ساعت ۱۲ روز گزارش). متن خبرها بازنویسی نمی‌شود.</p><textarea id="dailyReport" readonly><?=htmlspecialchars($report,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8')?></textarea><p><button type="button" onclick="copyReport()">کپی گزارش روزانه</button> <span id="copyStatus" class="muted"></span></p></section>
<h3>آخرین خبرهای دریافتی</h3>
<?php foreach(array_slice($items,0,100) as $it): ?><article class="item"><b><?=htmlspecialchars((string)($it['title']??'خبر دریافتی'))?></b><p><?=nl2br(htmlspecialchars((string)($it['summary']??$it['text']??'')))?></p><div class="muted"><?=htmlspecialchars((string)($it['source']??''))?> · <?=htmlspecialchars((string)($it['received_at']??''))?> · <?=htmlspecialchars(catOf($it))?></div><?php if(!empty($it['url'])):?><p><a href="<?=htmlspecialchars((string)$it['url'])?>" target="_blank" rel="noopener">مشاهده پیام اصلی</a></p><?php endif;?></article><?php endforeach;?>
<?php if(!$items):?><div class="item">هنوز خبری دریافت نشده است. اتصال دریافت پیام‌ها هنوز نصب نشده است.</div><?php endif;?>
</div><script>
function copyReport(){const el=document.getElementById('dailyReport');el.select();el.setSelectionRange(0,el.value.length);let p=navigator.clipboard&&navigator.clipboard.writeText?navigator.clipboard.writeText(el.value):Promise.reject();p.then(()=>document.getElementById('copyStatus').textContent='گزارش کپی شد').catch(()=>{try{document.execCommand('copy');document.getElementById('copyStatus').textContent='گزارش کپی شد'}catch(e){document.getElementById('copyStatus').textContent='متن را دستی انتخاب و کپی کنید'}})}
</script></html>
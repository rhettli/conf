<?php 
use lyx\log\Log;
//use lyx\ip\Ip;
use lyx\jsondump\JsonDump;

include_once('../core/JsonDump.php');
include_once('../core/Log.php');
//include_once('core/Ip.php');

//10 Can\'t get content,

header("Content-type: text/html; charset=utf-8"); 

if(isset($_GET["kw"]))
{
	$kw=mb_convert_encoding($_GET["kw"], "GBK", "UTF-8");
	//$kw=$_GET["kw"];
}else
{
	//记录日志：搜索关键字,没带关键词，直接结束
	Log::DoError('Detail coreFunc access without kw');
	die("Error:No kw!");
}
//require_once("coreFunc.php");

if(isset($_GET["page"]))
{ 
	$page=$_GET["page"];
}else{
	$page="1";
}

//记录日志：搜索关键字
//Log::DoInfo('api   access kw='.$kw.' page='.$page);
	
function _url($Data){ 
	$ch = curl_init();
	$timeout = 8; 
	curl_setopt ($ch, CURLOPT_URL, "$Data"); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)"); 
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
	$contents = curl_exec($ch); 
	curl_close($ch); 
	return $contents; 
}

//getPageInfo($contents);
//echo "http://www.sosobta.net/s/".$kw."/".$page.".html";
$uri="http://www.sosobt.net/s/".$kw."/".$page.".html";
$content=_url($uri);
//echo $content;
if($content=="")
{
	//记录日志：搜索关键字,没带关键词，直接结束
	Log::DoError('api get page content > can\'t get url content:'.$uri);
	die(JsonDump::ArryToJson(array('error_code'=>10,'error_string'=>'Can\'t get content','url'=>$uri)));
}

$garr=require_once '../coref.zz.php';

//print_r($arr);

function getpageMax($Data,$arr){ 
	$rule  = $arr['1']['MAX_PAGE'];//'/totalPages: (.*?),/i';  
	   preg_match($rule,$Data,$res);
   if(!isset($res[1])){
	   return '';
   }
   return $res[1];
}

getPageInfo($content,$garr,$uri);

function getPageInfo($cont,$arr,$url_){
 $rule  =$arr['1']['INIT_LINE'];  //初始化页面匹配
 preg_match_all($rule,$cont,$result);
 $L=count($result[0]);
 //echo 'L:'. $L."<br/>";
 if($L==0){
	 die(JsonDump::ArryToJson( array('error_code'=>1,'error_string'=>'Can\'t get page number','url'=>$url_)));
 }

 $d=getpageMax($cont,$arr);
if($d==''){
	//记录日志：搜索关键字,没有获取到页码
	Log::DoError('['.Ip::GetClientIP().'] Detail coreFunc access ，can\'t get url page total:');
	die(JsonDump::ArryToJson( array('error_code'=>1,'error_string'=>'Can\'t get page number','url'=>$url_)));
}
echo '{"page":"'.$d.'",';
echo '"data":[';
for($i=0;$i<$L;$i++){
 $rule  = $arr['1']['LINE_RULE'];  
preg_match($rule,$result[0][$i],$res);  
 
 echo  '{"url":"'.$res[1].'","title":"'. str_replace("\n","",$res[2]).'","time":"'.$res[3].'","size":"'.$res[4].'","number":"'.$res[5].'","magnet":"'.$res[6].'"}';
if($i!=$L-1)
	{
	echo ',';
	}
}

echo ']}';
}
?> 
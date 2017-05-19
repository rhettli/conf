<?php
use lyx\log\Log;
//use lyx\ip\Ip;
use lyx\jsondump\JsonDump;


// 5 Can\'t get content,6 Can\'t much all in 7,0 ok ,8 no url

include_once('../core/Log.php');
//include_once('./core/Ip.php');
include_once('../core/JsonDump.php');


header("Content-type: text/html; charset=utf-8"); 

if(isset($_GET["url"]))
{
	//记录日志：获取详情接入
//Log::DoInfo('API Detail page access ');
$url=$_GET["url"];
$url_=mb_convert_encoding($_GET["url"], "GBK", "UTF-8");
}else
{
//记录日志：获取详情接入没带参数错误
Log::DoError('API Detail page access without url');
die(JsonDump::ArryToJson( array('error_code'=>8,'error_string'=>'no url')));
//header("location: https://www.sogou.com/link?url=DSOYnZeCC_qw3s5fctzWBdN79-hyH77Vj_YsWg20Euc.&query=cloudbooks.top");
}
//echo $url;
function _url($Data){ 
    $ch = curl_init(); 
    $timeout = 8; 
    curl_setopt ($ch, CURLOPT_URL, "$Data"); 
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.1(compatible; MSIE 6.0; Windows 7; SP1)"); 
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
    $contents = curl_exec($ch); 
    curl_close($ch); 
    return $contents; 
}

$content=_url("http://www.sosobta.net".$url_);
if($content==''){
    Log::DoError('API Detail page > can\'t get content at '.$url_);
    die(JsonDump::ArryToJson( array('error_code'=>5,'error_string'=>'Can\'t get content','url'=>$url_)));
}

//$rule  = '/<ul style="list-style: none;[\s\S]*<!--The end/i';  
//preg_match_all($rule,$content,$result);
//$L=count($result[0]);
//echo $content.'-------------------------------------------';

$rule = '/<img src="\/images.*?[vip"|file"]\/>(.*)?<\/h1>[\s\S]*创建时间：<\/span>(.*?)<\/span>[\s\S]*?更新时间：<\/span>(.*)?<\/span>[\s\S]*?大小：<\/span>(.*)?<\/span>[\s\S]*文件个数：<\/span>(.*)?<\/span>[\s\S]*访问热度：<\/span>(.*?)<\/span>[\s\S]*?磁力链接.*?href="(.*?)">/i';  
preg_match($rule,$content,$res);
if(count($res)<7){
    Log::DoError('API Detail page > Can\'t much all in 7 '.$url_);
    die(JsonDump::ArryToJson( array('error_code'=>6,'error_string'=>'Can\'t much all in 7','url'=>$url_)));
}

$rule='/<ul style="list-style-type:(.*)<\/li><\/ul>/i';
 preg_match_all($rule,$content,$result);  //第一次匹配，查找列表的全部内容
 $L=count($result[0]);
 //echo $result[0][0];
//应该只能找到一个符合项目。preg_match_all,第一个数组保存完整的数据，第二个数组保存括号中的数据

$data='';
 if($L==1){
	//查找列表项目
	$rule='/title="(.*?)">.*?<span style="color.*?">(.*?)<\/span><\/li>/i';
	preg_match_all($rule,$result[0][0],$resl);
	
	if(count($resl[1])>0){
		for($i=0;$i<count($resl[1]);$i++){
			//echo '<tr><td>'..'</td> <td>'..'</td></tr>';
			$data=$data.JsonDump::ArryToJson(array('name'=>$resl[1][$i],'size'=>$resl[2][$i]));
			if($i<count($resl[1])-1){
					
					$data=$data.',';
					
			}
		}
	}
}



echo JsonDump::ArryToJson(array('error_code'=>0,'title'=>$res[1],'ctime'=>$res[2],'utime'=>$res[3],'size'=>$res[4],'file_number'=>$res[5],'hot_number'=>$res[6],'magnet'=>$res[7],''=>'"data":['.$data.']'));



//'/创建时间：<\/span>(.*?)<\/span>[\s\S]*?更新时间：<\/span>(.*)?<\/span>[\s\S]*?大小：<\/span>(.*)?<\/span>[\s\S]*文件个数：<\/span>(.*)?<
//\/span>[\s\S]*访问热度：<\/span>(.*?)<\/span>[\s\S]*?磁力链接.*?href="(.*?)">/i'





?>
<?php
include_once ('request.php');
require("RollingCurl.php");

function request_callback($response, $info, $request) {
	echo $response;
}
#$server = "192.168.135.142:8000/";

$s3 = new s3_method();

function partfun_m(&$part){
	global $s3;
	$res = $s3->initiate_multipart_upload($part);
	unset($part['query']['uploads']);
	settype($res,'string');

	$dom = DOMDocument::loadXML($res);
	$upload = $dom->getElementsByTagName('UploadId');
	$uploadid = $upload -> item(0) -> nodeValue;
	$part['query']['uploadId'] = $uploadid;
	return $part;
}

function args_deal($request){
	$query = "?";
	foreach($request as $k=>$v){
		$query.=$k;
		if((strlen($v)!=0 && $v != "") || ($v=='0')){
			$query.="=".$v.'&';
		}
		else{
			$query.='&';	
		}
	}
	return substr($query,0,-1);
}
	
function get_part($filename, $partnum, $partsize){
    $fh = fopen($filename,'rb');
	fseek($fh,0);
	fseek($fh,$partsize*$partnum*1024*1024);
	$content = fread($fh, $partsize*1024*1024);
	fclose($fh);

	return $content;
}

function set_http($part, $partnum){
	$body = get_part($part["object_file"], $partnum['partnum'], $part["size"]);
	return $body;
}

function deal_request($urls, $part){
	global $s3;
	$rc = new RollingCurl("request_callback");
	
	if (count($urls) > 4){$rc->window_size = 4;}
	else $rc->window_size = count($urls);
	
	foreach ($urls as $url) {
		$body = set_http($part, $url);
		$part['query']['partNumber']=$url['partnum'];
		$req = $s3->multi_sign($part);
	
		$header = array();
		$header[] = "Authorization".':'.$req->getHeader("Authorization");
		$header[] = "Date".':'.$req->getHeader("Date");
		$header[] = "Content-Length".':'.strlen($body);
		$header[] = "Accept".':'.'';
		$header[] = "Expect".':'.'';
		$header[] = "Content-Type".':'.'';
		$header[] =  "Transfer-Encoding:".'';
		
		$option = array(CURLOPT_HEADER => True, 
						CURLOPT_CUSTOMREQUEST => 'PUT', 
						CURLOPT_HTTPAUTH => True, 
						CURLOPT_HTTPHEADER => $header,
						CURLOPT_POSTFIELDS => $body,
						CURLOPT_RETURNTRANSFER => True
						);
    	$request = new RollingCurlRequest($url['url'],"PUT", $post_data = $body, $headers = $header, $option);
    	$rc->add($request);
	}
$rc->execute();
}	

function multi_thread_upload($part){
	global $ser;
	$server = $ser;	
	global $part_normal;
	$part = partfun_m($part);
	$fsize = filesize($part['object_file']);
	$partnum = $fsize/($part['size']*1024*1024);
	$urls = array();
	$items = array();
	for($i=0; $i<=(int)$partnum; $i++){
		$part['query']['partNumber']=$i;
		$args = args_deal($part['query']);
		$path = $part["bucket"].'/'.$part["object"];
		$urs = $server.$path.$args;
		$items['url'] = $urs;
		$items['partnum'] = $i;
		array_push($urls, $items);
	}
	deal_request($urls, $part);
	$part_normal = new part();
	$parts = array();
	for($i=0;$i<=(int)$partnum;$i++){
			array_push($parts,$i);
		}
	$part['partnum']=$parts;
	unset($part['query']['partNumber']);
	$part_normal->complete_multipart_upload($part);
}
	

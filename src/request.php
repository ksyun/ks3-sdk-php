<?php


require __DIR__ . '/../vendor/autoload.php';
use Guzzle\Http\Message\Request;
use Guzzle\Http\Client;
use Guzzle\Http\Message\EntityEnclosingRequestInterface;
use Guzzle\Http\Exception\BadResponseException;
include_once ('signature.php');

$ser = "192.168.135.142:8000/";

interface s3{
	function list_all($req);
	function list_bucket($req);
	function create_bucket($req);
	function create_object($req);
	function create_object_file($req);
	function get_object($req);
	function delete_object($req);
	function delete_bucket($req);
	function initiate_multipart_upload($req);
	function upload_part($req);
	function list_parts($req);
	function list_multipart_upload($req);
	function complete_multipart_uploads($content,$req);
	function abort_multipart_uploads($req);
}

class s3_method implements s3
{
	public $server;
    public function __construct() {
    	global $ser;
        $this->server = $ser;
    }
    
	protected function args_deal($request){
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
	
	protected function deal_exception($request){
		try{
				$response = $request -> send();
				return $response;
			}
			catch(BadResponseException $e){
				echo 'Uh oh! ' . $e->getMessage();
	    		echo 'HTTP request URL: ' . $e->getRequest()->getUrl() . "\n";
	    		#echo 'HTTP request: ' . $e->getRequest() . "\n";
	    		echo 'HTTP response status: ' . $e->getResponse()->getStatusCode() . "\n";
	    		echo 'HTTP response: ' . $e->getResponse() . "\n";
			}
	}
	
	public function list_all ($req){
		array_multisort($req["query"]);
		$url =$this->args_deal($req["query"]);
		$request = new Request("GET",$url,$req["header"]);
		$request->setUrl($url);
		$client = new Client();
		$request -> addHeader('Date', gmdate("D, d M Y H:i:s",time())." GMT");
		$sign = new signature();
		$request -> setScheme("http");
		$request -> setHost($this->server);
		$request -> setClient($client);
		$sign -> signRequest($request, $req );
		
		$response = $this -> deal_exception($request);
		if($response){
			$response_body = $response-> getBody();
			return $response_body;	
		}
		else{
			return "error happened!";
		}
	}
	
	
	
	public function create_bucket($req){
		array_multisort($req["query"]);
		$url = implode("&", $req["query"]);
		$request = new Request("PUT",$url,$req["header"]);
		$client = new Client();
		$request -> addHeader('Date', gmdate("D, d M Y H:i:s",time())." GMT");
		$request -> addHeader('Content-Length','0');
		$sign = new signature();
		$sign -> signRequest($request, $req );
		$request -> setScheme("http");
		$request -> setHost($this->server);
		$request -> setClient($client);
		$request -> setPath($req["bucket"]);
		$response = $this -> deal_exception($request);
		if($response){
			$response_body = $response-> getBody();
			return $response_body;	
		}
		else{
			return "error happened!";
		}
	}
	
	public function create_object($req){
		array_multisort($req["query"]);
		$url = implode("&", $req["query"]);
		$client = new Client();
		$request = $client -> createRequest("PUT",$url,$req["header"],$req["object_data"]);
		$request -> addHeader('Date', gmdate("D, d M Y H:i:s",time())." GMT");
		
		$sign = new signature();
		$sign -> signRequest($request, $req );
		$request -> setScheme("http");
		$request -> setHost($this->server);
		$client = new Client();
		
		$request -> setClient($client);
		$request -> setPath($req["bucket"].'/'.$req["object"]);
		$request -> addHeader('Content-Length',strlen($req["object_data"]));
	
		$response = $this -> deal_exception($request);
		if($response){
			$response_body = $response-> getBody();
			return $response_body;	
		}
		else{
			return "error happened!";
		}
	}
	
	public function create_object_file($req){
		array_multisort($req["query"]);
		$url = implode("&", $req["query"]);
		$client = new Client();
		$request = $client -> createRequest("PUT",$url,$req["header"],fopen($req["object_file"],'r'));
		$request -> addHeader('Date', gmdate("D, d M Y H:i:s",time())." GMT");
		
		$sign = new signature();
		$sign -> signRequest($request, $req );
		$request -> setScheme("http");
		$request -> setHost($this->server);
		$client = new Client();
		
		$request -> setClient($client);
		$request -> setPath($req["bucket"].'/'.$req["object"]);
		$request -> addHeader('Content-Length',filesize($req["object_file"]));
	
		$response = $this -> deal_exception($request);
		if($response){
			$response_body = $response-> getBody();
			return $response_body;	
		}
		else{
			return "error happened!";
		}
	}
	
	public function delete_object($req){
		array_multisort($req["query"]);
		$url = implode("&", $req["query"]);
		$client = new Client();
		$request = $client -> createRequest("DELETE",$url,$req["header"]);
		$request -> addHeader('Date', gmdate("D, d M Y H:i:s",time())." GMT");
		
		$sign = new signature();
		$sign -> signRequest($request, $req );
		$request -> setScheme("http");
		$request -> setHost($this->server);
		$client = new Client();
		
		$request -> setClient($client);
		$request -> setPath($req["bucket"].'/'.$req["object"]);
		$request -> addHeader('Content-Length','0');
	
		$response = $this -> deal_exception($request);
		if($response){
			$response_body = $response-> getBody();
			return $response_body;	
		}
		else{
			return "error happened!";
		}
	}
	
	public function delete_bucket($req){
		array_multisort($req["query"]);
		$url = implode("&", $req["query"]);
		$client = new Client();
		$request = $client -> createRequest("DELETE",$url,$req["header"]);
		$request -> addHeader('Date', gmdate("D, d M Y H:i:s",time())." GMT");
		
		$sign = new signature();
		$sign -> signRequest($request, $req );
		$request -> setScheme("http");
		$request -> setHost($this->server);
		$client = new Client();
		
		$request -> setClient($client);
		$request -> setPath($req["bucket"]);
		$request -> addHeader('Content-Length','0');
	
		$response = $this -> deal_exception($request);
		if($response){
			$response_body = $response-> getBody();
			return $response_body;	
		}
		else{
			return "error happened!";
		}
	}
	
	public function list_bucket($req){
		array_multisort($req["query"]);
		$url = implode("&", $req["query"]);
		$client = new Client();
		$request = $client -> createRequest("GET",$url,$req["header"]);
		$request -> addHeader('Date', gmdate("D, d M Y H:i:s",time())." GMT");
		
		$sign = new signature();
		$sign -> signRequest($request, $req );
		$request -> setScheme("http");
		$request -> setHost($this->server);
		$client = new Client();
		
		$request -> setClient($client);
		$request -> setPath($req["bucket"]);
		$request -> addHeader('Content-Length','0');
	
		$response = $this -> deal_exception($request);
		if($response){
			$response_body = $response-> getBody();
			return $response_body;	
		}
		else{
			return "error happened!";
		}
	}
	
	public function get_object($req){
		array_multisort($req["query"]);
		$url = implode("&", $req["query"]);
		$client = new Client();
		$request = $client -> createRequest("GET",$url,$req["header"]);
		$request -> addHeader('Date', gmdate("D, d M Y H:i:s",time())." GMT");
		
		$sign = new signature();
		$sign -> signRequest($request, $req );
		$request -> setScheme("http");
		$request -> setHost($this->server);
		$client = new Client();
		
		$request -> setClient($client);
		$request -> setPath($req["bucket"].'/'.$req["object"]);
		$request -> addHeader('Content-Length','0');
	
		$response = $this -> deal_exception($request);
		if($response){
			$response_body = $response-> getBody();
			$fs = fopen($req["object"],'w');
			fwrite($fs, $response_body);
			return $response_body;	
		}
		else{
			return "error happened!";
		}	
	}
	
	public function initiate_multipart_upload($req){
		
		$client = new Client();
		$request = $client -> createRequest("POST",null,$req["header"]);
		$request -> addHeader('Date', gmdate("D, d M Y H:i:s",time())." GMT");
	
		$request -> setScheme("http");
		$request -> setHost($this->server);
		$client = new Client();
		
		$request -> setClient($client);
		$path = $req["bucket"].'/'.$req["object"];
		array_multisort($req["query"]);
		$args = $this->args_deal($req["query"]);
		$urs = $path.$args;
		$request -> setPath($urs);
		$request -> addHeader('Content-Length','0');
		$sign = new signature();
		$sign -> signRequest($request, $req );
		$response = $this -> deal_exception($request);
		if($response){
			$response_body = $response-> getBody();
			return $response_body;	
		}
		else{
			return "error happened!";
		}	
	}
	
	public function upload_part($req){
		array_multisort($req["query"]);
		$args = $this->args_deal($req["query"]);
		$client = new Client();
		$fh = fopen($req["object_file"],'rb');
		$pos = $req['query']['partNumber'];
		fseek($fh,0);
		fseek($fh,$pos*$req["size"]*1024*1024);
		$content = fread($fh, $req["size"]*1024*1024);
		
		if (strlen($content) == 0){
			return;
		}
		fclose($fh);
		$request = $client -> createRequest("PUT",null,$req["header"],$content);
		$request -> addHeader('Date', gmdate("D, d M Y H:i:s",time())." GMT");
		
		$sign = new signature();
		$sign -> signRequest($request, $req );
		$request -> setScheme("http");
		$request -> setHost($this->server);
		$client = new Client();
		
		$request -> setClient($client);
		$path = $req["bucket"].'/'.$req["object"];
		$urs = $path.$args;
		$request -> setPath($urs);
		$len = 0;
		$request -> addHeader('Content-Length',$len );
		$response = $this -> deal_exception($request);
		if($response){
			$response_body = $response-> getBody();
			return $response_body;	
		}
		else{
			return "error happened!";
		}		
	}
	
	public function list_parts($req){
		array_multisort($req["query"]);
		$args = $this->args_deal($req["query"]);
		$client = new Client();
		$request = $client -> createRequest("GET",null,$req["header"]);
		$request -> addHeader('Date', gmdate("D, d M Y H:i:s",time())." GMT");
		
		
		$request -> setScheme("http");
		$request -> setHost($this->server);
		$client = new Client();
		
		$request -> setClient($client);
		$path = $req["bucket"].'/'.$req["object"];
		$urs = $path.$args;
		$request -> setPath($urs);
		$request -> addHeader('Content-Length','0' );
		$sign = new signature();
		$sign -> signRequest($request, $req );
		$response = $this -> deal_exception($request);
		if($response){
			$response_body = $response-> getBody();
			return $response_body;	
		}
		else{
			return "error happened!";
		}		
	}
	
	public function list_multipart_upload($req){
		array_multisort($req["query"]);
		$args = $this->args_deal($req["query"]);
		$client = new Client();
		$request = $client -> createRequest("GET",null,$req["header"]);
		$request -> addHeader('Date', gmdate("D, d M Y H:i:s",time())." GMT");
		
		$request -> setScheme("http");
		$request -> setHost($this->server);
		$client = new Client();
		
		$request -> setClient($client);
		$path = $req["bucket"].'/';
		$urs = $path.$args;
		$request -> setPath($urs);
		$request -> addHeader('Content-Length','0');
		$sign = new signature();
		$sign -> signRequest($request, $req );
		$response = $this -> deal_exception($request);
		if($response){
			$response_body = $response-> getBody();
			return $response_body;	
		}
		else{
			return "error happened!";
		}
	}
	
	public function complete_multipart_uploads($content, $req){
		array_multisort($req["query"]);
		$args = $this->args_deal($req["query"]);
		$client = new Client();
		$request = $client -> createRequest("POST",null,$req["header"],$content);
		$request -> addHeader('Date', gmdate("D, d M Y H:i:s",time())." GMT");
		
		$request -> setScheme("http");
		$request -> setHost($this->server);
		$client = new Client();
		
		$request -> setClient($client);
		$path = $req["bucket"].'/'.$req["object"];
		$urs = $path.$args;
		$request -> setPath($urs);
		$request -> addHeader('Content-Length','0');
		$sign = new signature();
		$sign -> signRequest($request, $req );
		$response = $this -> deal_exception($request);
		if($response){
			$response_body = $response-> getBody();
			return $response_body;	
		}
		else{
			return "error happened!";
		}
	}
	
	public function abort_multipart_uploads($req){
		array_multisort($req["query"]);
		$args = $this->args_deal($req["query"]);
		$client = new Client();
		$request = $client -> createRequest("DELETE",null,$req["header"]);
		$request -> addHeader('Date', gmdate("D, d M Y H:i:s",time())." GMT");
		
		$request -> setScheme("http");
		$request -> setHost($this->server);
		$client = new Client();
		
		$request -> setClient($client);
		$path = $req["bucket"].'/'.$req["object"];
		$urs = $path.$args;
		$request -> setPath($urs);
		$request -> addHeader('Content-Length','0');
		$sign = new signature();
		$sign -> signRequest($request, $req );
		$response = $this -> deal_exception($request);

		if($response){
			$response_body = $response-> getBody();
			return $response_body;	
		}
		else{
			return "error happened!";
		}
	}	
	
	public function multi_sign($req){
		array_multisort($req["query"]);
		$args = $this->args_deal($req["query"]);
		$client = new Client();
		$request = $client -> createRequest("PUT",null,$req["header"]);
		$request -> addHeader('Date', gmdate("D, d M Y H:i:s",time())." GMT");
		
		$sign = new signature();
		$sign -> signRequest($request, $req );
		return $request;
	}
}
<?php
# this is a demo to get signature url of object
# may be you do not need header field in your array
# the main function your need to call is url($req)

   $signableQueryString = array(
        'acl', 'delete', 'lifecycle', 'location', 'logging', 'notification',
        'partNumber', 'policy', 'requestPayment', 'torrent', 'uploadId',
        'uploads', 'versionId', 'versioning', 'versions', 'website',
        'response-cache-control', 'response-content-disposition',
        'response-content-encoding', 'response-content-language',
        'response-content-type', 'response-expires', 'restore', 'tagging', 'cors'
    );
	
    function args_deal($request){
		$query = "?";
		foreach($request as $k=>$v){
			$query.=$k;
			if((strlen($v)!=0 && $v != "") || ($v=='0')){
				$v = rawurlencode($v);
				$query.="=".$v.'&';
			}
			else{
				$query.='&';	
			}
		}
		return substr($query,0,-1);
	}
   
    $signableHeaders = array('Content-MD5', 'Content-Type');
    
    function url(array $credentials){
    	$query = args_deal($credentials["query"]);
    	$sign = sign_create($credentials);
    	$object = rawurlencode($credentials['object']);
    	$url = "http://".$credentials['bucket'].".kss.ksyun.com/".$object.$query.'&Signature='.$sign;
    	echo $url;
    	
    }

    function sign_create(array $credentials)
    {
        $stringToSign = createCanonicalizedString($credentials);
        $sign = signString($stringToSign, $credentials["crendit"]["access_key"]);
        return $sign;
    }

    function signString($string, $credentials)
    {
        return base64_encode(hash_hmac('sha1', $string, $credentials, true));
    }

    function createCanonicalizedAmzHeaders($req_header)
    {
        $headers = array();
        foreach ($req_header as $item => $value) {
            $name = strtolower($item);
            if (strpos($name, 'x-kss-') === 0) {
                if ($value || $value === '0') {
                    $req_header[$name] = $name . ':' . $value;
                }
            }
        }

        if (empty($req_header)) {
            return '';
        } else {
            ksort($req_header);

            return implode("\n", $req_header) . "\n";
        }
    }
    
    
    function createCanonicalizedString(array $req, $expires = null)
    {
    	global $signableQueryString;
        $buffer = $req['method'] . "\n";
        $buffer .= array_key_exists("Content-MD5", $req["header"]) ? $req["header"]["Content-MD5"]:null;
        $buffer .="\n";
        $buffer .= array_key_exists("Content-Type",$req["header"]) ? $req["header"]["Content-Type"]:null;
        $buffer .="\n";
        $buffer .= array_key_exists("Date",$req["header"]) ? $req["header"]["Date"]:null;
        $buffer .="\n";
        $buffer .= createCanonicalizedAmzHeaders($req["header"]);
        $bucket = array_key_exists("bucket", $req) ? $req["bucket"]:null;
        $buffer .= $bucket ? "/{$bucket}" : '';
        
        $object = array_key_exists("object", $req) ? $req["object"]:null;
        if ($object != null){
        	$object = rawurlencode($object);
        }
        $buffer .= $object ? "/{$object}" : '';
      
        $query = $req["query"];
        $first = true;
        foreach ($signableQueryString as $key) {
            if ( array_key_exists($key, $query)  ) {
            	$value = $query[$key];
                $buffer .= $first ? '?' : '&';
                $first = false;
                $buffer .= $key;
                if ($value !== "") {
                    $buffer .= "={$value}";
                }
            }
        }
        return $buffer;    
    }
    
    $listall = array(
			'crendit' => array(
			'access_id' => "FWJ4LA7KVMKW26YOUWZQ",
			'access_key' => "dDhI3IblBm4xzzuhW/Z95HDFB4fBzp1fJuRvwDK8",
			),
			'method' => 'GET',
			'bucket' => 'dfyl',
			'object' => '2013-07-04/CC-（交互流程设计）咖啡馆课程.rar',
			'header' => array(),
			'query' => array('response-content-disposition' =>'attachment; filename=fname.ext',
			'response-content-type' => 'text/html'
			)
           );
           
    echo url($listall);
    
   
    

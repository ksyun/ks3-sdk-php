<?php
$access_id = "your access id";
$access_key = "your access key";
$key = 'abc/';
$bucket_name = "your bucket";
$host = "kss.ksyun.com";
$host_uri = "http://".$bucket_name.".".$host;
$redirect = $host_uri;

function iso8601($time=false) {
    if ($time === false) $time = time();
    $date = date('Y-m-d\TH:i:s\.Z', $time);
    return (substr($date, 0, strlen($date)-2).'Z');
}

function def_policy(){
	global $key;
	global $redirect;
	$t = time() + (3600*24);
	$exp = iso8601($t);
	$policy = "{\"expiration\":\"$exp\",
\"conditions\": [
{\"bucket\": \"hejiarong\"},
[\"starts-with\", \"\$key\", \"$key\"],
{\"success_action_redirect\": \"$redirect\"},
[\"eq\", \"\$Content-Type\", \"text/html\"]
]
}";
	return $policy;
}

function cal_sign($policy){
	global $access_key;
	$sign = base64_encode(hash_hmac('sha1',$policy, $access_key, true));
	return $sign;
};

$policy = def_policy();
$policy = base64_encode($policy);
$sign = cal_sign($policy);
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<form action="<?php echo $host_uri;?>" method="post" enctype="multipart/form-data">
Key to upload: <input type="input" name="key" value="<?php echo $key;?>" /><br />
<input type="hidden" name="success_action_redirect" value="<?php echo $redirect;?>" />
<input type="hidden" name="Content-Type" value="text/html" />
<input type="hidden" name="KSSAccessKeyId" value="<?php echo $access_id;?>" />
<input type="hidden" name="Policy" value="<?php echo $policy;?>" />
<input type="hidden" name="Signature" value="<?php echo $sign;?>" />
File: <input type="file" name="file" /> <br />
<!-- The elements after this will be ignored -->
<input type="submit" name="submit" value="Upload to KSS S3" />
</form>
</html>

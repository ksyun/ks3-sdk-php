<?php

include_once ('request.php');
# if you need big file to upload, use multipart upload
include_once ('multi_deal_part.php');
include_once ('multithread_part.php');

$access_id = 'your access id';
$access_key = "your access key";
$bucket = "your bucket";

$listall = array(
			'crendit' => array(
			'access_id' => $access_id,
			'access_key' => $access_key,
			),
			'header' => array(),
			'query' => array()		
           );
           
$creat_bucket = array(
			'crendit' => array(
			'access_id' => $access_id,
			'access_key' => $access_key,
			),
			'bucket' => $bucket,
			'header' => array(),
			'query' => array()		
           );
           
$create_object = array(
			'crendit' => array(
			'access_id' => $access_id,
			'access_key' => $access_key,
			),
			'bucket' => $bucket,
			'object' => 'abc0',
			'object_data' =>'hello',
			'header' => array(),
			'query' => array()		
           );
           
$create_object1 = array(
			'crendit' => array(
			'access_id' => $access_id,
			'access_key' => $access_key,
			),
			'bucket' => $bucket,
			'object' => 'abc1',
			'object_data' =>'hello1',
			'header' => array(),
			'query' => array()		
           );
           
$create_object2 = array(
			'crendit' => array(
			'access_id' => $access_id,
			'access_key' => $access_key,
			),
			'bucket' => $bucket,
			'object' => 'abc2',
			'object_data' =>'hello2',
			'header' => array(),
			'query' => array()		
           );
           
$create_object_file = array(
			'crendit' => array(
			'access_id' => $access_id,
			'access_key' => $access_key,
			),
			'bucket' => $bucket,
			'object' => 'abc',
			'object_file' =>'test',
			'header' => array(),
			'query' => array()		
           );
           
$delete_object = array(
			'crendit' => array(
			'access_id' => $access_id,
			'access_key' => $access_key,
			),
			'bucket' => $bucket,
			'object' => 'abc',
			'header' => array(),
			'query' => array()		
           );
           
           
$delete_bucket = array(
			'crendit' => array(
			'access_id' => $access_id,
			'access_key' => $access_key,
			),
			'bucket' => $bucket,
			'header' => array(),
			'query' => array()		
           );
           
$list_bucket = array(
			'crendit' => array(
			'access_id' => $access_id,
			'access_key' => $access_key,
			),
			'bucket' => $bucket,
			'header' => array(),
			'query' => array()		
           );
           
$get_object = array(
			'crendit' => array(
			'access_id' => $access_id,
			'access_key' => $access_key,
			),
			'bucket' => $bucket,
			'object' => 'abc',
			'header' => array(),
			'query' => array()		
           );
           
# this is some function you can operation
           
$s3 = new s3_method();

echo "response result: ";
echo $s3->create_bucket($creat_bucket);

echo "response result: ";
echo $s3->create_object($create_object);
echo $s3->create_object($create_object1);
echo $s3->create_object($create_object2);

echo "response result: ";
echo $s3->create_object_file($create_object_file);

echo "response result: ";
echo $s3->list_bucket($list_bucket);

echo "response result: ";
$s3->get_object($get_object);

echo "response result: ";
echo $s3->delete_object($delete_object);

$delete_object['object'] = 'abc0';
echo "response result: ";
echo $s3->delete_object($delete_object);

$delete_object['object'] = 'abc1';
echo "response result: ";
echo $s3->delete_object($delete_object);

$delete_object['object'] = 'abc2';
echo "response result: ";
echo $s3->delete_object($delete_object);



# if it is big file, you can use multipart upload
$multi = array(
			'crendit' => array(
			'access_id' => $access_id,
			'access_key' => $access_key,
			),
			'bucket' => $bucket,
			'object' => 'mul_test5', # this is the name you store in kss 
			'object_file' => 'multi_test.zip', # this is your filename , may be object = object_file
			'size' => '6', # this is the size you want to split ,use MB
			'header' => array(),
			'query' => array(
			'uploads' => '',
			)	
           );
           
$size = 30*1024*1024;
$filename = "multi_test.zip";
$fp = fopen($filename, 'w'); 
fseek($fp, $size-1); 
fwrite($fp,'a'); 
fclose($fp); 

$mul = new part();
echo "response result: ";
echo $mul->multi_upload($multi);

# multi-thread multi upload
echo "\n.thread test:\n";
echo multi_thread_upload($multi);


echo "response result: ";
echo $s3->delete_bucket($delete_bucket);

unlink($filename);

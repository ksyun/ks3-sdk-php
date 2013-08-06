<?php
include_once ('request.php');
class part {
	var $s3 ;
	var $uploadid;
	var $partnum;
	public function partfun($part){
		$this->s3 = new s3_method();
		unset($part['query']['uploadId']);
		$res = $this->s3->initiate_multipart_upload($part);
		unset($part['query']['uploads']);
		
		$dom = DOMDocument::loadXML($res);
		$upload = $dom->getElementsByTagName('UploadId');
		$this->uploadid = $upload -> item(0) -> nodeValue;
		$part['query']['uploadId'] = $this->uploadid;
		$fs_name = $part['object_file'];
		$fsize = filesize($fs_name);
		$this->partnum = (int)($fsize/($part['size']*1024*1024));
		for($i=0; $i<=$this->partnum; $i++){
			$part['query']['partNumber']=$i;
			$this->s3->upload_part($part);
		}
	}
	public function list_parts($part){
		unset($part['query']['uploads']);
		unset($part['query']['partNumber']);	
		$part['query']['uploadId'] = $this->uploadid;
		return $this->s3->list_parts($part);
	}
	public function list_parts_uploadid($part){
		$this->s3 = new s3_method();
		unset($part['query']['uploads']);
		unset($part['query']['partNumber']);	
		return $this->s3->list_parts($part);
	}
	
	public function make_xml($res, $part){
		$this->s3 = new s3_method();
		$dom = DOMDocument::loadXML($res);
		$upload = $dom->getElementsByTagName('PartNumber');	
		$etag = $dom->getElementsByTagName('ETag');
		$partnum_dic = array();
		for ($i=0;$i<$upload->length;$i++){
			$partnum_dic[$upload->item($i)-> nodeValue] = $etag->item($i)-> nodeValue;
		}
		$complete_dom = new DOMDocument('1.0', 'utf-8');
		$ele = $complete_dom->createElement('CompleteMultipartUpload');
		$ele = $complete_dom->appendChild($ele);
		foreach ($part['partnum'] as $num){
			$ele_part = $complete_dom->createElement('Part');
			$ele_part = $ele->appendChild($ele_part);
			$ele_num = $complete_dom->createElement('PartNumber',$num);
			$ele_etag = $complete_dom->createElement('ETag',$partnum_dic[$num]);
			$ele_num = $ele_part->appendChild($ele_num);
			$ele_etag = $ele_part->appendChild($ele_etag);  
		}
		return $complete_dom->saveXML();	
	}
	public function complete_multipart_upload($part){
		$this->s3 = new s3_method();
		$res = $this->list_parts_uploadid($part);
		$content = $this->make_xml($res,$part);
		return $this->s3->complete_multipart_uploads($content, $part);
		
	}
	
	public function multi_upload($part){
		$this->partfun($part);
		$parts = array();
		for($i=0;$i<=$this->partnum;$i++){
			array_push($parts,$i);
		}
		unset($part['query']['uploads']);
		$part['query']['uploadId'] = $this->uploadid;
		$part['partnum']=$parts;
		$res = $this->list_parts_uploadid($part);
		$content = $this->make_xml($res,$part);
		return $this->s3->complete_multipart_uploads($content, $part);	
	}
	
	public function abort_upload($part){
		$this->partfun($part);
		$parts = array();
		$this->partnum = 2;
		for($i=0;$i<=$this->partnum;$i++){
			array_push($parts,$i);
		}
		unset($part['query']['uploads']);
		$part['query']['uploadId'] = $this->uploadid;
		$part['partnum']=$parts;
		return $this->s3->abort_multipart_uploads($part);
	}
		
}
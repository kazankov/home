<?php
require_once "HTTP/Request2.php";
//русский текст

abstract class PresentationSource
{
	protected $ext = null;
	function __construct($ext=null)
	{
		$this->ext = $ext;
	}
	abstract public function presType();
	abstract public function upload($filePath, $title, $fileName, $tags);
	abstract public function delete($url);
	static function factoryExt($ext)
	{
		$obj = null;
		if($ext == 'pptx') $ext = 'ppt';
		if($ext == 'ppt' || $ext == 'pdf') $obj = new Slideshare($ext);
		if($ext == 'avi' || $ext == 'flv' || $ext == 'mpg' || $ext == 'mpeg' || $ext == 'swf' || $ext == 'mp4' || $ext == 'wmv') $obj = new Youtube($ext);
		
		return $obj;
	}
	
	static function factoryPresType($pt)
	{
		$obj = null;
		if($pt == 'ppt' || $pt == 'pdf') $obj = new Slideshare($pt);
		if($pt == 'video') $obj = new Youtube();
		
		return $obj;
	}	
}

class Slideshare extends PresentationSource
{
	private $apiKey = '0LBfLsve';
	private $apiSecret = 'tkO5kMyW';
	private $userName = 'saas_ru';
	private $password = '1234567!';
		
	function presType()
	{
		return $this->ext;
	}
	
	function upload($filePath, $title, $fileName, $tags)
	{
		$apiKey = $this->apiKey;
		$apiSecret = $this->apiSecret;
		$userName = $this->userName;
		$password =$this->password;
		
		$ts = time();
		$hash = sha1("{$apiSecret}{$ts}");
		
		$req =new HTTP_Request2('https://www.slideshare.net/api/1/upload_slideshow', HTTP_Request2::METHOD_POST);
		$req->addPostParameter(array(
			'api_key' => $apiKey,
			'sharedsecret' => $apiSecret,
			'username' => $userName,
			'password' => $password,
			'slideshow_title' => $title,
			'slideshow_tags' => $tags,
			'ts' => $ts,
			'hash' => $hash 
		));
		$req->setConfig(array(
			'ssl_verify_peer'   => FALSE,
			'ssl_verify_host'   => FALSE
		));				
		
		$req->addUpload('slideshow_srcfile', $filePath);
		
		$response = $req->send();
		if (200 != $response->getStatus()) {
			error('Unexpected HTTP status: ' . $response->getStatus() . ' ' .
				 $response->getReasonPhrase());
		}
		
		
		$xmlString = $response->getBody();
		$id = null;
		if(preg_match('/\<slideshowid\>(\d+)\<\/slideshowid\>/i', $xmlString, $matches))
		{
			$id = $matches[1];
		}
		if(!$id)error('ошибка slideshow '. $xmlString);
		return "http://www.slideshare.net/slideshow/embed_code/$id";		
	}
	
	function delete($url)
	{
		$apiKey = $this->apiKey;
		$apiSecret = $this->apiSecret;
		$userName = $this->userName;
		$password =$this->password;
		
		$ts = time();
		$hash = sha1("{$apiSecret}{$ts}");
		
		$id = null;
		if(preg_match('/slideshare\.net\/slideshow\/embed_code\/(\d+)/', $url, $matches))
		{
			$id = $matches[1];
		}
		if(!$id) error('неверный адрес');
		
		$req =new HTTP_Request2('https://www.slideshare.net/api/1/delete_slideshow', HTTP_Request2::METHOD_POST);
		$req->addPostParameter(array(
			'api_key' => $apiKey,
			'sharedsecret' => $apiSecret,
			'username' => $userName,
			'password' => $password,
			'slideshow_id' => $id,
			'ts' => $ts,
			'hash' => $hash 
		));
		$req->setConfig(array(
			'ssl_verify_peer'   => FALSE,
			'ssl_verify_host'   => FALSE
		));		
		$response = $req->send();
		if (200 != $response->getStatus()) {
			error('Unexpected HTTP status: ' . $response->getStatus() . ' ' .
				 $response->getReasonPhrase());
		}
		
		$xmlString = $response->getBody();
		$id = null;
		if(preg_match('/\<slideshowid\>(\d+)\<\/slideshowid\>/i', $xmlString, $matches))
		{
			$id = $matches[1];
		}
		if(!$id)error('ошибка slideshow '. $xmlString);
		return $id;			
	}
}

class Youtube extends PresentationSource
{
	private $email = 'portal@saas.ru';
	private $password = 'qldtr2012';
	private $key = 'AI39si5AXRVOyax3tLPbFjtLZ3OUDVOpRIPP9V6mFjzKuhnD6wCIZgu_g1g4OmjA0qLx0hnf7ojkaqj_IIDufjEMzIJlbPgfBw';
	private $api_name = 'saas.ru';
	
	function presType()
	{
		return 'video';
	}
	
	function upload($filePath, $title, $fileName, $tags)
	{
		$email = $this->email;
		$password = $this->password ;
		$key = $this->key;
		$api_name = $this->api_name;
		
		//получение токена********
		$req =new HTTP_Request2('https://www.google.com/youtube/accounts/ClientLogin', HTTP_Request2::METHOD_POST);
		$req->addPostParameter(array(
			'Content-Type' => 'application/x-www-form-urlencoded'
		));
		$req->setConfig(array(
			'ssl_verify_peer'   => FALSE,
			'ssl_verify_host'   => FALSE
		));
		$req->setBody("Email=$email&Passwd=$password&service=youtube&source=saas_ru");
		
		$response = $req->send();
		if (200 != $response->getStatus()) {
			error('Unexpected HTTP status: ' . $response->getStatus() . ' ' .
				 $response->getReasonPhrase());
		}

		$xmlString = $response->getBody();	
		$token = null;
		if(preg_match('/Auth\=(\S+)\s+YouTubeUser\=(\S+)/', $xmlString, $matches))
		{
			$token = $matches[1];
			$api_name = $matches[2];
		}		
		if(!$token) error('invalid token');
		//****************
		
		//отправка заголовков и видео***** 
		$boundary = 'f93dcbA3';
		
		if(!$tags) 
		{
			$tags = 'saas.ru';
		}else{
			$buf = explode(',', $tags);
			$buf2 = array();
			foreach($buf as $iter) //длина слова от 2 до 25 символов
			{
				if(mb_strlen($iter) < 2) $iter .= '_saas.ru';
				$buf2[]= mb_substr($iter, 0, 25);  
			}
			$tags = mb_substr(implode(',', $buf2), 0, 120); //всех вместе - меньше 120 символов
		}
		
		$xml = 	"<?xml version='1.0'?>
		<entry xmlns='http://www.w3.org/2005/Atom'
		xmlns:media='http://search.yahoo.com/mrss/'
		xmlns:yt='http://gdata.youtube.com/schemas/2007'>
		<media:group>
		<media:title type='plain'>$title</media:title>
		<media:description type='plain'>$title</media:description>
		<media:category scheme='http://gdata.youtube.com/schemas/2007/categories.cat'>People</media:category>
		<media:keywords>$tags</media:keywords>
		</media:group>
		</entry>";
		
		$body = 
		"\r\n\r\n".
		"--$boundary\r\n".
		"Content-Type: application/atom+xml; charset=UTF-8\r\n".
		"\r\n".
		$xml.
		"\r\n".
		"--$boundary\r\n".
		"Content-Type: video/mpeg\r\n".
		"Content-Transfer-Encoding: binary\r\n".
		"\r\n";		
		$body.=file_get_contents($filePath);
		$body.="\r\n--$boundary--";
		
		$fp = fsockopen ("uploads.gdata.youtube.com", 80, $errno, $errstr, 20);
		if(!$fp) error('cant open socket');

		$request ="POST /feeds/api/users/default/uploads HTTP/1.1\r\n";
		$request.="Host: uploads.gdata.youtube.com\r\n";
		$request.="Content-Type: multipart/related; boundary=$boundary\r\n";
		$request.="Content-Length: ".strlen($body)."\r\n";
		$request .="Authorization: GoogleLogin auth=$token\r\n";
		$request.="X-GData-Key: key=$key \r\n";
		$request.="Slug: ".pathinfo($fileName, PATHINFO_FILENAME)." \r\n";
		$request.="GData-Version: 2 \r\n";
		
		$request.="\r\n";
		$request.=$body;
		$request.="\r\n";
		
		socket_set_timeout($fp, 10);
		fputs($fp,$request,strlen($request)) or error('Ошибка записи в сокет');
		$response = fread($fp,3280);
		fclose($fp);	
		
		$videoUrl = null;
		if(preg_match('/\<id\>\S+\:video\:(\S+)\<\/id\>/', $response, $matches))
		{
			$id = $matches[1];
			$videoUrl = "http://www.youtube.com/embed/$id";
		}		
		if(!$videoUrl) error('Ошибка видео: '.$response);
		return $videoUrl;	
	}
	function delete($url)
	{
		$email = $this->email;
		$password = $this->password ;
		$key = $this->key;
		$api_name = $this->api_name;
		
		if(preg_match('/youtube\.com\/embed\/([A-Za-z_0-9\-]+)/', $url, $matches))
		{
			$id = $matches[1];
		}
		if(!$id) error('неверный адрес');
		
		//получение токена********
		$req =new HTTP_Request2('https://www.google.com/youtube/accounts/ClientLogin', HTTP_Request2::METHOD_POST);
		$req->addPostParameter(array(
			'Content-Type' => 'application/x-www-form-urlencoded'
		));
		$req->setConfig(array(
			'ssl_verify_peer'   => FALSE,
			'ssl_verify_host'   => FALSE
		));
		$req->setBody("Email=$email&Passwd=$password&service=youtube&source=saas_ru");
		
		$response = $req->send();
		if (200 != $response->getStatus()) {
			error('Unexpected HTTP status: ' . $response->getStatus() . ' ' .
				 $response->getReasonPhrase());
		}

		$xmlString = $response->getBody();	
		$token = null;
		if(preg_match('/Auth\=(\S+)\s+YouTubeUser\=(\S+)/', $xmlString, $matches))
		{
			$token = $matches[1];
			$api_name = $matches[2];
		}		
		if(!$token) error('invalid token');
		//****************
		
		//удаление видео***** 
		$fp = fsockopen ("gdata.youtube.com", 80, $errno, $errstr, 20);
		if(!$fp) error('cant open socket');

		$request ="DELETE /feeds/api/users/default/uploads/$id HTTP/1.1\r\n";
		$request.="Host: gdata.youtube.com\r\n";
		$request.="Content-Type: application/atom+xml\r\n";
		$request .="Authorization: GoogleLogin auth=$token\r\n";
		$request.="X-GData-Key: key=$key \r\n";
		$request.="GData-Version: 2 \r\n";
		
		$request.="\r\n";
		socket_set_timeout($fp, 10);

		fputs($fp,$request,strlen($request));
		$response = fread($fp,3280);
		fclose($fp);	
		
		if(!preg_match('/200 OK/', $response, $matches))
		{
			error('Ошибка видео: '.$response);
		}		
		return $id;		
	}
}
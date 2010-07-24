<?php
/**
 * Este ficheiro vai ler coisas de um url e saca-las para algum sitio.
 * vai tambem tentar sacar coisas do legendas.tv para me facilitar a vida!
 * 
 */
class httpdownload
{
	private $debug;
	private $tmp_folder;
	private $error_msg = false;
	private $cookie_file = false;
	private $downloaded_file = false;
	private $last_bytes_downloaded = 0;
	private $last_url_downloaded = "";
	
	/**
	 * quanto este objecto eh criado, temos logo a pasta onde isto vai servir de base
	 * 
	 * @param string $pasta
	 */
	public function __construct($tmp_folder)
	{
		$this->tmp_folder = $tmp_folder;
		$this->debug = debug::getInstance();
	}
	
	public function getErrorMsg()
	{
		return $this->error_msg;
	}
	
	private function initLegendastv()
	{
		$this->debug->log(__METHOD__."() initing legendas.tv login!!");
		$this->cookie_file = $this->tmp_folder."/legendastv_cookiefile";
		if (!file_exists($this->cookie_file)) {
			$f = fopen($this->cookie_file,'w') or die('The cookie file could not be opened. Make sure this directory has the correct permissions');
			fclose($f);
		} 
		// fazer login
		$this->downloadUrlCurl("http://legendas.tv/login_verificar.php", array("chkLogin"=>"1","entrar.x"=>"27","entrar.y"=>"14","txtLogin"=>"pluto","txtSenha"=>"pluto123"));
	}
	
	/**
	 * funcao que faz o download com o curl de um file!
	 * 
	 * @param string $url
	 */
	private function downloadUrlCurl($url, $post=false)
	{
		$this->debug->log(__METHOD__."() starting download of $url ...");
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6"); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		if($post!=false)
		{
			$this->debug->logArray(__METHOD__."() setting POST info for $url!", $post);
			$post = http_build_query($post);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		
		if ($this->cookie_file != false) curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
		if ($this->cookie_file != false) curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file); 
		$rec_data = curl_exec($ch);
		$info = curl_getinfo($ch);
		$this->debug->logArray(__METHOD__."() Curl Download Log", $info);

		if($rec_data==false)
		{
			$this->error_msg = curl_error($ch);
			curl_close ($ch);
			return false;
		}
		
		if(isSet($info['size_download']))
			$this->last_bytes_downloaded = $info['size_download'];
		
		if(isSet($info['url']))
			$this->last_url_downloaded = $info['url'];	
			
		curl_close ($ch);
		return $rec_data;
	}
	
	public function getLastDownloadedBytes()
	{
		return $this->last_bytes_downloaded;
	}
	
	private function saveToFile($data, $filename)
	{
		$this->debug->log(__METHOD__."() saving data to $filename ...");
		if(file_put_contents($filename, $data, FILE_BINARY))
			return $filename;
		else
			return false;
	}
	
	public function downloadUrl($url)
	{
		if(strpos($url, "legendas.tv")!==false)
		{
			// eh um legendas.tv, toca a fazer login e retirar o www.
			$url = str_replace("www.","",$url);
			$this->initLegendastv();
		}
		
		$data = $this->downloadUrlCurl($url);
		if($data!==false)
		{
			$this->downloaded_file = $this->tmp_folder.DIRECTORY_SEPARATOR.substr($this->last_url_downloaded, strripos($this->last_url_downloaded, "/"));
			return $this->saveToFile($data, $this->downloaded_file);
		}
		
		$this->debug->error(__METHOD__."() error in downloading file! no data returned from curl...");
		return false;
	}
	
	public function cleanup()
	{
		if($this->downloaded_file!=false && is_file($this->downloaded_file))
		{
			unlink($this->downloaded_file);
		}	
	}
}
?>
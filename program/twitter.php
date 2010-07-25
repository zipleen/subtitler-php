<?php
class twitter_legendastv{
	/**
	 * @var $debug debug
	 */
	private $debug;
	
	// url do twitter com a feed json do legendas onde se vai fazer parse da cena
	private $twitter_url = "http://api.twitter.com/1/statuses/user_timeline.json?id=legendas";
	
	private $data = false;
	
	public function __construct()
	{
		$this->debug = debug::getInstance();
	}
	
	private function getTwitterData()
	{
		include_once(dirname(__FILE__)."/httpdownload.php");
		$httpdownload = new httpdownload("/tmp");
		$response = $httpdownload->downloadUrlCurl($this->twitter_url);
		if($response!=false)
		{
			$jsondata = json_decode($response);
			if(!is_array($jsondata))
			{
				$this->debug->error(__METHOD__."() nao consegui fazer decode do json!");
				return false;	
			}
			$this->debug->logArray(__METHOD__." twitter replyed json data",$jsondata);
			foreach($jsondata as $i=>$jsd)
			{
				$str = $jsd->text;
				// primeiro encontrar se isto eh um tweet de legenda
				if(stripos($str, "http://legendas.tv/p/"))
				{
					$this->debug->log(__METHOD__."() processing ".$str);
					$url = trim(substr($str, stripos($str, "http://legendas.tv/p/")));
					
					$t = substr($str, 8);
					$title = trim( substr( $t, 0, stripos($t, "postada:") ) );
					$this->data[strtolower($title)] = $url;
				}
				else
					$this->debug->log(__METHOD__."() skipped $str...");	
			}
			$this->debug->logArray(__METHOD__."() twitter data in memory", $this->data);
			return true;
		}
		else 
		{
			$this->debug->error(__METHOD__."() nao consegui sacar o url do twitter!");
			return false;
		}
	}
	
	public function getUrlForSrt($name)
	{
		// primeiro temos de saber se temos a cena em ram
		if($this->data==false)
		{
			if(!$this->getTwitterData())
				return false; // isto eh para se houver algum tipo de erro parvo
			
		}
		
		// tentar limpar o nome!
		$delete_names = array(".avi",".[VTV].avi", "/");
		$name = strtolower(str_replace($delete_names, "", $name));
		
		if(isSet($this->data[$name]))
		{
			return $this->data[$name];
		}
		
		return false;
	}
}
?>
<?php
class debug{
	
	private $do_debug = false;
	private $filephp;
	
	/**
	 * singleton
	 *
	 * @return object debug
	 */
	public static function getInstance ()
    // this implements the 'singleton' design pattern.
    {
		static $instance;

        if (!isset($instance)) {
            $c = __CLASS__;
            $instance = new $c;
        } // if
       
        return $instance;
    } // getInstance
	
	public function init($debugit)
	{
		if($debugit==true)
		{
			$this->do_debug = true;
			require(dirname(__FILE__).'/FirePHP.class.php');
			$this->firephp = FirePHP::getInstance(true);
			// exception e register error handler
			$this->firephp->registerErrorHandler();
			$this->firephp->registerExceptionHandler();
		}
	}
	
	public function log($text, $level=1)
	{
		if(!$this->do_debug)
			return;
			
		$text = html_entity_decode($text);
		switch ($level){
			case 1: $this->firephp->fb($text, FirePHP::LOG); break;
			case 2: $this->firephp->fb($text, FirePHP::INFO); break;
			case 3: $this->firephp->fb($text, FirePHP::WARN); break;
			case 4: $this->firephp->fb($text, FirePHP::ERROR); break;
			default: $this->firephp->fb($text, FirePHP::INFO); break;
		}
	}
	
	public function logArray($msg, array $array, $titles = false)
	{
		if(!$this->do_debug)
			return;
		
		$table = array();
		if($titles==false)
			$table[] = array('','');
		else
			$table[] = $titles;
		foreach($array as $p=>$t){
			$table[] = array($p,$t);
		}
		$this->firephp->table($msg, $table);
	}
	
}
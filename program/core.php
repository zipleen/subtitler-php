<?php
/*
 * Este ficheiro vai tratar de inicializar as coisas
 * 
 */

class core{
	private $config = array();
	private $filesystem;
	private $pasta_selecionada;
	private $debug;
	
	/**
	 * singleton
	 *
	 * @return object core
	 */
	public static function getInstance ()
    // this implements the 'singleton' design pattern.
    {
		static $instance;

        if (!isSet($instance)) {
            $c = __CLASS__;
            $instance = new $c;
        } // if
        return $instance;
    } // getInstance
	
	/**
	 * Este metodo inicializa a sessao e guarda umas variaveis
	 */
	public function __construct()
	{
		// inicializar sessao
		session_start();
		
		// ler o ficheiro de config que vai conter o array de valores
		if(!is_file(dirname(__FILE__)."/../config/cnf.php"))
			die("Ficheiro cnf.php nao existe!");
		include(dirname(__FILE__)."/../config/cnf.php");
		
		include(dirname(__FILE__)."/debug.php");
		$this->debug = debug::getInstance();
		$this->debug->init($debug);
		
		if(is_array($config) && count($config)>0)
		{
			foreach($config as $nome=>$opc)
			{
				if(isSet($opc['dir']) && is_dir($opc['dir']))
				{
					if(!isSet($primeira_pasta))
						$primeira_pasta = $nome;
					$this->config[$nome]=$opc['dir'];
					$this->config_nomes[$nome]=$opc['nome'];
				}
			}
			$this->debug->logArray("config:",$this->config);	
			$this->debug->logArray("config names:",$this->config_nomes);	
		}
		else
		{
			die("Configure o ficheiro config/cnf.php !!");
		}
		
		// ir buscar os valores que devem estar na sessao
		if( isSet($_SESSION['pasta']) && isSet($this->config[$_SESSION['pasta']]) )
		{
			$this->debug->log(__METHOD__."() existe a pasta! setting to: ".$_SESSION['pasta']);
			$this->pasta_selecionada = $_SESSION['pasta'];
		}
		else
		{
			$this->debug->log(__METHOD__."() first time setting pasta! -> ".$primeira_pasta);
			$this->pasta_selecionada = $primeira_pasta;
		}
		
		if(!isSet($this->config[$this->pasta_selecionada]))
		{
			die("pasta selecionada nao eh valida! verificar config / sessao...");
		}
		
		// abrir objectos e inicializa-los
		include(dirname(__FILE__)."/filesystem.php");
		$this->filesystem = new filesystem( $this->config[$this->pasta_selecionada] );
	}
	
	public function __destruct()
	{
		
	}
	
	/**
	 * Devolve o nome da pasta selecionada
	 * 
	 * @return string
	 */
	public function getCurrentName()
	{
		return $this->config_nomes[$this->pasta_selecionada];
	}
	
	/**
	 * devolve <a href='link'>directoria</a> o array 
	 * 
	 * @param string $separator - string que separa os links
	 * @return array
	 */
	public function getLinksForDirectories($separator=" ")
	{
		$html = "";
		foreach($this->config_nomes as $tipo=>$nome)
		{
			if($tipo==$this->pasta_selecionada)
				$html .= "<span class='active'>$nome</span>".$separator;
			else
				$html .= "<a href='index.php?op=changedir&tipo=$tipo'>$nome</a>".$separator;
		}
		return $html;
	}
	
	public function getDirectories()
	{
		return $this->config;
	}
	
	/**
	 * muda a directoria que estamos neste momento a mostrar
	 * 
	 * @param string $name
	 */
	private function changeDirectory($name)
	{
		if( isSet($this->config[$name]) )
		{
			$_SESSION['pasta'] = $name;
			$this->debug->log(__METHOD__."() setting pasta para ".$_SESSION['pasta']);
			session_write_close(); // parece que nao estava a gravar a sessao, vamos forcar isto
			$this->redirectBack();
		}
		else
		{
			echo "Option does not exist!";
			exit();
		}
	}

	/**
	 * faz o redirect para a pagina "anterior", ou se nao viemos da pagina em questao, faz redirect para o script principal
	 * 
	 */
	private function redirectBack()
	{
		
		$this->debug->log(__METHOD__."() redirecting to ".$_SERVER['SCRIPT_NAME']);
		header("location: ".$_SERVER['SCRIPT_NAME']);
		exit();
	}
	
	private function output($html)
	{
		echo $html;
	}
	
	/**
	 * mesmo metodo que o lv2 :D aqui eh onde as coisas vao ser processadas
	 */
	public function dispatchEvents()
	{
		$answer = '';
		$op = $_REQUEST['op'];
		$this->debug->log(__METHOD__."() action: $op");
		switch($op)
		{
			case 'getFileTree':
				$answer = $this->filesystem->getFileTree();
				break;
			
			case 'getsubtitle':
				if(isSet($_REQUEST['file']))
					$this->filesystem->getFile($_REQUEST['file']);
				else 
					$answer = "Erro - falta o filename!";
				break;
				
			case 'submit_subtitle':
				if(isSet($_POST['filename']) && isSet($_FILES['file1']) )
					$answer = $this->filesystem->submitFile($_POST['filename'], "file1");
				else
					$answer = "Erro - nao ha filename ou file!";
				break;
			
			case 'changedir':
				$this->changeDirectory($_REQUEST['tipo']);
				break;
				
			default:
				// mostrar a pagina
				ob_start();
				include(dirname(__FILE__)."/../config/template.php");
				$answer = ob_get_contents();
				ob_end_clean();
				
				break;
		}
		$this->output($answer);
	}
}
?>
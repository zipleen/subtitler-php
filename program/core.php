<?php
/*
 * Este ficheiro vai tratar de inicializar as coisas
 * 
 */

class core{
	private $config = array();
	private $filesystem;
	private $pasta_selecionada;
	
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
		}
		else
		{
			die("Configure o ficheiro config/cnf.php !!");
		}
		
		// ir buscar os valores que devem estar na sessao
		if( isSet($_SESSION['pasta']) && isSet($this->config[$_SESSION['pasta']]) )
		{
			$this->pasta_selecionada = $_SESSION['pasta'];
		}
		else
		{
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
				$html .= $nome.$separator;
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
			$_SESSION['pasta'] = $this->pasta_selecionada;
			$this->redirectBack();
		}
		else
		{
			echo "Option does not exist!";
			exit();
		}
	}
	
	/**
	 * Comparar 2 urls
	 * Os www. sao ignorados. 
	 * se o url 1 bater certo com o url2 eh retornado true.
	 * 
	 * @param string $url1
	 * @param string $url2
	 * @return bool
	 */
	public static function comparteTwoUrls($url1, $url2){
		$p1 = parse_url($url1);
		$p2 = parse_url($url2);
		if(trim(strtolower(str_replace("www.","",$p1['host'])))==trim(strtolower(str_replace("www.","",$p2['host']))) && $p1['scheme']==$p2['scheme'])
			return true;
		else
			return false;
	}
	
	/**
	 * faz o redirect para a pagina "anterior", ou se nao viemos da pagina em questao, faz redirect para o script principal
	 * 
	 */
	private function redirectBack()
	{
		if( $this->comparteTwoUrls($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']) == true )
			header("location: ".$_SERVER['HTTP_REFERER']);
		else
			header("location: ".$_SERVER['REQUEST_URI']);	
		exit();
	}
	
	/**
	 * mesmo metodo que o lv2 :D aqui eh onde as coisas vao ser processadas
	 */
	public function dispatchEvents()
	{
		$op = $_REQUEST['op'];
		switch($op)
		{
			case 'getFileTree':
				echo $this->filesystem->getFileTree();
				break;
			
			case 'getsubtitle':
				if(isSet($_REQUEST['file']))
					$this->filesystem->getFile($_REQUEST['file']);
				else 
					echo "Erro - falta o filename!";
				break;
				
			case 'submit_subtitle':
				if(isSet($_POST['filename']) && isSet($_FILES['file1']) )
					$this->filesystem->submitFile($_POST['filename'], "file1");
				else
					echo "Erro - nao ha filename ou file!";
				break;
			
			case 'changedir':
				$this->changeDirectory($_REQUEST['tipo']);
				break;
				
			default:
				// mostrar a pagina
				include(dirname(__FILE__)."/../config/template.php");
				break;
		}
	}
}
?>
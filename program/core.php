<?php
/*
 * Este ficheiro vai tratar de inicializar as coisas
 * 
 */

class core{
	private $config = array();
	private $config_nomes = array();
	private $config_pics = array();
	private $supported_files = array('mkv','avi');
	
	private $filesystem;
	private $pasta_selecionada;
	private $temp_folder;
	/**
	 * 
	 * @var debug
	 */
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
					if(is_readable($opc['dir']))
					{
						if(!isSet($primeira_pasta))
							$primeira_pasta = $nome;
						$this->config[$nome]=$opc['dir'];
						$this->config_nomes[$nome]=$opc['nome'];
						if(isSet($opc['pic']))
						{
							$this->config_pics[$nome]=$opc['pic'];
						}
						else
						{
							$this->config_pics[$nome]='';
						}
					}
					else
					{
						echo "Directorio ".$opc['dir']." nao eh possivel de ser LIDO pelo webserver! configure as permissoes ou mude o user que esta a executar esta pagina! <br/>";
					}
				}
			}
			$this->debug->logArray("config:",$this->config);	
			$this->debug->logArray("config names:",$this->config_nomes);	
			$this->debug->logArray("config pics:",$this->config_pics);	
		}
		else
		{
			die("Configure o ficheiro config/cnf.php !!");
		}
		
		if(isSet($supported_files))
		{
			$this->debug->logArray(__METHOD__." Setting suported filetypes to:", $supported_files);
			$this->supported_files = $supported_files;
		}
		
		// primeiro verificar se temos um REQUEST para a "pasta"
		if( isSet($_REQUEST['pasta']) && isSet($this->config[$_REQUEST['pasta']]) )
		{
			$this->debug->log(__METHOD__."() foi pedido uma pasta diferente logo no request que existe! -> ".$_REQUEST['pasta']);
			$this->pasta_selecionada = $_REQUEST['pasta'];
		}
		elseif( isSet($_SESSION['pasta']) && isSet($this->config[$_SESSION['pasta']]) )
		{
			// ir buscar os valores que devem estar na sessao
			$this->debug->log(__METHOD__."() existe a pasta! setting to: ".$_SESSION['pasta']);
			$this->pasta_selecionada = $_SESSION['pasta'];
		}
		elseif( isSet($primeira_pasta) )
		{
			// default!
			$this->debug->log(__METHOD__."() first time setting pasta! -> ".$primeira_pasta);
			$this->pasta_selecionada = $primeira_pasta;
		}
		
		if(!isSet($this->config[$this->pasta_selecionada]))
		{
			die("pasta selecionada nao eh valida! verificar config / sessao...");
		}
		
		if(isSet($temp_folder))
			$this->temp_folder = $temp_folder;
		else
			$this->temp_folder = "/tmp";
		
		// abrir objectos e inicializa-los
		include(dirname(__FILE__)."/filesystem.php");
		$this->filesystem = new filesystem( $this->config[$this->pasta_selecionada], $this->supported_files);
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
	
	public function getBrowserMode()
	{
		if (strstr($_SERVER['HTTP_USER_AGENT'], "iPhone")) {
		    return "iphone";
		}
		if (strstr($_SERVER['HTTP_USER_AGENT'], "iOS")) {
		    return "ios";
		}
		return "desktop";
	}
	
	/**
	 * devolve <a href='link'>directoria</a> o array 
	 * 
	 * @param string $separator - string que separa os links
	 * @return array
	 */
	public function getLinksForDirectories($separator=" ", $before_html = "", $after_html = "")
	{
		$html = "";
		foreach($this->config_nomes as $tipo=>$nome)
		{
			$pic = '';
			if( $this->config_pics[$tipo]!="" )
			{
				$this->debug->log(__METHOD__." Metendo pic  - ".$this->config_pics[$tipo]);
				$pic = "<img class='icon_folder' src='".$this->config_pics[$tipo]."' title='$tipo'> ";
			}
			if($tipo==$this->pasta_selecionada)
				$html .= $before_html."<span class='button_directorios active'>".$pic.$nome."</span>".$separator.$after_html;
			else
				$html .= $before_html."<a class='button_directorios' href='index.php?op=changedir&pasta=$tipo'>".$pic.$nome."</a>".$separator.$after_html;
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
	 * imprime os ficheiros de uma directoria em <ul> <li> - isto foi feito para o jqueryFileTree
	 * 
	 * @param string $dir
	 * @return string
	 */
	public function getFileTreeInUL($dir)
	{
		$files = $this->filesystem->getFileTree($dir);
		$this->debug->logArray(__METHOD__."() got these files!", $files);
		$html = '';
		if( count($files) > 0 ) 
		{ 
			
			$html .= "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
			foreach($files as $t=>$opc)
			{
				if($opc['tipo']=="dir")
				{
					$html .= "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($opc['filename'], ENT_QUOTES, 'UTF-8') . "/\">" . htmlentities($opc['nome'], ENT_QUOTES, 'UTF-8') . "</a></li>";
				}
				if($opc['tipo']=="file")
				{
					$html .= "<li class=\"".$opc['class']."\"><a href=\"#\" rel=\"" . htmlentities($opc['filename'], ENT_QUOTES, 'UTF-8') . "\">" . htmlentities($opc['nome'], ENT_QUOTES, 'UTF-8') . "</a></li>";
				}
			}
			$html .= "</ul>";
		}	
		return $html;
	}
	
	/**
	 * Cria um conjunto de links html com os ultimos ficheiros que ainda nao tem legendas!
	 * 
	 * @return string
	 */
	public function getLastModifiedFilesInHtml()
	{
		$html = '';
		$i = 1;
		$array = $this->filesystem->getLastModifiedFiles();
		if(count($array)>0)
		{
			natcasesort($array);
			include_once(dirname(__FILE__)."/twitter.php");
			$twitter = new twitter_legendastv();
			foreach($array as $f)
			{
				if(strpos(strtolower($f), "sample")===false)
					$auto = "";
					$srtdownload = $twitter->getUrlForSrt($f);
					if($srtdownload!=false)
						$auto = " <span class='autodownload' id='autodownload_$i'><a href='#' onclick='helperSubmit(\"".$srtdownload."\",\"".$f."\",\"autodownload_$i\")'> Download $f.srt automaticamente do legendas.tv!</a></span>";
					$html .= "<p><a href='#' onclick='makeDownload(\"$f\")'>".$f."</a> $auto</p>";
					//$html .= "<a href='#' onclick='makeDownload(\"$f\")'>".substr($f, strrpos($f, "/")+1)."</a><br/>";
			}
		}
		else
		{
			$html = "N&atilde;o h&aacute; ficheiros nos &uacute;ltimos 15 dias que n&atilde;o tenham legendas! fixe.";
		}
		return $html;
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
		$this->debug->log(__METHOD__."() outputing html ! bye =)");
		echo $html;
	}
	
	private function renderPage()
	{
		$browser = $this->getBrowserMode();
		if(is_file(dirname(__FILE__)."/../config/template_$browser.php"))
		{
			include(dirname(__FILE__)."/../config/template_$browser.php");
		}
		else
			include(dirname(__FILE__)."/../config/template.php");
	}
	
	/**
	 * mesmo metodo que o lv2 :D aqui eh onde as coisas vao ser processadas
	 */
	public function dispatchEvents()
	{
		$answer = '';
		if(isSet($_REQUEST['op']))
			$op = $_REQUEST['op'];
		else
			$op = "";
		$this->debug->logArray("_POST", $_POST);
		$this->debug->logArray("_REQUEST", $_REQUEST);
		$this->debug->log(__METHOD__."() action: $op");
		switch($op)
		{
			case 'getLastModifiedFilesInHtml':
				$answer = $this->getLastModifiedFilesInHtml();
				break;
			
			case 'getFileTree':
				if(isSet($_REQUEST['dir']))
					$answer = $this->getFileTreeInUL($_REQUEST['dir']);
				else
					$answer = "nao esta definida a directoria para processar!";
				break;
			
			case 'getsubtitle':
				if(isSet($_REQUEST['file']))
					$this->filesystem->getFile($_REQUEST['file']);
				else 
					$answer = "Erro - falta o filename!";
				break;

			// sacar um subtitle sacando o URL e depois mandando como se fosse um file!
			case 'submit_subtitle_geturl':
				if(isSet($_POST['avifilename']) && isSet($_POST['urlsubtitle']) && preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $_POST['urlsubtitle']))
				{
					$url = $_POST['urlsubtitle'];
					// saca file
					include_once(dirname(__FILE__)."/httpdownload.php");
					$httpdownload = new httpdownload($this->temp_folder);
					$filename = $httpdownload->downloadUrl($url);
					
					if($filename!==false)
					{
						// "fakar" um upload
						$form_name = "file1";
						$_FILES = array();
						$_FILES[$form_name]['size'] = $httpdownload->getLastDownloadedBytes();
						$_FILES[$form_name]['error'] = 0;
						$_FILES[$form_name]['name'] = substr($filename, strrpos($filename, "/"));
						$_FILES[$form_name]['tmp_name'] = $filename;
						$answer = $this->filesystem->submitFile($_POST['avifilename'], "file1");
						$httpdownload->cleanup();
					}
					else
					{
						$answer  = "{ error: 'Erro a sacar o ficheiro do url pedido - mensagem: ".$httpdownload->getErrorMsg()."', msg: ' '}";
					}
				}
				else
				{
					$answer = "{ error: 'Erro - nao ha url, ou url nao valido!', msg: ' '}";
				}
				break;
				
			case 'submit_subtitle':
				if(isSet($_POST['filename']) && isSet($_FILES['file1']) )
					$answer = $this->filesystem->submitFile($_POST['filename'], "file1");
				else
					$answer = "{ error: 'Erro - nao ha filename ou file!', msg: ' '}";
					//$answer = "Erro - nao ha filename ou file!";
				break;
			
			case 'changedir':
				$this->changeDirectory($_REQUEST['pasta']);
				break;
				
			default:
				// mostrar a pagina
				ob_start();
				$this->renderPage();
				$answer = ob_get_contents();
				ob_end_clean();
				
				break;
		}
		$this->output($answer);
	}
}
?>
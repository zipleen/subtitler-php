<?php
/*
 * Este ficheiro vai tratar de ler os ficheiros do filesystem e gravar os ficheiros do filesystem
 * 
 */

class filesystem{
	private $supported_files;
	
	private $debug;
	private $pasta;
	
	/**
	 * quanto este objecto eh criado, temos logo a pasta onde isto vai servir de base
	 * 
	 * @param string $pasta
	 */
	public function __construct($pasta, $supported_files)
	{
		$this->supported_files = $supported_files;
		$this->pasta = $pasta;
		$this->debug = debug::getInstance();
		$this->debug->log(__METHOD__."() pasta selectionada como root: ".$this->pasta);
	}
	
	private function unpack($extension, $uploadedfilename, $targetfile)
	{
		include(dirname(__FILE__)."/unpack.php");
		$unpack = new unpack($extension);
		return $unpack->unpack($uploadedfilename, $targetfile);
	}
	
	/**
	 * limpa o nome do ficheiro para nao haver falhas de seguranca
	 * 
	 * @param string $filename
	 * @return string
	 */
	private function cleanFilename($filename)
	{
		$save = $filename;
		$filename = str_replace('../', '', html_entity_decode($filename)); // prevent illegal directory traversing
		$filename = str_replace('..', '', $filename); // dupla verificacao, para desaparecer todos os ".."
		$filename = urldecode($filename);
		$this->debug->log(__METHOD__."() got $save sent $filename");
		return $filename;
	}
	
	private function writeUploadedFile($form_name, $to, $dir)
	{
		// se a legenda ja existe, delete
		/*if(file_exists($root.$subtitle )){
			if(!rmdir($root.$file)) echo "erro a eliminar srt! ".$root.$file;
			//echo "eliminar $root . $subtitle";
		}*/
		//echo "copiar : ".$_FILES['file1']['tmp_name']. " para ". $root . $subtitle;
		if(is_writable($dir))
		{
			// $_FILES[$form_name]['name'] = original name
			$packed_ext = array('zip', 'rar');
			$ext = preg_replace('/^.*\./', '', strtolower($_FILES[$form_name]['name']));
			if( in_array($ext, $packed_ext) )
			{
				// vamos entrar em modo de descompactar o file e escreve-lo!
				$this->debug->log(__METHOD__."() going to try to unpack this file!!!");
				include(dirname(__FILE__)."/unpack.php");
				$unpack = new unpack($ext);
				$from = $unpack->unpack($_FILES[$form_name]['tmp_name'], substr($to, strripos($to, "/")));
				if($from===false)
				{
					$this->debug->error(__METHOD__."() erro no unpack!");
					return $unpack->getError();
				}
			}
			else
			{
				$this->debug->log(__METHOD__."() maneira normal de srt upload!");
				$from = $_FILES[$form_name]['tmp_name'];
			}
			
			$error = false;
			
			// mover o file!
			if($from!==false && rename($from, $to))
			{
				chmod($to, "775");
				$this->debug->log(__METHOD__."() Consegui escrever o file para $to!");
			}
			else
			{
				$this->debug->error(__METHOD__."() erro a escrever em " . $to);
			} 
			
			// cleanup! - se foi descompactado temos de fazer o clean
			if(isSet($unpack) && is_object($unpack))
			{
				$unpack->cleanUp();
			}
			if(is_file($_FILES[$form_name]['tmp_name']))
			{
				$this->debug->log(__METHOD__."() cleaning up o uploaded file!");
				unlink($_FILES[$form_name]['tmp_name']);
			}
			
			if($error)
				return "erro a escrever em " . $to;
			else
				return "";
		}
		else
		{
			$this->debug->error(__METHOD__."directoria nao eh possivel ser escrita");
			return "directoria nao eh possivel ser escrita";
		}

	}
	
	/**
	 * faz o submit do ficheiro! aqui eh onde eh processado tudo e depois gravado o ficheiro
	 * 
	 * @param string $filename - nome do ficheiro que veio do post
	 * @param string $form_name - nome do form
	 */
	public function submitFile($filename, $form_name)
	{
		$msg = '';
		
		$filename = $this->cleanFilename($filename);
		
		if( $filename!="" && $_FILES[$form_name]['size']<=300000 && $_FILES[$form_name]['error']==0)
		{
			if( file_exists($this->pasta . $filename) ) 
			{
				// ficheiro que vai ser o novo subtitle
				$subtitle = substr($filename,0,-3)."srt";
			
				// directoria que onde vai ser gravado o ficheiro
				// ROOT + directoria onde estava o ficheiro original
				// '/bla' + '/file/orig.mkv' fica '/bla/file'
				$dir = $this->pasta . substr($filename, 0, strripos($filename, "/"));
				
				$error = $this->writeUploadedFile($form_name, $this->pasta . $subtitle, $dir);
				if($error=="")
					$msg = "OK";
			}
			else
			{
				$this->debug->error(__METHOD__."() ficheiro ".$this->pasta . $filename." nao existe!!!");
				$error = "ficheiro ".$this->pasta . $filename." nao existe!!!";
			}
		}
		
		return "{ error: '$error', msg: '$msg '}";
	}
	
	/**
	 * envia um ficheiro srt !
	 * 
	 * @param string $filename
	 */
	public function getFile($filename)
	{
		require_once 'PEAR.php';
		require_once 'HTTP/Download.php';
		
		$filename = $this->cleanFilename($filename);
		$subtitle = substr($filename,0,-3)."srt";
		
		if( file_exists($this->pasta . $subtitle) ) 
		{
			$dl = &new HTTP_Download();
			$dl->setFile($this->pasta . $subtitle);
			$dl->send();
		}
		else 
			return "Error - file not found!";
	}
	
	/**
	 * imprime os ficheiros de uma directoria em <ul> <li> - isto foi feito para o jqueryFileTree
	 * 
	 * @param string $dir
	 * @return string
	 */
	public function getFileTreeInUL($dir)
	{
		$files = $this->getFileTree($dir);
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
	 * cria um array com o conteudo do directorio
	 * 
	 * @param string $dir
	 * @return array
	 */
	public function getFileTree($dir)
	{
		$data = array();
		$i = 0;
		$dir = $this->cleanFilename($dir);
		
		if( file_exists($this->pasta . $dir) ) 
		{
			$files = scandir($this->pasta . $dir);
			natcasesort($files);
			
			if( count($files) > 2 ) 
			{ 
				// All dirs
				foreach( $files as $file ) 
				{
					/* The 2 accounts for . and .. */
					if( file_exists($this->pasta . $dir . $file) && $file != '.' && $file != '..' && $file != '.AppleDouble' && is_dir($this->pasta . $dir . $file) ) 
					{
						$array = array();
						$array['tipo'] = "dir";
						$array['filename'] = $dir . $file;
						$array['nome'] = $file;
						$data[$i++] = $array;
					}
				}
				// All files
				foreach( $files as $file ) 
				{
					if( file_exists($this->pasta . $dir . $file) && $file != '.' && $file != '..' && !is_dir($this->pasta . $dir . $file) ) 
					{
						$ext = preg_replace('/^.*\./', '', $file);
						if( in_array(strtolower($ext), $this->supported_files) )
						{
							$subtitle = substr($file,0,-3)."srt";
							if(file_exists($this->pasta . $dir . $subtitle))
								$subtitle_existe = "tem";
							else $subtitle_existe = "n_tem";
							$array = array();
							$array['tipo'] = "file";
							$array['class'] = "file ext_$ext $subtitle_existe";
							$array['filename'] = $dir . $file;
							$array['nome'] = $file;
							$data[$i++] = $array;
						}
					}
				}
			}		
		}
		return $data;
	}
}
?>
<?php
/*
 * Este ficheiro vai tratar de ler os ficheiros do filesystem e gravar os ficheiros do filesystem
 * 
 */

class filesystem{
	
	private $pasta;
	private $unpack;
	
	/**
	 * quanto este objecto eh criado, temos logo a pasta onde isto vai servir de base
	 * 
	 * @param string $pasta
	 */
	public function __construct($pasta)
	{
		$this->pasta = $pasta;
	}
	
	private function unpack()
	{
		include(dirname(__FILE__)."/unpack.php");
		$this->unpack = new unpack();
		
		
	}
	
	/**
	 * limpa o nome do ficheiro para nao haver falhas de seguranca
	 * 
	 * @param string $filename
	 * @return string
	 */
	private function cleanFilename($filename)
	{
		$filename = str_replace('../', '', html_entity_decode($filename)); // prevent illegal directory traversing
		$filename = str_replace('..', '', $filename); // dupla verificacao, para desaparecer todos os ".."
		$filename = urldecode($filename);
		
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
				
		if(is_writable($dir)){
			if(move_uploaded_file($_FILES[$form_name]['tmp_name'], $to))
				return "";
			else
				return "erro a escrever em " . $to; 
		}else{
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
		
		if( $filename!="" && $_FILES[$form_name]['size']<=300000 && $_FILES[$form_name]['error']==0){
			if( file_exists($this->pasta . $filename) ) {
				// ficheiro que vai ser o novo subtitle
				$subtitle = substr($filename,0,-3)."srt";
			
				// directoria que onde vai ser gravado o ficheiro
				// ROOT + directoria onde estava o ficheiro original
				// '/bla' + '/file/orig.mkv' fica '/bla/file'
				$dir = $this->pasta . substr($filename, 0, strripos($filename, "/"));
				
				$error = $this->writeUploadedFile($_FILES[$form_name]['tmp_name'], $this->pasta . $subtitle, $dir);
				if($error=="")
					$msg = "OK";
			}
		}
		
		echo "{";
			echo				"error: '" . $error . "',\n";
			echo				"msg: '" . $msg . "'\n";
			echo "}";
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
			echo "Error - file not found!";
	}
	
	/**
	 * imprime os ficheiros de uma directoria em <ul> <li> - isto foi feito para o jqueryFileTree
	 * 
	 * @param string $dir
	 * @return string
	 */
	public function getFileTree($dir)
	{
		$dir = $this->cleanFilename($dir);
		
		if( file_exists($this->pasta . $dir) ) {
			$files = scandir($this->pasta . $dir);
			natcasesort($files);
			if( count($files) > 2 ) { /* The 2 accounts for . and .. */
				echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
				// All dirs
				foreach( $files as $file ) {
					if( file_exists($this->pasta . $dir . $file) && $file != '.' && $file != '..' && $file != '.AppleDouble' && is_dir($this->pasta . $dir . $file) ) {
						echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($dir . $file) . "/\">" . htmlentities($file) . "</a></li>";
					}
				}
				// All files
				foreach( $files as $file ) {
					if( file_exists($this->pasta . $dir . $file) && $file != '.' && $file != '..' && !is_dir($this->pasta . $dir . $file) ) {
						$ext = preg_replace('/^.*\./', '', $file);
						if($ext=="avi" || $ext=="mkv" || $ext=="mpg" || $ext=="mp4"){
							$subtitle = substr($file,0,-3)."srt";
							if(file_exists($this->pasta . $dir . $subtitle))
								$subtitle_existe = "tem";
							else $subtitle_existe = "n_tem";
							echo "<li class=\"file ext_$ext $subtitle_existe\"><a href=\"#\" rel=\"" . htmlentities($dir . $file) . "\">" . htmlentities($file) . "</a></li>";
						}
					}
				}
				echo "</ul>";	
			}
		}
	}
}
?>
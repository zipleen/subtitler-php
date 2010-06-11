<?php
/**
 * Este ficheiro vai tratar de descompactar o ficheiro e verificar se eh possivel ter um ficheiro srt e devolver o ficheiro srt
 * 
 */

class unpack{
	private $debug;
	private $type;
	private $error = false;
	
	private $temp_folder;
	private $srt_file;
	
	public function getError()
	{
		return $this->error;
	}
	
	public function __construct($extension)
	{
		$this->type = $extension;	
		$this->debug = debug::getInstance();
	}
	
	/**
	 * Elimina uma directoria recursivamente, serve para depois quando acabarmos o unpack
	 * 
	 * @param string $dir
	 */
	private function deleteDirectory($dir) {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir)) 
        {
        	$this->debug->log(__METHOD__."() Deleting file $dir !");
        	return unlink($dir);
        }
        $files = scandir($dir);
        foreach ($files as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!$this->deleteDirectory($dir.DIRECTORY_SEPARATOR.$item)) return false;
        }
        $this->debug->log(__METHOD__."() Deleting directory $dir !");
        return rmdir($dir);
    }
	
	/**
	 * Cria um directorio temporario para deopis ser eliminado
	 * 
	 * @return string
	 */
	private function createTempDir()
	{
		// usando um ciclo for metemos um limite se houver algum erro nisto
		for($i=1; $i<=100; $i++)
		{
			if(!is_dir("/tmp/subtitlestemp-$i") && !is_file("/tmp/subtitlestemp-$i"))
			{
				if(mkdir("/tmp/subtitlestemp-$i"))
				{
					$this->temp_folder = "/tmp/subtitlestemp-$i";
					return "/tmp/subtitlestemp-$i";
				}
				else
				{
					// deu erro ?! 
					$this->debug->error(__METHOD__."() erro a criar a directoria /tmp/subtitlestemp-$i !");
				}
			}
		}
		$this->error = "Erro a criar a directoria temporaria para se conseguir descompactar o ficheiro.";
		return false;
	}
	
	/**
	 * Este metodo vai procurar um nome de ficheiro que ainda nao exista!
	 * 
	 */
	private function getTempFilename()
	{
		for($i=1; $i<=100; $i++)
		{
			if(!is_file("/tmp/srttemp-$i.srt"))
			{
				return "/tmp/srttemp-$i.srt";
			}
		}
		$this->debug->error(__METHOD__."() entao temos 100 files ja criados!? wtf ?!");
		unlink("/tmp/srttemp-1.srt");
		return "/tmp/srttemp-1.srt";
	}
	
	private function uncompressFile( $uploadedfilename , $to )
	{
		$output = '';
		switch($this->type)
		{
			case 'rar':
				$command = 'unrar x -o+ '.escapeshellarg($uploadedfilename).' '.$to.' 2>&1';
				$this->debug->log(__METHOD__."() going to unrar with: ".$command);
				exec($command, $output);
				foreach($output as $o){
					if(strpos($o,'password incorrect')!==false||strpos($o,'CRC failed in')!==false){
						$this->debug->error(__METHOD__."() houve erros a descompactar ?! -> $o");
					}
				}
				
				break;
				
			case 'zip':
				$command = 'unzip -o '.escapeshellarg($uploadedfilename).' -d '.$to.' 2>&1';
				$this->debug->log(__METHOD__."() going to unzip with: ".$command);
				exec($command, $output);
				foreach($output as $o){
					if(strpos($o,'error')!==false){
						$this->debug->error(__METHOD__."() houve erros a descompactar ?! -> $o");
					}
				}
				break;
		}
	}
	
	/**
	 * Copia o ficheiro para um sitio temporario no /tmp para depois ser "movido" para o sitio certo
	 * 
	 * @param unknown_type $dir
	 * @param unknown_type $item
	 */
	private function moveFileToBeCopied($dir, $item)
	{
		$path = $dir .DIRECTORY_SEPARATOR. $item;
		$newfile = $this->getTempFilename();
		$this->debug->log(__METHOD__."() encontrei um file com o nome $file que existe em $path ! Vou move-lo para $newfile .");
		rename($path, $newfile);
		$this->srt_file = $newfile;
	}
	
	/**
	 * Encontrar o ficheiro $file dentro da $dir - basicamente quero ver se consigo encontrar um match para o ficheiro srt, porque so um pode ser eleito :X
	 * 
	 * @param string $dir
	 * @param string $file
	 */
	private function findFileInDirectory( $dir, $file )
	{
		$filelower = strtolower(file);
		if(is_dir($dir))
		{
		 	$files = scandir($dir);
	        foreach ($files as $item) 
	        {
	            if ($item == '.' || $item == '..') continue;
	            // verificar se o file eh mesmo este !
	            if( strtolower($item) == $filelower )
	            {
	            	// eh este o file que quero!!!
	            	$this->moveFileToBeCopied($dir, $item);
	            	return $this->srt_file;
	            }
	        }
	        // nao encontrei nenhum ficheiro com o mesmo nome!!! omg vamos tentar fazermos de chico-espertos e tentar descobrir um file srt!
	        $hd = false;
	        if( strpos($filelower, "x264")!==false )
	        {
	        	$hd = true;
	        }
	        
	        $this->debug->log(__METHOD__."() nao descobri nenhum file com o mesmo nome!!");
	        foreach ($files as $item) 
	        {
	        	if ($item == '.' || $item == '..') continue;
	        	$ext = preg_replace('/^.*\./', '', strtolower($item));
	        	if($ext == "srt")
	        	{
	        		// vamos processar o file
	        		$filename = strtolower($item);
	        		if($hd==true)
	        		{
	        			if( strpos($filename, "x264")!==false )
	        			{
	        				$this->debug->log(__METHOD__."() acho que este file tem potencial.. vou escolher este! -> ".$item);
	        				$this->moveFileToBeCopied($dir, $item);
	            			return $this->srt_file;
	        			}
	        		}
	        		else
	        		{
	        			// procurar xvid
	        			if( strpos($filename, "xvid")!==false )
	        			{
	        				$this->debug->log(__METHOD__."() acho que este file tem potencial.. vou escolher este! -> ".$item);
	        				$this->moveFileToBeCopied($dir, $item);
	            			return $this->srt_file;
	        			}
	        		}
	        	}
	        }
	        
	        // se cheguei aqui, nao tenho file !
	        $this->debug->error(__METHOD__."() nao encontrei file para descompactar :(");
	        $this->error = "Nao encontrei nenhum ficheiro .srt que conseguisse usar automaticamente. Por favor descompacte manualmente o ficheiro e envie apenas o srt.";
	        return false;
		}
		else
		{
			$this->debug->error(__METHOD__."()  directoria para encontrar nao existe ?!?!");
			return false;
		}
	}
	
	/**
	 * Metodo base para fazer unpack de um ficheiro, e procura pelo targetfile
	 * 
	 * @param string $uploadedfilename
	 * @param string $targetfile
	 */
	public function unpack( $uploadedfilename, $targetfile )
	{
		// 1 - criar pasta temporaria
		$tempDir = $this->createTempDir();
		if($tempDir!==false)
		{
			// 2 - descompactar o ficheiro
			$this->uncompressFile( $uploadedfilename, $tempDir );
			
			// 3 - encontrar o ficheiro srt que queremos e retornar a path!!
			return $this->findFileInDirectory( $tempDir, $targetfile );
		}
		else
		{
			$this->debug->error(__METHOD__."() erro a criar a directoria?!");
			return false;
		}
	}
	
	/**
	 * metodo que depois de o ficheiro ter sido movido, temos de chamar para limpar o lixo que fizemos!
	 * 
	 */
	public function cleanUp()
	{
		if(is_file($this->srt_file))
		{
			$this->debug->log(__METHOD__."() deleting $this->srt_file !");
			unlink($this->srt_file);
		}
		if(is_dir($this->temp_folder))
		{
			$this->debug->log(__METHOD__."() going to delete directory $this->temp_folder !");
			$this->deleteDirectory($this->temp_folder);
		}
	}
}
?>
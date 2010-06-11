<?php

// ficheiro de config. 
//
// eh apenas um array com as directorias que queremos ler/escrever com um id
$config = array(
			'torrents'=>array('nome'=>'Torrents', 'dir'=>'/home/torrents'),
			'series'=>array('nome'=>'Series', 'dir'=>'/home/ftp/MANDA_PARA_AKI/TVRIP'),
			'serieshd'=>array('nome'=>'Series HD', 'dir'=>'/home/ftp/MANDA_PARA_AKI/TVHD'),
			'filmeshd'=>array('nome'=>'Filmes HD', 'dir'=>'/home/ftp/MANDA_PARA_AKI/HD'),
			'filmes'=>array('nome'=>'Filmes', 'dir'=>'/home/ftp/MANDA_PARA_AKI/XVID'),
			'disco_pai'=>array('nome'=>'Disco Pai', 'dir'=>'/mnt/disco_pai')
	);
$supported_files = array('avi','mkv','mp4','mpg');	
$debug = true;	
?>
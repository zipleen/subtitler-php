<?php
// SECURITY MOD FOR DEMO
session_start();
if($_SESSION['tipo_dir']>=1 && $_SESSION['tipo_dir']<=5){
        switch($_SESSION['tipo_dir']){
        case '1':
                $root='/home/ftp/MANDA_PARA_AKI/TVRIP';
                break;
        case '2':
                $root='/home/ftp/MANDA_PARA_AKI/XVID';
                break;
        case '3':
		$root='/home/torrents';
		break;
	case '4':
		$root='/mnt/disco_pai';
		break;
	case '5':
		$root='/home/ftp/MANDA_PARA_AKI/TVHD';
		break;
	case '6':
		$root='/home/ftp/MANDA_PARA_AKI/HD';
		break;
	default:
                $root='/home/ftp/MANDA_PARA_AKI/TVRIP';
                break;
        }
}else{
        $root = '/home/ftp/MANDA_PARA_AKI/TVRIP';
}
$_POST['filename'] = str_replace('../', '', $_POST['filename']); // prevent illegal directory traversing
// END SECURITY MOD

$_POST['filename'] = urldecode($_POST['filename']);

if(/*$_POST['submit']=="enviar" &&*/ $_POST['filename']!="" && $_FILES['file1']['size']<=300000 && $_FILES['file1']['error']==0){
	if( file_exists($root . $_POST['dir']) ) {
		$file = $_POST['filename'];
		
		$subtitle = substr($file,0,-3);
		$subtitle.="srt";
	
		$dir = $root.substr($file, 0, strripos($file, "/"));
		
		// se a legenda ja existe, delete
		/*if(file_exists($root.$subtitle )){
			if(!rmdir($root.$file)) echo "erro a eliminar srt! ".$root.$file;
			//echo "eliminar $root . $subtitle";
		}*/
		//echo "copiar : ".$_FILES['file1']['tmp_name']. " para ". $root . $subtitle;
		if(is_writable($dir)){
			if(!move_uploaded_file($_FILES['file1']['tmp_name'], $root.$subtitle))
				$error .= "erro a escrever em ".$root.$subtitle; 
		}else{
			$error .= "directoria nao eh possivel ser escrita";
		}
	}
}
//header("location: /~zipleen/series");
echo "{";
	echo				"error: '" . $error . "',\n";
	echo				"msg: '" . $msg . "'\n";
	echo "}";
?>

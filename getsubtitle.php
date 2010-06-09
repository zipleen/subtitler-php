<?php
require_once 'PEAR.php';
require_once 'HTTP/Download.php';
// SECURITY MOD FOR DEMO
session_start();
if($_SESSION['tipo_dir']>=1 && $_SESSION['tipo_dir']<=6){
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
$_GET['file'] = str_replace('../', '', $_GET['file']); // prevent illegal directory traversing
// END SECURITY MOD

$_GET['file'] = urldecode($_GET['file']);
$file = $_GET['file'];
$subtitle = substr($file,0,-3);
$subtitle.="srt";
if( file_exists($root . $subtitle) ) {
		
	$dl = &new HTTP_Download();
	$dl->setFile($root . $subtitle);
	$dl->send();
	}else echo "file not found!";
?>

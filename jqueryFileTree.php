<?php
//
// jQuery File Tree PHP Connector
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// History:
//
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
// Output a list of files for jQuery File Tree
//

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
$_POST['dir'] = str_replace('../', '', $_POST['dir']); // prevent illegal directory traversing
// END SECURITY MOD

$_POST['dir'] = urldecode($_POST['dir']);

if( file_exists($root . $_POST['dir']) ) {
	$files = scandir($root . $_POST['dir']);
	natcasesort($files);
	if( count($files) > 2 ) { /* The 2 accounts for . and .. */
		echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
		// All dirs
		foreach( $files as $file ) {
			if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && $file != '.AppleDouble' && is_dir($root . $_POST['dir'] . $file) ) {
				echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "/\">" . htmlentities($file) . "</a></li>";
			}
		}
		// All files
		foreach( $files as $file ) {
			if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && !is_dir($root . $_POST['dir'] . $file) ) {
				$ext = preg_replace('/^.*\./', '', $file);
				if($ext=="avi" || $ext=="mkv" || $ext=="mpg" || $ext=="mp4"){
					$subtitle = substr($file,0,-3);
					$subtitle.="srt";
					if(file_exists($root . $_POST['dir'] . $subtitle))
						$subtitle_existe = "tem";
					else $subtitle_existe = "n_tem";
					echo "<li class=\"file ext_$ext $subtitle_existe\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "\">" . htmlentities($file) . "</a></li>";
				}
			}
		}
		echo "</ul>";	
	}
}

?>

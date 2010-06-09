<?php
session_start();

$tipo = $_GET['tipo'];
if($tipo == 1 || $tipo == 2 || $tipo == 3 || $tipo == 4 || $tipo == 5 || $tipo== 6){
	$_SESSION['tipo_dir']=$tipo;
}else {
	$_SESSION['tipo_dir']=1;
}
header("location: ".$_SERVER['HTTP_REFERER']);
?>

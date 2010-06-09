<?php
session_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<title>legendas <?php echo $_SESSION['tipo_dir'];?></title>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		
		<style type="text/css">
			BODY,
			HTML {
				padding: 0px;
				margin: 0px;
			}
			BODY {
				font-family: Verdana, Arial, Helvetica, sans-serif;
				font-size: 11px;
				background: #EEE;
				padding: 15px;
			}
			
			H1 {
				font-family: Georgia, serif;
				font-size: 20px;
				font-weight: normal;
				text-align: center;
			}
			
			H2 {
				font-family: Helvetica,Georgia, serif;
				font-size: 16px;
				font-weight: normal;
				margin: 0px 0px 10px 0px;
			}
			
			.example {
				float: left;
				margin-left: 100px;
				margin-top: 20px;
			}
			
			.demo {
				width: 600px;
				height: 400px;
				border-top: solid 1px #BBB;
				border-left: solid 1px #BBB;
				border-bottom: solid 1px #FFF;
				border-right: solid 1px #FFF;
				background: #FFF;
				overflow: scroll;
				padding: 5px;
			}
			
			P.note {
				color: #999;
				clear: both;
			}
		</style>
		
		<script src="jquery.js" type="text/javascript"></script>

		<script src="jquery.easing.js" type="text/javascript"></script>
		<script src="jqueryFileTree.js" type="text/javascript"></script>
		<script src='jquery.simplemodal.js' type='text/javascript'></script>
		<script type="text/javascript" src="ajaxfileupload.js"></script>
		<link href="jqueryFileTree.css" rel="stylesheet" type="text/css" media="screen" />
		<link type='text/css' href='css/basic.css' rel='stylesheet' media='screen' />
		

<!-- IE 6 hacks -->
<!--[if lt IE 7]>
<link type='text/css' href='css/basic_ie.css' rel='stylesheet' media='screen' />
<![endif]-->

		<script type="text/javascript">
			
			$(document).ready( function() {
				
				$('#fileTreeDemo_1').fileTree({ root: '/', script: 'jqueryFileTree.php' }, 
					function(file) { 
						//file.preventDefault();
						$('#nome_file')[0].innerHTML = file;
						document.getElementById('download').innerHTML="<a href='getsubtitle.php?file="+file+"'> sacar </a>";
						$('#legenda :hidden')[0].value = file;
						$('#basicModalContent').modal();
						//alert(file);
					});
				
				/*$('#fileTreeDemo_2').fileTree({ root: 'demo/', script: 'jqueryFileTree.php', folderEvent: 'click', expandSpeed: 750, collapseSpeed: 750, multiFolder: false }, function(file) { 
					alert(file);
				});
				
				$('#fileTreeDemo_3').fileTree({ root: 'demo/', script: 'jqueryFileTree.php', folderEvent: 'click', expandSpeed: 750, collapseSpeed: 750, expandEasing: 'easeOutBounce', collapseEasing: 'easeOutBounce', loadMessage: 'Un momento...' }, function(file) { 
					alert(file);
				});
				
				$('#fileTreeDemo_4').fileTree({ root: 'demo/', script: 'jqueryFileTree.php', folderEvent: 'dblclick', expandSpeed: 1, collapseSpeed: 1 }, function(file) { 
					alert(file);
				});*/
				
			});
		</script>

<script type="text/javascript">
	function ajaxFileUpload()
	{
		$("#loading")
		.ajaxStart(function(){
			$(this).show();
		})
		.ajaxComplete(function(){
			$(this).hide();
		});

		$.ajaxFileUpload
		(
			{
				url:'submit_subtitle.php',
				secureuri:false,
				fileElementId:'file1',
				dataType: 'json',
				success: function (data, status)
				{
					if(typeof(data.error) != 'undefined')
					{
						if(data.error != '')
						{
							alert(data.error);
						}else
						{
							//alert(data.msg);
						}
					}
				},
				error: function (data, status, e)
				{
					alert(e);
				}
			}
		)
		
		return false;

	}
	</script>	

	</head>
	
	<body>
		
		<h1>legendador de
	<?php
	switch($_SESSION['tipo_dir']){
	case '1':
		echo "Series";
		break;
	case '2':
		echo "Filmes";
		break;
	case '3':
		echo "directoria dos torrents";
		break;
	case '4':
		echo "Disco do pai";
		break;
	case '5':
		echo "Series HD";
		break;
	case '6':
		echo "Filmes HD";
		break;
	default:
		$_SESSION['tipo_dir'] = 1;
		echo "Series";
		break;
	}
	?>
	</h1>

<h2> Mudar legendas para: 
<?php if($_SESSION['tipo_dir']!=1) echo "<a href='mudar_dir.php?tipo=1'>Series</a>";?> 
<?php if($_SESSION['tipo_dir']!=2) echo "<a href='mudar_dir.php?tipo=2'>Filmes</a>";?> 
<?php if($_SESSION['tipo_dir']!=3) echo "<a href='mudar_dir.php?tipo=3'>Torrents</a>";?> 
<?php if($_SESSION['tipo_dir']!=4) echo "<a href='mudar_dir.php?tipo=4'>Disco Pai</a>";?> 
<?php if($_SESSION['tipo_dir']!=5) echo "<a href='mudar_dir.php?tipo=5'>Series HD</a>";?> 
<?php if($_SESSION['tipo_dir']!=6) echo "<a href='mudar_dir.php?tipo=6'>Filmes HD</a>";?>
</h2>
<h2>Ver estado dos torrents <a href="https://rasteirinho.myphotos.cc/~zipleen/torrents">aqui</a></h2>

		<div class="example">

			<div id="fileTreeDemo_1" class="demo"></div>
		</div>

		<div style="clear:both; margin-left: 200px; padding-top: 20px;";>
		<p style='color:#FF3333'>A vermelho encontram-se os ficheiros SEM legendas.</p>
		<p style='color:#00FF66'>A verde encontram-se os ficheiros que já tem legendas</p>
		</div>

		<div id="basicModalContent" style='display:none'>
			<h1>Upload Legenda</h1>
			<div id="nome_file">AA</div>
			
			<p>Selecionar o ficheiro para fazer upload da legenda e carregar no upload</p>
			<p>Isto *vai* substituir qualquer ficheiro de legenda que ja esteja no sistema! cuidado para nao substituir legendas funcionais!</p>
			<form id="legenda" enctype="multipart/form-data" method="post" action="">
				<input type="file" size="40" id="file1" name="file1"/>
				<input type="button" name="submit" id="submit" value="enviar" onclick="return ajaxFileUpload();" />
				<input type="hidden" name="filename" id="filename" value="abc"/>
			</form>
			<img id="loading" src="loading.gif" style="display:none;">
			<p>Para sacar a legenda que este avi possa ter, seguir este link: <span id='download'></span> </p>
		</div>

	</body>
	
</html>

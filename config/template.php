<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<?php 
$core = core::getInstance();
?>
<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<title>Legendas para <?=$core->getCurrentName()?></title>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		
		<style type="text/css">
			body,
			html {
				padding: 0px;
				margin: 0px;
			}
			body {
				font-family: Verdana, Arial, Helvetica, sans-serif;
				font-size: 11px;
				background: #EEE;
				padding: 15px;
			}
			
			h1 {
				font-family: Georgia, serif;
				font-size: 20px;
				font-weight: normal;
				text-align: center;
			}
			
			h2 {
				font-family: Helvetica,Georgia, serif;
				font-size: 16px;
				font-weight: normal;
				margin: 0px 0px 10px 0px;
			}
			
			.example {
				float: left;
				margin-left: 100px;
				margin-top: 2px;
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
			
			p.note {
				color: #999;
				clear: both;
			}
			
			lastmodified p {
				margin-bottom: 0;
				font-size: 13px; 				
			}
		</style>
		
		<script src="js/jquery.js" type="text/javascript"></script>

		<script src="js/jquery.easing.js" type="text/javascript"></script>
		<script src="js/jqueryFileTree.js" type="text/javascript"></script>
		<script src='js/jquery.simplemodal.js' type='text/javascript'></script>
		<script type="text/javascript" src="js/ajaxfileupload.js"></script>
		<link href="css/jqueryFileTree.css" rel="stylesheet" type="text/css" media="screen" />
		<link type='text/css' href='css/basic.css' rel='stylesheet' media='screen' />
		

<!-- IE 6 hacks -->
<!--[if lt IE 7]>
<link type='text/css' href='css/basic_ie.css' rel='stylesheet' media='screen' />
<![endif]-->

		<script type="text/javascript">
			function makeDownload(file)
			{
				$('#nome_file')[0].innerHTML = file;
				document.getElementById('download').innerHTML="<a href='index.php?op=getsubtitle&file="+file+"'> sacar </a>";
				$('#legenda :hidden')[0].value = file;
				$('#basicModalContent').modal();
			}
			
			$(document).ready( function() {
				
				$('#fileTreeDemo_1').fileTree({ root: '/', script: 'index.php?op=getFileTree', loadMessage: 'Um momento por favor...' }, 
					function(file) { 
						//file.preventDefault();
						makeDownload($file);
						/*$('#nome_file')[0].innerHTML = file;
						document.getElementById('download').innerHTML="<a href='index.php?op=getsubtitle&file="+file+"'> sacar </a>";
						$('#legenda :hidden')[0].value = file;
						$('#basicModalContent').modal();*/
						//alert(file);
					});

				$.post('index.php?op=getLastModifiedFilesInHtml', function(data) {
					  $('#lastmodified').html(data);
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
				url:'index.php?op=submit_subtitle',
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
		
		<h1>Meter legendas na pasta <?=$core->getCurrentName()?></h1>

		<h2> Mudar legendas para... </h2>
		<h3> <?=$core->getLinksForDirectories()?></h3>

		<div class="example">

			<div id="fileTreeDemo_1" class="demo"></div>
		</div>

		<div style="clear:both; margin-left: 200px; padding-top: 10px;">
		<p style='color:#FF3333'>A vermelho encontram-se os ficheiros SEM legendas.</p>
		<p style='color:#00FF66'>A verde encontram-se os ficheiros que já tem legendas</p>
		</div>

		<div style="clear:both; margin-left: 120px; ">
			Ficheiros dos últimos 15 dias que precisam de legendas
			<div id='lastmodified' style='padding-top: 10px;'><img src='images/spinner.gif'></img> Loading...</div>
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
			<img id="loading" src="images/loading.gif" style="display:none;">
			<p>Para sacar a legenda que este avi possa ter, seguir este link: <span id='download'></span> </p>
		</div>

	</body>
	
</html>
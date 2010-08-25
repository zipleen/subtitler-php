<!DOCTYPE html >
<?php 
$core = core::getInstance();
?>
<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<title>Legendas para <?=$core->getCurrentName()?></title>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<meta name="HandheldFriendly" content="true" />
		<meta name="Viewport" content="width=device-width" />
		
		<script src="js/jquery.js" type="text/javascript"></script>
		<script src="js/jquery.easing.js" type="text/javascript"></script>
		<script src="js/jqueryFileTree.js" type="text/javascript"></script>
		<script src='js/jquery.simplemodal.js' type='text/javascript'></script>
		<script src="js/ajaxfileupload.js" type="text/javascript"></script>
		
		<link href="css/jqueryFileTree.css" rel="stylesheet" type="text/css" media="screen" />
		<link type='text/css' href='css/basic.css' rel='stylesheet' media='screen' />

		<script type="text/javascript">
			function makeDownload(file)
			{
				$('#nome_file')[0].innerHTML = file;
				document.getElementById('download').innerHTML="<a href='index.php?op=getsubtitle&file="+file+"'> sacar </a>";
				//$('#legenda :hidden')[0].value = file;
				$('#filename').val(file);
				$('#basicModalContent').modal();
			}
			
			$(document).ready( function() {
				
				$('#fileTreeDemo_1').fileTree({ root: '/', script: 'index.php?op=getFileTree', loadMessage: 'Um momento por favor...' }, 
					function(file) { 
						//file.preventDefault();
						$('#nome_file')[0].innerHTML = file;
						document.getElementById('download').innerHTML="<a href='index.php?op=getsubtitle&file="+file+"'> sacar </a>";
						$('#legenda :hidden')[0].value = file;
						$('#basicModalContent').modal();
						//alert(file);
					});

				$.post('index.php?op=getLastModifiedFilesInHtml', function(data) {
					  $('#lastmodified').html(data);
					});
				
			});

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
		);
		
		return false;

	}

	function ajaxUrlSubmit(urlsubtitle, avifilename)
	{
		$("#loading")
		.ajaxStart(function(){
			$(this).show();
		})
		.ajaxComplete(function(){
			$(this).hide();
		});

		$.ajax
		(
			{
				type: 'POST',
				url:'index.php?op=submit_subtitle_geturl',
				dataType: 'json',
				data: "urlsubtitle="+escape(urlsubtitle)+"&avifilename="+escape(avifilename), 
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
		);
	}

	function helperSubmit(urlsubtitle, avifilename, div)
	{
		$.ajax
		(
			{
				type: 'POST',
				url:'index.php?op=submit_subtitle_geturl',
				dataType: 'json',
				data: "urlsubtitle="+escape(urlsubtitle)+"&avifilename="+escape(avifilename), 
				success: function (data, status)
				{
					if(typeof(data.error) != 'undefined')
					{
						if(data.error != '')
						{
							document.getElementById(div).innerHTML=data.error;
							alert(data.error);
						}else
						{
							document.getElementById(div).innerHTML=" Download ok!";
							//alert(data.msg);
						}
					}
				},
				error: function (data, status, e)
				{
					alert(e);
				}
			}
		);
	}

	
	</script>	

	</head>
	
	<body>
		<div id="header">	
			
			<ul> <?=$core->getLinksForDirectories(" ", "<li>", "</li>")?></ul>
		</div>
		
		
					<div class="example">
						<div id="fileTreeDemo_1" class="demo"></div>
						</div>
					</div>
						
					<div style="clear:both; margin-left: 120px; ">
						<h3 style='margin-bottom:0px;'>Ficheiros dos últimos 15 dias que precisam de legendas</h3>
						<div id='lastmodified'><img src='images/spinner.gif' /> Loading...</div>
					</div>
				
		<div id="basicModalContent" style='display:none'>
			<h1>Upload Legenda</h1>
			<div id="nome_file">AA</div>

			<p>Selecionar o ficheiro para fazer upload da legenda e carregar no upload</p>
			<p>Isto *vai* substituir qualquer ficheiro de legenda que ja esteja no sistema! cuidado para nao substituir legendas funcionais!</p>
			<fieldset>
				<legend>Upload de um ficheiro .srt / .zip</legend>
				<form id="legenda" enctype="multipart/form-data" method="post" action="">
					<input type="file" size="40" id="file1" name="file1"/>
					<input type="button" name="submit" id="submit" value="enviar srt/zip" onclick="return ajaxFileUpload();" />
					<input type="hidden" name="filename" id="filename" value="abc"/>
				</form>
			</fieldset>
			
			<fieldset>
				<legend>Upload a partir de um URL</legend>
				<p>Sacar legenda por URL: <input type="text" name="urlsubtitle" id="urlsubtitle" style="width: 245px" /> <input type="button" value="obter URL" onclick="return ajaxUrlSubmit( $('#urlsubtitle').val(), $('#filename').val() );"/></p>
			</fieldset>
			
			<fieldset>
				<legend>Download do ficheiro .srt</legend>
				<p>Para sacar a legenda que este avi possa ter, seguir este link: <span id='download'></span> </p>
			</fieldset>	
			
			<div id="loading" style="display:none; text-align:center;"><img src="images/loading.gif"> Loading.. Please wait..</div>
		</div>

	</body>
	
</html>
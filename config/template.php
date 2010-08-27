<!DOCTYPE html >
<?php 
$core = core::getInstance();
?>
<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<title>Legendas para <?=$core->getCurrentName()?></title>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		
		<script src="js/jquery.js" type="text/javascript"></script>
		<script src="js/jquery.easing.js" type="text/javascript"></script>
		<script src="js/jqueryFileTree.js" type="text/javascript"></script>
		<script src='js/jquery.simplemodal.js' type='text/javascript'></script>
		<script src="js/ajaxfileupload.js" type="text/javascript"></script>
		<script src="js/jquery.tabs.min.js" type="text/javascript"></script>
		
		<link href="css/jqueryFileTree.css" rel="stylesheet" type="text/css" media="screen" />
		<link type='text/css' href='css/basic.css' rel='stylesheet' media='screen' />
<link rel="stylesheet" href="css/jquery.tabs.css" type="text/css" media="print, projection, screen">

		<!-- IE 6 hacks -->
		<!--[if lt IE 7]>
		<link type='text/css' href='css/basic_ie.css' rel='stylesheet' media='screen' />
		<![endif]-->

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

	 function resizeDivs() 
	 {  
		 $(".demo").css("height", (window.innerHeight - 82) + "px");
		 $(".demo").css("width", (window.innerWidth / 2 - 85) + "px");

		 $("#lastmodified").css("height", (window.innerHeight - 104) + "px");
		 $("#lastmodified").css("width", (window.innerWidth / 2 - 85) + "px");

		 if(window.innerWidth<900)
		 {	 
			 $("#header h1").css("display","none");
			 $(".col2").css("clear","left");
			 $(".demo").css("width", (window.innerWidth - 85) + "px");
			 $(".col1").css("width", "100%");
		 }
		 else
		 {
			$("#header h1").css("display","block");
			$(".col2").css("clear","none");
			$(".col1").css("width", "46%");
		 }	 
	 }
	</script>	

	</head>
	
	<body onload="resizeDivs()" onresize="resizeDivs()">
		<div id="header">	
			<h1><?=$core->getCurrentName()?> - Legendas<span></span></h1>
			<ul> <?=$core->getLinksForDirectories(" ", "<li>", "</li>")?></ul>
		</div>
		
		<div class="colmask doublepage">
			<div class="colleft">
				<div class="col1">
					<div class="example">
						<div id="fileTreeDemo_1" class="demo"></div>
						</div>
					</div>

				<div class="col2">			
					<div>
						<h3 style='margin-bottom:4px;'>Ficheiros dos últimos 15 dias que precisam de legendas</h3>
						<div id='lastmodified'><img src='images/spinner.gif' /> Loading...</div>
					</div>
				</div>
			</div>
		</div>
		<div id="basicModalContent" style='display:none'>
			<div id="modalheader">
			<h1>Upload Legenda</h1>
			</div>
			<div id="nome_file">AA</div>

			<p>Isto <b>vai</b> substituir qualquer ficheiro de legenda que ja esteja no sistema!</p> 
			<p>Cuidado para nao substituir legendas funcionais!</p>
			<div id="container-1">
            <ul>
                <li><a href="#fragment-1"><span>Upload de um ficheiro .srt / .zip</span></a></li>
                <li><a href="#fragment-2"><span>Upload a partir de um URL</span></a></li>

                <li><a href="#fragment-3"><span>Download do ficheiro .srt</span></a></li>
            </ul>
            <div id="fragment-1">
            	<p>Selecionar o ficheiro para fazer upload da legenda e carregar no upload</p>
            	 <form id="legenda" enctype="multipart/form-data" method="post" action="">
					<input type="file" size="40" id="file1" name="file1"/>
					<input type="button" name="submit" id="submit" value="enviar srt/zip" onclick="return ajaxFileUpload();" />
					<input type="hidden" name="filename" id="filename" value="abc"/>
				</form>
            </div>

            <div id="fragment-2">
               <p>Sacar legenda por URL: <input type="text" name="urlsubtitle" id="urlsubtitle" style="width: 245px" /> <input type="button" value="obter URL" onclick="return ajaxUrlSubmit( $('#urlsubtitle').val(), $('#filename').val() );"/></p>
            </div>
            <div id="fragment-3">
              
				<p>Para sacar a legenda que este avi possa ter, seguir este link: <span id='download'></span> </p>
            </div>
        </div>
			<script type="text/javascript">
			$('#container-1').tabs();
			</script>
			<!--  
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
			-->
			<div id="loading" style="display:none; text-align:center;"><img src="images/loading.gif"> Loading.. Please wait..</div>
		</div>

	</body>
	
</html>
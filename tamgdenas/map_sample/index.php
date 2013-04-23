<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="/js/jquery-1.6.min.js" type="text/javascript"></script>
<script src="/js/jquery-ui.custom.min.js" type="text/javascript"></script>
<link href="/css/ui.dynatree.css" rel="stylesheet" type="text/css">
<script src="/js/jquery.dynatree.min.js" type="text/javascript"></script>
<script language="JavaScript">
	$(function(){
		var tree = $("#tree").dynatree({
			checkbox: true
		}).dynatree('getRoot');
	});
</script>
</head>
<body>
<table>
<tr>
<td>
<?php //русский текст 
	function processNode($node)
	{
	?>
		<li id="<?=$node->id?>"><?=$node->title?>
		<? if(count($node->children)>0) 
		{ ?>
		<ul>
			<? 
				foreach($node->children as $child)
				{
					processNode($child);
				}
			?>
		</ul>	
		</li>
	<?
		}
	}
?>
	<div id="tree">
		<ul>
			<? foreach($... as $typeRec) { processNode($typeRec); }?>
		</ul>
	</div>
</td>
<td>
<?php 
	$map->draw('100%', '500px');
?>
</td>
</tr>
</table>

</body>
</html>

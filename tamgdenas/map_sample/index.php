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
	$mongo = new MongoClient("mongodb://217.199.220.183");
	function getTypes($parentId=null)
	{
		global $mongo;
		$cursor = $mongo->poi->poiTypes->find(array('parent'=>$parentId));
		if(count($cursor) > 0)
		{
		?>
			<ul>
			<? foreach($cursor as $iter) { ?>
				<li id="<?=$iter['_id']?>"><?=$iter['name']?>
			<?	getTypes($iter['_id']);
			} ?>
			</ul>
		<?
		}
	}
?>
	<div id="tree">
		<? getTypes(); ?>
	</div>
</td>
<td>
<?php 
	//$map->draw('100%', '500px');
?>
</td>
</tr>
</table>

</body>
</html>

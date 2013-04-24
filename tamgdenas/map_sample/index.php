<?
	require_once 'common.php';
	require_once 'map.php';
	
	$map = new map();
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="/js/common.js" type="text/javascript"></script>
<script src="/js/jquery-1.6.min.js" type="text/javascript"></script>
<script src="/js/jquery-ui.custom.min.js" type="text/javascript"></script>
<link href="/css/ui.dynatree.css" rel="stylesheet" type="text/css">
<script src="/js/jquery.dynatree.min.js" type="text/javascript"></script>


<? $map->head(); ?>
<script language="JavaScript">
	var tree = null;
	$(function(){
		tree = $("#tree").dynatree({
			checkbox: true
		}).dynatree('getRoot');
		
		<? $map->doLoad();?>
	});
	
	function refreshMap()
	{
		var selNodes = tree.tree.getSelectedNodes();
		var types =[];
		$.map(selNodes, function(node1){
            types.push(node1.data.key);
		});

		cmdAsync(prepareCmd('pois.php', {types: types}), function(pois)
		{
			alert(JSON.stringify(pois));
			//map.addMarkers(pois);
		});
	}
</script>
</head>
<body>
<table>
<tr>
<td valign="top">
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
	<button onclick="refreshMap();">Отобразить POI</button>
	<div id="tree">
		<? getTypes(); ?>
	</div>
	
</td>
<td>
<?php 
	$map->draw('800px', '600px');
?>
</td>
</tr>
</table>

</body>
</html>

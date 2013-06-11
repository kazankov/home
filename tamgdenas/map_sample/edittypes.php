<?
$cfg = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/config.ini', true);
foreach($cfg as $k=>$v)
{
	$cfg[$k] = (object)$v; 
}
$cfg = (object)$cfg;

$mongo = new MongoClient("mongodb://{$cfg->mongo->host}");
$pg = pg_connect("host={$cfg->pg->host} dbname={$cfg->pg->db} user={$cfg->pg->user} password={$cfg->pg->password}");

function getOurTypes($parentId=null)
{
	global $mongo;
	$cursor = $mongo->poi->poiTypes->find(array('parent'=>$parentId));
	if(count($cursor) > 0)
	{
	?>
		<ul>
		<? foreach($cursor as $iter) { ?>
			<li id="<?=$iter['_id']?>"  data="alias: '<?if($iter['sourceNames']) echo implode(', ', $iter['sourceNames']);?>', name: '<?=addslashes($iter['name'])?>'"><?=$iter['name']?> <?if($iter['sourceNames']) echo "(".implode(', ', $iter['sourceNames']).")";?>
		<?	getOurTypes($iter['_id']);
		} ?>
		</ul>
	<?
	}
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="/js/jquery-1.6.min.js" type="text/javascript"></script>
<script src="/js/jquery-ui.custom.min.js" type="text/javascript"></script>
<link href="/css/ui.dynatree.css" rel="stylesheet" type="text/css">
<script src="/js/jquery.dynatree.min.js" type="text/javascript"></script>
<script src="/js/jquery.contextMenu-custom.js" type="text/javascript"></script>
<link href="/css/jquery.contextMenu.css" rel="stylesheet" type="text/css" >
<script src="/js/jquery.simplemodal.js" type="text/javascript"></script>
<script src="/js/common.js" type="text/javascript"></script>
<script language="Javascript">
	function Tabs(header, body)
	{
		var i = 0;
		header.children().each(function()
		{
			$(this).attr('tab-id', i);
			$(this).click(function()
			{
				header.children().removeClass('active');
				var h = $(this);
				var tabId = h.attr('tab-id');
				h.addClass('active');
				
				var j = 0;
				body.children('div .tab').each(function()
				{
					var b = $(this); 
					if(j == tabId) 
					{
						b.show();
					}else{
						b.hide();
					}
					j++;
				});
			});								
			i++;
		});
		header.find('>:first-child').trigger('click');
	}
	
	function setOur(nd, name, alias)
	{
		cmdAsync(prepareCmd('treeaction.php', 
			{
				tree:'our',
				action: 'edit',
				id: nd.data.key,
				name: name,
				alias: alias
			}), 
			function(res)
			{
				if(res)
				{
					nd.data.name = name;
					nd.data.alias = alias;
					nd.data.title = name;
					if(alias) nd.data.title+='('+alias+')';
					nd.render();
				}
			}
		);		
	}
	
	function bindContextMenu(node, span) {
		$(span).contextMenu({menu: 'ourMenu'}, function(action, el, pos) 
		{
			var editFunc = function(nd)
			{
				$('#ourEditForm').modal({
					onShow: function(dialog)
					{
						var nameEl = dialog.data.find('.name').first();
						var aliasEl = dialog.data.find('.alias').first();
						
						nameEl.val(nd.data.name);
						aliasEl.val(nd.data.alias);
						dialog.data.find('.ok').click(function()
						{
							var name = nameEl.val();
							var alias = aliasEl.val();
							$.modal.close();
							setOur(nd, name, alias);
						});
					}
				});			
			}
			
			var node = $.ui.dynatree.getNode(el);
			
			if(action == 'edit') 
			{
				editFunc(node);
				return false;
			}
			
			if(action == 'delete')
			{
				if(!confirm('Вы уверены, что хотите удалить '+ node.data.name+'?')) return false;
			}
			
			cmdAsync(prepareCmd('treeaction.php', 
				{
					tree:'our',
					action: action,
					id: node.data.key
				}), 
				function(res)
				{
					if(action == 'delete')
					{
						if(res) node.remove();
					}
					if((action == 'add' || action == 'addchild') && res)
					{
						var obj = {
							key: res._id['$id'],
							name: res.name,
							title: res.name
						};
						if(res.sourceNames)
						{
							obj.alias = res.sourceNames.join(', ');
							obj.title = obj.title + '('+ obj.alias + ')';
						}
						
						var newNode = null; 						
						if(action == 'add') newNode = node.getParent().addChild(obj);
						if(action == 'addchild') newNode = node.addChild(obj);
						if(!newNode) alert('error');

						editFunc(newNode);
						newNode.activate();
					}
					
				}
			);	
		});
	}
	
	var ourTree = null;
	
	$(document).ready(function()
	{
		Tabs($('#sources .header'), $('#sources .body')); 
		
		ourTree = $("#ourTree").dynatree(
		{
			dnd: 
			{
			    onDragEnter: function(node, sourceNode) 
				{
					return ["over"];
				},
			    onDrop: function(node, sourceNode, hitMode, ui, draggable) 
				{
					
					if(!sourceNode) return false;
					
					var buf = [];
					if(node.data.alias) buf = node.data.alias.split(',');
					var sourceName = $.trim(sourceNode.data.title);
					for(var i=0; i<buf.lenght; i++) buf[i] = $.trim(buf[i]);
					if(buf.indexOf(sourceName) == -1)
					{
						buf.push(sourceName);
					}
					setOur(node, node.data.name, buf.join(', '));
				}
			},
			onCreate: bindContextMenu
		}).dynatree('getRoot');
		
		
		var trees = ['foursquareTree', 'googleplacestree', 'geonamestree'];
		for(var i=0; i < trees.length; i++)
		{
			var iter = trees[i];
			$('#'+iter).dynatree(
			{
				dnd: 
				{
					onDragStart: function(event)
					{
						return true;
					}
				}
			});
		}	
	});
</script>
<style type="text/css">
	#sources .header .active {background-color:white;border-bottom: 1px solid white;}
	#sources .header>*{cursor: pointer; border: 1px solid black;   background-color: #cccccc; display:inline-block; padding:5px; position: relative; top:1px;}
	#sources .body {border: 1px solid black; padding:5px;}
	
	label, input { display:block; }
</style>
</head>
<body>

<ul id="ourMenu" class="contextMenu" title="Редактирование типа">
	<li class="edit"><a href="#edit">Изменить</a></li>
	<li class="delete separator"><a href="#delete">Удалить</a></li>
	<li class="add"><a href="#add">Добавить</a></li>
	<li class="addchild"><a href="#addchild">Потомок</a></li>
</ul>

<div id="ourEditForm" title="Редактирование типа" style="display:none; border:1px solid black; padding-bottom:10px;">
	<div style="text-align:right;background-color:#cccccc; border-bottom: 1px solid black;"><span class="simplemodal-close" style="display:inline-block; cursor:pointer; padding-right:5px;"><b>x</b></span></div>
	<fieldset style="border:0">
		<label for="name" >Название</label> <input type="text" name="name"  class="name" />
		<label for="alias" >Алиасы</label> <input type="text" name="alias"  class="alias"  />
	</fieldset>
	<div style="text-align:center"><button class="ok">OK</button></div>
</div>

<table>
	<tr>
		<td valign="top" width="500">
			<div style="position:fixed; left:0; top:0;">
				Наши типы<br>
				<div id="ourTree">
				<? getOurTypes(); ?>
				</div>
			</div>
		</td>
		<td valign="top">
			Типы истоников<br><br>
			<div id="sources">
				<div class="header">
					<span>foursquare</span>
					<span>google places</span>
					<span>geonames</span>
				</div>
				<div class="body">
					<div id="foursquareTree" class="tab">
<?
function getFoursquareTypes($parentId=null)
{
	global $pg;
	$result = pg_query("select * from foursquare_categories_en where parent_id = '{$parentId}'") or die('Query failed: ' . pg_last_error());
	if(pg_num_rows($result) > 0)
	{
	?>
		<ul>
		<? while ($iter = pg_fetch_array($result, null, PGSQL_ASSOC)) { ?>
			<li id="<?=$iter['id']?>"><?=$iter['name']?>
		<?	getFoursquareTypes($iter['id']);
		} ?>
		</ul>
	<?
	}
}
getFoursquareTypes('root');
?>					
					</div>
					<div class="tab" id="googleplacestree">
<?
	global $pg;
	$result = pg_query("select * from googleplaces_poi_types_en") or die('Query failed: ' . pg_last_error());
	if(pg_num_rows($result) > 0)
	{
	?>
		<ul>
		<? while ($iter = pg_fetch_array($result, null, PGSQL_ASSOC)) { ?>
			<li id="<?=$iter['name']?>"><?=$iter['name']?>
		<?	
		} ?>
		</ul>
	<?
	}
?>					
					</div>
					<div class="tab" id="geonamestree">
<?
	global $pg;
	$result = pg_query("select * from geoname_feature_codes_ru") or die('Query failed: ' . pg_last_error());
	if(pg_num_rows($result) > 0)
	{
	?>
		<ul>
		<? while ($iter = pg_fetch_array($result, null, PGSQL_ASSOC)) { ?>
			<li id="<?=$iter['fcode']?>"><?=$iter['name']?>
		<?	
		} ?>
		</ul>
	<?
	}	
?>									
					</div>				
				</div>
			</div>
		</td>		
	</tr>
</table>



</body>
</html>
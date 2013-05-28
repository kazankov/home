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
			<li id="<?=$iter['_id']?>"><?=$iter['name']?>
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
				body.children().each(function()
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
	
	var ourTree = null;
	var foursquareTree = null;
	
	
	$(document).ready(function()
	{
		Tabs($('#sources .header'), $('#sources .body')); 
		
		ourTree = $("#ourTree").dynatree({}).dynatree('getRoot');
		foursquareTree = $("#foursquareTree").dynatree({}).dynatree('getRoot');
	});
</script>
<style type="text/css">
	#sources .header .active {background-color:white;border-bottom: 1px solid white;}
	#sources .header>*{cursor: pointer; border: 1px solid black;   background-color: #cccccc; display:inline-block; padding:5px; position: relative; top:1px;}
	#sources .body {border: 1px solid black; padding:5px;}
</style>
</head>
<body>

<table>
	<tr>
		<td valign="top" width="300">
			Наши типы<br>
			<div id="ourTree">
			<? getOurTypes(); ?>
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
					<div id="foursquareTree">
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
					<div>google places</div>
					<div>geonames</div>				
				</div>
			</div>
		</td>		
	</tr>
</table>



</body>
</html>
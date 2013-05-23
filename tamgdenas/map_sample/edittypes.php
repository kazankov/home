<html>
<head>
<script src="/js/jquery-1.6.min.js" type="text/javascript"></script>
<script src="/js/jquery-ui.custom.min.js" type="text/javascript"></script>
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
	
	$(document).ready(function()
	{
		Tabs($('#sources .header'), $('#sources .body')); 
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
		<td valign="top">
			Наши типы<br><br>
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
					<div>foursquare</div>
					<div>google places</div>
					<div>geonames</div>				
				</div>
			</div>
		</td>		
	</tr>
</table>



</body>
</html>
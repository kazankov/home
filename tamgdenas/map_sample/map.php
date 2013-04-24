<?php
//русский текст
require_once 'common.php';
class map extends Unit{

function map()
{

}
function head()
{
?>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript" src="js/markerclusterer.js"></script>
<script type="text/javascript" src="js/infobubble.js"></script>

<script language="JavaScript">
	
var map = function()
{ 	
	var canvas = null;
	var directionsDisplay = null;
	var infoBubble = new InfoBubble( {
	  <?php echo file_get_contents('design/infowindow.txt');?>
	});	
	
	function aboutToClose(e)
	{		
		for(var iter = e.target; iter; iter = iter.parentNode)
		{
			if(iter == infoBubble) return;
		}
		infoBubble.close();
	}
	infoBubble.hideCloseButton();
	return {
	    mc: null,
		directionsService: null, 
		infoWnd: function(marker, content)
		{
			infoBubble.setContent(content);		
			infoBubble.open(canvas, marker);
			document.body.addEventListener('mousedown', aboutToClose, false); 
		},
		
		getCenter: function()
		{
			return canvas.getCenter();
		},
		
		getZoom: function()
		{
			return canvas.getZoom();
		},

		closeInfoWnd: function()
		{
			infoBubble.close();
		},
		init: function(params)
		{
			var myLatlng = new google.maps.LatLng(params && params.latitude || 55.716088, params && params.logitude || 37.682247);
			var myOptions = {
			  zoom: params.zoom || 2,
			  center:  myLatlng,
			  mapTypeId: google.maps.MapTypeId.ROADMAP
			}
			canvas = new google.maps.Map(document.getElementById('map_canvas'), myOptions);	
					
			this.mc = new MarkerClusterer(canvas, null, {maxZoom: 19, styles:[{url: 'marker.php', height:41, width:25, textColor:'white'}]});
			
			directionsDisplay = new google.maps.DirectionsRenderer({suppressMarkers: true, polylineOptions: {strokeColor: 'lime', strokeWeight: 5}});
			directionsDisplay.setMap(canvas);
			this.directionsService = new google.maps.DirectionsService();	
		},
		
		//TODO: слишком много параметров - передавать объектом
		addMarker: function (title, lat, lon, callback, infoWindow, image, noText, color)
		{
			var markerCmd = '';
			
			if(!noText) markerCmd = 'text='+encodeURIComponent(title);
			if(image) 
			{
				markerCmd = 'image='+encodeURIComponent(image);
			}
			
			if(color) markerCmd+='&color='+encodeURIComponent(color);
			
			var marker = new google.maps.Marker({
				  title: title,
				  position: new google.maps.LatLng(
					  lat, lon),
				  clickable: true,
				  draggable: false,
				  flat: true,
				  icon: 'marker.php?'+markerCmd
				});	
			if(callback) google.maps.event.addListener(marker, 'click', function(){callback(marker);});
			this.mc.addMarker(marker);
			if(infoWindow)
			{
				google.maps.event.addListener(marker, 'click', function() 
				{
					infoBubble.setContent(infoWindow);		
					infoBubble.open(canvas, marker);
				});

			}
			return marker;
		},
		
		addMarkers: function(markers)
		{
			var buf=[];
			for (var i=0; i<markers.length; i++)
			{
				var marker = markers[i];
				buf.push(
					new google.maps.Marker(
					{
						title: marker.name,
       				 	position: new google.maps.LatLng(marker.latitude, marker.longitude),
				  		clickable: true,
				  		draggable: false,
				  		flat: true,
				 	       icon: 'marker.php?text='+encodeURIComponent(marker.name)
						
      					})
				);
			}
			this.mc.addMarkers(buf);
			this.mc.redraw();
		},
		
		addSimpleMarker: function(data)
		{
			var marker = new google.maps.Marker({
				position: data.ll,
				map: canvas
			});
			return marker;
		},
		
		removeMarker: function(marker)
		{
			this.mc.removeMarker(marker);
			marker.setMap(null);
		},
		
		show: function(bbox)
		{
			var southWest = new google.maps.LatLng(bbox.s, bbox.w);
			var northEast = new google.maps.LatLng(bbox.n, bbox.e);
			var bounds = new google.maps.LatLngBounds(southWest,northEast);
			canvas.fitBounds(bounds);		
		},
		
		clearMarkers: function()
		{
			this.mc.clearMarkers();
		},
		
		//методы для планировщика
		selectLocation: function(clb, data)
		{
			var oldCursor = canvas.draggableCursor;
			canvas.draggableCursor = 'crosshair';
			var listenerId = google.maps.event.addListener(canvas, 'click', function(e)
			{
				canvas.draggableCursor = oldCursor;
				google.maps.event.removeListener(listenerId);
				clb(e.latLng, data);
			});		
		},
		
		drawRoute: function(route)
		{			
			if(!route) 
			{				
				directionsDisplay.setDirections({routes: []});
				return;
			}
			
			directionsDisplay.setDirections(route);	
		}
	}
}();
</script>
<?php
}

function doLoad($latitude='null', $longitude='null', $zoom='null')
{
	echo "map.init({latitude: $latitude, longitude: $longitude, zoom: $zoom});\n";
}

function draw($w, $h)
{
	echo "<div style='width: {$w}; height: {$h};' id='map_canvas'></div>";
}

function draw_smarty($params)
{
	$this->draw($params['w'], $params['h']);
}

}

?>
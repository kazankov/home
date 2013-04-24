	function getXmlHttp(){
	  var xmlhttp;
	  try {
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	  } catch (e) {
		try {
		  xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		} catch (E) {
		  xmlhttp = false;
		}
	  }
	  if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
		xmlhttp = new XMLHttpRequest();
	  }
	  return xmlhttp;
	}
	
	function prepareCmd(url, params)
	{
		var buf = [];
		for(var p in params)
		{
			var value = params[p];
			var tp = typeof value;
			if(tp == 'object' || tp == 'array')
			{
				value = JSON.stringify(value);//преобразование структуры в строку
			}
			buf.push(p+'='+encodeURIComponent(value));
		}		
		return url+'?'+buf.join('&');
	}
	
	function cmdAsync(cmd, callback, ctx)
	{
		//ctx - контекст функции обратного вызова(объект, в случае если функция - его метод)
		if(!ctx) ctx = this;
		var req = getXmlHttp();
		req.open('GET', cmd, true);
		req.onreadystatechange = function()
		{
			if(req.readyState!=4) return;
			
			if(req.status == 200)
			{
				callback.call(ctx, JSON.parse(req.responseText));//преобразование строки в структуру
			}else{
				alert("error! command: "+cmd+"\nresponse: "+req.responseText);
			}
		}	
		req.send(null);
	}
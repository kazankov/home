# -*- coding: utf-8 -*-import pymongofrom pymongo import Connectionimport urllib2 import jsonimport mathdef sqr(x):	return x*x;def LatLongToMerc(lon, lat):    if lat>89.5:        lat=89.5    if lat<-89.5:        lat=-89.5     rLat = math.radians(lat)    rLong = math.radians(lon)     a=6378137.0    b=6356752.3142    f=(a-b)/a    e=math.sqrt(2*f-f**2)    x=a*rLong    y=a*math.log(math.tan(math.pi/4+rLat/2)*((1-e*math.sin(rLat))/(1+e*math.sin(rLat)))**(e/2))    return {'x':x,'y':y}	def bboxArea(n, s, w, e):	topLeft = LatLongToMerc(w, n)	bottomRight = LatLongToMerc(e, s)	return abs((bottomRight['x']-topLeft['x'])*(bottomRight['y']-topLeft['y']))import timedef searchVenue(n, s, w, e):	time.sleep(1) #86400 запросов в сутки (лимит = 100 000) 		key = "AIzaSyBeaQz_vzwERgPQZDncZYNhyAkYMG1JJY4"	lat = (n+s)/2	lon = (w+e)/2	nw = LatLongToMerc(w, n)	se = LatLongToMerc(e, s)	r = math.sqrt(sqr(se['x']-nw['x'])+sqr(se['y']-nw['y']))		url= "https://maps.googleapis.com/maps/api/place/search/json?location=%d,%d&radius=%d&sensor=false&language=ru&key=%s" % (lat, lon, r, key)	#можно запросить еще 2 страницы (тогда venMax = 60), но это два лишних запроса, поэтому не целесообразно (легче делать больше уточняющих запросов)	res =  json.loads(urllib2.urlopen(url).read())	return resmConn = Connection("217.199.220.182")mGP = mConn.googleplaces.placescount=0def process(n, s, w, e):	global count	def procHalf():		halfLat = (s+n)/2		halfLon = (e+w)/2		process(n, halfLat, w, halfLon)		process(n, halfLat, halfLon, e)		process(halfLat, s, w, halfLon)		process(halfLat, s, halfLon, e)			areaMax = 1250000000 #1250 квадратных километра (радиус = 50 километров)	areaMin = 100	venMax=20	area = bboxArea(n, s, w, e)	try:		if area > areaMax: #здесь прямоугольник распадается на  4 части			procHalf()		else:			res = searchVenue(n, s, w, e)			if len(res['results'])>=venMax and area>areaMin:				procHalf()			else:				for iter in res['results']:					obj = mGP.find_one({"id": iter['id']})					if obj:						iter['_id'] = obj['_id']													mGP.save(iter)					count = count+1		except Exception, e:		print "Error: "+str(e)		sys.stdout.flush() 			process(46.53468, 62.93923, 62.57813, 28.74023)print count			
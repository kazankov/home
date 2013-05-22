import psycopg2
import psycopg2.extras
import pymongo
from pymongo import Connection
import json
import urllib2
import base64
import sys

conn = psycopg2.connect("dbname='poi' user='postgres' host='localhost' password='postgres'",connection_factory=psycopg2.extras.DictConnection)

mConn = Connection("217.199.220.182")
#**** base creation
mConn.poi.poi.ensure_index('sourceId');
mConn.poi.poi.ensure_index('geoPoint', '2d');
#******************
mPoi = mConn.poi.poi
mPoiTypes = mConn.poi.poiTypes
mReviews = mConn.poi.reviews

cur = conn.cursor()
cur.execute("SELECT distinct foursquareid, categories, name, lat, lng, description from foursquare")

c = 0
c2 = 0
for row in cur:
	c2+=1
	if mPoi.find_one({"sourceId": "foursquare_"+row["foursquareid"]}):
		continue
		
	try:
		cur2 = conn.cursor()
		cur2.execute("select tag from foursquare_tags where foursquareid='"+row["foursquareid"]+"'")
		tags = []
		for rec in cur2:
			tags.append(rec["tag"])
			
		cur2 = conn.cursor()
		cur2.execute("select url from foursquare_photos where foursquareid='"+row["foursquareid"]+"'")
		photos = []
		for rec in cur2:
			photo = None
			try:
				photo = base64.b64encode(urllib2.urlopen(rec["url"]).read())
			except:
				photo = None
			if photo:
				photos.append(photo)
		
		types = []
		if row["categories"]:
			buf = row["categories"].split(";")
			for typeName in buf:
				cur2 = mPoiTypes.find({"sourceNames": typeName}, {"_id":1})
				obj = next(cur2, None)
				if(obj):
					types.append(obj["_id"])
		if not types:
			continue #no types found, skip
				
		reviews = []
		cur2 = conn.cursor()
		cur2.execute("select text from foursquare_tips where foursquareid='"+row["foursquareid"]+"'")
		for review in cur2:
			reviews.append(mReviews.insert({"text": review["text"]}, safe=True))
		
		
		obj = {
			"name": row["name"],
			"geoPoint": {"lat": row["lat"], "lon": row["lng"]},
			"desc": row["description"],
			"addFields": tags,
			"images": photos,
			"types": types,
			"reviews": reviews,
			"sourceId": "foursquare_"+row["foursquareid"]
		}
		mPoi.insert(obj, safe=True)
		c+=1
		if c % 1000 == 0:
			print str(c)+"_"+str(c2)
			sys.stdout.flush()
	except Exception, e:
		print "Error"+str(e)
		sys.stdout.flush()
print "poi ok"

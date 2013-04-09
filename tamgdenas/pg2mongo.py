import psycopg2
import psycopg2.extras
import pymongo
from pymongo import Connection
import json
import urllib2
import base64

conn = psycopg2.connect("dbname='poi' user='postgres' host='localhost' password='postgres'",connection_factory=psycopg2.extras.DictConnection)
cur = conn.cursor()

mConn = Connection()
mPoi = mConn.poi.poi
mPoiTypes = mConn.poi.poiTypes
mReviews = mConn.poi.reviews


def processType(parent, mParentId):
	cur.execute("select * from foursquare_categories_en where parent_id = %s", (parent,))
	buf = cur.fetchall()
	for iter in buf:
		obj = {
			"name": iter["name"],
			"icon": base64.b64encode(urllib2.urlopen(iter["icon_url"]).read()),
			"parent": mParentId
		}
		print obj
		id = mPoiTypes.insert(obj)
		processType(iter["id"], id)
processType("root", None)
	

cur.execute("SELECT foursquare.* from foursquare")
rows = cur.fetchall()

c = 0
for row in rows:
	cur.execute("select tag from foursquare_tags where foursquareid='"+row.foursquareid+"'")
	buf = cur.fetchall()
	tags = []
	for rec in buf:
		tags.append(rec["tag"])
		
	cur.execute("select url from foursquare_photos where foursquareid='"+row.foursquareid+"'")
	buf = cur.fetchall()
	photos = []
	for rec in buf:
		photos.append(base64.b64encode(urllib2.urlopen(rec["url"]).read()))
	
	types = []
	buf = row.categoires.split(";")
	for typeName in buf:
		cur = mPoiTypes.find({"name": typeName}, {"_id":1})
		obj = next(cur, None)
		if(obj):
			types.append(obj._id)
			
	reviews = []
	cur.execute("select text from foursquare_tips where foursquareid='"+row.foursquareid+"'")
	buf = cur.fetchall()
	for review in buf:
		reviews.append(mReviews.insert({"text": review["text"]}))
	
	
	obj = {
		"name": row["name"],
		"geoPoint": {"lat": row["lat"], "lon": row["lng"]},
		"desc": row["description"],
		"addFields": tags,
		"images": photos,
		"types": types,
		"reviews": reviews
	}
	mPoi.insert(obj)
	c+=1
print c


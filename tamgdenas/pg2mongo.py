import psycopg2
import pymongo
from pymongo import Connection

conn = psycopg2.connect("dbname='poi' user='postgres' host='localhost' password='postgres'")
cur = conn.cursor()
cur.execute("SELECT * from foursquare")
rows = cur.fetchall()

mConn = Connection()
mColl = connection.poi.foursquare

for row in rows:
	obj = {
		"name": ,
		"geoPoint": {"lat": , "lon": },
	}
	mColl.insert(obj)


import psycopg2
import psycopg2.extras
import pymongo
from pymongo import Connection

mConn = Connection()
mPoiTypes = mConn.poi.poiTypes

f = open("types.txt")
parent = None
lines = f.readlines()
for line in lines:
	
	


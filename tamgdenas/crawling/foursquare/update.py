import foursquareimport pymongofrom pymongo import Connectionimport sysconsumer_key = '2FJY5GX5RSKGTNQJRHUBSGSQSUOFRYNDMM5P0S5XZARET5P5';consumer_secret = 'WQFGRJPGV3PC3OWWDSQJ2JVMUBW5YFL0AQUUKO5XPIBJKI34';fs = foursquare.Foursquare(client_id=consumer_key, client_secret=consumer_secret, redirect_uri='Callback URL')mConn = Connection("217.199.220.182")mPoi = mConn.poi.poifor iter in mPoi.find({"sourceId": {'$regex': '^foursquare_'}}):	try:		fsId = iter['sourceId'][len('foursquare_'):]		rec = fs.venues(fsId)		if 'description' in rec:			print rec.description	except Exception, e:		print "Error: "+str(e)		sys.stdout.flush()
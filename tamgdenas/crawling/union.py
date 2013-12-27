# -*- coding: utf-8 -*-import foursquareimport pymongofrom pymongo import Connection, GEO2Dimport sysimport urllib2 import base64mConn = Connection("217.199.220.182")mCol1 = mConn.foursquare.venues_testmCol2 = mConn.googleplaces.places_testmUnion = mConn.foursquare_googleplaces.poimUnion.remove()mCol2.ensure_index([("geoPoint", pymongo.GEO2D)])mCol2.ensure_index('lc_name')for iter in mCol2.find(): #{'lc_name': {'$exists': False}}  - пропустить уже добавленные	loc = iter['geometry']['location']	iter['lc_name'] = iter['name'].lower().strip()	iter['geoPoint'] = {'lat': loc['lat'], 'lon': loc['lng']}	mCol2.save(iter)doubles = {}	for iter in mCol1.find():	if iter['location'] and iter['name']:		loc = iter['location']		jiter = mCol2.find_one({'geoPoint': {'$maxDistance': 0.01, '$near': {'lat': loc['lat'], 'lon': loc['lng']}}, 'lc_name': iter['name'].lower().strip()})   #около 2000 м 		if jiter:			doubles[jiter['_id']] = True			print jiter			print iter			print '__________________'					addFields = []		if iter['categories']:			for cat in  iter['categories']:				addFields.append(cat['name'])					obj = {'name': iter['name'], 'geoPoint': {'lat': loc['lat'], 'lon': loc['lng']}, 'source': 'foursquare', 'sourceId': iter['id'], 'addFields': addFields}						if 'photos' in iter and iter['photos']['count']>0:			photos = []			for group in iter['photos']['groups']:								for  photoRec in group['items']:					url = '%s%dx%d%s'%(photoRec['prefix'], photoRec['width'], photoRec['height'], photoRec['suffix'])					photos.append(base64.b64encode(urllib2.urlopen(url).read()))			obj['images'] = photos						mUnion.save(obj)		for iter in mCol2.find():	if iter['geoPoint'] and iter['name']:		loc = iter['geoPoint']				if iter['_id'] in doubles:			continue			obj = {'name': iter['name'], 'geoPoint': loc, 'source': 'googleplaces', 'sourceId': iter['reference'], 'addFields': iter['types']}		mUnion.save(obj)							
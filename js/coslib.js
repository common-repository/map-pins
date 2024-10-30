//	Global coslib namespace for helpers
(function( coslib, $, undefined ) {
	//-------------------------------------------------------------------
	// my private parts
	//-------------------------------------------------------------------
	function handleNoGeolocation(errorFlag) {
		if (errorFlag) {
			alert("Geolocatie service fout.");

		} else {
			alert("Sorry, this browser does not support geolocation");
		}
	};
	//-------------------------------------------------------------------
	// Calcdistance - calculate distance in meter 
	//-------------------------------------------------------------------
	coslib.calcDistance=function(lat1,lon1,lat2,lon2) {
		var toRad=function(deg) {
			 return deg * Math.PI / 180;
		};
		var R = 6371; // km
		var x = (toRad(lon2)-toRad(lon1)) * Math.cos((toRad(lat1)+toRad(lat2))/2);
		var y = (toRad(lat2)-toRad(lat1));
		var d = Math.sqrt(x*x + y*y) * R;
		return Math.round(d*1000);	// meters
	};
	coslib.distanceToString=function(d) {
		d=d/1000;	// to kilometers
		if (d<1)
			return Math.round(d*1000)+"m";
		if (d<100)
			return (Math.round(d*10)/10)+'km';
		return Math.round(d)+'km';
	};
	//-------------------------------------------------------------------
	// getMyLocation - GPS location for all.
	//	fnback - called after succesful location determind 
	//  NEEDS GOOGLE MAPS for support of LatLng objects
	//-------------------------------------------------------------------
	coslib.getMyLocation=function(fnback) {
		var initialLocation;
		// Try W3C Geolocation (Preferred)
		if(navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function(position) {
					myLocation = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
					if (fnback)
						fnback(myLocation);
				}, function() {
					handleNoGeolocation(true);
				},
				{ enableHighAccuracy: true }
			);
			return true;	// succeeded.
		// Try Google Gears Geolocation
		} 
		else if (google.gears) {
			var geo = google.gears.factory.create('beta.geolocation');
			geo.getCurrentPosition(function(position) {
					myLocation = new google.maps.LatLng(position.latitude,position.longitude);
					if (fnback)
						fnback(myLocation);
				}, function() {
					handleNoGeoLocation(true);
				}
			);
			return true;
		}
		handleNoGeoLocation(false);
	};
	//-------------------------------------------------------------------
	// searchLocation - adress to latlong
	//	map - if given show the address on this map and use map boundaries for search
	//	endfunc - called after succesful location determind (with LatLng as argument)
	//  errfunc - called when nothing found
	//  NEEDS GOOGLE MAPS GEOCODER (places library)
	//-------------------------------------------------------------------
	coslib.searchLocation=function(map,address,endfunc,errfunc) {
		var myCoder = new google.maps.Geocoder();
		var opts={address:address};
		// optional: use bounds of a map
		if (map)	
			opts.bounds = map.getBounds();
		myCoder.geocode(opts,function(results, status) {
			console.log('geocoder ready');
			if (status==google.maps.GeocoderStatus.OK) {
				// show first result
				var place = results[0];
				if (endfunc)
					endfunc(place.geometry.location);
				if (map) {
					// optional: show location on the map by setting viewport
                    if (place.geometry.viewport) {
                        map.fitBounds(place.geometry.viewport);
                    } else {
                        map.setCenter(place.geometry.location);
                        map.setZoom(17);  // Why 17? Because it looks good.    
                    }
				}
			}
			else {
				//console.log('geocoder error');
				if (errfunc)
					errfunc();
			}
		});
	};
	
	//-------------------------------------------------------------------
	// 	makeAutocomplete - Transform a text-input to a geo-search box
	//	input = search field
	//	map = (optional) map to sync with results
	//	
	//-------------------------------------------------------------------
	coslib.makeAutocomplete=function(input,map,cbfunc) {
        var autocomplete = new google.maps.places.Autocomplete(input,{
			//bounds: bounds,
			componentRestrictions: { country:  mappins_options.defcountrycode }
		});
        google.maps.event.addListener(autocomplete, 'place_changed', function() {
			var place = this.getPlace();
			if (place && place.geometry) {
				if (place.geometry.viewport) {
				    map && map.fitBounds(place.geometry.viewport);
				} else {
				    map && map.setCenter(place.geometry.location);
				    map && map.setZoom(17);  // Why 17? Because it looks good.
				}
				cbfunc && cbfunc(place.geometry.location);
			}
			else {
				coslib.searchLocation(map, place.name+', '+mappins_options.defcountryname, cbfunc); // firstResult);
			}
		});
	};
}(window.coslib=window.coslib || {},jQuery));




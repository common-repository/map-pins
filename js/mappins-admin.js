// my global
var mappin_gmap={
	mymap: null,mycacheloc: null, markerloc: null, latfld: null, lngfld: null, mymarker: null,address: null, 
	setMarkerfields: function(latfld,lngfld) {
		// hidden inputs that move with the marker location
		// field ID!! 
		this.latfld=latfld;
		this.lngfld=lngfld;
	},
	setAddress: function(address) {
		// hidden inputs that move with the marker location
		this.address=address;
	},
	field2marker: function() {
		var $=jQuery,thisC=this;
		var lat=$("#"+this.latfld).val();
		var lng=$("#"+this.lngfld).val();
		var markerpos=new google.maps.LatLng(lat,lng);
		if (this.mymarker) {
			this.mymarker.setPosition(markerpos);
		}
	},
	marker2field: function() {
		var $=jQuery;
		if (this.mymarker) {
			var markerpos=this.mymarker.getPosition();
			$("#"+this.latfld).val(markerpos.lat());
			$("#"+this.lngfld).val(markerpos.lng());
		}
	},		
	initLatLngFields: function() {
		var $=jQuery,thisC=this;
		var lat=$("#"+this.latfld).val();
		var lng=$("#"+this.lngfld).val();
		thisC.init(lat,lng,15,null);
	},
	init: function(lat,lng,zoom,address) {
		var thisC=this;
		if (!lat || !lng) {
			if (address) {
				thisC.geolocator(address,function(place) {
					//if (place.geometry.viewport) {
					//	thisC.map.fitBounds(place.geometry.viewport);
					//} else {
						thisC.init(place.geometry.location.lat(),place.geometry.location.lng(),15,null);   
					//}				
				});
			}
			else {
				// 52.30376 5.61970 = hoofdkantoor sterkliniek
				thisC.init(52.30376,5.61970,8,null);
				// thisC.getmylocation(function (myloc) {
				// 	thisC.init(myloc.lat(),myloc.lng(),8,null);
				// });
			}
			return; 
		}
	    var mapOptions = {
	      center: new google.maps.LatLng(lat,lng),
	      zoom: zoom,
	      mapTypeId: google.maps.MapTypeId.ROADMAP,
		  gestureHandling: 'greedy',
	    };
	    thisC.mymap = new google.maps.Map(document.getElementById("mappins-editloc"), mapOptions);	
	    thisC.mymarker = new google.maps.Marker({
	            position: thisC.mymap.getCenter(),
	            map: thisC.mymap,
	            title: 'venue'
	        });
		google.maps.event.addListener(thisC.mymap, 'click', function(me) {
			thisC.mymarker.setPosition(me.latLng);
			thisC.marker2field();
		});	
		
	},

	// search an address 
	geolocator: function (address,endfunc,errfunc) {
		var thisC=this;	// closure..
		var geocoder2 = new google.maps.Geocoder();
		var opts={address:address};
		//if (thisC.mymap)	
		//	opts.bounds = thisC.mymap.getBounds();
		geocoder2.geocode(opts,function(results, status) {
			if (status==google.maps.GeocoderStatus.OK) {
				// show first result
				var place = results[0];
				if (endfunc)
					endfunc(place);
			}
			else {
				//console.log('geocoder error');
				if (errfunc)
					errfunc();
			}
		});
	},
	searchbar: function($) {
		var thisC=this;
        var input = document.getElementById('gmapsearch');
        var autocomplete = new google.maps.places.Autocomplete(input);
        google.maps.event.addListener(autocomplete, 'place_changed', function() {
			var place = this.getPlace();
			if (place && place.geometry) {
                if (place && place.geometry) {
                    if (place.geometry.viewport) {
                        thisC.mymap.fitBounds(place.geometry.viewport);
                    } else {
                        thisC.mymap.setCenter(place.geometry.location);
                        thisC.mymap.setZoom(17);  // Why 17? Because it looks good.
                    }
                }
			}
		});
		$('#gmapmyloc').click(function(e) {
			coslib.getMyLocation(function(myloc) {
 				thisC.mymap.setCenter(myloc);
				thisC.mymarker.setPosition(myloc);
				thisC.marker2field();
			});
			e.preventDefault();
		});
		$('#gmapvenueaddress').click(function(e) {
			var adr=$(this).data('address');
			if (adr)
				coslib.searchLocation(thisC.mymap,adr);
		});		
		$('#gmapvenuezip').click(function(e) {
			var adr=$(this).data('zip');
			if (adr)
				coslib.searchLocation(thisC.mymap,adr);
		});
	}
}

jQuery(function($){
	var map;

	// Callback function to be called after loading the maps api
	window['mappins_admin'] = function(){
		mappin_gmap.setMarkerfields("mappin_map_lat","mappin_map_lng");
		mappin_gmap.initLatLngFields();
		mappin_gmap.searchbar(jQuery);
	};

	var map = $('#mappins-editloc');
	if(map.length){
		if(typeof google == 'undefined' || google['maps'] == null){
			// Create the script tag and load the maps api
			var script=document.createElement('script');
			script.type  = "text/javascript";
			script.src='https://maps.google.com/maps/api/js?key='+mappins_options.googlekey+'&libraries=places&callback=mappins_admin';	// use this callback!
			document.body.appendChild(script);
		}
		else{
			mappins_init();
		}
	}	
	
});

jQuery(function($){
	var map;
	var myLocation=null;

	var showbarInit=function(map,bounds) {
        var input = document.getElementById('gmapsearch');
		coslib.makeAutocomplete(input,map,function(loc) {
			//console.log('location found: '+loc.lat()+','+loc.lng());
			coslib.markers.listCenter(loc);	// new center
		});

	};

	//-------------------------------------------------------------------
	// MAIN
	//-------------------------------------------------------------------
	// Callback function to be called after loading the maps api
	window['mappins_init'] = function(){
		var bounds=coslib.markers.getBoundaries();
		var map=null;
		if ($('.mappins-map').length) {
			//$('.mappins-map').each(function(){
	        var mapOptions = {
	          center: new google.maps.LatLng(-34.397, 150.644),
	          zoom: 9,
	          mapTypeId: google.maps.MapTypeId.ROADMAP,
			  gestureHandling: 'greedy',

	        };
	        map = new google.maps.Map(document.getElementById("mappins-map"),
	            mapOptions);
			if (bounds)
				map.fitBounds(bounds);
			coslib.markers.putMarkers(map);
			coslib.markers.listClickable(true);

		}
		coslib.markers.currentSel='#mappins-list';	// set list selector
		if (mappins_options.initiallist=='Y') 
			coslib.markers.showList();	// show initial list!
		
		if (mappins_options.showbar) {
			showbarInit(map,bounds);
			$('#gmapmyloc').click(function(e) {
				coslib.getMyLocation(function(myloc) {
	 				map && map.setCenter(myloc);
                    map && map.setZoom(15); 
					coslib.markers.listCenter(myloc);	// new center
				});
				e.preventDefault();
			});
			$('#gmapopen').click(function(e) {
				var check=$(this).prop("checked");	// current value?
				coslib.markers.markersVisible(map,check);					
			});
		};
		if (map) {
			$('.mappins-list').on('click','.mappins-list-item',function(e) {
				var lat=$(this).data('lat');
				var lng=$(this).data('lng');
				var item=$(this).data('markerid');
				var latlng=new google.maps.LatLng(lat, lng);
				map && map.setZoom(10);
				map && map.setCenter(latlng);	
				coslib.markers.markerInfoWindow(map,coslib.markers.getMarkerFromID(item));	// and show balloon
				//alert('lat='+lat+' lng='+lng);
			});
		}
	};
		
	var map = $('.mappins-map');
	if(map.length || $('.mappins-list').length){
		if(typeof google == 'undefined' || google['maps'] == null){
			// Create the script tag and load the maps api
			var script=document.createElement('script');
			script.type  = "text/javascript";
			script.src='https://maps.google.com/maps/api/js?key='+mappins_options.googlekey+'&libraries=places&callback=mappins_init';
			document.body.appendChild(script);
		}
		else{
			mappins_init();
		}
	}	
	
});
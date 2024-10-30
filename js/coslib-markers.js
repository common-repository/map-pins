//-------------------------------------------------------------------
// coslib MARKERS extension
// uses GLOBAL mappins_allmarkers and mappins_options
//-------------------------------------------------------------------
(function( coslib, $, undefined ) {	
	coslib.markers={

		//-------------------------------------------------------------------
		// getBoundaries - get boundary along ALL markers
		//-------------------------------------------------------------------
		getBoundaries:function() {
			var bounds=null;
			for (var m in mappins_allmarkers) {
				var marker=mappins_allmarkers[m];
				if (!bounds)
					bounds=new google.maps.LatLngBounds();
				bounds.extend(new google.maps.LatLng(marker.lat,marker.lng))
			}
			return bounds;
		},
		//-------------------------------------------------------------------
		// putMarkers - put markers on the map
		//-------------------------------------------------------------------
		putMarkers:function(map) {
			var self=this;
			for (var m in mappins_allmarkers) {
				var marker=mappins_allmarkers[m];
				var curpos = new google.maps.LatLng(marker.lat,marker.lng);
				var isopen=true;
				if (marker.opentimes) {
					var openspec=JSON.parse(marker.opentimes);
					isopen=coslib.opentimes.isNowOpen(openspec);
				}
				marker.pin = new google.maps.Marker({
					position: curpos, 
					map: map,
				    //animation: google.maps.Animation.DROP,
					icon: {	
						url: mappins_options.pics_base_url+((isopen||!marker.afterhoursdesc) ? marker.icon:'grey_night.png')	// default anchor on center of bottom of image
					}, 
					flat: true,
					title:marker.description,
					zIndex: 1
				});
				(function(marker2) {
					google.maps.event.addListener(marker.pin, 'click', function() {
						self.markerInfoWindow(map,marker2);
					});
				})(marker);
			}
		},
		//-------------------------------------------------------------------
		// markersVisible - show all or only markers that are OPEN 
		//	openOnly - true=show only open stores. false=show all
		//-------------------------------------------------------------------
		markersVisible:function(map,openOnly) {
			for (var m in mappins_allmarkers) {
				var marker=mappins_allmarkers[m];		
				if (!openOnly) {
					marker.invisible=false;	//	always show all markers
				}
				else {
					// toon alleen geopende filialen
					if (marker.opentimes) {
						var openspec=JSON.parse(marker.opentimes);
						var isopen=coslib.opentimes.isNowOpen(openspec);
						marker.invisible=!isopen && !marker.afterhoursdesc;
					}
					else {
						marker.invisible=true;	// no open info.. no show for openOnly
					}
				}
				if (marker.pin && map) {
					// the marker itself might be a night-icon 
					marker.pin.setVisible(!marker.invisible);	// no spec, not open	
				}			
			}
			this.listOpenOnly(openOnly);
		},
		//-------------------------------------------------------------------
		// show info Balloon of marker
		// 	marker - if null, closes current marker
		//-------------------------------------------------------------------
		infowindow: null,
		markerInfoWindow:function(map,marker) {
			this.infowindow && this.infowindow.close();	// close old
			if (marker) {
				// open new
				this.infowindow = new google.maps.InfoWindow({ 
					content:  this.markerHtml(marker,null,true),	// balloon=true	
					//size: new google.maps.Size(10,10)
					maxWidth: 240
				});
				this.infowindow.open(map,marker.pin);
			}
		},
		getMarkerFromID:function(id) {	// database ID
			for (var m in mappins_allmarkers) {
				var marker=mappins_allmarkers[m];
				if (marker.id==id)
					return marker;
			}
			return null;
		},
		//-------------------------------------------------------------------
		// sort markers for use in showList. 
		// if center given, sort on distance
		// if not, no sorting
		//-------------------------------------------------------------------
		sortMarkers: function(center) {
			var self=this;
			self.markerSort=[];
			for (var m in mappins_allmarkers) {
				var marker=mappins_allmarkers[m];	
				if (marker.invisible)
					continue;
				var distance;	
				if (center)
					distance= coslib.calcDistance(marker.lat,marker.lng,center.lat(),center.lng());
				else
					distance=0;
				self.markerSort.push({marker: m, distance: distance} );
			}
			if (center) {
				self.markerSort.sort(function(a,b) {
					return a.distance-b.distance;
				});
			}
		},
		//-------------------------------------------------------------------
		// Show list in a DIV using sel selector
		//	if center is defined, use that to sort for distance
		//-------------------------------------------------------------------
		markerSort:null,	// local current sort
		currentCenter:null,
		currentOpenOnly:false,
		currentSel:null,	
		currentClickable:false,
		listCenter: function(center) {
			this.currentCenter=center;
			this.showList();
		},
		listOpenOnly: function(openonly) {
			if (openonly!=this.currentOpenOnly) {
				this.currentOpenOnly=openonly;
				this.showList();
			}
		},	
		listClickable:function(clickable) {
			this.currentClickable=clickable;
		},
		markerHtml:function(marker,ms,balloon) {
			var html='';
			var listclass=balloon? "mappins-balloon": "mappins-list-item";
			var clickclass=this.currentClickable && !balloon ?' mappins-clickable ':'';
			html+='<div class="'+listclass+'" data-lat="'+marker.lat+'" data-lng="'+marker.lng+'" data-markerid="'+marker.id+'">';
			html+='<h3 class="'+clickclass+'">';
				if (marker.link)
					html+='<a href="'+marker.link+'" >'+marker.description+' &#x25b6;</a>';
				else
					html+=marker.description;
				if (ms && ms.distance)
					html+=' ('+coslib.distanceToString(ms.distance)+')';
			html+='</h3>';
			html+='<img class="mappins-info-marker '+clickclass+'" src="'+mappins_options.pics_base_url+marker.icon+'">';
			html+='<div class="mappins-info">';
				html+='<div>'+marker.address+'</div>';
				html+='<div>'+marker.zip+' '+marker.city+'</div>';
				if (marker.tel)
					html+='<div>'+mappins_options.__tel+' '+marker.tel+'</div>';

				// if (marker.link) 
				// 	html+='<div>ga naar de <a href="'+marker.link+'" target="_blank">website</a></div>';
				// if (ms.distance) 
				// 	html+='<div style="">Afstand: '+coslib.distanceToString(ms.distance)+'</div>';
			html+='</div>';

			html+='<div style="font-size:11px;margin:5px 0">';
			if (marker.remarks)
				html+='<div>'+marker.remarks.replace(/\n/g,"<br>")+'</div>';
			if (marker.opentimes && marker.opentimes!='[]') {
				html+='<div class="mappins-ot" style="float:left;margin:0 5px 0 0;">'+mappins_options.__openinghours+':</div>';
				html+='<div style="float:left;">'+coslib.opentimes.toString(marker.opentimes)+'</div>';
				html+='<div style="clear:both"></div>';
			}
			if (marker.afterhoursdesc) {
				html+='<div>'+marker.afterhoursdesc.replace(/\n/g,"<br>")+'</div>';
			}
			html+='</div>';

			html+='</div>';
			return html;
		},
		showList:function(sel) {
			this.markerInfoWindow();	// close balloons when list refreshes
			if (!this.currentSel && sel)
			 	this.currentSel=sel;
			if (!$(sel))
				return;	// no list or invalid sel
			this.sortMarkers(this.currentCenter);
			var html='';

			if (this.currentCenter) {
				html+='<div class="mappins-list-header">'+mappins_options.__nearlocation+'</div>';
			}
			for (var m in this.markerSort) {
				var ms=this.markerSort[m];
				var marker=mappins_allmarkers[ms.marker];

				html+=this.markerHtml(marker,ms);

			}
			$(this.currentSel).html(html);		
		}
	};	
}(window.coslib=window.coslib || {},jQuery));

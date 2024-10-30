//-------------------------------------------------------------------
// OPENTIMES library
//-------------------------------------------------------------------
(function( coslib, $, undefined ) {	
	//------------------------------------------------------------------------------
	// XCos Opentimes field support
	// opentimes json spec: [ { day: 'mazo', times: [ {van: '09:00',tot: '18:00'} ] } ]
	//------------------------------------------------------------------------------
	coslib.opentimes={
		opensrcid: null,
		// moved to mappins_options.dagendef -- dagendef: { mazo: 'Alle dagen',mavr: 'Ma-Vr',ma: 'Maandag',di: 'Dinsdag', wo: 'Woensdag', 'do': 'Donderdag', vr: 'Vrijdag', za: 'Zaterdag', zo: 'Zondag' },
		// srcid is ID of (hidden) input whith openspec as string
		init: function(srcid) {
			var self=this,$=jQuery;
			self.opensrcid=srcid;	// store srcid locally
			self.src2fields();
			$('#mappins_idopentimes').on('change',function() {
				self.fields2src();
			});
			$('#mappins_idopentimes').on('click','#otaddrow',function() {
					var srcval=self.fields2src();
					srcval.push({ day: 'mazo', times: [ {van: '09:00',tot: '18:00'} ] });	// add row
					$('#'+self.opensrcid).val(JSON.stringify(srcval));	
					self.src2fields();	// and back			
			});		
			$('#mappins_idopentimes').on('click','#otdelrow',function() {
					var srcval=self.fields2src();
					if (srcval.length>0)
						srcval.pop();	// delrow
					$('#'+self.opensrcid).val(JSON.stringify(srcval));	
					self.src2fields();	// and back			
			});
			$('#mappins_idopentimes').on('click','#otaddcol',function(e) {
				var rowclick=$(e.target).data('row');
				var srcval=self.fields2src();
				srcval[rowclick].times.push({van: '09:00',tot: '18:00'});
				//	srcval.push({ day: 'ma-zo', times: [ {van: '09:00',tot: '18:00'} ] });	// add row
				$('#'+self.opensrcid).val(JSON.stringify(srcval));	
				self.src2fields();	// and back			
			});
			$('#mappins_idopentimes').on('click','#otdelcol',function(e) {
				var rowclick=$(e.target).data('row');
				var srcval=self.fields2src();
				if (srcval[rowclick].times.length>1)
					srcval[rowclick].times.pop();
				//	srcval.push({ day: 'ma-zo', times: [ {van: '09:00',tot: '18:00'} ] });	// add row
				$('#'+self.opensrcid).val(JSON.stringify(srcval));	
				self.src2fields();	// and back			
			});
		},
		src2fields: function() {
			var self=this,$=jQuery;	
			var srcval=$('#'+self.opensrcid).val();		// json value
			var html='';
			if (!srcval) {
				srcval=[ { day: 'mazo', times: [ {van: '09:00',tot: '18:00'} ] } ];
				$('#'+self.opensrcid).val(JSON.stringify(srcval));				
			}
			else {
				try {
					srcval=JSON.parse(srcval);
				}
				catch(err) {
					srcval=[];	// empty 
				}
			}
			for (var i in srcval) {
				var row=srcval[i];
				html+='<tr class="vantotrow" data-row="'+i+'">';
				html+='<td><select class="vantotselect">';
				for (var dd in mappins_options.dagendef) { // self.dagendef
					html+='<option value="'+dd+'" '+(row.day==dd?'selected=selected':'')+' >'+mappins_options.dagendef[dd]+'</option>';
				}
				html+='</select></td>';
				for (var j in row.times) {
					var timespec=row.times[j];
					html+='<td class="spec">'+mappins_options.__From+': <input class="vantottimes van" type="text" value="'+timespec.van+'" name="van-'+i+'-'+j+'">';
					html+=' - '+mappins_options.__Till+': <input type="text" class="vantottimes tot" value="'+timespec.tot+'" name="tot-'+i+'-'+j+'"></td>';
				}
				html+='<td>';
				if (row.times.length<3)
					html+='<i class="icom-plus-sign" id="otaddcol" data-row='+i+'></i>';
				if (row.times.length>1)
					html+='<i class="icom-minus-sign" id="otdelcol" data-row='+i+'></i>';
				html+='</td>';				
				html+='</tr>';
			}
			html+='<tr><td>';
			if (srcval.length<7) 
				html+='<i class="icom-plus-sign" id="otaddrow"></i>';
			if (srcval.length>0)
				html+='<i class="icom-minus-sign" id="otdelrow"></i>';
			html+='</td></tr>';
			$('#mappins_idopentimes').html('<table class="vantottbl">'+html+'</table>');
		},
		fields2src: function() {
			var self=this,$=jQuery;
			var srcval=[],rowval,vantot;
			$('.vantotrow').each(function() {
				rowval={};
				rowval.day=$(this).find('.vantotselect').val();
				rowval.times=[];
				$(this).find('.vantottimes').each(function() {
					// sanitize
					var time=self.timeSanitize($(this).val());
					$(this).val(time);
					// now save it to our object
					if ($(this).hasClass('van')) {
						vantot={van:$(this).val()};
					}
					else {
						//tot
						vantot.tot=$(this).val();
						rowval.times.push(vantot);
						vantot={};
					}
				});
				srcval.push(rowval);
			});
			$('#'+self.opensrcid).val(JSON.stringify(srcval));	
			return srcval;	// and return it!
		},
		timeSanitize: function(time) {
			var zeroPad = function(number) {
				return ("0"+number).substr(-2,2);
			}
			// convert time string to hh:mm
			time = time.replace(/[^0-9:]+/gi,'');	// only 0-9: is allowd, replace the rest
			var atime=time.match(/([0-9]+):([0-9]+)/);
			var hour=0,min=0;
			if (atime) {
				hour=atime[1];min=atime[2];
			}
			else {
				if (parseInt(time)) {
					hour=parseInt(time);
					if (hour>99) {
						// format 910 or 1215
						min=Math.floor(hour % 100);
						hour=Math.floor(hour / 100);
					}
				}
			}
			if (hour>23) {	// top off to a max of 23:59
				hour=23;min=59;
			}
			if (hour<0)
				hour=0;
			if (min>59) 
				min=59;
			if (min<0)
				min=0;
			return zeroPad(hour)+':'+zeroPad(min);
		},
		isNowOpen: function(openspec) {
			// true if it is open NOW 
			var d = new Date();
			var dow=d.getDay();	// 0=sun 1=ma 2=di 3=wo 4=do 5=vr 6=za
			var hourmin=d.getHours()*60+d.getMinutes();	// minute of the day 00:00 - 23:59
			for (var i in openspec) {
				var spec=openspec[i];
				var isDay=false;
				switch(spec.day) {
				case 'mazo':
					isDay=true;
					break;
				case 'mavr':
					// 0=sunday 6=sat
					if (dow>0 && dow<6)
						isDay=true;
					break;
				case 'mado':
					if (dow>0 && dow<5)
						isDay=true;
					break;
				case 'ma':	isDay=(dow==1);break;
				case 'di':	isDay=(dow==2);break;
				case 'wo':	isDay=(dow==3);break;
				case 'do':	isDay=(dow==4);break;
				case 'vr':	isDay=(dow==5);break;
				case 'za':	isDay=(dow==6);break;
				case 'zo':	isDay=(dow==0);break;
				}
				if (isDay) {
					// day is OK, now check the TIME
					for (var t in spec.times) {
						var timespec=spec.times[t];
						var vantime=timespec.van.match(/([0-9]+):([0-9]+)/);
						var tottime=timespec.tot.match(/([0-9]+):([0-9]+)/);
						if (vantime && tottime) {
							var vanhm=parseInt(vantime[1])*60+parseInt(vantime[2]);
							var tothm=parseInt(tottime[1])*60+parseInt(tottime[2]);
							if (vanhm<=hourmin && tothm>=hourmin)
								return true;	// a HIT!!
						}
					}
				}
			}
			return false;	
		},
		toString: function(openspec) {
			var self=this;
			var html='';
			srcval=JSON.parse(openspec);
			for (var i in srcval) {
				var row=srcval[i];
				html+='<div>';
				html+='<div class="mappins-ot" style="float:left;width:60px;">'+mappins_options.dagendef[row.day]+'</div>';
				for (var j in row.times) {
					var timespec=row.times[j];
					html+='<span class="mappins-ot" style="margin:0 10px;">'+timespec.van+'-'+timespec.tot+'</span>';
				}
				html+='</div>';
			}
			return html;
		}
	};
}(window.coslib=window.coslib || {},jQuery));


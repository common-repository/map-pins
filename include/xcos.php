<?php
if ( !defined('ABSPATH') ) die('-1');	// no direct access

class xcos {
	static public function ifset(&$v1,$v2=null) {
		if (isset($v1))
			return $v1;
		return $v2;
	}
	
	// convert [style]=>'width:230px' to  " style="width:230px" "
	static public function args2tags($a) {
		$rv='';
		foreach($a as $k=>$v) {
			$rv.=" $k=\"$v\" ";
		}
		return $rv;
	}
	
	// convert a 0=>vala 1=>valb to a vala=>vala valb=>valb array 
	// only if all keys are numbers!
	public static function array_v_to_kv($a) {
		$rv=array();
		foreach($a as $k=>$v) {
			if (is_numeric($k))
				$rv[$v]=$v;
			else
				return $a;	// non-numeric key
		}
		return $rv;
	}
	
	static public function TrFormInput($name,$label,$value='',$args=array(),$helptext='') {
		$fullid="mappin_map_$name";
		$fullname="mappin_map[$name]";
		echo '
			<tr valign="top">
				<th scope="row"><label for="'.$fullid.'">'.$label.'</label></th>
				<td>
					<input type="text" '.XCos::args2tags($args).' name="'.$fullname.'" id="'.$fullid.'" value="'.$value.'" />'.
					($helptext?'<span class="description"> '.$helptext.'</span>':'').
				'</td>
			</tr>';
	}
	static public function TrFormTextarea($name,$label,$value='',$args=array(),$helptext='') {
		$fullid="mappin_map_$name";
		$fullname="mappin_map[$name]";
		echo '
			<tr valign="top">
				<th scope="row"><label for="'.$fullid.'">'.$label.'</label></th>
				<td>
					<textarea '.XCos::args2tags($args).' name="'.$fullname.'" id="'.$fullid.'">'.$value.'</textarea>'.
					($helptext?'<span class="description"> '.$helptext.'</span>':'').
				'</td>
			</tr>';
	}
	static public function TrFormSelect($name,$label,$value='',$opts=array(),$args=array(),$helptext='') {
		$fullid="mappin_map_$name";
		$fullname="mappin_map[$name]";
		echo '
			<tr valign="top">
				<th scope="row"><label for="'.$fullid.'">'.$label.'</label></th>
				<td>
					<select name="'.$fullname.'" id="'.$fullid.'" '.XCos::args2tags($args).'>';
					foreach($opts as $optval=>$optdesc) {
						echo "<option value='$optval' ".($value==$optval?'selected=selected':'')." >".$optdesc."</option>";
					}
		echo '		</select>'.
					($helptext?'<span class="description"> '.$helptext.'</span>':'').
				'</td>
			</tr>';
	}
	static public function TrFormIcons($name,$label,$value='',$opts=array(),$args=array()) {
		$fullid="mappin_map_$name";
		$fullname="mappin_map[$name]";
		echo '
			<tr valign="top">
				<th scope="row"><label for="'.$fullid.'">'.$label.'</label></th>
				<td>';
					foreach($opts as $optval=>$optdesc) {
						echo "<input name='$fullname' type='radio' value='$optval' ".($value==$optval?'checked=checked':'').">";
						echo "<img src='".MAPPINS_PICS_URL.'/markers/'.$optdesc."'>&nbsp;";
					}
		echo '		</select>
				</td>
			</tr>';

	}
	static public function TrFormIconsCheck($name,$label,$value='',$opts=array(),$args=array()) {
		$fullid="mappin_map_$name";
		$fullname="mappin_map[$name]";
		// assume comma-separated value for each checked optval
		if ($value && !is_array($value) && $value)
			$value=explode(',',$value);
		if (!$value)
			$value=array();
		echo '
			<tr valign="top">
				<th scope="row"><label for="'.$fullid.'">'.$label.'</label></th>
				<td>';
					$cnt=0;
					foreach($opts as $optval=>$optdesc) {
						echo "<input name='{$fullname}[{$cnt}]' type='checkbox' value='$optval' ".(in_array($optval,$value)?'checked=checked':'').">";
						echo "<img src='".MAPPINS_PICS_URL.'/markers/'.$optdesc."'>&nbsp;";
						$cnt++;
						if ($cnt%15==0)
							echo "<br>";
					}
		echo '		</select>
				</td>
			</tr>';

	}
	
	// names lat and lng
	static public function TrFormGoogleMap($label,$values,$opts=array()) {
		echo '<tr valign="top">
				<th scope="row"><label for="mappins-editloc">'.$label.'</label></th>
				<td>';
		echo "<input type='hidden' name='mappin_map[lat]' id='mappin_map_lat' value='".$values[0]."'>";
		echo "<input type='hidden' name='mappin_map[lng]' id='mappin_map_lng' value='".$values[1]."'>";
		echo '<div style="width:600px;height:550px">';
			echo '<div class="mappin-bar" style="margin-bottom:0" >
					<div class="navbar-inner">
						<div class="navbar-search pull-left">
							<input type="text" id="gmapsearch" class="search-query" placeholder="Enter location">
						</div>
						<ul class="nav">
							<li></li>
						 	<li class="divider-vertical"></li>
							<li><a href="#" id="gmapmyloc"><i class=" icom-map-marker"></i> my location</a></li>
							';
							if (isset($opts['address'])) {
								echo '
									<li class="divider-vertical"></li>
									<li><a href="#" id="gmapvenueaddress" data-address="'.$opts['address'].'"> Zoek adres</a></li>';
							}
							if (isset($opts['zip'])) {
								echo '
									<li class="divider-vertical"></li>
									<li><a href="#" id="gmapvenuezip" data-zip="'.$opts['zip'].'"> Zoek postcode</a></li>';
							}
							echo '
						</ul>
					</div>
				 </div>';
			echo "<div id='mappins-editloc' style='height:500px;width:600px'></div>";	
		echo '</div>
			</td>
			</tr>';

		// echo '	<script>
		// 		var mappin_idLat="mappin_map_'.$names[0].'",mappin_idLng="mappin_map_'.$names[1].'";
		// 		</script>';
	}
	
	static public function TrFormOpentimes($name,$label,$value='',$args=array()) {
		$fullid="mappin_map_$name";
		$fullname="mappin_map[$name]";
		//echo '
		//	<tr valign="top">
		//		<th scope="row"><label for="'.$fullid.'">Opentimes debugging</label></th>
		//		<td>
		//			<input type="text" name="'.$fullname.'" id="'.$fullid.'" style="width:600px;" value="'.esc_html($value).'" />
		//		</td>
		//	</tr>';
		echo '<input type="hidden" name="'.$fullname.'" id="'.$fullid.'" value="'.esc_html($value).'" >';
		echo '
			<tr valign="top">
				<th scope="row"><label for="'.$fullid.'">'.$label.'</label></th>
				<td id="mappins_idopentimes">
					
				</td>
			</tr>';
		echo '<script>
				jQuery(function() {
					//mappins_opentimes.init("'.$fullid.'");
					coslib.opentimes.init("'.$fullid.'");
					
				});
				</script>';
	}
	
	static public function findIcons() {
		$icons=array();
		$dir = opendir(MAPPINS_PICS_DIR.'/markers');
		if ($dir) {
		    while(false !== ( $file = readdir($dir)) ) { 
				if ( !is_dir(MAPPINS_PICS_DIR.'/markers/' . $file) && 
					in_array(strtolower(pathinfo($file,PATHINFO_EXTENSION)),array('png','jpg','gif')) ) { 
					$icons[$file]=$file;
		        } 
		    } 
		    closedir($dir); 
		}
		asort($icons);
		return $icons;
	}
	
	
	// quoter
	public static function muq($value,$cast='') {	// mysql quoter
		// Quote if not a number or a numeric string
		if (!is_numeric($value) || $cast=='string') {
			//$value = "'" . mysql_real_escape_string($value) . "'";
			$value = "'" . addslashes($value) . "'";
		}
		return $value;
	}	
	public static function uq($value) {	// mysql quoter
		$value =  addslashes($value) ;
		return $value;
	}
	
}

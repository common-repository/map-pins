<?php
if ( !defined('ABSPATH') ) die('-1');	// no direct access

class mappins {
	public $showbar=true;		
	private $shortcode_included=false;
	
	function __construct() {
		//error_reporting(E_ALL & ~E_NOTICE);
	}
	
	function replace_shortcode($atts) {
		extract( $opts=shortcode_atts( array(
			'width' => '600',
			'height' => '900',	// height='none' for lists
			'searchbar'=>'Y',
			'list'=>'right',	// none/left/right
			'initiallist'=>'Y',	// default immediately visible
			'showmap'=>'show',	// none/show
			'listwidth'=>'35%',	// default 100% for when map
            'category' =>'',    // default NO category
		), $atts, 'mappins-map' ) );		

		if ($this->shortcode_included) {
			return '<div>'.__("Sorry, the widget can only be embedded once per page",'mappins').'</div>';
		}
		$this->shortcode_included=true;

		$this->showbar=($searchbar=='Y');
		if (strpos($width,'%')) {
			$widthstr=$width;	// literal
		}
		else {
			$widthstr=$width.'px';	// pixels
		}
		if (strpos($listwidth,'%')) {
			$listwidthstr=$listwidth;	
		}
		else {
			$listwidthstr=$listwidth.'px';	// fixed listwidth
		}

		$this->load_resources($opts);
		$rv='';
		

        $rv.= "<div  style='background-color:#fff;width:{$widthstr};$heightstyle:{$height}px;'>";
        $mapheight=$height;
        if ($this->showbar) {
            $rv .= "<div class='mappin-searchbar'>
                    <div style='float:left;width:50%;min-width:270px;'>
                        <input type='text' id='gmapsearch' class='search-query' placeholder='".__('Search city or zipcode', 'mappins')."' autocomplete='off'>
                    </div>
                    <div class='mappin-mylocation'>
                        <a href'#' id='gmapmyloc'><i class='icom-map-marker'></i>".__("my location", 'mappins')."</a>
                    </div>
                    <div class='mappin-mylocation'>
                        <input type='checkbox' id='gmapopen' class='gmapgeopend'> ".__("now open", 'mappins')." 
                    </div>
                    <div style='clear:both'></div>
                </div>";
            $mapheight -= 40;
        }
        if ($showmap=='none') {
            if ($initiallist!='Y')
                $heightstyle='max-height';	// invisible first so use dynamic height
            else
                $heightstyle='height';	// we use a real height
            $rv.= "<div class='mappins-list' id='mappins-list' style='width:none;$heightstyle:{$mapheight}px;'></div>";
        }
        else {
            switch ($list) {
            case 'none':
                $rv.= "<div class='mappins-map' id='mappins-map' style='width:auto;height:{$mapheight}px;'></div>";
                break;
            case 'left':
                $rv.= "<div class='mappins-list' id='mappins-list' style='width:{$listwidthstr};float:left;height:{$mapheight}px;'></div>";
                $rv.= "<div class='mappins-map' id='mappins-map' style='width:auto;min-width:100px;height:{$mapheight}px;'></div>";
                break;
            case 'right':
                // inserting the float-right BEFORE the main otherwise width:auto of main will fill whole
                $rv.= "<div class='mappins-list' id='mappins-list' style='width:{$listwidthstr};height:{$mapheight}px;float:right;'></div>";
                $rv.="<div class='mappins-map' id='mappins-map' style='width:auto;min-width:100px;height:{$mapheight}px;'></div>";
                break;
            }
        }
        $rv.="<div style='clear:both'></div>";
        $rv.="</div>";
		return $rv;
	}
	
	function load_footer_resources() {
		// totally on the bottom of the page..
		//echo "Mappins: LOAD FOOTER RESOURCES<br>";
	}
		
	function load_commonresources($opts) {
		// both admin and normal resources
		echo "<!-- MAPPINS COMMON -->";
		wp_enqueue_style( 'mappins_style', MAPPINS_PLUGIN_URL.'/styles/mappins-styles.css');
		wp_enqueue_script( 'mappins_coslib', MAPPINS_PLUGIN_URL.'/js/coslib.js', array('jquery'));	// handle, src, depts
		wp_enqueue_script( 'coslib_opentimes', MAPPINS_PLUGIN_URL.'/js/coslib-opentimes.js', array('jquery','mappins_coslib'));	// handle, src, depts
		wp_enqueue_script( 'coslib_markers', MAPPINS_PLUGIN_URL.'/js/coslib-markers.js', array('jquery','mappins_coslib'));	// handle, src, depts
		wp_localize_script('mappins_coslib', 'mappins_options',array_merge($opts,array(
			'pics_base_url'=>MAPPINS_PICS_URL.'/markers/',
			'showbar'=>$this->showbar,
			'googlekey'=>$this->get_configuration_option('googlekey'),
			'defcountrycode'=>$this->get_configuration_option('defcountry'),
			'defcountryname'=> xcos_country::getCoutryName($this->get_configuration_option('defcountry'),'nodomain'),	// default extension for country name 
			'dagendef'=>array('mazo'=>__('Every day','mappins'),'mavr'=>__('Mon-Fri','mappins'),'mado'=>__('Mon-Thr','mappins'),
								'ma'=>__('Monday','mappins'),'di'=>__('Tuesday','mappins'),'wo'=>__('Wednesday','mappins'),
			 					'do'=>__('Thursday','mappins'),'vr'=>__('Friday','mappins'),'za'=>__('Saturday','mappins'),'zo'=>__('Sunday','mappins')),
			'__openinghours'=>__('opening hours','mappins'),
			'__from'=>__('from','mappins'),
			'__till'=>__('till','mappins'),
			'__From'=>__('From','mappins'),
			'__Till'=>__('Till','mappins'),
			'__nearlocation'=>__('Around this location','mappins'),
			'__tel'=>__('Phone:','mappins'),
		)));
	}
	function load_resources($opts) {
		// for frontend - only loaded when shortcode is beeing replaced!
		echo "<!-- MAPPINS -->";
		$this->load_commonresources($opts);
		echo "<!-- ".print_r($opts,true)." -->";
		wp_enqueue_script( 'mappins_script', MAPPINS_PLUGIN_URL.'/js/mappins.js', array('jquery','mappins_coslib'));
		if (isset($opts['category']) && $opts['category']) {
		    // only show markers from a certain category
		    wp_localize_script('mappins_script', 'mappins_allmarkers', marker::byCategory($opts['category']) );	// include all info of all markers
		}
		else
		    wp_localize_script('mappins_script', 'mappins_allmarkers', marker::all());	// include all info of all markers
	} // End load_resources

	
	//-------------------------------------------------------------------
	// SETTINGS IN ADMIN
	//-------------------------------------------------------------------
	function load_admin_resources() {
		echo "<!-- Mappins: LOAD ADMIN RESOURCES -->";
		$this->load_commonresources(array());
		wp_enqueue_script('admin_mappins_script', MAPPINS_PLUGIN_URL.'/js/mappins-admin.js', array('jquery','mappins_coslib'),null,true);
	}

	function mappins_settings() {
		// if (function_exists('add_options_page')) { 
		// 	add_options_page('Map Pins settings', 'Map Pins settings', 'manage_options', basename(__FILE__), array(&$this, 'settings_page')); 
		// } 
		// DOC http://codex.wordpress.org/Function_Reference/add_menu_page
        // remove duplicate: https://wordpress.stackexchange.com/questions/52675/how-to-remove-duplicate-link-from-add-menu-page
		$adminmenu=$this->get_configuration_option('adminmenu');
		//             htmlTITLE      leftmenu        user access       unique name         function                           icon
	    add_menu_page($adminmenu,    $adminmenu,   'edit_plugins',     'map-pins-menu',     array(&$this, 'marker_page'), MAPPINS_PLUGIN_URL."/pics/map_app_small.png");
	    add_submenu_page('map-pins-menu', 'MapPins - Locations',              'Locations',              'edit_plugins' , 'mappins-menu-markers', array(&$this, 'marker_page'));
	    add_submenu_page('map-pins-menu', __('MapPins - Settings','mappins'), __('Settings','mappins'), 'edit_plugins' , 'mappins-menu-settings', array(&$this, 'settings_page'));
	    add_submenu_page('map-pins-menu', __('MapPins - Help','mappins'),     __('Help','mappins'),     'edit_plugins' , 'mappins-menu-help', array(&$this, 'help_page'));
        remove_submenu_page( 'map-pins-menu', 'map-pins-menu' );    // trick
	}
	
	// Markers manager
	function marker_page() {
		if (isset($_GET['id'])) {
			$id=$_GET['id'];
			// CARD
			switch(XCos::ifset($_GET['action'])) {
			case 'trash':
				if (isset($_POST['submit'])) 
					return $this->marker_delete($id);	// SURE! 
				return $this->marker_edit($id,true/*trash*/);
			case 'edit':
				if (isset($_POST['submit'])) 
		            return $this->marker_post($id);
				return $this->marker_edit($id);
			}
		}
		// LIST
		$results=marker::all();
		// global $menu;echo "<pre>".print_r($menu,true)."</pre>"; 
		echo"<div class=\"wrap\"><div id=\"icom-edit\" class=\"icon32 icon32-posts-post\"><br></div><h2>".__('Locations','mappins')."<a href='?page=map-pins-menu&action=edit&id=new' class='add-new-h2'>".__('add','mappins')."</a></h2>";
	    echo "
	      <table class=\"wp-list-table widefat fixed \" cellspacing=\"0\">
			<thead>
			<tr>
				<th scope='col' id='map_title' class='manage-column column-map_title desc'  style=''><span>".__('Name','mappins')."</span></th>
				<th scope='col' id='map_address' class='manage-column column-map_address desc'  style=''><span>".__('Address','mappins')."</span></th>
				<th scope='col' id='map_city' class='manage-column column-map_city desc'  style=''><span>".__('City','mappins')."</span></th>
				<th scope='col' id='map_category' class='manage-column column-map_category' style=''>".__('Category','mappins')."</th>
				<th scope='col' id='map_icon' class='manage-column column-map_icon' style=''>".__('Icon','mappins')."</th>
			</tr>
			</thead>
	        <tbody id=\"the-list\" class='list:wp_list_text_link'>";
	    foreach ( $results as $result ) {
	        echo "<tr id=\"record_".$result->id."\">";
	        echo "<td class='map_title column-map_title'><strong><big><a href='?page=map-pins-menu&action=edit&id=".$result->id."'>".$result->description."</a></big></strong>";
				echo "<br /><a href='?page=map-pins-menu&action=edit&id=".$result->id."' title='Edit'>".__('Edit','mappins')."</a> | <a href='?page=map-pins-menu&action=trash&id=".$result->id."' title='Trash'>".__('Trash','mappins')."</a></td>";
	        echo "<td class='map_address column-map_address'>".$result->address."</td>";	      
			echo "<td class='map_city column-map_city'>".$result->city."</td>";
	        echo "<td class='map_category column-map_category'>".$result->category."</td>";	      
	 		echo "<td class='map_icon column-map_icon'><img src='".MAPPINS_PICS_URL.'/markers/'.$result->icon."'></td>";
	        echo "</tr>";
	    }
	    echo "</table>";
		echo "</div>";
	}
	
	function marker_edit($id,$dotrash=false) {
		if ($id>0) {
			$row=marker::find($id);
			if (!$row) { echo __("Nothing found",'mappins');	return; }
			echo '<h2>'.($dotrash?__('Trash','mappins'):__('Edit','mappins')).' '.__('location','mappins').'</h2>';
		}
		else {
			if ($dotrash) {echo __('No ID found','mappins');return;}
			$row=marker::create();
			echo '<h2>'.__('New location','mappins').'</h2>';
		}
		// VIEW
		//echo "<pre>";print_r($row);echo "</pre>";
		$availicons=$this->get_configuration_option('availicons');
		if ($availicons && !is_array($availicons) )
			$availicons=explode(',',$availicons);
		if (!$availicons)
			$availicons=array('marker.png'=>'marker.png');
		$availicons=xcos::array_v_to_kv($availicons);
			
		echo '<div class="wrap">
			<form method="post" action="'.$_SERVER['REQUEST_URI'].'">';
		echo "<input type='hidden' name='needredirect' value='Y'>";
		echo '<table class="form-table">';
		XCos::TrFormInput('description',__('Name','mappins'),XCos::ifset($row->description),array('style'=>'width:400px'));
		XCos::TrFormInput('address',__('Address','mappins'),XCos::ifset($row->address),array('style'=>'width:300px'));
		XCos::TrFormInput('zip',__('Zipcode','mappins'),XCos::ifset($row->zip),array('style'=>'width:80px'));
		XCos::TrFormInput('city',__('City','mappins'),XCos::ifset($row->city),array('style'=>'width:300px'));
		XCos::TrFormSelect('country',__('Country','mappins'),
			XCos::ifset($row->country,$this->get_configuration_option('defcountry')),
			xcos_country::getCountries(),
			array('style'=>'width:180px'));
		XCos::TrFormInput('tel',__('Telephone','mappins'),XCos::ifset($row->tel),array('style'=>'width:300px'));
		XCos::TrFormSelect('category',__('Category','mappins'),XCos::ifset($row->category),
			xcos::array_v_to_kv(explode(',',$this->get_configuration_option('categories'))),
			//self::$categories,
			array('style'=>'width:180px'));
		XCos::TrFormIcons('icon',__('Icon','mappins'),XCos::ifset($row->icon),
			//xcos::findIcons(),
			$availicons,
			//self::$icons,
			array('style'=>'width:100px','format'=>'<img src="%s">'));
		XCos::TrFormInput('link',__('Website (incl. http://)','mappins'),XCos::ifset($row->link),array('style'=>'width:500px'));
		XCos::TrFormTextarea('remarks',__('Remarks','mappins'),XCos::ifset($row->remarks),array('style'=>'width:500px'));
		XCos::TrFormOpentimes('opentimes',__('Opening times','mappins'),XCos::ifset($row->opentimes),array());
		XCos::TrFormInput('afterhoursdesc',__('After hours notification','mappins'),XCos::ifset($row->afterhoursdesc),array('style'=>'width:500px'));
		//XCos::TrFormInput('lat','lat',XCos::ifset($row->lat),array('style'=>'width:80px'));
		//XCos::TrFormInput('lng','lng',XCos::ifset($row->lng),array('style'=>'width:80px'));
		$opts=array();
		if ($row->zip)
			$opts['zip']=str_replace(' ','',$row->zip).", ".xcos_country::getCoutryName($this->get_configuration_option('defcountry'),'nodomain');
		if ($row->address && $row->city) 
			$opts['address']=$row->address.", ".$row->city.", ".xcos_country::getCoutryName($this->get_configuration_option('defcountry'),'nodomain');
		XCos::TrFormGooglemap(__('Map','mappins'),array(XCos::ifset($row->lat),XCos::ifset($row->lng)),$opts);
		echo "</table>";
		echo '<div class="submit"><input type="submit" name="submit" class="button-primary" value="'.($dotrash?' '.__('Trash? Are you sure?','mappins').' ':__('Save','mappins')).'" /></div>';
		echo "</form></div>";
	}
	
	function marker_post($id) {
		//$data=$_POST['mappin_map'];	
		// WTF -> wp has magic quotes ON!?!???!!!!! 
		$data = stripslashes_deep( $_POST['mappin_map'] );	// all fields names are "mappin_map[zoom]" etc
		//echo "posting: ".$data['opentimes'];exit;
		if ($id>0) 
			marker::update($id,$data);
        else 
			marker::insert($data);
		wp_redirect(admin_url('admin.php?page=map-pins-menu&action=edit'));
		exit;
	}
	function marker_delete($id) {
		marker::delete($id);
		wp_redirect(admin_url('admin.php?page=map-pins-menu&action=edit'));
		exit;
	}
		
	function settings_page() {
		// echo "Mappins: SETTINGS PAGE<br>";
		// Check if post exists and save the configuraton options
		if (isset($_POST['mappins_map_noncename']) && wp_verify_nonce($_POST['mappins_map_noncename'],__FILE__)){
			// SAVE POST
			$options = $_POST['mappin_map'];
			if (isset($options['availicons']))
				$options['availicons'] =implode(',',$options['availicons']);	// glue together to one string
			//echo "<pre>";print_r($options);exit;
            $options['windowhtml'] = $this->get_configuration_option('windowhtml');
			update_option('mappins_config', $options);
			echo '<div class="updated"><p><strong>'.__("Settings Updated",'mappins').'</strong></div>';
		}
		else{
			// GET CONFIG
			$options = $this->get_configuration_option();
		}
		?>
			<div class="wrap">
				<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<?php	
			$this->_deploy_map_form($options);

		?>
			<div class="submit"><input type="submit" class="button-primary" value="<?=__('Update Settings', 'mappins')?>" /></div>
			<?php 
				// create a custom nonce for submit verification later
				echo '<input type="hidden" name="mappins_map_noncename" value="' . wp_create_nonce(__FILE__) . '" />'; 
			?>
			</form>
			</div>
		<?php

	}

	function _deploy_map_form($options = NULL, $single = false){
		?>
		<h2><?=__('Mappins Configuration','mappins')?></h2>
		<table class="form-table"><?php
			xcos::TrFormInput('adminmenu',__('Admin menu name:','mappins'),xcos::ifset($options['adminmenu'],'Map pins'),['size'=>40]);
			xcos::TrFormInput('googlekey',__('Google Maps Api key:','mappins'),xcos::ifset($options['googlekey'],'GOOGLE-API-KEY'),['size'=>60],
			    "Get your <a target=_blank href='https://developers.google.com/maps/documentation/javascript/get-api-key'>google maps API key</a>"
            );
			xcos::TrFormInput('categories',__('Categories:<br>(comma separated list)', 'mappins'),xcos::ifset($options['categories'],'first,second'),array('size'=>40));
			//                 $name,           $label,                      $value                                   ,$opts=array(),            $args $helptext
			xcos::TrFormSelect('defcountry',__('Default country', 'mappins'),xcos::ifset($options['defcountry'],'NL'),xcos_country::getCountries(),[],
                "For localization of the search-box"
            );
			XCos::TrFormIconsCheck('availicons',__('Available icons','mappins'),xcos::ifset($options['availicons'],''),xcos::findIcons(),array('style'=>'width:100px','format'=>'<img src="%s">'));
		?>
		</table>
		<p>credit: Maps Icons Collection http://code.google.com/p/google-maps-icons/ </p>
		<p><?=__('Need more icons? Just store them in wp-content/plugins/map-pins/pics/markers','mappins')?></p>
		<?php
	} // End _deploy_map_form


	function help_page() {
		?>
		<div class="wrap">
            <h3>Google maps API key</h3>
                A google API key is now required. Please protect your key by restricting it to your domain, because web site traffic to google maps can be very expensive!<br>
                Please get your <a href='https://developers.google.com/maps/documentation/javascript/get-api-key'>google maps API key</a> before using this plugin.

			<h3>Shortcuts</h3>
			<ul>
				<li>[mappins-map] - show the mappins widget with google map, searchbar and/or a list or places</li>
			</ul>
			<h3>Mappins-map shortcut options</h3>
			<ul>
				<li>width - in pixels or in %. ex: width="500" - 500 pixels width="100%" - 100% wide (responsive!). Default: 600</li>
				<li>height - in pixels. voorbeeld: height="600". Default: 900</li>
				<li>searchbar - show searchbar Y of N. Without searchbar there can be no list. Default Y</li>
				<li>list - show marker list. values: none, left,right. Default: right. List is sorted on distance when searched or my-location is used</li>
				<li>showmap - show the map. Values: none,show. Default: show</li>
				<li>listwidth - width of the list in pixes or percent of total width. Default 35% (or 100% when showmap="none")</li>
				<li>initiallist - value Y,N. If N, don't show a list until after the user has used the search bar. Only works in combination with searchbar=Y and showmap=none</li>
                <li>category - show only this category locations. Default: show all locations</li>
			</ul>
			<h3>examples</h3>
<pre>
  [mappins-map width="100%" height="900" searchbar="Y" list="left" showmap="show" listwidth="40%"]
     Show map filling the whole wordpress column,height 900 pixels, with searchbar and a list on the leftside 40% of the whole widget.

  [mappins-map width="500" height="900" searchbar="N" list="none"]
     Just show a map 

  [mappins-map showmap="none"]
     Show a searchbar with a list.

  [mappins-map showmap="none" height="400" initiallist="N"]
     Just show a searchbar, no list, no map. The height is ignored initially. After the user has searched, the list becomes visible with results.

  [mappins-map category="other"]
     Only show locations that are in the `other` category
</pre>
		</div>
		<?php
	}
	

	
	//-------------------------------------------------------------------
	// R/W config
	//-------------------------------------------------------------------
	/**
	 * Get default configuration options
	 */
	function _default_configuration(){
		return array(
				'zoom' => '10',
				'width' => '450',
				'height' => '450',
				'adminmenu' => 'Map pins',
				'categories' => 'no-category-defined',
				'defcountry'=> 'NL'
				);
	} // End _default_configuration
	
	/**
	 * Set default system and maps configuration
	 */
	function set_default_configuration($default = false){
		$mappins_default = $this->_default_configuration();
							
    	$options = get_option('mappins_config');
		if ($default || $options === false) {
			update_option('mappins_config', $mappins_default);
			$options = $mappins_default;
		}
		return $options;
	} // End set_default_configuration
	
	/**
	 * Get a part of option variable or the complete array
	 */
	function get_configuration_option($option = null){
	
		$options = get_option('mappins_config');	// it is boolean FALSE of not set
		$default = $this->_default_configuration();

		if(!isset($options) || !$options){
			$options = $default;
		}
		// return ONE option 
		if(isset($option)){
            return (isset($options[$option])) ? $options[$option] : ((isset($default[$option])) ? $default[$option] : null);
		}
		// return ALL
		return $options;
	} // End get_configuration_option

}
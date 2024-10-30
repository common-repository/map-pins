<?php
if ( !defined('ABSPATH') ) die('-1');	// no direct access

// Marker Model
class marker {
	static function GetTable() {
		// prefix / base_prefix (for multisite base_prefix is the network-admin wordpress, prefix is the subdomains (one tableset per subdomain))
		return  $GLOBALS['wpdb']->base_prefix . "mappins_markers";
	}
	
	static function update($id,$cols) {
		$table_name=self::GetTable();
		$fsql='';
		foreach($cols as $c=>$v) {
			if ($fsql)
				$fsql.=", ";
			$fsql.= "$c = ".XCos::muq($v);
		}
		$sql="update $table_name set $fsql where id=".XCos::muq($id);
		//echo $sql;exit;
		$rv = $GLOBALS['wpdb']->query($sql);
		return $rv;
	}
	
	static function insert($cols) {
		$table_name=self::GetTable();
		$fsql='';
		foreach($cols as $c=>$v) {
			if ($fsql)
				$fsql.=", ";
			$fsql.= "$c = ".XCos::muq($v);
		}	
		$sql="insert into $table_name set $fsql ";
		//echo $sql;exit;
		$rv = $GLOBALS['wpdb']->query($sql);
		return $rv;
	}	
	
	static function delete($id) {
		$table_name=self::GetTable();
		$rv = $GLOBALS['wpdb']->query($GLOBALS['wpdb']->prepare("delete from $table_name where id=%d",$id));
		return $rv;				
	}
	
	static function find($id) {
		$table_name = self::GetTable(); 
		$results = $GLOBALS['wpdb']->get_results($GLOBALS['wpdb']->prepare("select * from $table_name where id=%d",$id));
		if ($results)
			return $results[0];
		return false;
	}
	
	static function all() {
		$table_name = self::GetTable(); 
		return $GLOBALS['wpdb']->get_results("select * from $table_name order by city"); // order by id desc
	}
	static function byCategory($category) {
		$table_name = self::GetTable();
		return $GLOBALS['wpdb']->get_results($GLOBALS['wpdb']->prepare("select * from $table_name where category=%s order by city",$category));
	}
	static function create() {
		$row=new stdClass();
		// fill in the defautls
		$row->category='sterkliniek'; 
		$row->icon='sterkliniek.png';	
		return $row;
	}
}
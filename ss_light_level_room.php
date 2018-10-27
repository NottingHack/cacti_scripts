<?php
	require_once("/home/cacti/www_secure/db.php");

	/* do NOT run this script through a web browser */
	if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
		die("<br><strong>This script is only meant to run at the command line.</strong>");
	}
	
	$no_http_headers = true;
	
	/* display ALL errors */
	error_reporting(0);
	
	if (!isset($called_by_script_server)) {
		include_once(dirname(__FILE__) . "/../include/global.php");
		
		array_shift($_SERVER['argv']);

		print call_user_func("ss_light_level", $_SERVER['argv']);
	}
	
	function ss_light_level($room) {
//		$db_host = "localhost";
//		$db_user = "gk";
//		$db_pass = "gk";
//		$db_db = "instrumentation";


//		$idb = new mysqli($db_host, $db_user, $db_pass, $db_db);
		$idb = db_link();
		$query = "SELECT name, reading FROM light_level WHERE name = ".$room;

		$result = $idb->query($query);
		$output = "";

		while ($row = $result->fetch_object())
			$output .= 'light_level:'.$row->reading.' ';

		return $output;
	}

?>

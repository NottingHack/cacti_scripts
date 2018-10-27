<?php
require "/home/cacti/www_secure/db.php";

/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
    die("<br><strong>This script is only meant to run at the command line.</strong>");
}

$no_http_headers = true;

/* display ALL errors */
error_reporting(0);

if (!isset($called_by_script_server)) {
    include_once(dirname(__FILE__) . "/../include/global.php");
    
    print call_user_func("ss_current_members");
}

function ss_current_members() {
    $idb = db_link();
    $query = "SELECT count(member_id) FROM members WHERE member_status = 5";

    $result = $idb->query($query);
    $output = "";

    while ($row = $result->fetch_object())
        $output .= 'CurrentMembers:'.$row->count;

    return $output;
}

?>

<?php
require_once "/home/cacti/www_secure/db.php";

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
    print call_user_func_array("ss_current_members", $_SERVER['argv']);
}

function ss_current_members($status) {
    $idb = db_link();
    switch ($status) {
        case 5:
            $roles = "('member.current', 'member.young', 'member.temporarybanned')";
            break;

        case 6:
            $roles = "('member.ex', 'member.banned')";
            break;
        default:
            return ;
            break;
    }

    $query = "SELECT count(user_id) AS members FROM user u INNER JOIN role_user ru ON (ru.user_id = u.id) INNER JOIN roles r ON (r.id = ru.role_id) WHERE r.name IN $roles";

    $result = $idb->query($query);
    $output = "";

    while ($row = $result->fetch_object())
        $output .= 'members:'.$row->members;

    return $output;
}

?>

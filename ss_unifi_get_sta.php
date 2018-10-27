<?php
//Cacti Script to gather stats from Unifi Devices....

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

    print call_user_func("ss_unifi_get_sta", $_SERVER['argv']);
}

function ss_unifi_get_sta($host, $user, $pass, $info, $site)
{
    //force to lowercase and change : to -
    $info = strtolower(str_replace(":", "-", $info));

    $baseurl = 'https://'.$host.':8443';

    //get the data
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_REFERER, $baseurl."/manage/account/login");
    curl_setopt($ch, CURLOPT_URL, $baseurl.'/api/login');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch,CURLOPT_HTTPHEADER,array (
        "Content-Type: application/json",
        "X-Requested-With:XMLHttpRequest",
        "Connection:keep-alive"
    ));
    curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSLVERSION, 1);
    //curl_setopt($ch, CURLOPT_GET, false);
    //Error Checking:
    //curl_setopt($ch, CURLOPT_VERBOSE, true);

    $postData = json_encode(array("username" => $user, "password" => $pass));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookie.txt');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $store = curl_exec($ch);
    //echo $store;

    curl_setopt($ch, CURLOPT_URL, $baseurl.'/api/s/'.$site.'/stat/sta');
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_REFERER, $baseurl."/manage");
    curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    // curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSLVERSION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $output = curl_exec($ch);
    curl_close($ch);

    //echo $output;

    $json_a = json_decode($output,true);

    foreach($json_a['data'] as $data) {

        //for debug, will list all the output
        /*
        echo 'ap_mac: '.$data['ap_mac'].'
        essid: '.$data['essid'].'
        mac: '.$data['mac'].'
        signal: '.$data['signal'].'
        rssi: '.$data['rssi'].'
        rx_bytes: '.$data['rx_bytes'].'
        tx_bytes: '.$data['tx_bytes'].'
        
        ';
        */

        $mySSIDArray[] = $data['essid'];
        $myAPArray[] = str_replace(":","-",$data['ap_mac']);

        //Get rssi for each ssid in an array
        $myessid = $data['essid'];
        ${$myessid}[] = $data['rssi'];

        //Get rssi per AP in an array
        $myap = str_replace(":","-",$data['ap_mac']);
        ${$myap}[] = $data['rssi'];


    }


    $SSIDcount = array_count_values($mySSIDArray); //count up the number of times it shows up
    $APcount = array_count_values($myAPArray); //count up the number of times it shows up

    foreach ($SSIDcount as $key => $value) {
        if ($info == strtolower($key)) {
            $myrssi = round(array_sum(${$key})/count(${$key}), 2);
            print "connections:$value rssi:$myrssi";
        }
    }
    foreach ($APcount as $key => $value) {
        if ($info == $key) {
            $myrssi = round(array_sum(${$key})/count(${$key}),2);
            print "connections:$value rssi:$myrssi";
        }
    }
}

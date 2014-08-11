<?php

/**
 * Encode shop ID with consumer_secret
 *
 * @param $string
 * @param $consumer_secret
 * @param $timestamp
 * @return bool|string
 */
function encode_shop_id($string, $consumer_secret, $timestamp){
    $encode_method = 'sha512';
    return hash_hmac($encode_method, $string.$timestamp, $consumer_secret);
}

$status = array('statusCode' => 401);
if(count($_GET)>0 && isset($_GET['signature']) && isset($_GET['userId']) && isset($_GET['timestamp'])){

        $date = new DateTime();
        $timestamp_now = $date->getTimestamp();

        $signature = mysql_real_escape_string($_GET['signature']);
        $user_id = mysql_real_escape_string($_GET['userId']);
        $timestamp = mysql_real_escape_string($_GET['timestamp']);
        $user = get_userdata($user_id);

        $shop_id = get_user_meta($user_id, 'soundest-radar-shopID', true);
        $consumer_key = get_user_meta($user_id, 'woocommerce_api_consumer_key', true);
        $consumer_secret = get_user_meta($user_id, 'woocommerce_api_consumer_secret', true);

        if(encode_shop_id($shop_id, $consumer_secret, $timestamp) == $signature){
            $status['statusCode'] = 200;
            $status['consumer_key'] = $consumer_key;
            $status['consumer_secret'] = $consumer_secret;
            $status['shopID'] = $shop_id;
            $status['userEmail'] = $user->user_email;
        }else{
            $status['statusCode'] = 401;
            $status['error'] = 'no signature found';
        }

}
else{
    $status['statusCode'] = 401;
    $status['error'] = 'parameters is missing';
}

echo (json_encode($status));
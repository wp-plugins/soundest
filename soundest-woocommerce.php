<?php

/*
    Plugin Name: Soundest
    Plugin URI: http://www.soundest.com
    Description: Soundest is an easy to use email marketing service for small and medium ecommerce sites. There is no need to code, to design or to build anything. It works seamlessly with your online store to collect customer data, build promotions and track sales. You can find us in Woocommerce tab on your left.
    Version: 1.2
    Author: Soundest
    Author URI: http://www.soundest.com
*/

define('SWP_VERSION', '1.3');
define('PARTNER_ID', '53cf5accb64ceef36168080b');

require_once ('functions.php');

register_activation_hook(__FILE__, 'plugin_activation');
add_action('admin_menu', 'add_menu_item');


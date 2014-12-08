<?php

/**
 * Add admin menu item
 */
function add_menu_item() {
    add_submenu_page('woocommerce', "Soundest", "Soundest", 'manage_woocommerce', 'soundest', 'sd_admin_page');
}

/**
 * Show admin page
 */
function sd_admin_page(){

    global $wp_rewrite;
    $date = new DateTime();
    $timestamp = $date->getTimestamp();
    $current_user = wp_get_current_user();
    enable_wc_options();

    // generate consumer key & secret if is not generated
    if( empty($current_user->woocommerce_api_consumer_key) )
        generate_user_keys($current_user);

    $site_url = get_shop_url();
    $shop_id = urlencode(get_shop_id(true));
    $shop_url = urlencode($site_url);
    $signature = encode_string(get_user_meta($current_user->data->ID, 'soundest-radar-shopID', true), $timestamp);

    $url_parameters = 'shopURL='.$shop_url.'/&shopID='.$shop_id.'&userID='.$current_user->data->ID.'&signature='.$signature.'&timestamp='.$timestamp.'&version='.SWP_VERSION;

    if (PARTNER_ID !== 'NotPartnerID') {
        $url_parameters .= '&partnerID='.PARTNER_ID;
    }

    if(check_htaccess()){
        // print content
        echo '<br />';
        echo '<p><b>Authentication parameters: </b></p>';
        echo '<p><b>Consumer key: </b> '.$current_user->woocommerce_api_consumer_key.'</p>';
        echo '<p><b>Consumer secret: </b> '.$current_user->woocommerce_api_consumer_secret.'</p>';
        echo '<p><b>Shop ID: </b> '.get_shop_id(true).'</p>';

        echo '
            <br />
            <a href="https://login.soundest.net/REST/authorize/woocommerce?'.$url_parameters.'"  class="button button-primary button-large" target="_blank"> Go to Soundest </a>
        ';
    }else{
        echo '<p>If your <code>.htaccess</code> file were <a href="http://codex.wordpress.org/Changing_File_Permissions">writable</a>, we could do this automatically, but it isn&#8217;t so these are the mod_rewrite rules you should have in your <code>.htaccess</code> file. Click in the field and press <kbd>CTRL + a</kbd> to select all.</p>';
        //echo '<br/><p style="color: #FF0000; font-weight: bold;"> .htaccess file is not writable. Please update it manualy. </p>';
        echo '<form action="options-permalink.php" method="post">';
        echo '<p><textarea rows="6" class="large-text readonly" name="rules" id="rules" readonly="readonly">'.esc_textarea( $wp_rewrite->mod_rewrite_rules() ).'</textarea></p>';
        echo '</form>';
        echo '<p>1. Create .htaccess file in public_html/ directory.</p>';
        echo '<p>2. Set .htaccess file permissions to 777.</p>';
        echo '<p>3. Paste code from text box above to .htaccess file and save.</p></br>';
        echo 'If you are experiencing any problems or need help, contact us at <a href="mailto:support@soundest.com">support@soundest.com</a>';
    }

}

/**
 * Enable woocommerce options
 */
function enable_wc_options(){

    $permalink_option = get_option('permalink_structure');
    $rest_api_enabled_option = get_option('woocommerce_api_enabled');

    // set permalinks
    if( empty($permalink_option) )
        update_option('permalink_structure', '/%postname%/');

    // enable REST API
    if( $rest_api_enabled_option == 'no' )
        update_option('woocommerce_api_enabled', 'yes');

}

/**
 * Function called when plugin is activated
 */
function plugin_activation() {
    get_shop_id();
}

/**
 * Generate random unique shop ID
 *
 * @return string
 */
function generate_unique_shop_id(){

    $shop_id = '';
    $date = new DateTime();

    $str = get_site_url().'/'.$date->getTimestamp().'/'.substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 40);
    $shop_id = hash('sha256', $str, false).hash('sha1', $str, false);

    return $shop_id;
}

/**
 * Create shop autentitication file with generated shop ID
 */
function get_shop_id($key = false){

    $current_user = wp_get_current_user();

    // generate and save shop id in db for current user if not generated
    $shop_id_meta = get_user_meta($current_user->data->ID, 'soundest-radar-shopID', true);
    if(empty($shop_id_meta)){
        $shopId = generate_unique_shop_id();
        update_user_meta( $current_user->data->ID, 'soundest-radar-shopID', $shopId );
    }

    //return shop id
    if($key){
        return get_user_meta($current_user->data->ID, 'soundest-radar-shopID', true);
    }
}

/**
 * Generate user consumer key & consumer secret
 * @param $user
 */
function generate_user_keys($user){
    $consumer_key = 'ck_' . hash( 'md5', $user->data->user_login . date( 'U' ) . mt_rand() );
    update_user_meta( $user->data->ID, 'woocommerce_api_consumer_key', $consumer_key );
    $consumer_secret = 'cs_' . hash( 'md5', $user->data->ID . date( 'U' ) . mt_rand() );
    update_user_meta( $user->data->ID, 'woocommerce_api_consumer_secret', $consumer_secret );
    update_user_meta( $user->data->ID, 'woocommerce_api_key_permissions', 'read' );
}

/**
 * Encode shop ID with consumer_secret
 *
 * @param $string
 * @param $timestamp
 * @internal param $str ing* ing
 * @return string
 */
function encode_string($string, $timestamp){
    $encode_method = 'sha512';
    $current_user = wp_get_current_user();
    $current_user_consumer_secret = get_user_meta($current_user->data->ID, 'woocommerce_api_consumer_secret', true);

    return hash_hmac($encode_method, $string.$timestamp, $current_user_consumer_secret);
}

/**
 * Add script and push product page productID
 */
function add_product_script() {
    global $post;
    $post_type = get_post_type( $post );

    if($post_type == 'product'){
        if(!is_shop()){
            $product_params = json_encode(array('productID' => $post->ID));
        } else {
            $product_params = json_encode(array('productID' => ""));
        }
    } else {
        $product_params = json_encode(array('productID' => ""));
    }
    
    wp_register_script('in-store-email-builder',  plugins_url().'/'.plugin_basename(__DIR__).'/in-store-email-builder.js');
    wp_enqueue_script('in-store-email-builder');
    wp_localize_script('in-store-email-builder', 'product_params', $product_params);
}
add_action('wp_footer', 'add_product_script' );

/**
 * Register custom endpoint
 */
function custom_endpoint( ){
    global $wp_rewrite;
    add_rewrite_endpoint( 'soundest', EP_ROOT );
    $wp_rewrite->flush_rules();
}
add_action( 'init', 'custom_endpoint' );

/**
 * If custom link
 */
function ao_template_redirect() {
    if(get_query_var('soundest')){
        require_once ('check-shop-id.php'); exit();
    }
}
add_action( 'template_redirect', 'ao_template_redirect' );

/**
 * get shop url without http://
 * @return mixed
 */
function get_shop_url(){
    $site_url = get_site_url();
    $site_url = explode('//', $site_url);
    return $site_url[1];
}

/**
 * @return bool
 */
function check_htaccess(){
    $home_path = get_home_path();
    if (( ! file_exists( $home_path . '.htaccess' ) && is_writable( $home_path ) ) || is_writable( $home_path . '.htaccess' ))
        return true;
    else
        return false;
}
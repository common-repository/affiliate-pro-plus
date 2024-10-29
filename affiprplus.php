<?php  
/*
Plugin Name: Affiliate Pro Plus
Plugin URI: http://howitworkz.com/wordpress/affiliate-pro-plus/
Description: Affiliate Pro Plus lets your registered users get credit for new users they recruit
Author: sunny
Version: 3.0.1
Author URI: http://howitworkz.com/wordpress/affiliate-pro-plus/
Questions, sugestions, problems? Let me know at Chaz@blogio.net
*/


function affiplus_install()
{
    global $wpdb;
	//set the options
	$newoptions['affp_override'] = '1';
	$newoptions['affp_expire'] = '30';
	$newoptions['affp_trkpar'] = 'affid';
	$newoptions['affp_usrpage'] = '1';
	$newoptions['affp_showid'] = '1';
	add_option('affiliate_plus_cfg', $newoptions);
	
	// create table
    $table = $wpdb->prefix."affp_referrals";
    $structure = "CREATE TABLE $table (
        affp_id BIGINT(20) unsigned NOT NULL,
        affp_referral VARCHAR(100) NOT NULL,
	UNIQUE KEY affp_id (affp_id)
    );";
	
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($structure);
}

register_activation_hook(__FILE__,'affiplus_install');

// process incomming referral
function affiplus_getreferral()
{
	global $wpdb;
	$affiliate_plus_cfg = array(
		'affp_override'			=> '',
		'affp_expire'			=> '',
		'affp_redir'			=> '',
		'affp_trkpar'			=> '',
		'affp_usrpage'			=> '',
		'affp_land'				=> '',
		'affp_showid'			=> ''
	);
	$affiliate_plus_cfg = get_option('affiliate_plus_cfg');
	
	if(!($affiliate_plus_cfg['affp_trkpar'])){
		$affiliate_plus_cfg['affp_trkpar'] = 'affid';
	}

	foreach ($_GET as $key => $value) {
		if ($key == $affiliate_plus_cfg['affp_trkpar']) {
			$affid = $value;
    	}
	}
	
	if(isset($affid)) {
		if (!$affiliate_plus_cfg['affp_override']){
			// check if cookie already exists
			if(isset($_COOKIE['affiplus'])){
				return;
			}
		}
		if($affiliate_plus_cfg['affp_expire']){
			$exp = time()+60*60*24*$affiliate_plus_cfg['affp_expire'];
		}
		$wp_root = get_option('home');
		$htp 		= "http://";
		$htps		= "https://";
		$affp_domain = str_replace($htp, ".", $wp_root);
		$affp_domain = str_replace($htps, ".", $affp_domain);
		$affp_domain = explode("/",$affp_domain);
		// set cookie
		setcookie('affiplus', $affid, $exp, '/', $affp_domain[0]);
		if($affiliate_plus_cfg['affp_land']) {
			header("Location: ".$affiliate_plus_cfg['affp_land']);
			exit(0);
		}
	}

	
}
	
add_action("init", "affiplus_getreferral");

function affiplus_signupform()
{
	global $wpdb;
	$affiliate_plus_cfg = array(
		'affp_override'			=> '',
		'affp_expire'			=> '',
		'affp_redir'			=> '',
		'affp_trkpar'			=> '',
		'affp_usrpage'			=> '',
		'affp_showid'			=> ''
		);
	$affiliate_plus_cfg = get_option('affiliate_plus_cfg');

	// check if we have a cookie
	if(isset($_COOKIE['affiplus'])){
		$form_referral = $_COOKIE['affiplus']."\" readonly=\"readonly";
		if ($affiliate_plus_cfg['affp_showid']){
			echo'<p>
			<label>Referral ID<br />
			<input type="text" name="affiplus_referral" id="user_login" class="input" value="'.$form_referral.'" size="20" tabindex="30" /></label>
			</p>';
		} else {
			echo'<input type="hidden" name="affiplus_referral" id="user_login" class="input" value="'.$form_referral.'" size="20" tabindex="30" />';
		}
	} else {
		echo'<p>
			<label>Referral ID<br />
			<input type="text" name="affiplus_referral" id="user_login" class="input" value="'.$form_referral.'" size="20" tabindex="30" /></label>
		</p>';
	}
}

add_action("register_form", "affiplus_signupform"); 

function affiplus_register($userid)
{
	global $wpdb;
	$reffered = $_POST['affiplus_referral'];
	//$reffered = $wpdb->escape($_COOKIE['affiplus']);
	$table = $wpdb->prefix."affp_referrals";
    $wpdb->query("INSERT INTO $table(affp_id, affp_referral) VALUES('$userid', '$reffered')");
}

add_action("user_register", "affiplus_register"); 

function affiplus_settings()
{
    global $wpdb;
    include 'affipsettings.php';

}

function affiplus_menu()
{
	add_submenu_page('options-general.php', 'Affiliate Pro Plus Settings', 'Affiliate Pro Plus', 9, 'affiplus_settings', 'affiplus_settings');
}

add_action("admin_menu", "affiplus_menu");

function affiplus_redirect($redirect_to, $requested_redirect_to, $user)
{
	if ( !isset ( $user->user_login ) ) {
		return $redirect_to;
	}
	
	if($user->user_level){
		if($user->user_level > 7){
			return $requested_redirect_to;
		}
	}
	
	$affiliate_plus_cfg = array(
		'affp_override'			=> '',
		'affp_expire'			=> '',
		'affp_redir'			=> '',
		'affp_trkpar'			=> '',
		'affp_usrpage'			=> '',
		'affp_showid'			=> ''
		);
	$affiliate_plus_cfg = get_option('affiliate_plus_cfg');
	
	if ($affiliate_plus_cfg['affp_redir']){
		return $affiliate_plus_cfg['affp_redir'];
	} else {
		return $requested_redirect_to;
	}
}

add_filter("login_redirect", "affiplus_redirect", 10, 3);

// Add new column to the user list
function affiplus_addcolumn( $columns ) {
	// This requires WP 2.8+
	$columns['affiplus_refbycol'] = __('referred by', 'user-locker');
	$columns['affiplus_refcountcol'] = __('referred', 'user-locker');

	return $columns;
}

add_filter("manage_users_columns", "affiplus_addcolumn");
		
// Add column content for each user on user list
function affiplus_fillcolumn( $value, $column_name, $user_id ) {
	global $wpdb;
	if ( $column_name == 'affiplus_refbycol' ) {
		// get referral name
		$user_info = get_userdata($user_id);
    	$table = $wpdb->prefix."affp_referrals";
		$referral = $wpdb->get_var("SELECT affp_referral FROM $table WHERE affp_id=$user_id");
		if($referral){
			return $referral;
		}else{
			return "-";
		}
	}
	
	if ( $column_name == 'affiplus_refcountcol' ) {
		// count referrals by this user
		$user_info = get_userdata($user_id);
    	$table = $wpdb->prefix."affp_referrals";
		$ref_count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE affp_referral = '$user_info->user_login'");
		if($ref_count){
			return $ref_count;
		}else{
			return "-";
		}
	}

	return $value;
}

add_filter("manage_users_custom_column", "affiplus_fillcolumn", 10, 3 );

function affiplus_userpage($user_id)
{
	global $wpdb;
	$affiliate_plus_cfg = array(
		'affp_override'			=> '',
		'affp_expire'			=> '',
		'affp_redir'			=> '',
		'affp_trkpar'			=> '',
		'affp_usrpage'			=> '',
		'affp_showid'			=> ''
		);
	$affiliate_plus_cfg = get_option('affiliate_plus_cfg');
	
	if(!($affiliate_plus_cfg['affp_trkpar'])){
		$affiliate_plus_cfg['affp_trkpar'] = 'affid';
	}

	if ($affiliate_plus_cfg['affp_usrpage']){
		$affurl = get_option('siteurl').'/?'.$affiliate_plus_cfg['affp_trkpar'].'='.$user_id->user_login;
		// count referrals by this user
		$table = $wpdb->prefix."affp_referrals";
		$ref_count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE affp_referral = '$user_id->user_login'");
		if(!$ref_count){
			$ref_count = "-";
		}
		// get referral name
		$referral = $wpdb->get_var("SELECT affp_referral FROM $table WHERE affp_id = '$user_id->ID'");
		if(!$referral){
			$referral = '-';
		}	
		
		echo"<h3>Affiliate Pro Plus</h3>
			<table class=\"form-table\">
				<tr>
					<th>referred by</th>
					<td>$referral</td>
				</tr>
				<tr>
					<th>number of members referred</th>
					<td>$ref_count</td>
				</tr>
				<tr>
					<th>referral URL</th>
					<td><input type=\"text\" value=\"$affurl\" class=\"regular-text\"/><br/>
					<span class=\"description\">send people to this URL to get credit for new signups</span></td>
				</tr>
			</table>";
	}	
}

add_action("profile_personal_options", "affiplus_userpage"); 
?>
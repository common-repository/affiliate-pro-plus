<?php 
$affiliate_plus_cfg = array(
	'affp_override'			=> '',
	'affp_expire'			=> '',
	'affp_redir'			=> '',
	'affp_trkpar'			=> '',
	'affp_usrpage'			=> '',
	'affp_land'				=> '',
	'affp_showid'			=> ''
);
$ol_flash = '';
if(isset($_POST['affp_submit'])) {
	$affiliate_plus_cfg['affp_override'] = $_POST['affp_override'];
	$affiliate_plus_cfg['affp_expire'] = $_POST['affp_expire'];
	$affiliate_plus_cfg['affp_redir'] = $_POST['affp_redir'];
	$affiliate_plus_cfg['affp_trkpar'] = $_POST['affp_trkpar'];
	$affiliate_plus_cfg['affp_usrpage'] = $_POST['affp_usrpage'];
	$affiliate_plus_cfg['affp_land'] = $_POST['affp_land'];
	$affiliate_plus_cfg['affp_showid'] = $_POST['affp_showid'];
	update_option('affiliate_plus_cfg',$affiliate_plus_cfg);
	$ol_flash = "Your settings have been saved.";
}
if ($ol_flash != '') echo '<div id="message"class="updated fade"><p>' . $ol_flash . '</p></div>';

$affiliate_plus_cfg = get_option('affiliate_plus_cfg');
$affurl = get_option('siteurl').'/?affid=';
?>

<div class="wrap">
<div id="icon-options-general" class="icon32"><br /></div>
<h2>Affiliate Pro Plus Settings</h2>

<table class="form-table">
<form method="post">
<tr valign="top">
<th scope="row">override cookie</th>
	<td><input name="affp_override" type="checkbox" value="1" <?php if($affiliate_plus_cfg['affp_override']){echo"checked";}?>></td>
	<td>select if the cookie should be overridden if one already exists </td>
</tr>
<tr valign="top">
<th scope="row">cookie expire</th>
	<td><input name="affp_expire" type="text" value="<?php echo $affiliate_plus_cfg['affp_expire'];?>" size="10" maxlength="10"></td>
	<td>number of days untill the cookie expires, 0 to let it expire after session end (ie.closing the browser window)</td>
</tr>
<tr>
<tr valign="top">
<th scope="row">redirect URL</th>
	<td><input name="affp_redir" type="text" value="<?php echo $affiliate_plus_cfg['affp_redir'];?>" size="30" maxlength="100"></td>
	<td>URL to send users to after they log in (does not affect users with admin privileges) </td>
</tr>
<tr>
<tr valign="top">
<th scope="row">landingpage URL </th>
	<td><input name="affp_land" type="text" value="<?php echo $affiliate_plus_cfg['affp_land'];?>" size="30" maxlength="100"></td>
	<td>URL to send visitors to after arriving at your site through an affiliate link</td>
</tr>
<tr valign="top">
<th scope="row">affiliate tracking parameter </th>
	<td><input name="affp_trkpar" type="text" value="<?php echo $affiliate_plus_cfg['affp_trkpar'];?>" size="30" maxlength="10"></td>
	<td>name of the parameter to use for affiliate tracking (ie affid, sendby, hearedfrom) default value 
	is affid, which means user phil should send visitors to <?php echo $affurl; ?>phil<br/>
	<strong>warning: this setting should not be changed while your affiliate program is already running as 
	excisting users that have published affiliate links could be sending new signups to the old URL which will no longer track the referral! </strong></td>
</tr>
<tr valign="top">
<th scope="row">show referral ID on signup page</th>
	<td><input name="affp_showid" type="checkbox" value="1" <?php if($affiliate_plus_cfg['affp_showid']){echo"checked";}?>></td>
	<td>select to show referral ID on the signup form.</td>
</tr>
<tr valign="top">
<th scope="row">show affiliate details on user page </th>
	<td><input name="affp_usrpage" type="checkbox" value="1" <?php if($affiliate_plus_cfg['affp_usrpage']){echo"checked";}?>></td>
	<td>select to show 'referred by' 'number of referrals' and 'affiliate URL' on the users profile page.</td>
</tr>
<tr>
<tr valign="top">
<th scope="row"></th>
	<td><p class="submit"><input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" /></p></td>
</tr>
<input name="affp_submit" type="hidden" value="1">
</form>
</table>
</div>
<?php
/*
Plugin Name: WP aMember Dashboard Widget
Plugin URI: http://www.joshmac.net/wordpress/wp_amember_dashboard_widget.html
Description: Adds aMember account info in your WP/WPMU/WPMS dashboard. Users will be able to have some of their payment account info in their dashboard.
Author: Joshua Parker
Version: 0.2.2
Author URI: http://www.joshmac.net/
released under the terms of the GNU GPL
*/

global $aMember_dashboard_widget_settings_page;

if ( version_compare($wp_version, '3.0.9', '>') ) {
	$aMember_dashboard_widget_settings_page = 'settings.php';
} else {
	$aMember_dashboard_widget_settings_page = 'ms-admin.php';
}

add_action( 'admin_init', 'register_aMember_dashboard_widget_settings' );
add_action( 'admin_menu', 'aMember_dashboard_widget_menu' );

function register_aMember_dashboard_widget_settings() {
	//register our settings
	register_setting( 'aMember-dashboard-widget-settings-group', 'amemberurl' );
	register_setting( 'aMember-dashboard-widget-settings-group', 'amember_dashboard_title' );
}

function aMember_dashboard_widget_menu() {
if(!is_multisite()) {
	//create options menu
	add_options_page('aMember Dashboard Widget Settings', 'aMember Dashboard', 'manage_options', 'aMember-dashboard-widget-settings', 'aMember_dashboard_widget_settings_page');
} else {
	add_submenu_page($aMember_dashboard_widget_settings_page, __('aMember Dashboard Widget', 'aMember_dashboard_widget'), __('aMember Dashboard Widget', 'aMember_dashboard_widget'), 10, 'aMember-dashboard-widget', 'aMember_dashboard_widget_settings_page');
	}

}

function set_aMember_dashboard_widget_options() {
	add_option('amember_dashboard_title','Blog Hosting Account','aMember Dashboard Widget Title');
	add_option('amemberurl','http://example.com/aMember','aMember Dashboard Widget');
}

function unset_aMember_dashboard_widget_options() {
	delete_option('amember_dashboard_title');
	delete_option('amemberurl');
}

if(is_multisite()) {

function set_network_aMember_dashboard_widget_options() {
	add_site_option('amember_dashboard_title','Blog Hosting Account','aMember Dashboard Widget Title');
	add_site_option('amemberurl','http://example.com/aMember','aMember Dashboard Widget');
}

function unset_network_aMember_dashboard_widget_options() {
	delete_site_option('amember_dashboard_title');
	delete_site_option('amemberurl');
}

	update_site_option('amember_dashboard_title', get_option('amember_dashboard_title'));
	update_site_option('amemberurl', get_option('amemberurl'));
}


function aMember_dashboard_widget_settings_page() {
global $wpdb;
?>
<div class="wrap">
<h2><?php _e('WP aMember Dashboard Widget'); ?></h2>

<form method="post" action="<?php if(!is_multisite()) { ?>options.php<?php } else { ?>/wp-admin/network/settings.php?page=aMember-dashboard-widget-settings<?php } ?>">
    <?php settings_fields( 'aMember-dashboard-widget-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php _e('aMember Dashboard Title'); ?></th>
        <td><input type="text" name="amember_dashboard_title" value="<?php if(!is_multisite()) { echo get_option('amember_dashboard_title'); } else { echo get_site_option('amember_dashboard_title'); } ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e('URL to your aMember install without trailing slash.'); ?></th>
        <td><input type="text" name="amemberurl" value="<?php if(!is_multisite()) { echo get_option('amemberurl'); } else { echo get_site_option('amemberurl'); } ?>" /></td>
        </tr>
    </table>
    
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>
<?php }
if((get_option('amemberurl') != '') && (get_option('amember_dashboard_title') != '')) {
add_action('wp_dashboard_setup', 'amember_wp_dashboard_setup');

function amember_wp_dashboard_setup() {
	wp_add_dashboard_widget( 'wp_amember_dashboard', get_option('amember_dashboard_title'), 'widget_wpAmember' );
}

function widget_wpAmember_init() {

	if (!isset($_SESSION)) session_start(); 

	function widget_wpAmember($args) {
		$options = get_option('widget_wpAmember'); 
		$title = $options['title']; 
		$price = $options['price'];
   		$activetitle = $options['activetitle'];
		$amemberurl = $options['amemberurl']; 
        if ($au=$_SESSION['_amember_user']) {// user is logged-in

?>
    	
<a href='<?php echo get_option('amemberurl'); ?>/member.php'><?php _e('Account Details'); ?></a> | <a href='<?php echo get_option('amemberurl'); ?>/profile.php'><?php _e('Edit Profile'); ?></a><br /><br /> 
<span class='widgettitle'><?php echo $activetitle ?></span>
<ul>

<?php
foreach ($_SESSION['_amember_products'] as $p) {
?>

<li><?php _e('Package:'); ?> <a href='<?php echo $p['url'] ?>'><?php echo $p['title'] ?></a> | <?php _e('Payment:'); ?> <?php echo $p['price'] ?></li>

<?php
}
	print "</ul>"; 
	
} else {// user is not logged-in

?>
    	
<form action='<?php echo get_option('amemberurl'); ?>/login.php' method='post' />
Username:<input type='text' name='amember_login' id='a_login' /><br />
Password:<input type='password' name='amember_pass' id='a_password' />
<input type='submit' id='amembersubmit' value='Login' />
			
<?php			
		}
		}
	} 
}
	add_action('widgets_init','widget_wpAmember_init');
<?php
/*
Plugin Name: Bounce Grab Lite
Description: Displays an exit corner peel
Version: 1.0
Author: Andrew Lewin
Author URI: http://andrewlewin.info
License: GPLv2
*/

define ( 'albg_INSERTJS', plugin_dir_url(__FILE__).'js');
define ( 'albg_INSERTCSS', plugin_dir_url(__FILE__).'css');
define ( 'albg_INSERTIMAGE', plugin_dir_url(__FILE__).'images');

class WP_Cornerp {

	public $options;

	public function __construct(){
		$this->options = get_option('albg_settings_section');
		$this->register_settings_and_fields();
	}

	public function addMenuPage(){		
		add_options_page('Bounce Grab', 'Bounce Grab', 'administrator', __FILE__, array('WP_Cornerp', 'display_options_page'));
	}

	public function display_options_page(){
		
		?>

		<div class="wrap">
			<?php screen_icon(); ?>
			<h2> Bounce Grab Lite </h2>
			<form method="post" action="options.php" enctype="multipart/form-data">
				<input id='albg_reset_default' name='albg_reset_default' type='hidden'/>
				<input id='albg_reset_banner' type='hidden'/>
				<?php settings_fields('albg_settings_section'); ?>
				<?php do_settings_sections(__FILE__); ?>
				
				<p class="submit">
					<input name="submit" type="submit" class="button-primary" value="Save Changes"/>
				</p>
			</form>
		</div>

		<?php

	}

	public function register_settings_and_fields(){

		register_setting('albg_settings_section','albg_settings_section', array($this,'albg_settings_section_validate_settings'));		
		add_settings_section('albg_settings_section', 'Edit Settings', array($this,'main_section_cb'), __FILE__);		
		add_settings_field('albg_upgrade_button', 'Upgrade to the PRO Version', array($this, 'albg_upgrade_button'), __FILE__, 'albg_settings_section');
		add_settings_field('albg_enable_setting', 'Disable Corner Peel Exit Pop', array($this, 'albg_enable_setting'), __FILE__, 'albg_settings_section');
		add_settings_field('albg_banner_image', 'Banner Image', array($this, 'albg_banner_image'), __FILE__, 'albg_settings_section');
		add_settings_field('albg_redirect_url', 'Redirect URL', array($this, 'albg_redirect_url'), __FILE__, 'albg_settings_section');
		
		
		add_settings_field('albg_reset_button', 'Reset To Default Settings', array($this, 'albg_reset_button'), __FILE__, 'albg_settings_section');

		
	}

	public function albg_settings_section_validate_settings($plugin_options){
		
		//reset the form
		if ( !empty( $_POST[ 'albg_reset_default' ] ) ){
			delete_option('albg_settings_section');
			return;
		}
		
		if ( !empty( $_POST[ 'albg_reset_image' ] ) ){
			$this->options['albg_banner_image'] = null;
		}

		
		//if using files, should test for the correct types
		if(!empty($_FILES['albg_banner_image_uload']['tmp_name'])){
			$overide = array('test_form' => false);
			$file = wp_handle_upload($_FILES['albg_banner_image_uload'], $overide);
			$plugin_options['albg_banner_image'] = $file['url'];
		} else {
			$plugin_options['albg_banner_image'] = $this->options['albg_banner_image'];
		}

		return $plugin_options;
	}

	public function main_section_cb(){
		
	}


	/*
	*
	* Inputs
	*
	*/

	public function albg_upgrade_button(){
		echo '<b>To Get the pro version with Audio playback and a tone more features click<br/><a style="font-size:150%" href="http://www.bouncegrab.com/" target="_blank">Upgrade To Bounce Grab Pro Version</a></b>';
	}
	
	public function albg_enable_setting(){
		if (isset($this->options['albg_enable_setting']) && $this->options['albg_enable_setting']=="true"){
			$checked = "checked";
		} else {
			$checked = "";
		}	
		echo "<input size='40' name='albg_settings_section[albg_enable_setting]' type='checkbox' value='true' {$checked}/>";
	}
	
	public function albg_redirect_url(){
		echo "<input size='40' name='albg_settings_section[albg_redirect_url]' type='text' value='{$this->options['albg_redirect_url']}'/> <i>(Include http://)</i>";
	}
	
	public function albg_reset_button(){
		echo '<input type="submit" class="button" value="Restore Defaults" name="albg_reset_button" onClick="javascript:document.getElementById(\'albg_reset_default\').value = \'true\';" style="width:auto;" />';
	}

	public function albg_banner_image(){

		echo '<input type="file" name="albg_banner_image_uload"/> <i> image should be 350px by 350px<i><br/><br/><input type="submit" class="button" value="Delete Banner" name="albg_reset_banner" onClick="javascript:document.getElementById(\'albg_reset_banner\').value = \'true\';" style="width:auto;" /><br/><br/>';
		if ( isset($this->options['albg_banner_image']) ){
			echo "<img src='{$this->options['albg_banner_image']}' alt='' />";
		}
	}
	
	public function setupPeel(){
		
		if (albg_enable_setting()=='true'){
			return;
		}
		
		wp_deregister_script( 'jquery' );
		wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js', array(), '1.7.2', false);
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.6/jquery-ui.min.js', array('jquery'), '1.8.6');
  	    wp_register_script( 'aleu_insert_turn_script', albg_INSERTJS .'/turn-min.js', array( 'jquery-ui-dialog' ) );
  	    wp_enqueue_script('aleu_insert_turn_script');	
		wp_enqueue_script('aleu_insert_easing_script',  albg_INSERTJS .'/jquery.easing.js');
		//wp_enqueue_script('aleu_insert_spund_media_script',  albg_INSERTJS .'/peelsound.js');
		wp_enqueue_script('aleu_insert_media_script',  albg_INSERTJS .'/jquery.exitpeel-1.0-min.js');
		wp_enqueue_script('aleu_insert_expand_script',  albg_INSERTJS .'/exxPnd-min.js');
		wp_enqueue_style('aleu_insert_style',  albg_INSERTCSS .'/turn.css');
		
		$jsloc = albg_INSERTJS;

		$cornImageLocation = albg_banner_image();
		$cornImageUrl = albg_redirect_url();
		
		
		$mediaWidth = '400';
		$mediaHeight = '532';	
		
		
$optin = <<<HTML
		'<div id="rText" style="display:none"></div>
		<script>
  				
				
				function externalFun(mySound) {
					mySound = mySound;
					
					var options = {
				      delayLoad: "",       
				      audioLocation: "",     
					  cornImageLocation: "{$cornImageLocation}",
				      cornImageUrl: "{$cornImageUrl}" ,  
					  backgroundLocation: "",
					  mediaHeight: "{$mediaHeight}",
					  mediaWidth: "{$mediaWidth}",
  					  overlayBrightness: \'\',
				      jslocation: \'{$jsloc}\',
				      sound: mySound
			    };
			    jQuery("body").exitpeel(options);
				}
		externalFun("");
			
		</script>'

HTML;
//echo $optin;
		add_action( 'wp_footer', create_function( '', "echo $optin;" ) );
  	
	}


}

add_action( 'wp', 'albg_setup_peel' );

function albg_setup_peel(){
	WP_Cornerp::setupPeel();
	
}

add_action('admin_menu','add_menu_page_from_WP_Cornerp');

function add_menu_page_from_WP_Cornerp(){
	WP_Cornerp::addMenuPage();
}

add_action('admin_init','instantiate_WP_Cornerp');

function instantiate_WP_Cornerp(){
	new WP_Cornerp();
}


function albg_banner_image(){
	
	$options = get_option('albg_settings_section');
	
	if (isset($options['albg_banner_image']) && $options['albg_banner_image']!=""){
		$image = $options['albg_banner_image'];
	} else {
		$image = albg_INSERTIMAGE . '/wait.png';
	}

	return $image;
}

function albg_redirect_url(){
	
	$options = get_option('albg_settings_section');
	
	if (isset($options['albg_redirect_url']) && $options['albg_redirect_url']!=''){
		return $options['albg_redirect_url'];
	} else {
		return 'http://bouncegrab.com';
	}

}

function albg_enable_setting(){
	
	$options = get_option('albg_settings_section');
	
	if (isset($options['albg_enable_setting']) && $options['albg_enable_setting']=="true"){
		return 'true';
	} else {
		return 'false';
	}

}


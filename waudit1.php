<?php
/*
Plugin Name: SecureMoz Security Audit
Version: v1.0.1
Plugin URI: http://wpsecurity.securemoz.com/
Author: Ram, Haim
Author URI: http://wpsecurity.securemoz.com/
Description: Performs a security audit to detect PHP and WordPress vulnerabilities and solve them
*/
require_once("inc/simple_html_dom.php"); 					// public function test_if_html_contains_generator($find='meta[name=generator]') {
require_once("class/waudit_API.php"); 					// Application Programming Interface
require_once("class/waudit_ContextualHelpMenu.php"); 	// Contextual Help Menu class creates contextual Help menu
require_once("class/waudit_WPAdminMenuBar.php"); 	// Contextual Help Menu class creates contextual Help menu
require_once("class/waudit_Test.php"); 				// Tests class contains tests to determine health of the system
require_once("class/waudit_Helper.php"); 				// Helper class contains various helperm ethods
require_once("class/waudit_Compress.php");			// Compress class compresses backups 
require_once("class/waudit_Mail.php"); 				// Mail class sends backups over email
require_once("class/waudit_Cron.php"); 				// Cron class schedules backups
require_once("class/waudit_View.php"); 				// View class renders output
require_once("class/VirusTotalApiV2.php"); 				// View class renders output VirusTotalApiV2
require_once("class/__functions.php"); 				// View class renders output our custom waudit API malware scanner
#if (!class_exists('GakplSecurityAudit')):

Class GakplSecurityAudit {

public $plugin_name = 'Waudit Security';
public $plugin_slug = 'waudit1';
public $pp = 'waudit'; // Plugin Prefix for options,tables, other db related data
public $plugin_backupdir = 'backups';
public $plugin_backupdir_files = 'files';
public $plugin_backupdir_database = 'database';
public $plugin_backupdir_wpconfig = 'wpconfig';

public $plugin_database_dir = 'database'; // Remove and replace with $plugin_backupdir_database
public $plugin_wpconfig_dir = 'wpconfig'; // Remove?
public $plugin_system_dir = 'files';	// Remove and replace with $plugin_backupdir_files

public $tmp_extension = ".csatmp";
public $backup_file_permission = 0600;
public $root_path = ABSPATH;
public $root_url;
public $backupdir;
public $imgdir;

public $keys = array('AUTH_KEY', 
					'SECURE_AUTH_KEY', 
					'LOGGED_IN_KEY', 
					'NONCE_KEY',
					'AUTH_SALT', 
					'SECURE_AUTH_SALT', 
					'LOGGED_IN_SALT', 
					'NONCE_SALT');
public $result_colors = array(0 => 'green', 1 => 'red');

public $T = ''; // 'Test' class
public $H = ''; // 'Helper' class
public $C = ''; // 'Contextual Help' class
public $Y = ''; // 'Compression' class
public $M = ''; // 'Mail' class
public $V = ''; // 'View' class
public $A = ''; // 'API' class

public $all_settings = array(
						'general' => array(
							'auto_rerun_all_on_change' => array(
								'element' => 'input',
								'type' => 'checkbox',
								'd' => true,
								'title' => 'Auto update score on each test run.'
							),
							'dont_bck_bckp_directory' => array(
								'element' => 'input',
								'type' => 'checkbox',
								'd' => true,
								'title' => 'Ignore backup directory when making Files backup (faster).'
							),							
							'bckp_file_permission' => array(
								'element' => 'input',
								'type' =>	'text',
								'size' =>	'4',
								'd' => '0600',
								'title' => 'Backup file permission (Octal notation)'
							),
						),

						'serverphp' => array(
			/**/			'upload_max_filesize' => array( //ini.core name
								'element' => 'slider',
								'type' => 'bytes',
								'd' => 67108864,
								'title' => 'Maximum size of an uploaded file',
							),
			/**/			'post_max_size' => array( //ini.core name
								'element' => 'slider',
								'type' => 'bytes',
								'd' => 67108864,
								'title' => 'Maximum page load size',
							),
			/**/			'max_file_uploads' => array( //ini.core name
								'element' => 'slider',
								'type' => '',// int
								'd' => 30,
								'title' => 'Maximum number of simultaneously uploaded files',
								'min' => 0,
							),
			/**/			'max_input_time' => array( //ini.core name
								'element' => 'slider',
								'type' => 'seconds',
								'd' => -1,
								'title' => 'Maximum input parsing time',
								'min' => -1,
							),
			/**/			'max_execution_time' => array( //ini.core name
								'element' => 'slider',
								'type' => 'seconds',
								'd' => 90,
								'title' => 'Maximum script parsing time',
								'min' => -0,
							),
			/**/			'memory_limit' => array( //ini.core name
								'element' => 'slider',
								'type' => 'bytes',
								'd' => 4294967296,
								'title' => 'RAM allocated to Wordpress',
							),
						),

						'backup' => array(
							'object-database' => array(
								'element' => 'input',
								'type' => 'checkbox',
								'd' => true,
								'title' => 'Database backup'
								),
							'object-files' => array(
								'element' => 'input',
								'type' => 'checkbox',
								'd' => false,
								'title' => 'Files backup'
								),
							'filename-files' => array(
								'element' => 'input',
								'type' =>	'text',
								'd' => 'waudit_backup_files_%Y-%m-%d_%H-%i',
								),
							'filename-database' => array(
								'element' => 'input',
								'type' =>	'text',
								'd' => 'waudit_backup_db_%Y-%m-%d_%H-%i',
								),
							'filename-files-format' => array(
								'element' => 'select',
								'd' => 'uncompressed',
								'values' => array( 	'uncompressed'=>'Uncompressed',
													'.zip'=>'.zip',
													'.tar'=>'.tar (experimental)',
													'.tar.gz'=>'.tar.gz (experimental)',
													'.tar.bz2'=>'.tar.bz2 (experimental)',
											)
								),
							'filename-database-format' => array(
								'element' => 'select',
								'd' => '.sql',
								'values' => array(	'.sql'=>'.sql',
													'.zip'=>'.zip',
													'.tar'=>'.tar',
													'.tar.gz'=>'.tar.gz',
													'.tar.bz2'=>'.tar.bz2',

											)
								),
							'to-directory' => array(
								'element' => 'input',
								'type' => 'radio',
								'd' => true,
								'title' => 'Directory on server',
								),
							'email_enabled' => array(
								'element' => 'hidden',
								'type' => 'checkbox',
								'd' => false,
								'title' => ''
								),
							'email_objects' => array(
								'element' => 'select',
								'd' => 'database',
								'values' => array(	'database'=>'database',
													'files'=>'files',
													'database+files'=>'database and files',
											)
								),
							'email_to' => array(
								'element' => 'input',
								'type' =>	'text',
								'd' => '',
								),
							'email_subject' => array(
								'element' => 'input',
								'type' =>	'text',
								'd' => 'Waudit backup',
								),



							'schedule_enabled' => array(
								'element' => 'input',
								'type' => 'hidden',
								'd' => false,
								'title' => ''
								),
							'schedule_objects' => array(
								'element' => 'select',
								'd' => 'database',
								'values' => array(	'database'=>'database',
													'files'=>'files',
													'database+files'=>'database and files',
											)
								),
							'schedule_frequency' => array(
								'element' => 'select',
								'd' => '.sql',
								'values' => array(	'hourly'=>'1h',
													'twicedaily'=>'12h',
													'daily'=>'day',
													'weekly'=>'week',
													'monthly'=>'month',
											)
								),



						),
										
					);
public $path_chmod = array(
		'../' => 0755,
		'.htaccess' => 0644,
		'readme.html' => 0600,
		'wp-includes/' => 0755,
		'wp-admin/' => 0755,
		'wp-admin/js/' => 0755,
		'wp-admin/index.php' => 0644,
		'wp-admin/install.php' => 0600,
		'wp-admin/upgrade.php' => 0600,
		'wp-content/' => 0755,
		'wp-content/index.php' => 0655,
		'wp-content/themes/' => 0755,
		'wp-content/themes/index.php' => 0644,
		'wp-content/plugins/' => 0755,
		'wp-content/plugins/index.php' => 0644,
		);



public function __construct() {

	// Registering 3 magic hooks
	register_activation_hook( __FILE__, array( &$this, 'register_hook_activate' ) );
	register_deactivation_hook( __FILE__, array( &$this, 'register_hook_deactivate' ) );
	#register_uninstall_hook( __FILE__, array( get_class($this), 'register_hook_uninstall' ) );

	$this->root_url = site_url();
	$this->backupdir = $this->root_path.'wp-content/'.$this->plugin_slug.'/';
	$this->imgdir = $this->root_url.'/'.PLUGINDIR.'/'.$this->plugin_slug.'/images/';
  $this->imgmalware = $this->root_url.'/'.PLUGINDIR.'/'.$this->plugin_slug.'/img/';
  $this->docs = $this->root_url.'/'.PLUGINDIR.'/'.$this->plugin_slug.'/documentation/';  

}

public function init() {

	// Object is constructed, lets make it work

	if (file_exists($this->root_path . 'wp-includes/pluggable.php')) {

		// Call to undefined public function wp_get_current_user()
		require_once($this->root_path . 'wp-includes/pluggable.php');

		// Load translations
		add_action('plugins_loaded', array($this, 'waudit_load_translations'));

		if(is_admin() && current_user_can('manage_options')) {

			// Admin actions
			add_action('admin_enqueue_scripts', array( $this, 'enqueue_styles_admin' ));
			
			add_action('admin_enqueue_scripts', array( $this, 'enqueue_scripts_admin' ));
			add_action('admin_menu', array( $this, 'waudit_admin_menu'));
			
			$plugin = plugin_basename( __FILE__ );
			add_filter( "plugin_action_links_$plugin", array(&$this, 'plugin_add_settings_link') );
			add_filter( 'plugin_row_meta', array(&$this, 'plugin_add_meta_link'), 10, 2 );

			// Instances of sub-classes
			$this->T = new waudit_Test;
			$this->H = new waudit_Helper;	
			$this->C = new waudit_ContextualHelpMenu;
			$this->Y = new waudit_Compress;
			$this->M = new waudit_Mail;
			$this->S = new waudit_Cron;
			$this->V = new waudit_View;

			// Set up missing options
			$current_user = wp_get_current_user();
			$current_user_email = $current_user->user_email;
			$this->all_settings['backup']['email_to']['d'] = $current_user_email;
			
			if ($this->is_setting_on('general','auto_rerun_all_on_change')) {
				if (isset($_POST['waudit_submitted_form']) && $this->validate_form_nonce($_POST['waudit_submitted_form'])==true) {
					
					// Ok, lets not rerun everything on options save, they have secret input field, check for that
					if (array_key_exists('do_not_rerun_all_tests', $_POST) && $_POST['do_not_rerun_all_tests']=='please') {
						// Ask and you shall receive, tests not rerun
					} else {
						$this->T->perform_test('all');					
					}
				
				}				
			}
			
			if (array_key_exists('skip_init_backup_step', $_GET) && $_GET['skip_init_backup_step']==true ) {
						
						// skip init, set setting
						$this->waudit_update_option("setting_system_ignore_plugin_version",'1');
					
				}	


		} else {

			// Front-End hooks
			
			add_action('wp_enqueue_scripts', array( $this, 'enqueue_styles_frontend' ));

			add_action( "init", array($this,'WPAdminMenuBarMenuInit'));

			// Enable API in frontend
			$this->A = new waudit_API;
		}
	}
}


/**
 * Run on plugin activation
 */
public static function register_hook_activate() {

	// Add schedules
	add_filter( 'cron_schedules', array($this, 'cron_add_custom_intervals') );

	// Create default directories
	$c = new GakplSecurityAudit;
	$c->init();
	$c->create_default_directory_paths();


}

/**
 * Run on plugin deactivation
 */
public static function register_hook_deactivate() {
	
	$c = new GakplSecurityAudit;
	$c->init();

	// Delete backup schedule
	$c->S->unset_cron('add_action_do_backup');
	
	// Remove schedules
	remove_filter( 'cron_schedules', array($c, 'cron_add_custom_intervals') );
	
	// delete default directories , R
	chmod($c->backupdir, 0777);
	if (is_dir($c->backupdir) && is_writable($c->backupdir))
		$c->H->delete_files($c->backupdir);
}

/**
 * Run on plugin uninstallation
 * @todo Add uninstall.php(?) instead of this
 */
/*public static function register_hook_uninstall() {

	$c = new GakplSecurityAudit;
	$c->init();

	//if uninstall not called from WordPress exit
	if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
		exit();	

	
}*/




public function cron_add_custom_intervals() {
	// add a 'weekly' interval
	$schedules['weekly'] = array(
		'interval' => 604800,
		'display' => __('Weekly')
	);
	$schedules['monthly'] = array(
		'interval' => 2635200,
		'display' => __('Monthly')
	);
	return $schedules;
}



public function waudit_load_translations() {

  load_plugin_textdomain('waudit', false, basename( dirname( __FILE__ ) ) . '/languages' );

}


/**
 * Adds styles to admin panel
 */
public static function enqueue_styles_admin() {
	wp_register_style('nsa-style', plugins_url('/css/admin.css', __FILE__) );
	wp_register_style('font-awesome', plugins_url('/css/font-awesome/css/font-awesome.css', __FILE__) );
	wp_register_style('retro', plugins_url('/css/retro/jquery-ui-1.9.0.custom.css', __FILE__) );
	
	wp_enqueue_style('nsa-style');
	wp_enqueue_style('font-awesome');
	wp_enqueue_style('retro');
	
}/**
 * Adds styles to frontend
 */
public static function enqueue_styles_frontend() {
	
	wp_register_style('font-awesome', plugins_url('/css/font-awesome/css/font-awesome.css', __FILE__) );

	wp_enqueue_style('font-awesome');

	
}

/**
 * Adds scripts to admin panel
 */
public static function enqueue_scripts_admin() {
	
	wp_register_script('el_primero',plugins_url('/javascript/el_primero.js', __FILE__), null, null, false );
    wp_register_script('jquery_knob',plugins_url('/javascript/jquery.knob.js', __FILE__), null, null, false ); /* last true to include in footer*/
	wp_register_script('get_that_knob_working',plugins_url('/javascript/get_that_knob_working.js', __FILE__), null, null, false );
	wp_register_script('slider_custom',plugins_url('/javascript/slider_custom.js', __FILE__),
						array('jquery','jquery-ui-dialog','jquery-ui-core','jquery-ui-slider','jquery-ui-widget','jquery-ui-mouse'),'1.9.1', false);

	wp_enqueue_script('el_primero');
    wp_enqueue_script('jquery_knob');
	wp_enqueue_script('get_that_knob_working');
	wp_enqueue_script('slider_custom');
    

}

/**
 * Add Settings link to plugin page
 */
public function plugin_add_settings_link( $links ) {
    $settings_link = '<a href="admin.php?page='.$this->plugin_slug.'">Settings</a>';
  	array_unshift( $links, $settings_link );
  	return $links;
}

/**
 * Add FAQ link in plugin page
 */
public function plugin_add_meta_link( $links, $file ) {
	$plugin = plugin_basename(__FILE__);
	if ( $file == $plugin ) {
		return array_merge(
			$links,
			array( '<a href="http://wpsecurity.securemoz.com/about/faq/" target="_blank" title="FAQ">FAQ</a>' )
		);
	}
	return $links;
}

public function create_default_directory_paths() {

	// Create directories on users server
	//@todo - remove them on unistall
	$paths = array( $this->backupdir,
					$this->backupdir.$this->plugin_backupdir_files,
					$this->backupdir.$this->plugin_backupdir_database,
					$this->backupdir.$this->plugin_backupdir_wpconfig,
				);

	foreach ($paths as $path) {
		if (!file_exists($path))
	    	mkdir($path, 0755, true);
	}
}


/*
*
**
// ADMIN
**
*
*/
public function waudit_admin_menu() {
	add_menu_page($this->plugin_name,$this->plugin_name,'manage_options',$this->plugin_slug, array(&$this, 'waudit_admin_menu_page'), $this->imgdir.'waudit_griffin logo_light_24.png');
}

public function waudit_admin_menu_page() {

	

	$header = '';
	$active_tab = isset( $_GET['tab']) ? $_GET['tab'] : 'options_safety_test';

	if (isset($_POST['waudit_submitted_form']) && $this->validate_form_nonce($_POST['waudit_submitted_form'])==true) {
		$name_of_correspoding_execution = 'do_form_'.$_POST['waudit_submitted_form'];
		
		$this->$name_of_correspoding_execution();
	}
	?>
	<div class="wrap wauditadmin">
		
		
		
	<?php
  
	// Last version run option
	// If last version differs from current	
	if ($this->plugin_get_version() !== $this->waudit_get_option("plugin_version_last_run_all_tests") && !$this->is_setting_on('system','ignore_plugin_version')) {
		// Needs reset
		$welcome_message = "This plugin version has not run yet!<br/> 
			With more functionality you also get new potential risks, there is a slight chance this version being incompatible with your current Wordpress installation.<br/>
			We don't take chances.<br/>
			<br/>
			It is recommended to do the following before starting:
			<ul>
				<li><i class=\"icon-archive\"></i>&nbsp;Full Wordpress Installation backup
				<li><i class=\"icon-list-alt\"></i>&nbsp;Full Wordpress database backup
			</ul>";
		?>
		
		
		<div class="metabox-holder has-right-sidebar columns-1">
			<div class="inner-sidebar">
				<?php echo $this->waudit_sidebar(); ?>
			</div>
			<div id="post-body">
				<div id="post-body-content">

				<?php
				echo $this->postbox_col('top',0,100);
				echo $this->admin_form_safety_test();
				echo $this->postbox_col('bottom');

				echo $this->postbox_col('top',0,49);
				echo $welcome_message;
				echo $this->postbox_col('bottom');
				?>
				</div>
			</div>
	<?php
	
	} else {
		// It's fine, no reset needed
		?>
		<h2 class="nav-tab-wrapper">
			<a href="?page=<?php echo $this->plugin_slug ?>&tab=options_safety_test" class="nav-tab <?php echo $active_tab == 'options_safety_test' ? 'nav-tab-active' : ''; ?>"><i class="icon-stethoscope"></i>&nbsp;<?php _e('Security audit','waudit'); ?></a>
            <a href="?page=<?php echo $this->plugin_slug ?>&tab=scan_malware" class="nav-tab <?php echo $active_tab == 'scan_malware' ? 'nav-tab-active' : ''; ?>"><i class="icon-bug"></i>&nbsp;<?php _e('Malware scan','waudit'); ?></a>
			<a href="?page=<?php echo $this->plugin_slug ?>&tab=options_settings" class="nav-tab <?php echo $active_tab == 'options_settings' ? 'nav-tab-active' : ''; ?>"><i class="icon-wrench"></i>&nbsp;<?php _e('Settings','waudit'); ?></a>
			<a href="?page=<?php echo $this->plugin_slug ?>&tab=options_backup" class="nav-tab <?php echo $active_tab == 'options_backup' ? 'nav-tab-active' : ''; ?>"><i class="icon-cloud"></i>&nbsp;<?php _e('Backup','waudit'); ?></a>
			<a href="?page=<?php echo $this->plugin_slug ?>&tab=options_database" class="nav-tab <?php echo $active_tab == 'options_database' ? 'nav-tab-active' : ''; ?>"><i class="icon-inbox"></i>&nbsp;<?php _e('Database','waudit'); ?></a>
			<a href="?page=<?php echo $this->plugin_slug ?>&tab=options_server" class="nav-tab <?php echo $active_tab == 'options_server' ? 'nav-tab-active' : ''; ?>"><i class="icon-hdd"></i>&nbsp;<?php _e('Server','waudit'); ?></a>
			<a href="?page=<?php echo $this->plugin_slug ?>&tab=options_user" class="nav-tab <?php echo $active_tab == 'options_user' ? 'nav-tab-active' : ''; ?>"><i class="icon-user"></i>&nbsp;<?php _e('User','waudit'); ?></a>
			<a href="?page=<?php echo $this->plugin_slug ?>&tab=options_configphp" class="nav-tab <?php echo $active_tab == 'options_configphp' ? 'nav-tab-active' : ''; ?>"><i class="icon-cog"></i>&nbsp;<?php _e('Configuration','waudit'); ?></a>
			</h2>
		<div class="metabox-holder has-right-sidebar columns-1">
			<div class="inner-sidebar">
				<?php echo $this->waudit_sidebar(); ?>
			</div>
			<?php
		if( $active_tab && method_exists($this, $active_tab) ) {
			?>
			<div id="post-body">
				<div id="post-body-content">

				<?php
				echo $header;
				echo $this->$active_tab();
				?>
				</div>
			</div>
			<?php
		}
	}
	?>
	</div>
	</div>
	<?php

}

// Methods for each tab
public function options_safety_test() {

	$html='';
	$html .= $this->postbox_col('top',0,100);
	$html .= $this->admin_form_safety_test();
	$html .= $this->postbox_col('bottom');

	$html .= $this->postbox_col('top',0,100);
	$html .= $this->admin_form_safety_test3();
	$html .= $this->postbox_col('bottom');
	return $html;
}


public function options_settings() {
	$html='';
	$html .= $this->postbox_col('top',2,49);
	$html .= $this->admin_form_options_settings_general();
	$html .= $this->postbox_col('bottom');

	$html .= $this->postbox_col('top',0,49);
	$html .= $this->admin_form_options_settings_php();
	$html .= $this->postbox_col('bottom');

	return $html;
}

public function options_backup() {
	$html = '';
	$html .= $this->postbox_col('top',0,100);
	$html .= $this->admin_form_backup();
	$html .= $this->postbox_col('bottom');

	return $html;

}

public function options_database() {
	$html ='';

	$html .= $this->postbox_col('top',2,49);
	$html .= $this->admin_form_database_update_prefix();
	$html .= $this->postbox_col('bottom');

	$html .= $this->postbox_col('top',2,23);
	$html .= $this->admin_form_configphp_debug2();
	$html .= $this->postbox_col('bottom');

	$html .= $this->postbox_col('top',0,24);
	$html .= $this->admin_form_configphp_save_queries();
	$html .= $this->postbox_col('bottom');

	return $html;
}

public function options_user() {
	$html ='';
	if ($this->validate_form_nonce("admin_form_user_username")==true) {
		$html .= $this->do_form_admin_form_user_username();
	}

	$html .= $this->postbox_col('top',2,70);
	$html .= $this->admin_form_user_username();
	$html .= $this->postbox_col('bottom');

	return $html;
}

public function options_configphp() {
	$html ='';
	$html .= $this->postbox_col('top',2,70);
	$html .= $this->admin_form_configphp_keys();
	$html .= $this->admin_form_htaccess_secure();
	$html .= $this->postbox_col('bottom');

	$html .= $this->postbox_col('top',0,28);
	$html .= $this->admin_form_configphp_debug1();
	$html .= $this->admin_form_configphp_debug3();
	$html .= $this->admin_form_configphp_debug4();
	$html .= $this->admin_form_configphp_trash_exist();
	$html .= $this->postbox_col('bottom');


	return $html;
}

public function scan_malware() {

    $domain				=	trim(_POST("url", "", true));

if($_POST){
	
	
if(substr($domain, 0, 7) == "http://")	$domain	=	substr_replace($domain, "", 0, 7);
			$url	=	"http://" . $domain;
	
			$domain	=	get_domain_name($url);
			$domain = str_replace("www.","",$domain);
			$url	=	"http://" . $domain;
			$en_url = urlencode($url);	
			

		$content = get_web_page($url);
		
		
		$malware_check = get_web_page_s("https://sb-ssl.google.com/safebrowsing/api/lookup?client=api&apikey=ABQIAAAA-TupMdiEURbDKQxShJgRTBSr3g6UydUrHrn7ItxvjeiZIKCU2A&appver=1.0&pver=3.0&url=$en_url");		
		
		if($malware_check == "malware"){
			
			$malware_status = "<b><i>Yes</i><b>";
             $malware_class = "backgroundcolor-red";
			$malware_img = "error";
			
		}else{
			
			$malware_status = "No";
             $malware_class = "backgroundcolor-green";
			$malware_img = "check";
		}
		
		
		if($malware_check == "malware"){
			
			$google_class = "ui-state-error widg-error ui-corner-all";
			$google_span = "ui-icon ui-icon-alert";
			$google_html = '<p class="color-red"><i class="icon-thumbs-down-alt"></i>&nbsp;Domain blacklisted by Google Safe Browsing <a target="_blank" href="http://safebrowsing.clients.google.com/safebrowsing/diagnostic?site='.$domain.'"><i class="icon-external-link"></i>&nbsp;Reference</a></p>';
			
			
		}else{
			
			$google_class = "ui-state-highlight  widg-info ui-corner-all";
			$google_span = "ui-icon ui-icon-info";
			$google_html = '<p class="color-green"><i class="icon-thumbs-up-alt"></i>&nbsp;Domain clean by Google Safe Browsing <a target="_blank" href="http://safebrowsing.clients.google.com/safebrowsing/diagnostic?site='.$domain.'"><i class="icon-external-link"></i>&nbsp;Reference</a> <a href="http://twitter.com/share?text='.$domain.'%20Site%20%23Clean%20by%20%23Google%20%23SafeBrowsing%20Try%20yours!!&amp;url=https://securemoz.com&amp;via=securemoz&amp;count=none" target="_blank"><i class="icon-twitter"></i>&nbsp;</a>&nbsp;<a href="http://www.facebook.com/sharer/sharer.php?u=#'.$domain.'%20Site%20%23Clean%20by%20%23Google%20%23SafeBrowsing%20Try%20yours!!&amp;url=https://www.securemoz.com&amp;via%23securemoz&amp;count=none" target="_blank"><i class="icon-facebook"></i></a>&nbsp;<a href="https://plus.google.com/share?url='.$domain.'%20Site%20%23Clean%20by%20%23Google%20%23SafeBrowsing%20Try%20yours!!&amp;url=https://wpsecurity.securemoz.com&amp;via%23securemoz&amp;count=none" target="_blank"><i class="icon-google-plus"></i></a>&nbsp;<font color="#FF6347">Share it!</font></a></p>';
		}		

		
		$yandex_content = get_web_page_g("http://www.yandex.com/infected?url=$domain&l10n=en");

		preg_match('#Visiting this site may harm your computer#is',$yandex_content,$yandex_check);
		if ($yandex_check[0]) $yandex = "1";
		else $yandex = "0";
				
		if($yandex == "1"){
			
			$yandex_class = "ui-state-error widg-error ui-corner-all";
			$yandex_span = "ui-icon ui-icon-alert";
			$yandex_html = '<p class="color-red"><i class="icon-thumbs-down-alt"></i>&nbsp;Domain blacklisted by Yandex (via Sophos) <a target="_blank" href="http://www.yandex.com/infected?url='.$domain.'&amp;l10n=en"><i class="icon-external-link"></i>&nbsp;Reference</a></p>';
			
		}else{
			
			$yandex_class = "ui-state-highlight  widg-info ui-corner-all";
			$yandex_span = "ui-icon ui-icon-info";
			$yandex_html = '<p class="color-green"><i class="icon-thumbs-up-alt"></i>&nbsp;Domain clean by Yandex (via Sophos) <a target="_blank" href="http://www.yandex.com/infected?url='.$domain.'&amp;l10n=en"><i class="icon-external-link"></i>&nbsp;Reference</a> <a href="http://twitter.com/share?text='.$domain.'%20Site%20%23Clean%20by%20%23Yandex%20%23Sophos%20Try%20yours!!&amp;url=https://securemoz.com&amp;via=securemoz&amp;count=none" target="_blank"><i class="icon-twitter"></i></a>&nbsp;<a href="http://www.facebook.com/sharer/sharer.php?u=#'.$domain.'%20Site%20%23Clean%20by%20%23Yandex%20%23Sophos%20Try%20yours!!&amp;url=https://www.securemoz.com&amp;via%23securemoz&amp;count=none" target="_blank"><i class="icon-facebook"></i></a>&nbsp;<a href="https://plus.google.com/share?url='.$domain.'%20Site%20%23Clean%20by%20%23Yandex%20%23Sophos%20Try%20yours!!&amp;url=https://wpsecurity.securemoz.com&amp;via%23securemoz&amp;count=none" target="_blank"><i class="icon-google-plus"></i></a>&nbsp;<font color="#FF6347">Share it!</font></p>';
		}		
		
		
		
		$mcafee_content = get_web_page_g("http://www.siteadvisor.com/sites/$domain");

		preg_match('#siteRed#is',$mcafee_content,$mcafee_check);
		if ($mcafee_check[0]) $mcafee = "1";
		else $mcafee = "0";
				
		if($mcafee == "1"){
			
			$mcafee_class = "ui-state-error widg-error ui-corner-all";
			$mcafee_span = "ui-icon ui-icon-alert";
			$mcafee_html = '<p class="color-red"><i class="icon-thumbs-down-alt"></i>&nbsp;Domain blacklisted by SiteAdvisor (McAfee) <a target="_blank" href="http://www.siteadvisor.com/sites/'.$domain.'"><i class="icon-external-link"></i>&nbsp;Reference</a></p>';
			
		}else{
			
			$mcafee_class = "ui-state-highlight  widg-info ui-corner-all";
			$mcafee_span = "ui-icon ui-icon-info";
			$mcafee_html = '<p class="color-green"><i class="icon-thumbs-up-alt"></i>&nbsp;Domain clean by SiteAdvisor (McAfee) <a target="_blank" href="http://www.siteadvisor.com/sites/'.$domain.'"><i class="icon-external-link"></i>&nbsp;Reference</a> <a href="http://twitter.com/share?text='.$domain.'%20Site%20%23Clean%20by%20%23McAfee%20%23SiteAdvisor%20Try%20yours!!&amp;url=https://securemoz.com&amp;via=securemoz&amp;count=none" target="_blank"><i class="icon-twitter"></i></a>&nbsp;<a href="http://www.facebook.com/sharer/sharer.php?u=#'.$domain.'%20Site%20%23Clean%20by%20%23McAfee%20%23SiteAdvisor%20Try%20yours!!&amp;url=https://www.securemoz.com&amp;via%23securemoz&amp;count=none" target="_blank"><i class="icon-facebook"></i></a>&nbsp;<a href="https://plus.google.com/share?url='.$domain.'%20Site%20%23Clean%20by%20%23McAfee%20%23SiteAdvisor%20Try%20yours!!&amp;url=https://wpsecurity.securemoz.com&amp;via%23securemoz&amp;count=none" target="_blank"><i class="icon-google-plus"></i></a>&nbsp;<font color="#FF6347">Share it!</font></p>';
		}		
		


		$norton_content = get_web_page_g("http://safeweb.norton.com/report/show?url=$domain");
		
		
		preg_match('#Total threats found: <strong>(.*?)</strong>#is',$norton_content,$norton);
		$norton = $norton[1];		
		if($norton == "") $norton = "0";
		
		if($norton > "0"){
			
			$norton_class = "ui-state-error widg-error ui-corner-all";
			$norton_span = "ui-icon ui-icon-alert";
			$norton_html = '<p class="color-red"><i class="icon-thumbs-down-alt"></i>&nbsp;Domain blacklisted by Norton Safe Web <a target="_blank" href="http://safeweb.norton.com/report/show?url='.$domain.'"><i class="icon-external-link"></i>&nbsp;Reference</a></p>';
			
			
		}else{
			
			$norton_class = "ui-state-highlight  widg-info ui-corner-all";
			$norton_span = "ui-icon ui-icon-info";
			$norton_html = '<p class="color-green"><i class="icon-thumbs-up-alt"></i>&nbsp;Domain clean by Norton Safe Web <a target="_blank" href="http://safeweb.norton.com/report/show?url='.$domain.'"><i class="icon-external-link"></i>&nbsp;Reference</a> <a href="http://twitter.com/share?text='.$domain.'%20Site%20%23Clean%20by%20%23Norton%20%23SafeWeb%20Try%20yours!!&amp;url=https://securemoz.com&amp;via=securemoz&amp;count=none" target="_blank"><i class="icon-twitter"></i></a>&nbsp;<a href="http://www.facebook.com/sharer/sharer.php?u=#'.$domain.'%20Site%20%23Clean%20by%20%23Norton%20%23SafeWeb%20Try%20yours!!&amp;url=https://www.securemoz.com&amp;via%23securemoz&amp;count=none" target="_blank"><i class="icon-facebook"></i></a>&nbsp;<a href="https://plus.google.com/share?url='.$domain.'%20Site%20%23Clean%20by%20%23Norton%20%23SafeWeb%20Try%20yours!!&amp;url=https://wpsecurity.securemoz.com&amp;via%23securemoz&amp;count=none" target="_blank"><i class="icon-google-plus"></i></a>&nbsp;<font color="#FF6347">Share it!</font></p>';
		}		
		
		if($norton > "0" || $malware_check == "malware" || $mcafee == "1" || $yandex == "1"){
			
			$bl_status = "<b><i>Yes</i><b>";
            $bl_class = "backgroundcolor-red";
			$bl_img = "error";
			
			$report_img = "warn2";
			$report_status = "Site blacklisted";
			$report_warning = "Warnings found";
			$report_class = "color-red";
			
		}else{
			
			$bl_status = "No";
            $bl_class = "backgroundcolor-green";
			$bl_img = "check";
			
			
			$report_img = "green";
			$report_status = "Site clean";
			$report_warning = "No threats found";
			$report_class = "color-green";			
			
		}		

		
		preg_match('#Viruses</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$v);
		$v = $v[2];
		if($v == "") $v = "0";
		
		if($v > "0"){
			
			$v_status = "<b><i>Yes</i><b>";
            $v_class = "backgroundcolor-red";
			$v_img = "error";
			
		}else{
			
			$v_status = "No";
            $v_class = "backgroundcolor-green";
			$v_img = "check";
		}		
		
		
		
		
		
		preg_match('#Drive-By Downloads</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$dbd);
		$dbd = $dbd[2];
		if($dbd == "") $dbd = "0";
		
		if($dbd > "0"){
			
			$dbd_status = "<b><i>Yes</i><b>";
            $dbd_class = "backgroundcolor-red";
			$dbd_img = "error";
			
		}else{
			
			$dbd_status = "No";
            $dbd_class = "backgroundcolor-green";
			$dbd_img = "check";
		}	
		
		
		
		preg_match('#Malicious Downloads</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$md);
		$md = $md[2];
		if($md == "") $md = "0";
		
		if($md > "0"){
			
			$md_status = "<b><i>Yes</i><b>";
            $md_class = "backgroundcolor-red";
			$md_img = "error";
			
		}else{
			
			$md_status = "No";
            $md_class = "backgroundcolor-green";
			$md_img = "check";
		}

		
		
		preg_match('#Worms</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$w);
		$w = $w[2];
		if($w == "") $w = "0";
		
		if($w > "0"){
			
			$w_status = "<b><i>Yes</i><b>";
            $w_class = "backgroundcolor-red";
			$w_img = "error";
			
		}else{
			
			$w_status = "No";
            $w_class = "backgroundcolor-green";
			$w_img = "check";
		}

		
		preg_match('#Suspicious Applications</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$sa);
		$sa = $sa[2];
		if($sa == "") $sa = "0";
		
		if($sa > "0"){
			
			$sa_status = "<b><i>Yes</i><b>";
            $sa_class = "backgroundcolor-red";
			$sa_img = "error";
			
		}else{
			
			$sa_status = "No";
            $sa_class = "backgroundcolor-green";
			$sa_img = "check";
		}

		
		
		preg_match('#Suspicious Browser Changes</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$sbc);
		$sbc = $sbc[2];
		if($sbc == "") $sbc = "0";
		
		if($sbc > "0"){
			
			$sbc_status = "<b><i>Yes</i><b>";
             $sbc_class = "backgroundcolor-red";
			$sbc_img = "error";
			
		}else{
			
			$sbc_status = "No";
             $sbc_class = "backgroundcolor-green";
			$sbc_img = "check";
		}

		
		preg_match('#Security Risks</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$sr);
		$sr = $sr[2];
		if($sr == "") $sr = "0";
		
		if($sr > "0"){
			
			$sr_status = "<b><i>Yes</i><b>";
              $sr_class = "backgroundcolor-red";
			$sr_img = "error";
			
		}else{
			
			$sr_status = "No";
              $sr_class = "backgroundcolor-green";
			$sr_img = "check";
		}

		
		preg_match('#Heuristic Viruses</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$hv);
		$hv = $hv[2];
		if($hv == "") $hv = "0";
		
		if($hv > "0"){
			
			$hv_status = "<b><i>Yes</i><b>";
              $hv_class = "backgroundcolor-red";
			$hv_img = "error";
			
		}else{
			
			$hv_status = "No";
              $hv_class = "backgroundcolor-green";
			$hv_img = "check";
		}

		
		preg_match('#Adware</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$a);
		$a = $a[2];
		if($a == "") $a = "0";
		
		if($a > "0"){
			
			$a_status = "<b><i>Yes</i><b>";
            $a_class = "backgroundcolor-red";
			$a_img = "error";
			
		}else{
			
			$a_status = "No";
            $a_class = "backgroundcolor-green";
			$a_img = "check";
		}

		
		
		preg_match('#Trojans</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$t);
		$t = $t[2];
		if($t == "") $t = "0";
		
		if($t > "0"){
			
			$t_status = "<b><i>Yes</i><b>";
            $t_class = "backgroundcolor-red";
			$t_img = "error";
			
		}else{
			
			$t_status = "No";
            $t_class = "backgroundcolor-green";
			$t_img = "check";
		}

		
		preg_match('#Phishing Attacks</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$pa);
		$pa = $pa[2];
		if($pa == "") $pa = "0";
		
		if($pa > "0"){
			
			$pa_status = "<b><i>Yes</i><b>";
            $pa_class = "backgroundcolor-red";
			$pa_img = "error";
			
		}else{
			
			$pa_status = "No";
            $pa_class = "backgroundcolor-green";
			$pa_img = "check";
		}

		
		preg_match('#Spyware</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$s);
		$s = $s[2];
		if($s == "") $s = "0";
		
		if($s > "0"){
			
			$s_status = "<b><i>Yes</i><b>";
            $s_class = "backgroundcolor-red";
			$s_img = "error";
			
		}else{
			
			$s_status = "No";
            $s_class = "backgroundcolor-green";
			$s_img = "check";
		}

		
		preg_match('#Backdoors</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$b);
		$b = $b[2];
		if($b == "") $b = "0";
		
		if($b > "0"){
			
			$b_status = "<b><i>Yes</i><b>";
            $b_class = "backgroundcolor-red";
			$b_img = "error";
			
		}else{
			
			$b_status = "No";
            $b_class = "backgroundcolor-green";
			$b_img = "check";
		}

		
		preg_match('#Remote Access Software</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$ras);
		$ras = $ras[2];
		if($ras == "") $ras = "0";
		
		if($ras > "0"){
			
			$ras_status = "<b><i>Yes</i><b>";
            $ras_class = "backgroundcolor-red";
			$ras_img = "error";
			
		}else{
			
			$ras_status = "No";
            $ras_class = "backgroundcolor-green";
			$ras_img = "check";
		}

		
		preg_match('#Information Stealers</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$is);
		$is = $is[2];
		if($is == "") $is = "0";
		
		if($is > "0"){
			
			$is_status = "<b><i>Yes</i><b>";
            $is_class = "backgroundcolor-red";
			$is_img = "error";
			
		}else{
			
			$is_status = "No";
            $is_class = "backgroundcolor-green";
			$is_img = "check";
		}

		
		preg_match('#Dialers</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$d);
		$d = $d[2];
		if($d == "") $d = "0";
		
		if($d > "0"){
			
			$d_status = "<b><i>Yes</i><b>";
            $d_class = "backgroundcolor-red";
			$d_img = "error";
			
		}else{
			
			$d_status = "No";
            $d_class = "backgroundcolor-green";
			$d_img = "check";
		}

		
		preg_match('#Downloaders</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$dl);
		$dl = $dl[2];
		if($dl == "") $dl = "0";
		
		if($dl > "0"){
			
			$dl_status = "<b><i>Yes</i><b>";
            $dl_class = "backgroundcolor-red";
			$dl_img = "error";
			
		}else{
			
			$dl_status = "No";
            $dl_class = "backgroundcolor-green";
			$dl_img = "check";
		}		


		
		preg_match('#Embedded Link To Malicious Site</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$el);
		$el = $el[2];
		if($el == "") $el = "0";
		
		if($el > "0"){
			
			$el_status = "<b><i>Yes</i><b>";
            $el_class = "backgroundcolor-red";
			$el_img = "error";
			
		}else{
			
			$el_status = "No";
            $el_class = "backgroundcolor-green";
			$el_img = "check";
		}		
		
		

		
				
		$adsense = "";
		$analytics = "";

 		preg_match('/("|\')UA-([0-9]+)-([0-9]{1,3})("|\')/is',$content,$analytics);
		$analytics = str_replace(array('"',"'"),"",$analytics[0]);
		
 		preg_match('/pub-([0-9]+)/is',$content,$adsense);
		$adsense = $adsense[0];

		if($adsense) $adsense = '<div class="ui-widget"><div class="ui-state-highlight  widg-info ui-corner-all"><span class="ui-icon ui-icon-info"></span>Google Adsense installed:  '.$adsense.'<br /></div></div>';
		if($analytics) $analytics = '<div class="ui-widget"><div class="ui-state-highlight  widg-info ui-corner-all"><span class="ui-icon ui-icon-info"></span>Google Analytics installed:  '.$analytics.'<br /></div></div>';
		$ip = gethostbyname("www.$domain");
		
		$powered_by = phpversion();
		
		$runing_on = $_SERVER['SERVER_SOFTWARE'];
		

		
		preg_match_all('#<script(.*?)>#is',$content,$js_links);
		$js_count = 0;
		$js_html = '';

		foreach ($js_links[1] as $js_link){
			preg_match('#(\.js)#is',$js_link,$js_check);
			if($js_check[1]){
				preg_match('#src=("|\')(.*?)("|\')#is',$js_link,$js_file);			
				$js_file = $js_file[2];
				if(substr($js_file, 0, 4) != "http")	$js_file	=	$url.'/'.trim($js_file,"/");
				$js_html .= $js_file.'  <br />';
				$js_count++;
			}
		}

		
		
			$internal_links = 0;
			$internal_follow = 0;
			$internal_html = "";
			$external_links = 0;
			$external_follow = 0;
			$external_html = "";
			$link_text = "";
			$internal_pages = array();
			preg_match_all('/<a(.*?)<\/a>/is',$content,$page_links);
			foreach ($page_links[0] as $key => $link){
				
				preg_match('/<a(.*?)href="(.*?)"(.*?)>(.*?)<\/a>/is',$link,$anchor_link);
				preg_match('/<img/is',$link,$check_link_image);
				preg_match('/nofollow/is',$link,$check_nofollow);
				if($check_link_image[0]) $anchor_text = "[image]";
				else $anchor_text = strip_tags($anchor_link[4]);
				$link_text .= $anchor_link[4];
				if(trim(strip_tags($anchor_link[2])) != ""){
				if(substr($anchor_link[2], 0, 4) != "http"){
					$internal_pages[] = $url.$anchor_link[2];
					$internal_html .= $url.$anchor_link[2]."  <br />";
					if($check_nofollow[0] == "") $internal_follow++;
					$internal_links++;
					
				}else{
				preg_match("/$domain/is",$anchor_link[2],$check_link);
					
					if ($check_link[0]) {
						$internal_pages[] = $anchor_link[2];
						$internal_html .= $anchor_link[2]."  <br />";
						if($check_nofollow[0] == "") $internal_follow++;
						$internal_links++;
					}else{
						$external_html .= "<tr><td>".$anchor_text."</td><td>".$anchor_link[2]."</td></tr>";
						if($check_nofollow[0] == "") $external_follow++;						
						$external_links++;
						
					}

				
				}
				}

			}

			
		

$generated_content = <<<EOF


<div style="width:100%; margin-right:0%; float:left;"><div class="postbox"><h3><span>SecureMoz Waudit Security malware and virus audit</span></h3><div class="inside"><div class="columns3"><div class="col col1"><img alt="" src="$this->imgmalware$report_img.png" /></div><div class="col col2"><h2><strong><span class="$report_class">$report_status</span></strong></h2>
                   <p>
				   Scan for: <strong>$url</strong><br/>
				   Hostname: <strong>$domain</strong><br/>
				   IP address: <strong>$ip</strong><br/>
					</p>
					</div>
					<div class="col col3">$google_html $norton_html $mcafee_html $yandex_html</div></div><div class="clear"></div></div></div></div>



<div style="width:100%; margin-right:0%; float:left;"><div class="postbox"><h3><span>Detailed results from SecureMoz Waudit malware and virus audit ($report_warning)</span> <p class="color-green"><font color="#FF6347">Share it!&nbsp;</font><a href="http://twitter.com/share?text='.$domain.'%20Site%20%23Clean%20by%20%23SecureMoz%20%23Plugin%20Try%20yours!!&amp;url=https://securemoz.com&amp;via=securemoz&amp;count=none" target="_blank"><i class="icon-twitter"></i></a>&nbsp;&nbsp;<a href="http://www.facebook.com/sharer/sharer.php?u=#'.$domain.'%20Site%20%23Clean%20by%20%23SecureMoz%20%23Plugin%20Try%20yours!!&amp;url=https://www.securemoz.com&amp;via%23securemoz&amp;count=none" target="_blank"><i class="icon-facebook"></i></a>&nbsp;&nbsp;<a href="https://plus.google.com/share?url='.$domain.'%20Site%20%23Clean%20by%20%23SecureMoz%20%23Plugin%20Try%20yours!!&amp;url=https://wpsecurity.securemoz.com&amp;via%23securemoz&amp;count=none" target="_blank"><i class="icon-google-plus"></i></a>&nbsp;</p></h3><div class="inside">
<table class="nsawide"><thead>
       <th></th><th>What</th><th>Says</th></thead><tbody>

<tr class="$bl_class"><td><img style="position:relative;top:5px;"  alt="$bl_img" src="$this->imgmalware$bl_img.png" /></td><td><b>Blacklisted</b></td><td>$bl_status</td></tr>		    	
					
				<tr class="$malware_class"><td><img style="position:relative;top:5px;"  alt="$malware_img" src="$this->imgmalware$malware_img.png" /></td><td><b>Malware</b></td><td>$malware_status</td></tr>

				<tr class="$v_class"><td><img style="position:relative;top:5px;"  alt="$v_img" src="$this->imgmalware$v_img.png" /></td><td><b>Viruses</b></td><td>$v_status</td></tr>
				
				<tr class="$dbd_class"><td><img style="position:relative;top:5px;"  alt="$dbd_img" src="$this->imgmalware$dbd_img.png" /></td><td><b>Drive-By Downloads</b></td><td>$dbd_status</td></tr>
				
				<tr class="$md_class"><td><img style="position:relative;top:5px;"  alt="$md_img" src="$this->imgmalware$md_img.png" /></td><td><b>Malicious Downloads</b></td><td>$md_status</td></tr>
				
				<tr class="$w_class"><td><img style="position:relative;top:5px;"  alt="$w_img" src="$this->imgmalware$w_img.png" /></td><td><b>Worms</b></td><td>$w_status</td></tr>
				
				<tr class="$sa_class"><td><img style="position:relative;top:5px;"  alt="$sa_img" src="$this->imgmalware$sa_img.png" /></td><td><b>Suspicious Applications</b></td><td>$sa_status</td></tr>
				
				<tr class="$sbc_class"><td><img style="position:relative;top:5px;"  alt="$sbc_img" src="$this->imgmalware$sbc_img.png" /></td><td><b>Suspicious Browser Changes</b></td><td>$sbc_status</td></tr>
				
				<tr class="$sr_class"><td><img style="position:relative;top:5px;"  alt="$sr_img" src="$this->imgmalware$sr_img.png" /></td><td><b>Security Risks</b></td><td>$sr_status</td></tr>
				
				<tr class="$hv_class"><td><img style="position:relative;top:5px;"  alt="$hv_img" src="$this->imgmalware$hv_img.png" /></td><td><b>Heuristic Viruses</b></td><td>$hv_status</td></tr>
				
				<tr class="$a_class"><td><img style="position:relative;top:5px;"  alt="$a_img" src="$this->imgmalware$a_img.png" /></td><td><b>Adware</b></td><td>$a_status</td></tr>
				
				<tr class="$t_class"><td><img style="position:relative;top:5px;"  alt="$t_img" src="$this->imgmalware$t_img.png" /></td><td><b>Trojans</b></td><td>$t_status</td></tr>
				
				<tr class="$pa_class"><td><img style="position:relative;top:5px;"  alt="$pa_img" src="$this->imgmalware$pa_img.png" /></td><td><b>Phishing Attacks</b></td><td>$pa_status</td></tr>
				
				<tr class="$s_class"><td><img style="position:relative;top:5px;"  alt="$s_img" src="$this->imgmalware$s_img.png" /></td><td><b>Spyware</b></td><td>$s_status</td></tr>
				
				<tr class="$b_class"><td><img style="position:relative;top:5px;"  alt="$b_img" src="$this->imgmalware$b_img.png" /></td><td><b>Backdoors</b></td><td>$b_status</td></tr>
				
				<tr class="$ras_class"><td><img style="position:relative;top:5px;"  alt="$ras_img" src="$this->imgmalware$ras_img.png" /></td><td><b>Remote Access Software</b></td><td>$ras_status</td></tr>
				
				<tr class="$is_class"><td><img style="position:relative;top:5px;" alt="$is_img" src="$this->imgmalware$is_img.png" /></td><td><b>Information Stealers</b></td><td>$is_status</td></tr>
				
				<tr class="$d_class"><td><img style="position:relative;top:5px;"  alt="$d_img" src="$this->imgmalware$d_img.png" /></td><td><b>Dialers</b></td><td>$d_status</td></tr>
				
				<tr class="$dl_class"><td><img style="position:relative;top:5px;"  alt="$dl_img" src="$this->imgmalware$dl_img.png" /></td><td><b>Downloaders</b></td><td>$dl_status</td></tr>
				
				<tr class="$el_class"><td><img style="position:relative;top:5px;" alt="$el_img" src="$this->imgmalware$el_img.png" /></td><td><b>Embedded Link To Malicious Site</b></td><td>$el_status</td></tr>



</tbody></table></div></div></div>


EOF;

echo $generated_content;
}
	if(isset($_POST['url'])) {
          $this->malware_verifire("&f=dgdgd");    
        $api = new VirusTotalAPIV2('4bfe45b88d78da0c6e1a0d2483aaa07e03ead242e451173a2173e178e998184d');
        $result = $api->scanURL($_POST['url']);
        $report = $api->getURLReport($_POST['url']);
        //var_dump($report);
       
       
       print('
       <div style="width:100%; margin-right:0%; float:left;"><div class="postbox"><h3><span>VirusTotal Security malware and virus audit</span></h3><div class="inside"><div class="columns3"><div class="col col1"><input class="knob" data-thickness=".4" data-readOnly=true value="100" data-width="120" data-bgColor="#A4C639" data-fgColor="#A4C639"></div><div class="col col2"><p><strong>VirusTotal Report </strong><br/><br/><br/>
					
					<a class="button-primary" href="' . $api->getReportPermalink($report, FALSE) . '" target="_blank">See your report on Virustotal</a>
					</p>
					</div>
					<div class="col col3"><h2>We executed <strong>' . $api->getTotalNumberOfChecks($report) . '/53</strong> checks.</h2><p>If you found some issues, please, visit the official VirusTotal Analysis Report or try to search on Google how you can resolve the problem.</p></div></div><div class="clear"></div></div></div></div>
       
       
       
       ');
        
       print('<div style="width:100%; margin-right:0%; float:left;"><div class="postbox"><h3><span>Detailed results from VirusTotal malware and virus audit</span></h3><div class="inside"><table class="nsawide"><thead>
       <th>Who</th><th>Says</th></thead><tbody>');
       foreach($report->scans as $key => $array) {
    $data = $array->detected === true ? '<span style="color:#ff6347;">Detected</span>' : '<span style="color: #A4C639;">Undetected</span>';
    $color = $array->detected === true ? 'backgroundcolor-red' : 'backgroundcolor-green';
    echo '<tr class="' . $color . '"><td><strong>' . $key .'</strong></td><td> ' . $data . '</td></tr>';
       };
        print("</tbody></table></div></div></div>");
        
   
   
} else {
       
       
        echo('
        <div id="loading" class="loading-invisible">Scanning...</div>
        <div style="width:100%; margin-right:0%; float:left;"><div class="postbox"><h3><span>Malware & Viruses Scanner</span></h3><div class="inside">
<table class="form-table">        
<tbody>
<form id="analyze" action="" method="POST">
				<tr>
					<th scope="row">VirusTotal API*</th>
					<td>
					<label for="VIRUS_API"></label><input id="VIRUS_API" type="text" value="" name="VIRUS_API" class="standardinputtext">
                    </td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>
					<label for="url"></label><input type="hidden" value="' . $this->root_url .'" name="url" id="url" class="standardinputtext">
                    <input type="hidden" value="1" id="id" name="id" />
					</td>
                </tr>
                <tr>
                <th scope="row" height="5"></th>
                <td> ');
                if($this->malware_verifire("&f=dlimit") == 0)  
                  {
                    echo '<button type="submit" name="submit" class="button-primary" value="submit">Run scan</button>';
                  }else{
                    echo '<div align="left"><font color="#FF6347"><b>Monthly Limit Reached</b></font><br>Please Consider<br/><a href="http://wpsecurity.securemoz.com/" title="SecureMoz PRO Version" target="_blank"><img src="'. $this->imgdir.'upgrade_now.png" /></a></div>';
                  }
               echo('   </td>
                </tr>
                </form>
				</tbody>
			</table>
            
            <p><small>* We provide as default our public Api Key, so if you attempt to use it and you get limitations we recommend you to <a href="https://www.virustotal.com/es/documentation/public-api/" target="_blank">get your own VirusTotal API key here</a>.</small></p>
            <p><small>We use our custom Securemoz Waudit API to connect with some public and private resources and other APIs.</small></p>
            <p><small><strong>Note:</strong> this site just checks for common problems, a pass from here doesn\'t guarantee your site isn\'t hacked. We check for Viruses, Malware, Blacklist, Drive-By Downloads, Malicious Downloads, Worms, Suspicious Applications, Suspicious Browser Changes, Security Risks, Heuristic Viruses, Adware, Trojans, Phishing Attacks, Spyware, Backdoors, Remote Access Software, Information Stealers, Dialers, Downloaders and Embedded Link To Malicious Site, among many others, in more than 53 differents sites.<br/><br/>

Google works to provide the most accurate and up-to-date phishing and malware information. However, it cannot guarantee that its information is comprehensive and error-free: some risky sites may not be identified, and some safe sites may be identified in error.</small></p>
<p><small><a href="https://securemoz.com" target="_blank">Visit our site to scan remote urls</a>.</small></p>
            </div></div></div>
            ');
       
        	
    };
    
}

    
public function options_server() {
	$html ='';

	$html .= $this->postbox_col('top',0,100);
	$html .= $this->admin_form_chmod();
	$html .= $this->postbox_col('bottom');

	$html .= $this->postbox_col('top',2,49);
	$html .= $this->admin_form_server_php1();
	$html .= $this->admin_form_server_php4();
	$html .= $this->admin_form_server_php5();
	$html .= $this->postbox_col('bottom');

	$html .= $this->postbox_col('top',0,49);
	$html .= $this->admin_form_server_php2();
	$html .= $this->admin_form_server_php3();
	$html .= $this->admin_form_server_php6();
	$html .= $this->postbox_col('bottom');


	return $html;
}


/*
*	Tab: Backups
*/

/*public function admin_form_database_backup() {
	$html = '';
	$html .=  $this->postboxer('top',"Backup database");

	$t = "admin_form_database_backup";
	$html .= '<p>Please backup your database before making any changes!</p>';
	$html .= '<p><strong>Database last backed up: </strong>';
	$html .= ($this->waudit_get_option('database_backed_up')) ? $this->waudit_get_option('database_backed_up') : 'never' ;
	$html .= '</p>';
	$html .= '<p><strong>Database backup size: </strong>';
	$html .= ($this->waudit_get_option('database_backed_up_size')) ? $this->waudit_get_option('database_backed_up_size') : 'not available' ;
	$html .= '</p>';
	$html .= '<p><strong>Total backups: </strong>';
	$html .= $this->count_files($this->backupdir.$this->plugin_database_dir,'count');
	$html .= '</p>';
	$html .= '<p><strong>Total backups size: </strong>';
	$html .= $this->H->human_filesize($this->count_files($this->backupdir.$this->plugin_database_dir,'size'), $decimals=2);
	$html .= '</p>';
	$d = $this->backupdir.$this->plugin_database_dir;
	if (is_dir($d) && is_writable($d)) {

		$html .= '<p class="color-green">Database backup directory is writable.</p>';
		$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_backups').'">
				<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
				'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}").'
				<input type="submit" class="button-primary" value="Create database backup" />
				</p>
				</form>
				';
	} else {
		$html .= '<p class="color-red">Database backup directory is not writable.<p></p> Make directory <code>'.$d.'</code> writable in order to backup database.</p>';
	}

	$html .=  $this->postboxer('bottom');
	return $html;
}*/

public function gtml_form_field($pre, $slug, $options, $classes="") {

	$category = str_replace('setting', '', str_replace('_', '', $pre));

	$element = $this->get_settings_field($category, $slug, 'element');
	#$element = $this->get_option_element($options);
	$checked = ($this->waudit_get_option($pre.$slug)) ? 'checked' : "";

	switch ($element) {

		case 'select':

			$current_selected_option = $this->waudit_get_option($pre.$slug);

			if (isset($current_selected_option) && !empty($current_selected_option)) {
				$selected = $current_selected_option;
			} else {
				$selected = $options['d'];
			}

			$sub_options = '';			

			foreach ($options['values'] as $opt_slug => $opt_title) {
				$select = ($opt_slug==$selected) ? ' selected ' : null;
				$sub_options .= "<option value=\"$opt_slug\" $select>$opt_title</option>";
			}

			return "<select id=\"{$pre}.{$slug}\" name=\"{$pre}{$slug}\">$sub_options</select>";
			break;

		case 'input':

			switch ($options['type']) {
				
				case 'text':
					$current_option_value = $this->waudit_get_option($pre.$slug);
					$value = (isset($current_option_value) && !empty($current_option_value)) ? $current_option_value : $options['d'];
					$title = (isset($options['title']) && !empty($options['title'])) ? $options['title'] : '';
					
					return '<label for="'.$pre.$slug.'">'.$title.'</label><input id="'.$pre.$slug.'" type="'.$options['type'].'" value="'.$value.'" name="'.$pre.$slug.'" class="'.$classes.'">';

					break;
				

				case 'checkbox':

					return '<label for="'.$pre.$slug.'"> </label>
					<input id="'.$pre.$slug.'" type="hidden" name="'.$pre.$slug.'" value="0">
					<input id="'.$pre.$slug.'" type="checkbox"  name="'.$pre.$slug.'" value="1" '.$checked.'>
					'.$options['title'].'
					</label>';
					break;

				case 'radio':

					return '<label for="'.$pre.$slug.'">
					<input id="'.$pre.$slug.'" type="hidden" name="'.$pre.$slug.'" value="0">
					<input id="'.$pre.$slug.'" type="radio" name="'.$pre.$slug.'" value="1" '.$checked.'>
					'.$options['title'].'
					</label>';
					break;

				default:
					return false;
					break;
			}


		// jQuery UI slider
		case 'slider':


						$html = '
						
					    <div style="width:100%; height:40px; ">

							
							<div style="float:left; width: 15%; line-height: 36px;">
								<span class="sliderresultb" id="amount_'.$slug.'" name="setting_serverphp_'.$slug.'"></span>
							</div>

							<div style="float:right; width: 84%;">
								<span class="sliderresulta" for="amount_'.$slug.'">'. $options['title'].':</span>


								<section class="sliding">   
								<div id="slider_'.$slug.'"></div>  
								<span class="volume volume_'.$slug.'"></span> 
								</section> 
							</div>

						</div> 


						<input id="'.$pre.$slug.'" type="hidden" name="'.$pre.$slug.'" value="">
						';

			return $html;
			break;
				

		default:
			return false;
			break;
	}

}


/*
*	Tab: Backup
*/

public function admin_form_backup() {


// Print html for each setting
/*		

		$element =  $this->get_option_element($setting);

		$html .= $this->gtml_form_field($element, $pre, $setting_slug, $setting).'<br/>';
*/

	// Prepare all the options, default values for this form
	$setting_cat = 'backup';

	$backup_settings = $this->get_settings($setting_cat);

	$pre = 'setting_backup_';
	$t = "admin_form_backup";
	#$html = '<p>You can make manual backups or schedule automated backups here! Note that Waudit will make additional backups before running certain tests to protect your system.</p>';
	

$html 	=  $this->postboxer('top',"Backup");

	






	$html .= '<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_backup').'">';
	$html .= '<input type="hidden" name="waudit_submitted_form" value="'.$t.'" />';
	$html .= '<input type="hidden" name="do_not_rerun_all_tests" value="please" />
				'.wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}");
	#$html .= '<h3 class="title normaltitle">Backup</h3>';
	$html .= '<table class="form-table">
				<tbody>
				<tr>
					<th scope="row">Objects</th>
					<td>
					<fieldset>

					'.$this->gtml_form_field($pre, 'object-database',  			$backup_settings['object-database']).'<br/>
					'.$this->gtml_form_field($pre, 'object-files',  			$backup_settings['object-files']).'

					</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row">Files backup file name</th>
					<td>
					'.$this->gtml_form_field($pre, 'filename-files', 			$backup_settings['filename-files'],'standardinputtext').'
					'.$this->gtml_form_field($pre, 'filename-files-format', 	$backup_settings['filename-files-format']).'
					</td>
				</tr>
				<tr>
					<th scope="row">Database backup file name</th>
					<td>
					'.$this->gtml_form_field($pre, 'filename-database', 		$backup_settings['filename-database'],'standardinputtext').'
					'.$this->gtml_form_field($pre, 'filename-database-format', 	$backup_settings['filename-database-format']).'
					</td>
				</tr>

				<tr>
					<th scope="row">Backup to</th>
					<td>
					<fieldset>
					'.$this->gtml_form_field($pre, 'to-directory', 				$backup_settings['to-directory']).'
					</fieldset>
					</td>
				</tr>
				
				</tbody>
			</table>';


/*	add_thickbox();

	$html .= '<div id="display-date-format-help" style="display:none;">
     <p>
          This is my hidden content! It will appear in ThickBox when the link is clicked.
     </p>
</div>

<a href="#TB_inline?width=200&height=300&inlineId=display-date-format-help" class="thickbox">View my inline content!</a>';
	*/


	$html .= '<h3 class="title normaltitle">E-mail</h3> (<a href="http://wpsecurity.securemoz.com/" title="PRO Version" target="_blank">Upgrade To SecureMoz PRO</a>)';

	$html .= '<p>';
	$html .= $this->gtml_form_field($pre, 'email_enabled', $backup_settings['email_enabled']);
	$html .= 'Send e-mail with ';
	$html .= $this->gtml_form_field($pre, 'email_objects', $backup_settings['email_objects']);
	$html .= ' backups to ';
	$html .= $this->gtml_form_field($pre, 'email_to', $backup_settings['email_to']);
	$html .= ' with subject ';
	$html .= $this->gtml_form_field($pre, 'email_subject', $backup_settings['email_subject']);
	$html .= '</p>';



	$html .= '<h3 class="title normaltitle">Schedule</h3> (<a href="http://wpsecurity.securemoz.com/" title="PRO Version" target="_blank">Upgrade To SecureMoz PRO</a>)';

	$html .= '<p>';
	$html .= $this->gtml_form_field($pre, 'schedule_enabled', $backup_settings['schedule_enabled']);
	$html .= 'Schedule ';
	$html .= $this->gtml_form_field($pre, 'schedule_objects', $backup_settings['schedule_objects']);
	$html .= ' backups to occur every ';
	$html .= $this->gtml_form_field($pre, 'schedule_frequency', $backup_settings['schedule_frequency']);
	$html .= '</p>';



	$html .= '<table class="form-table">
				<tbody>
				<tr>
					<th scope="row"></th>
					<td>
					<button type="submit" name="submit" class="button-secondary" value="save-settings">Save Settings</button>
					<button type="submit" name="submit" class="button-primary" value="backup-now">Backup Now</button>
					<button type="submit" name="submit" class="button-secondary" value="delete-all-backups">Delete all backups</button>
					</td>
				</tr>
				</tbody>
			</table>';

	$html .= '</form>';
$html .=  $this->postboxer('bottom');

	return $html;
}

public function do_form_admin_form_backup($doing_cron=false) {

	if (array_key_exists('submit', $_POST)
	&& isset($_POST['submit']) 
	&& !is_null($_POST['submit'])) {
		

		// Save settings and inform user about result
		if ($_POST['submit']==="save-settings" ) { 

			if ($this->update_settings_from_post()) 
				$this->message('Settings saved.','updated');
			else 
				$this->message('Settings not saved. It is possible that none of the settings were changed and therefore did not require an update.','error');

		}

		if ($_POST['submit']==="backup-now" || $doing_cron!==false) { 

			// Save settings but do not inform user about result
			$this->update_settings_from_post();

			// 1. Is database backup needed?
			if ($this->waudit_get_option('setting_backup_object-database') ==="1") {
				
				// Do not run if this is schedule and this method is not selected to back up
				if ($doing_cron!==false && strpos($this->waudit_get_option('setting_backup_schedule_objects'), 'database') )
					break;
				

				// Make backup
				$filename = $this->string_date_symbol_parser($this->waudit_get_option('setting_backup_filename-database'));
				#$filename = $this->string_date_symbol_parser($_POST['setting_backup_filename-database']);
				$format = $this->waudit_get_option('setting_backup_filename-database-format');
				#$format = $_POST['setting_backup_filename-database-format'];
				$this->make_database_backup($filename,$format);

				// Keeping track
				$this->waudit_update_option('last_backup_database_time', date('d.m.Y G:i'));
				$this->waudit_update_option('last_backup_database_filename', $filename.$format);

			}

			// 2. Is files backup needed?
			if ($this->waudit_get_option('setting_backup_object-files') ==="1") {
				
				// Do not run if this is schedule and this method is not selected to back up
				if ($doing_cron!==false && strpos($this->waudit_get_option('setting_backup_schedule_objects'), 'files') )
					break;

				// Make backup
				$filename = $this->string_date_symbol_parser($this->waudit_get_option('setting_backup_filename-files'));
				#$filename = $this->string_date_symbol_parser($_POST['setting_backup_filename-files']);
				$format = $this->waudit_get_option('setting_backup_filename-files-format');
				$format = $this->waudit_get_option('setting_backup_filename-files-format');
				#$format = $_POST['setting_backup_filename-files-format'];
				$this->make_files_backup($filename,$format);

				// Keeping track
				$this->waudit_update_option('last_backup_files_time', date('d.m.Y G:i'));
				$this->waudit_update_option('last_backup_files_filename', $filename.$format);

			}

			// 3. Is email sender enabled?
			if ($this->waudit_get_option('setting_backup_email_enabled')) {


				// First determine what needs to backed up
				$objects = $this->waudit_get_option('setting_backup_email_objects');
				$types = (strpos($objects, '+')===false) ? array(0=>$objects) : explode('+', $objects);
				$failed_to_add = array();

				foreach ($types as $type) {
					$filename = $this->waudit_get_option('last_backup_'.$type.'_filename'); // good thing track was kept
					$file_path = $this->backupdir.$type.'/'.$filename; // assuming that each type of backup has its own directory with equal name under /plugin/backups/
					$added = $this->M->add_attachment($file_path);
					if ($added===false)
						array_push($failed_to_add, $type);
				}
				
				// Were all attachments added?
				if (!empty($failed_to_add)) {
					$this->message('Warning: Following backup types will not be attached to email: '.implode(',', $failed_to_add) ,'error');
				}

				// add to
				if ($this->M->add_to(trim($this->waudit_get_option('setting_backup_email_to')))===false) {
					$this->message('Error: Bad email address' ,'error');
				}
				
				// add subject
				if ($this->M->add_subject(trim($this->waudit_get_option('setting_backup_email_subject')))===false) {
					$this->message('Error: Bad email subject' ,'error');
				}		

				// add headers
					/*$domain=str_replace('www.', '', strtolower($_SERVER['HTTP_HOST'])); 
				if ($this->M->add_headers( "From: Waudit backup <waudit@$domain> \r\n" ) ===false) {
					$this->message('Error: Bad email headers' ,'error');
				}*/
				// add message
				if ($this->M->add_message('Waudit backup for '.$this->root_url.' generated '.date('r'))===false) {
					$this->message('Error: Bad email message' ,'error');
				}	
				

				// SEND EMAIL
				if ($this->M->send()) 
					$this->message('Email sent.','updated');
				else 
					$this->message('Email failed to send.','error');
					
			}

		}

		// @todo, determine whether or not this should be changeable via cron===true
		//			as in "should cron be able to schedule/unschedule itself ?"
		if ($_POST['submit']==="save-settings" || $_POST['submit']==="backup-now" ) { 


			// 4. Should this event be scheduled?
			// This is done on both "Save settings" and 
			if ($this->waudit_get_option('setting_backup_schedule_enabled')) {

				$frequency 	= $this->waudit_get_option('setting_backup_schedule_frequency');
				$obj = $this->waudit_get_option('setting_backup_schedule_objects');
				$args = array( 'objects' => $obj );
				$this->S->add_cron(false, current_time('timestamp'), 'add_action_do_backup',$args , $frequency );
	
			} else {

				// Settings were saved, but schedule unchecked
				// Delete any existing schedules then
				$this->S->unset_cron('add_action_do_backup');

			}
		}

		// 5. Delete all backups
		if ($_POST['submit']==="delete-all-backups") { 

			chmod($this->backupdir, 0777);

			if (is_dir($this->backupdir) && is_writable($this->backupdir)) {
				$this->H->delete_files($this->backupdir);
				if (!is_dir($this->backupdir)) {
					$this->message('All backups successfully deleted!','updated');
				}
			} else {
				$this->message('Deleting backups failed!','error');				
			}

			// Some default directories might have been lost, recreating
			$this->create_default_directory_paths();

		}



	}

}

public function make_database_backup($filename,$format) {


	if ($this->plugin_get_version() !== $this->waudit_get_option("plugin_version_last_run_all_tests") && !$this->is_setting_on('system','ignore_plugin_version')) {
		$this->message('Waudit will attempt to create initial database and file backups and perform first security audit. If it fails, please, '.$this->admin_form_skipinitbackup(),'error');
	}
	if (!isset($this->Y) || empty($this->Y)) {
		$this->Y = new waudit_Compress;
	}

	ini_set('memory_limit', '-1');

	global $wpdb;
	$original_deleted = true; // overridden by each compression function seperately

	$con=mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
	// Check connection
	if (mysqli_connect_errno()) {
		// Could not connect
		return "Error: Could not connect to database";
	}

	$tables = '*';
	$return = '';

	//get all of the tables
	if($tables == '*') {
		$tables = array();
		$result = mysqli_query($con,'SHOW TABLES');
		while($row = mysqli_fetch_row($result)) {
			$tables[] = $row[0];
		}
	} else {
		$tables = is_array($tables) ? $tables : explode(',',$tables);
	}

	//cycle through
	foreach($tables as $table) {
		$result = mysqli_query($con,'SELECT * FROM '.$table);
		$num_fields = mysqli_num_fields($result);

		$row2 = mysqli_fetch_row(mysqli_query($con,'SHOW CREATE TABLE '.$table));
		$return .= "\n\n".$row2[1].";\n\n";

		for ($i = 0; $i < $num_fields; $i++) {
			while($row = mysqli_fetch_row($result)) {
				$return.= 'INSERT INTO '.$table.' VALUES(';
				for($j=0; $j<$num_fields; $j++) {
					$row[$j] = addslashes($row[$j]);
					$row[$j] = str_replace("\n", "\\n", $row[$j]);
					if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
					if ($j<($num_fields-1)) { $return.= ','; }
				}
				$return.= ");\n";
			}
		}
		$return.="\n\n\n";
	}
	mysqli_close($con);

	// save .sql file
	$path_sql 		= $this->backupdir.$this->plugin_database_dir.'/'.$filename.'.sql';

	// formated as in has a format attached not formated as wiped out
	$path_formated 	= $this->backupdir.$this->plugin_database_dir.'/'.$filename.$format; 

	// needed in gz to remove original
	$path_sql_tar 	= $this->backupdir.$this->plugin_database_dir.'/'.$filename.'.tar'; 

	$path_nosql 	= str_replace(".sql.", '.', $path_formated); 
	$handle 		= fopen($path_sql,'w+');
	fwrite($handle,$return);
	fclose($handle);


	$texte = "";

	// if the format is .sql.zip, zip the file and delete original
	if ($format===".zip") {

		// Zip it up
		$result = $this->Y->zip($path_sql,$path_formated);
																							  //
	} elseif($format==='.tar' || $format==='.tar.gz' || $format==='.tar.bz2' ) { 			 // both formats land here
			   																			    //
		try {  																			   //
   																						  //
			$this->Y->phar_load($path_sql_tar,$filename.'.tar');				 		 // Create .tar archive
			$this->Y->phar_add_file($path_sql,$filename.'.sql');	     		    	// Add files to it
																					   //

		    // Compress if .tar.gz or tar.bz2 format selected
		    if ($format==='.tar.gz') {

			   $this->Y->phar_compress(Phar::GZ);

		    } elseif ($format==='.tar.bz2') {

				$this->Y->phar_compress(Phar::BZ2);

		    }

		    // Delete original .tar
			if ($format!=='.tar') {

		    	// Delete original .tar, leave just the compressed one
			    $orig_del = unlink($path_sql_tar);

		    }

		} catch (Exception $e) {
		    $texte .= "<br/> Error exception 1:  $e";
		}



	}	


	// Delete the original .sql file
	if ($format!=='.sql') {
		$original_deleted = unlink($path_sql);
	} else {
		// No need to check if orig deleted if no compression is used
		$original_deleted = true;
	}

	// protect the backup file
	$current_permissions = $this->waudit_get_option('setting_general_bckp_file_permission');
	$permissions = (isset($current_permissions) && !empty($current_permissions)) ? $current_permissions : $this->get_settings_field('general', 'bckp_file_permission', 'd');
	chmod($path_formated, intval($permissions,8));


	if (file_exists($path_formated) && $original_deleted) {
		$this->message('Database backed up successfully!','updated');
		return true;
	} else { 
		
		
		$this->message('Database backup failed!'.$texte ,'error');
		return false;
	}
	
}

/*public function admin_form_configphp_backup() {
	$html = '';
	$html .=  $this->postboxer('top',"Backup wp-config.php");

	$t = "admin_form_configphp_backup";
	$html .= '<p>This is where you can backup you wp-config.php file before making any changes.</p>';
	$html .= ($this->waudit_get_option('wpconfigphp_backed_up')) ? '<p><strong>File wp-config.php last backed up: </strong>'.$this->waudit_get_option('wpconfigphp_backed_up').'</p>' : false ;
	$html .= '</p>';
	$html .= '<p><strong>Backup size: </strong>';
	$html .= ($this->waudit_get_option('wpconfigphp_backed_up_size')) ? $this->waudit_get_option('wpconfigphp_backed_up_size') : 'not available' ;
	$html .= '</p>';
	$html .= '<p><strong>Total backups: </strong>';
	$html .= $this->count_files($this->backupdir.$this->plugin_wpconfig_dir,'count');
	$html .= '</p>';
	$html .= '<p><strong>Total backups size: </strong>';
	$html .= $this->H->human_filesize($this->count_files($this->backupdir.$this->plugin_wpconfig_dir,'size'), $decimals=2);
	$html .= '</p>';

	$d = $this->backupdir.$this->plugin_database_dir;
	if (is_dir($d) && is_writable($d)) {

		$html .= '<p class="color-green">File backup directory is writable.</p>';
		$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_backups').'">
				<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
				'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}").'
				<input type="submit" class="button-primary" value="Create backup" />
				</p>
				</form>
				';
	} else {
		$html .= '<p class="color-red">Database backup directory is not writable.<p></p> Make directory <code>'.$d.'</code> writable in order to backup database.</p>';
	}

	$html .=  $this->postboxer('bottom');
	return $html;
}
public function do_form_admin_form_configphp_backup() {
	$html='';
	$newfile = '';
	$time = time();
	$d = $this->backupdir.$this->plugin_wpconfig_dir;
	$file = $this->root_path.'wp-config.php';
	$file_backup = $d.'/wp-config-'.$time.'.php';
	// File1
	$handle = @fopen($file_backup, "w+");
	// File2
	$file_contents = file_get_contents($file);
	if ($handle && $file_contents) {
		if (is_dir($d) && is_writable($d)) {
			fwrite($handle, $file_contents);
		    fclose($handle);    
		    $this->waudit_update_option('wpconfigphp_backed_up', date('d.m.Y G:i'));
			$size = filesize($file_backup);	
			$this->waudit_update_option('wpconfigphp_backed_up_size', $this->H->human_filesize($size, $decimals=2));
			chmod($file_backup, $this->backup_file_permission);

		    $html .= $this->message('File wp-config.php successfully backed up!','updated');
		} else {
			$this->message('Error: Creating backup failed.','error');
		}
	} else {
		$this->message('Error: Reading wp-config.php file failed.','error');
	}

	return $html;	
}*/
/*public function admin_form_full_backup() {

	$html = '';
	$html .=  $this->postboxer('top',"Full System Backup");

	$t = "admin_form_full_backup";
	$html .= '<p>This generates full system backup, including plugins, themes, configuration. This will not back up the database. Note that full system backups can take couple of minutes, do NOT close the browser while backup is in process!</p>';
	$html .= '<p><strong>System last backed up: </strong>';
	$html .= ($this->waudit_get_option('system_backed_up')) ? $this->waudit_get_option('system_backed_up') : 'never' ;
	$html .= '</p>';
	$html .= '<p><strong>System backup size: </strong>';
	$html .= ($this->waudit_get_option('system_backed_up_size')) ? $this->waudit_get_option('system_backed_up_size') : 'not available' ;
	$html .= '</p>';
	$html .= '<p><strong>System backup time: </strong>';
	$html .= ($this->waudit_get_option('system_backed_up_time')) ? $this->waudit_get_option('system_backed_up_time').' seconds' : 'not available' ;
	$html .= '</p>';
	$html .= '<p><strong>Total backups: </strong>';
	$html .= $this->count_files($this->backupdir.$this->plugin_system_dir,'count');
	$html .= '</p>';
	$html .= '<p><strong>Total backups size: </strong>';
	$html .= ($this->waudit_get_option('system_backed_up_size_total')) ? $this->waudit_get_option('system_backed_up_size_total') : 'not available' ;
	$html .= '</p>';

	$d = $this->backupdir.$this->plugin_system_dir;
	if (is_dir($d) && is_writable($d)) {
		$html .= '<p class="color-green">System backup directory is writable.</p>';
	} else {
		$html .= '<p class="color-red">System backup directory is not writable.<p></p> Make directory <code>'.$d.'</code> writable in order to backup system.</p>';
	}
	if (is_dir($this->root_path) && is_readable($this->root_path)) {
		$html .= '<p class="color-green">System itself is readable.</p>';
	} else {
		$html .= '<p class="color-red">System itself is not writable.<p></p> Make directory <code>'.$d.'</code> writable in order to backup system.</p>';
	}
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_backups').'">
			<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
			'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}").'
			<input type="submit" class="button-primary" value="Create system backup" />
			</p>
			</form>
			';

	$html .=  $this->postboxer('bottom');
	return $html;
}*/

public function make_files_backup($filename,$format) {
	
	#set_time_limit(max(ini_get('max_execution_time'), $new_timout));
	set_time_limit(900);

	if (!isset($this->Y) || empty($this->Y)) {
		$this->Y = new waudit_Compress;
	}

	$html = '';
	$texte = '';
	$time = time();
	$src = $this->root_path;

	if(is_writable($this->backupdir.$this->plugin_system_dir) && is_readable($src)) {
		$dst = 	$this->backupdir.$this->plugin_system_dir.'/'.$filename;
		$path_formated = $this->backupdir.$this->plugin_system_dir.'/'.$filename.$format;
		$time_start = microtime(true);

    	if ($format==="uncompressed") {

			// Make a backup without compressing it
			$result = $this->H->recurse_copy($src,$dst);

		} elseif($format==='.zip') {
			


			// Make a backup using .zip compression
			$result = $this->Y->zip($src,$path_formated);

		} elseif($format==='.tar' || $format==='.tar.gz' || $format==='.tar.bz2' ) { // both formats land here
			
			try {  // Create .tar archive

			    $a = new PharData($path_formated);
				#$a->buildFromDirectory($src);
				$a->buildFromIterator(
					new RecursiveIteratorIterator(
						new RecursiveDirectoryIterator($src)),dirname($src)
					);

			    // Compress if .tar.gz or tar.bz2 format selected

			    if ($format==='.tar.gz') {

				   $a->compress(Phar::GZ);

			    } elseif ($format==='.tar.bz2') {

				    $a->compress(Phar::BZ2);
			    }
			 	

			    if ($format!=='.tar') {

			    	// Delete original .tar, leave just the comrpoessed one
				    $orig_del = unlink($path_formated);

					// rename file to be exactly like on options page
					if ($orig_del) {
						$e = rename($path_nosql, $path_formated);
					}

			    }


			    // Result
			    if (file_exists($path_formated)) {
			    	$result = true;
			    }

			} catch (Exception $e) {
			    $texte .= "<br/> Error exception 1:  $e";
			}

		}	



	    if ($result) {
	    	$time_end = microtime(true);
			$this->message('Files backed up successfully!','updated');
			$this->waudit_update_option('system_backed_up', date('d.m.Y G:i'));
			$time = $time_end - $time_start;
			$this->waudit_update_option('system_backed_up_time', round($time,1));
			$size = $this->H->recursive_directory_size($dst);
			$size_total = $this->H->recursive_directory_size($this->backupdir.$this->plugin_system_dir);
			$this->waudit_update_option('system_backed_up_size', $this->H->human_filesize($size, $decimals=2));
			$this->waudit_update_option('system_backed_up_size_total', $this->H->human_filesize($size_total, $decimals=2));
			
			// protect the file
			if ($format!=="uncompressed") {
				$current_permissions = $this->waudit_get_option('setting_general_bckp_file_permission');
				$permissions = (isset($current_permissions) && !empty($current_permissions)) ? $current_permissions : $this->get_settings_field('general', 'bckp_file_permission', 'd');
				chmod($path_formated, intval($permissions,8));

			}
			return true;
	    }
			
		$this->message('Error: Creating backup failed'.$texte,'error');
		return false;

	} else {
		$this->message('Error: Creating backup failed, directory is not writable','error');
		return false;
	}
}



public function is_setting_on($category,$setting_slug) {

	$current_value = $this->waudit_get_option("setting_{$category}_{$setting_slug}");
	if ($current_value === false) { // Never saved		
		if ($this->get_settings_field($category,$setting_slug,'d') == true ) {
			$checked = true;
		} elseif ($current_value == false) {
			$checked = false;
		}
	} else { // Saved as either 0 or 1
		if ($current_value === '1') {
			$checked = true;
		} elseif ($current_value === '0') {
			$checked = false;
		} else {
			// It is not a boolean integer, it is a string or array or int or null, or array
			// therefore it does not need to be checked, so returning the value
			$checked = $current_value;
		}
	}
	return $checked;
}

/**
 * Get all settings
 */
public function get_settings($category) {
	return $this->all_settings[$category];
}


/**
 * Get information about single test
 * @param  string $category  
 * @param  string $name  
 * @param  int/string $field
 * @return mixed [string/int on success, bool false on fail]
 */
public function get_settings_field($category, $name, $field) {
	$settings = $this->get_settings($category);
	// If got input and settings
	if (is_array($settings) 
		&& !empty($settings)
		&& isset($name)
		&& isset($field) ) {
			// If actual value exists
			if (array_key_exists($name, $settings)
				&& is_array($settings[$name])
				&& array_key_exists($field, $settings[$name])) {
					$v = $settings[$name][$field];
					return (isset($v) && !empty($v)) ? $v : false;
			}
	}
}


public function get_option_element($setting) {
	return (array_key_exists('element', $setting)) ?  $setting['element'] : 'checkbox';
}

public function admin_form_options_settings_general() {

	$pre 	= 'setting_general_';
	$general_settings = $this->get_settings('general');
	$html 	=  $this->postboxer('top',"General Settings");
	$t 		= "admin_form_settings_general"; // Works for all forms on page
	$html  .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_settings').'">';
	$html  .= '<ul>';	

	foreach ($general_settings as $setting_slug => $setting) {

		// Print html for each setting
		$html .= $this->gtml_form_field($pre, $setting_slug, $setting).'<br/>';

	}

	$html .= '</ul>';
	$html .= '<input type="hidden" name="do_not_rerun_all_tests" value="please" />
			<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
			'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}");
	$html .= '<input type="submit" class="button-primary" value="Update Settings" />';
	$html .= '</p>
			</form>
			';
	$html .=  $this->postboxer('bottom');



	return $html;			
}

public function admin_form_options_settings_php() {

	$t 		= "admin_form_settings_general"; // Works for all forms on page
	$pre 	= 'setting_serverphp_';
	$server_php_settings = $this->get_settings('serverphp');
	$html 	=  $this->postboxer('top',"PHP");
	$html  .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_settings').'">';

	foreach ($server_php_settings as $setting_slug => $setting) {

		// Print html for each setting
		$html .= $this->gtml_form_field($pre, $setting_slug, $setting).'<br/>';

	}

	$html .= '<b>Update Settings Available Only In PRO Version</b><br>';
	$html .= '<br><a href="http://wpsecurity.securemoz.com/" title="SecureMoz PRO Version" target="_blank"><img src="'. $this->imgdir.'upgrade_now.png" /></a>';
	$html .=  $this->postboxer('bottom');

	return $html;			
}

/*--------------------------------------------------------------------------*/
/*  String starts with needle
/*--------------------------------------------------------------------------*/ 
protected function starts_with($haystack, $needle) {
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

/*--------------------------------------------------------------------------*/
/*  String ends with needle
/*--------------------------------------------------------------------------*/ 
protected function ends_with($haystack, $needle) {
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

public function do_form_admin_form_settings_general() {
	$result = $this->update_settings_from_post();
	if ($result) {
		$this->message('Settings updated.','updated');
	} else {
		$this->message('Settings not updated. It is possible that none of the settings were changed and therefore did not require an update.','error');
	}
	return $result;
}



/**
 * @todo: Should return all not just the last boolean
 */
public function update_settings_from_post() {

	// Any settings submitted
	$settings = $_POST;

	foreach ($settings as $key => $value) {
		if ( $this->starts_with($key,'setting_') === true) {
			$result = $this->waudit_update_option($key,trim($value));
			if ($result===false) {
				// something went wrong, cancel updating rest of the options
				return false;
			}
			
		}		
	}

	return true;
}

public function admin_form_configphp_debug1() {
	$html ='';
	$name = 'test_debug';
	$r = $this->T->get_test_result($name);

	$html .=  $this->postboxer('top',"Global Debug");
	
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>";
	$html .= $this->T->get_test_field($name, $r);
	$html .= '</p>';
	$t = "admin_form_configphp_debug1";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_configphp').'">
			<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
			'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}");
	$html .= ($r==1) ? '<!--<input type="submit" class="button-primary" value="Disable Debug" />-->' 
				: '<!--<input type="submit" class="button-secondary" value="Fixed" />-->' ;
	$html .= '</p>
			</form>
			';
	$html .=  $this->postboxer('bottom');


	return $html;		
}
public function admin_form_configphp_debug2() {
	$html ='';
	$name = 'test_debug_mysqli';
	$r = $this->T->get_test_result($name);

	$html .=  $this->postboxer('top',"MySQLi Debug");
	
	
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>";
	$html .= $this->T->get_test_field($name, $r);
	$html .= '</p>';
	$t = "admin_form_configphp_debug2";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_configphp').'">
			<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
			'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}");
	$html .= ($r==1) ? '<!--<input type="submit" class="button-primary" value="Disable Debug" />-->' 
				: '<!--<input type="submit" class="button-secondary" value="Fixed" />-->' ;
	$html .= '</p>
			</form>
			';
	$html .=  $this->postboxer('bottom');


	return $html;		
}
public function admin_form_configphp_debug3() {
	$html ='';

	$html .=  $this->postboxer('top',"Javascipt/CSS Debug");
	$name = 'test_debug_scripts';
	$r = $this->T->get_test_result($name);
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>";
	$html .= $this->T->get_test_field($name, $r);
	$html .= '</p>';
	$t = "admin_form_configphp_debug3";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_configphp').'">
			<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
			'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}");
	$html .= ($r==1) ? '<!--<input type="submit" class="button-primary" value="Disable Debug" />-->' 
				: '<!--<input type="submit" class="button-secondary" value="Fixed" />-->' ;
	$html .= '</p>
			</form>
			';
	$html .=  $this->postboxer('bottom');


	return $html;		
}
public function admin_form_configphp_debug4() {
	$html ='';

	$html .=  $this->postboxer('top',"Disallow Plugin and Theme Editor");
	$name = 'test_editor';
	$r = $this->T->get_test_result($name);
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>";
	$html .= $this->T->get_test_field($name, $r);
	$html .= '</p>';
	$t = "admin_form_configphp_debug4";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_configphp').'">
			<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
			'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}");
	$html .= ($r==1) ? '<!--<input type="submit" class="button-primary" value="Disable Editor" />-->' 
				: '<!--<input type="submit" class="button-secondary" value="Fixed" />-->' ;
	$html .= '</p>
			</form>
			';
	$html .=  $this->postboxer('bottom');


	return $html;		
}
public function admin_form_configphp_save_queries() {
	$html ='';

	$html .=  $this->postboxer('top',"Save queries");
	$name = 'test_save_queries';
	$r = $this->T->get_test_result($name);
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>";
	$html .= $this->T->get_test_field($name, $r);
	$html .= '</p>';
	$t = "admin_form_configphp_save_queries";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_configphp').'">
			<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
			'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}");
	$html .= ($r==1) ? '<!--<input type="submit" class="button-primary" value="Disable Query saving" />-->' 
				: '<!--<input type="submit" class="button-secondary" value="Fixed" />-->' ;
	$html .= '</p>
			</form>
			';
	$html .=  $this->postboxer('bottom');


	return $html;		
}
public function admin_form_configphp_trash_exist() {
	$html ='';

	$html .=  $this->postboxer('top',"Trash exist");
	$name = 'test_trash_exist';
	$r = $this->T->get_test_result($name);
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>";
	$html .= $this->T->get_test_field($name, $r);
	$html .= '</p>';
	$t = "admin_form_configphp_trash_exist";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_configphp').'">
			<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
			'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}");
	$html .= ($r==1) ? '<!--<input type="submit" class="button-primary" value="Enable Trash" />-->' 
				: '<!--<input type="submit" class="button-secondary" value="Fixed" />-->' ;
	$html .= '</p>
			</form>
			';
	$html .=  $this->postboxer('bottom');


	return $html;		
}

public function admin_form_skipinitbackup() {
	return '<a href="'.admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_safety_test&skip_init_backup_step=true').'"> skip this step</a>';		
}

public function do_form_admin_form_skipinitbackup() {
	return $this->T->perform_test('all');
}



/*
*	@param $addifnotfound: 0 for skip, 1 to add $swapwithline to the end of file
*/
public function do_form_admin_form_configphp_debug($perform_test, $find_line, $swapwithline, $addifnotfound=0) {
	$html='';
	$newfile = '';
	$time = time();
	$foundit = false;
	$file = $this->root_path.'wp-config.php';
	// File1
	$handle = @fopen($this->root_path.'wp-configTEMP.php', "w+");
	// File2
	$file_contents = file_get_contents($file);
	if($this->T->get_test_result($perform_test)!=0) {
		if ($handle && $file_contents) {
			if(strpos($file_contents,$find_line)!==false){
				$file_contents = str_replace($find_line, $swapwithline, $file_contents);
				$foundit = true;
			}
			// Didn't found? add it to the end?
			if($foundit!==true) {
				$file_contents .= ($addifnotfound==1) ? "\n".$swapwithline : '' ;
			}
			
			fwrite($handle, $file_contents);
		    fclose($handle);

		    $d = $this->backupdir.$this->plugin_wpconfig_dir;
			if (is_dir($d) && is_writable($d)) {
			    //Backup original wp-conf
			    rename($this->root_path.'wp-config.php', $d.'/wp-config-'.$time.'.php');
			    chmod($d.'wp-config-'.$time.'.php', $this->backup_file_permission);
			    #$html .= $this->message('Original wp-config.php backed up to '.$d,'updated');

				//And let the new one rule
			    rename($this->root_path.'wp-configTEMP.php', $this->root_path.'wp-config.php');
			    chmod($this->root_path.'wp-config.php', 0644);
			    $this->waudit_update_option('wpconfigphp_backed_up', date('d.m.Y G:i'));

			    $html .= $this->message('File wp-config.php successfully changed','updated');
			    

			    // @todo: well: fuck.
			    // Otherwise Wordpress needs refesh to understand change of constants
			    // This updates all suboptions of a test in".$wpdb->prefix."_options table.
			    $this->update_all_suboptions($perform_test,0);



			} else {
				$this->message('Making changes on file wp-config.php failed','error');
			}
		} else {
			$this->message('Error: Reading wp-config.php file failed.','error');
		}
	} else {
		$this->message('This test has already been successfully performed','updated');
	}

	return $html;
}


public function admin_form_server_php1() {
	$html ='';

	$html .=  $this->postboxer('top',"PHP Register Globals");
	$name = 'test_register_globals';
	$r = $this->T->get_test_result($name);
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>";
	$html .= $this->T->get_test_field($name, $r);
	$html .= '</p>';
	$t = "admin_form_server_php1";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_server').'">';
	if($this->T->get_test_field($name, 'fix_tip')) {
		$html .= $this->T->get_test_field($name, 'fix_tip');
	}	
	if($r==1 && $this->T->get_test_field($name, 'fix')) {
		$html .= '<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
				'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}").'
				<input type="submit" class="button-primary" value="Fixed" />
				</p>';
	} 

	$html .= '</form>';
	$html .=  $this->postboxer('bottom');


	return $html;		
}

public function admin_form_server_php2() {
	$html ='';

	$html .=  $this->postboxer('top',"PHP Safe Mode");
	$name = 'test_safe_mode';
	$r = $this->T->get_test_result($name);
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>";
	$html .= $this->T->get_test_field($name, $r);
	$html .= '</p>';
	$t = "admin_form_server_php2";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_server').'">';
	if($this->T->get_test_field($name, 'fix_tip')) {
		$html .= $this->T->get_test_field($name, 'fix_tip');
	}	
	if($r==1 && $this->T->get_test_field($name, 'fix')) {
		$html .= '<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
				'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}").'
				<input type="submit" class="button-primary" value="Fixed" />
				</p>';
	} 

	$html .= '</form>';
	$html .=  $this->postboxer('bottom');


	return $html;		
}
public function admin_form_server_php3() {
	$html ='';

	$html .=  $this->postboxer('top',"PHP URL Include");
	$name = 'test_allow_url_include';
	$r = $this->T->get_test_result($name);
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>";
	$html .= $this->T->get_test_field($name, $r);
	$html .= '</p>';
	$t = "admin_form_server_php3";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_server').'">';
	if($this->T->get_test_field($name, 'fix_tip')) {
		$html .= $this->T->get_test_field($name, 'fix_tip');
	}	
	if($r==1 && $this->T->get_test_field($name, 'fix')) {
		$html .= '<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
				'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}").'
				<input type="submit" class="button-primary" value="Fixed" />
				</p>';
	} 

	$html .= '</form>';
	$html .=  $this->postboxer('bottom');


	return $html;		
}
public function admin_form_server_php4() {
	$html ='';

	$html .=  $this->postboxer('top',"PHP Display errors");
	$name = 'test_display_errors';
	$r = $this->T->get_test_result($name);
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>";
	$html .= $this->T->get_test_field($name, $r);
	$html .= '</p>';
	$t = "admin_form_server_php4";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_server').'">';
	if($this->T->get_test_field($name, 'fix_tip')) {
		$html .= $this->T->get_test_field($name, 'fix_tip');
	}	
	if($r==1 && $this->T->get_test_field($name, 'fix')) {
		$html .= '<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
				'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}").'
				<input type="submit" class="button-primary" value="Fixed" />
				</p>';
	} 

	$html .= '</form>';
	$html .=  $this->postboxer('bottom');


	return $html;		
}
public function admin_form_server_php5() {
	$html ='';

	$html .=  $this->postboxer('top',"PHP Expose");
	$name = 'test_expose_php';
	$r = $this->T->get_test_result($name);
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>";
	$html .= $this->T->get_test_field($name, $r);
	$html .= '</p>';
	$t = "admin_form_server_php5";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_server').'">';
	if($this->T->get_test_field($name, 'fix_tip')) {
		$html .= $this->T->get_test_field($name, 'fix_tip');
	}	
	if($r==1 && $this->T->get_test_field($name, 'fix')) {
		$html .= '<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
				'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}").'
				<input type="submit" class="button-primary" value="Fixed" />
				</p>';
	} 

	$html .= '</form>';
	$html .=  $this->postboxer('bottom');


	return $html;		
}
public function admin_form_server_php6() {
	$html ='';

	$name = 'test_allow_url_fopen';
	$html .=  $this->postboxer('top', $this->T->get_test_field($name,'description'));
	$r = $this->T->get_test_result($name);
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>";
	$html .= $this->T->get_test_field($name, $r);
	$html .= '</p>';
	$t = "admin_form_server_php5";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_server').'">';
	if($this->T->get_test_field($name, 'fix_tip')) {
		$html .= $this->T->get_test_field($name, 'fix_tip');
	}	
	if($r==1 && $this->T->get_test_field($name, 'fix')) {
		$html .= '<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
				'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}").'
				<input type="submit" class="button-primary" value="Fixed" />
				</p>';
	} 

	$html .= '</form>';
	$html .=  $this->postboxer('bottom');


	return $html;		
}


public function admin_form_chmod() {
	$html = '';
	$name = 'test_chmod_all_safe';
	$r = $this->T->get_test_result($name);
	$html .=  $this->postboxer('top',"File permissions");
	$html .=' <p>Protect Wordpress directories and files from unauthorised access.</p>';
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>";
	$html .= $this->T->get_test_field($name, $r);
	$html .= '</p>';

	// List permissions
	$html .= "<table class=\"nsawide\"><thead><th></th><th>Path</th><th>Safe Permissions</th><th>Current Permissions</th></thead><tbody>";
	foreach ($this->path_chmod as $path => $perm) {
		$path = $this->root_path.$path;
		$perm = decoct($perm);
		if(file_exists($path)) {
			$cperm = substr(decoct(fileperms($path)), -3);
			$html .= "<tr class=\"backgroundcolor-";
			$col = ($perm>=$cperm) ? 'green' : 'red';
			$html .= $col;
			$html .= "\">";
			$html .= "<td>".$this->H->gtml_img($col.'.png')."</td>";
			$html .= "<td>".$path."</td>";
			$html .= "<td>".$perm."</td>";
			$html .= "<td>".$cperm."</td>";
			$html .= "</tr>";
		} else {
			$html .= "<tr>";
			$html .= "<td>".$this->H->gtml_img('red.png')."</td>";
			$html .= "<td>".$path."</td>";
			$html .= "<td>".$perm."</td>";
			$html .= "<td>-</td>";
			$html .= "</tr>";				
		}
	}
	$html .= "</tbody></table>";

	//Change permissions
	$t = "admin_form_chmod";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_server').'">
			<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
			'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}").'
			<input type="submit" class="button-primary" value="Bulk fix permissions" />      
			</p>
			</form>
			';
	
	$html .=  $this->postboxer('bottom');

	return $html;
}

public function do_form_admin_form_chmod() {
	$html='';
	$i=0;
	foreach ($this->path_chmod as $path => $perm) {		
		if(chmod($this->root_path.$path, $perm)) {
			$i++;
		}
	}
	if ($i==count($this->path_chmod)) {
		$this->message('File and Directory permissions fixed.','updated');
	} else {
		$this->message('File and Directory permission chmod failed. User has no access to filesystem, please change the permissions manually.','error');
	}

	return $html;
}
public function admin_form_htaccess_secure() {
	$html = '';

	$html .='<p>Make files more secure using .htaccess file. Protect file from public access on server-side</p>';

	$html .=  $this->postboxer('top',"Secure wp-config.php");
	$name = 'test_htaccess_secured_wpconfig';
	$r = $this->T->get_test_result($name);
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>";
	$html .= $this->T->get_test_field($name, $r);
	$html .= '</p>';
	$t = "admin_form_htaccess_secure_wpconfigphp";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_configphp').'">
			<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
			'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}").'
			<input type="submit" class="button-primary" value="Secure wp-config.php file" />
			</p>
			</form>
			';
	$html .=  $this->postboxer('bottom');



	$html .=  $this->postboxer('top',"Secure .htaccess itself");
	$name = 'test_htaccess_secured_htaccess';
	$r = $this->T->get_test_result($name);
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>";
	$html .= $this->T->get_test_field($name, $r);
	$html .= '</p>';
	$t = "admin_form_htaccess_secure_htaccess";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_configphp').'">
			<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
			'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}").'
			<input type="submit" class="button-primary" value="Secure .htaccess file" />
			</p>
			</form>
			';
	$html .=  $this->postboxer('bottom');

	$html .=  $this->postboxer('top',"Prevent inside directories from public browsing");
	$name = 'test_htaccess_secure_dirs';
	$r = $this->T->get_test_result($name);
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>";
	$html .= $this->T->get_test_field($name, $r);
	$html .= '</p>';
	$t = "admin_form_htaccess_secure_inside_directories";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_configphp').'">
			<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
			'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}").'
			<b>Update Available Only In PRO Version</b><br>
			<a href="http://wpsecurity.securemoz.com/" title="SecureMoz PRO Version" target="_blank"><img src="'. $this->imgdir.'upgrade_now.png" /></a>
			
			</p>
			</form>
			';
	$html .=  $this->postboxer('bottom');
	return $html;		
}

public function do_form_admin_form_htaccess_secure_htaccess() {
	$filename = '.htaccess';
	$html ='';
	if($this->T->get_test_result('test_htaccess_secured_htaccess')!=0) {

		$path_to_file = $this->root_path.'.htaccess';
		$handle = @fopen($path_to_file, "a+");
		if ($handle) {
	    	$e = fwrite($handle, "\n<Files ".$filename.">\n	order allow,deny\n	deny from all\n</Files>\n");
	    	if($e>=1) {
	    		$this->message('File '.$filename.' secured.','updated');
	    		$this->update_all_suboptions('test_htaccess_secured_htaccess',0);
	    	} else {
	    		$this->message('Error: unexpected fgets() fail 1.','error');
	    	}
		    fclose($handle);		
		} else {
			$this->message('Error: Reading file failed.','error');
		}
	} else {
		$this->message('File '.$filename.' has already been secured.','updated');
	}
	return $html;		
}
public function do_form_admin_form_htaccess_secure_wpconfigphp() {
	$filename = 'wp-config.php';
	$html ='';
	if($this->T->get_test_result('test_htaccess_secured_wpconfig')!=0) {
		$path_to_file = $this->root_path.'.htaccess';
		$handle = @fopen($path_to_file, "a+");
		if ($handle) {
	    	$e = fwrite($handle, "\n<Files ".$filename.">\n	order allow,deny\n	deny from all\n</Files>\n");
	    	if($e>=1) {
	    		$this->message('File '.$filename.' secured.','updated');
	    		$this->update_all_suboptions('test_htaccess_secured_wpconfig',0);
	    	} else {
	    		$this->message('Error: unexpected fgets() fail 2.','error');
	    	}
		    fclose($handle);		
		} else {
			$this->message('Error: Reading file failed.','error');
		}
	} else {
		$this->message('File '.$filename.' has already been secured.','updated');
	}
	return $html;		
}

public function do_form_admin_form_htaccess_secure_inside_directories() {
	$path_to_file = $this->root_path.'.htaccess';
	$html = '';
	$handle = @fopen($path_to_file, "a+");
	if($this->T->get_test_result('test_htaccess_secure_dirs')!=0) {
		if ($handle) {
	    	$e = fwrite($handle, "Options -Indexes\n");
	    	if($e>=1) {
	    		$this->message('Inside directories secured from public browsing.','updated');
	    		$this->update_all_suboptions('test_htaccess_secure_dirs',0);
	    	} else {
	    		$this->message('Error: unexpected fgets() fail 1.','error');
	    	}
		    fclose($handle);		
		} else {
			$this->message('Error: Reading .htaccess file failed.','error');
		}
	} else {
		$this->message('Directory listing has already been disabled','updated');
	}
	return $html;
}

public function admin_form_configphp_keys() {
	$html = '';
	$name = 'test_wpconfig_for_empty_keys';
	$r = $this->T->get_test_result($name);

	$html .=  $this->postboxer('top',"Security keys");
	
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>".$this->T->get_test_field($name, $r)."</p>";
	$html .='<p><strong>Current security keys:</strong><p>';
	$html .= $this->admin_form_configphp_keys_list();
	$t = "admin_form_configphp_keys";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_configphp').'">
			<p>A secret key makes your site harder to hack and access by adding random elements to the password.</p>
			<p>Backup of current wp-config.php will be created automatically and secured from public access. Old security keys (if exist) will be replaced with new ones.</p>
			<p>For security reasons after this operation you will be <strong>logged out</strong> Do not chaneg keys if you do not know your username and password.</p>
			<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
			'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}").'
			<b>Update Security Keys Available Only In PRO Version</b><br>
			<a href="http://wpsecurity.securemoz.com/" title="SecureMoz PRO Version" target="_blank"><img src="'. $this->imgdir.'upgrade_now.png" /></a>
			</p>
			</form>
			';

	$html .=  $this->postboxer('bottom');
	return $html;
}
public function admin_form_configphp_keys_list() {
	$html = '';
	$keys = '';
	$found_keys = $this->loop_array_each_file_line($this->root_path.'wp-config.php', $this->keys,'r',"define('");
	$keys_salts = $this->extract_keys($returnarray=true);

	$html .= "<table class=\"nsawide\"><thead><th>Name</th><th>Current key</th></thead><tbody>";
	foreach ($keys_salts as $key => $pass) {
		if(strpos( $pass, 'put your unique phrase here')===false && strlen($pass)>10) {
			$pa = htmlspecialchars($pass);
			$pass = substr($pa,0, -10).'...';
		}
		$keys .= "<tr class=\"backgroundcolor-";
		$keys .= (strlen($pass) > 50) ? 'green' : 'red';
		$keys .= "\">";

		$keys .= "<td>".$key."</td>";
		$keys .= "<td>".$pass."</td>";

		$keys .= "</tr>";
	}
	$html .= $keys;

	$html .= "</tbody></table>";
	
	return ($keys) ? $html : 'No Keys Defined!';
}
public function do_form_admin_form_configphp_keys($nosalt=false) {
	$html='';
	$time = time();
	
	$keys = $this->keys;
	$salts = '';
	foreach ($keys as $key) {
		$salt = wp_generate_password(64, true, true);
		$spaces = str_repeat(' ',16 - strlen($key));
		$salts .= "define('{$key}',{$spaces}'{$salt}');\n";
		// Directly update option namesi in db
		update_option( strtolower($key), $salt );
	}

	//File1
	$handle = @fopen($this->root_path.'wp-configTEMP.php', "w+");
	if ($handle) {
		//File2
		$path_to_file = $this->root_path.'wp-config.php';
		$file_contents = file_get_contents($path_to_file);
		//create array separate by new line
		$convert = explode("\n", $file_contents);
		foreach ($convert as $v) {
			$trykey = 0;
			foreach ($keys as $key) {
				$findme = "define('".$key."'";
				if(strpos($v,$findme)===false){
					$trykey++;
				}


			}
			if ($trykey == 8 && $v!='' && $v != ' ') {
				fwrite($handle, $v."\n");
			}
			
		}
		if($nosalt !== true ) {
			fwrite($handle, "\n".$salts."\n");
		}
	    fclose($handle);

	    $d = $this->backupdir.$this->plugin_wpconfig_dir;
		if (is_dir($d) && is_writable($d)) {
			$filepath = $d.'/wp-config-'.$time.'.php';
		    //Backup original wp-conf
		    rename($this->root_path.'wp-config.php',$filepath );
		    chmod($filepath, $this->backup_file_permission);
			//And let the new one rule
		    rename($this->root_path.'wp-configTEMP.php', $this->root_path.'wp-config.php');
		    chmod($this->root_path.'wp-config.php', 0777);
		    $this->waudit_update_option('wpconfigphp_backed_up', date('d.m.Y G:i'));


		    





		} else {
			$this->message('Making changes on file wp-config.php failed','error');
		}
	    $html .= $this->message('Secret keys successfully updated in wp-config.php','updated');
	} else {
		$this->message('Making changes on file wp-config.php failed','error');
	}

	return $html;
}

// Methods for each form in tab
public function admin_form_database_update_prefix() {
	global $wpdb;
	$html = '';

	$html .=  $this->postboxer('top',"Change database prefix");
	
	$html .= '<p>The majority of reported WordPress database security attacks were performed by exploiting SQL Injection vulnerabilities. By renaming the WordPress database table prefixes you are securing your WordPress blog and website from zero day SQL injections attacks.</p>';
	$name = 'test_db_prefixes';

	// double check needed after changing db prefix!
	$this->T->perform_test('test_db_prefixes');

	$r = $this->T->get_test_result($name);
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>";
	$html .= $this->T->get_test_field($name, $r);
	$html .= '</p>';
	
	$html .= '<p><strong>Current database prefix:</strong> ';
	$html .=  $wpdb->prefix;
	$html .= '</p>';
	$html .= '<p><strong>Action required:</strong> ';
	$html .= ($this->T->get_test_result($name)==1) ? 'change of prefix' : 'none' ;
	$html .= '</p>';
	$t = "admin_form_database_update_prefix";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_database').'">
			<p><strong>Enter new prefix:</strong><input type="text" name="admin_form_database_update_prefix_prefix_new" size="6"></p>
			<p><small>Prefix should consist of alphanumeric characters and numbers + underscore "_" at the end (e.g. "waudit_" or "site1_")</p>
			<p>To perform this action current security keys in wp.config.php will be deleted.</small></p>
			<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
			'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}").'
			<input type="submit" class="button-primary" value="Change prefix" />

			</p>
			</form>
			';

	$html .=  $this->postboxer('bottom');
	return $html;
}

public function do_form_admin_form_database_update_prefix() {

	global $wpdb;
	
	$html='';
	$i=0;//Global success counter to change wpdb prefix


	// Attempt to delete SECURE KEYS from wp-config.php before moving forward
	if (!$this->H->delete_specific_lines_from_file($this->root_path.'wp-config.php',$this->keys)) {
		return $this->message('Cannot start change of database prefix - unable to clear old security keys in wp-config','error');
	}

	
	$prefix_o = $wpdb->prefix;
	$prefix_n = $_POST['admin_form_database_update_prefix_prefix_new'];
	if ($prefix_o === $prefix_n) {
		return $this->message('Database prefix change on options table failed <br/> More details: New prefix is identical to old prefix!','error');
	}


	if(!empty($prefix_n)  ) {
		if(!empty($prefix_o) && is_writable($this->root_path.'wp-config.php')){

			// Change prefix in wp-config.php
			$path_to_file = $this->root_path.'wp-config.php';
			$file_contents = file_get_contents($path_to_file);
			$file_contents = str_replace('$table_prefix  = \''.$prefix_o.'\';','$table_prefix  = \''.$prefix_n.'\';',$file_contents);
			if(file_put_contents($path_to_file,$file_contents)) {
				$this->message('Database prefix changed on file wp-config.php','updated');
				$i++;
			} else {
				$this->message('Database prefix change on file wp-config.php failed','error');
			}

			$con=mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
			// Check connection
			if (mysqli_connect_errno()) {
				// Could not connect
				return "Error: Could not access database";
			}

			// Change table names 
			$return = '';
			$query = '';
			$result = mysqli_query($con,'SHOW TABLES');
			$query .= "RENAME TABLE";
			while($row = mysqli_fetch_row($result)) {
				$query .= " `{$row[0]}` TO `".str_replace($prefix_o, $prefix_n, $row[0])."`,\n";
			}
			$query_ready = substr($query, 0,-2);
			
			if (mysqli_query($con,$query_ready)) { 
				$this->message('Database prefix changed on all database tables','updated');
				$i++;
			} else { 
				$this->message('Database prefix change on all database tables failed'."<br/>More details: ".mysqli_error(),'error');
			}

			// Change prefix inside ".$wpdb->prefix."_options
			$query = "UPDATE {$prefix_n}options 
					SET option_name='{$prefix_n}user_roles' 
					WHERE option_name='{$prefix_o}user_roles'";

			if ($result=mysqli_query($con,$query)) { 
				$this->message('Database prefix changed on options table','updated');
				$i++;
			} else { 
				$this->message('Database prefix change on options table failed'."<br/>More details: ".mysqli_error(),'error');
			}

			// Change prefix inside ".$wpdb->prefix."_usermeta
			$results = array();
			$countupdated = 0;
			$countsuccess = 0;


			$query = "SELECT meta_key 
					FROM  {$prefix_n}usermeta 
					WHERE  meta_key LIKE '{$prefix_o}%'";
			$result=mysqli_query($con,$query);
			while ($row = mysqli_fetch_array($result)) {
				$query2 = "UPDATE {$prefix_n}usermeta SET `meta_key`='".str_replace($prefix_o, $prefix_n, $row[0])."' WHERE `meta_key`='{$row[0]}';";	   
				$countupdated++;
				if (mysqli_query($con,$query2)) {
					$countsuccess++;
				}
			}

			if ($countupdated==$countsuccess) { 
				$this->message('Database prefix changed on usermeta table','updated');
				$i++;
			} else { 
				$this->message('Database prefix change on usermeta table failed'."<br/>More details: ".mysqli_error(),'error');
			}



			 if ($i===4) {

			 	// THIS TEST WILL NOT RUN!!!!!!!!!!!
			 	// Because all the tables in $wpdb are still pointing to the old ones.
			 	// Make a system that runs this after next refresh or something like that
			 	// 
			 	// OR
			 	// 
			 	// Make result generated on the fly for that form (!)
			 	// 
			 	// 	m?
			 	// 
				// @todo recode without line below
				// $this->perform_test('test_db_prefixes');

				// echo "<pre>";
				// print_r($wpdb);
				// echo "</pre>";
				
			}



		} else {
			$this->message('Make sure wp-config is writable for this procedure','error');
		}
	} else {
		$this->message('Prefix should consist of alphanumeric characters and numbers + underscore "_" at the end (e.g. "waudit_" or "site1_")','error');
	}

	return $html;
}

public function admin_form_safety_test() {
	$html = '';
	$name = 'all';
	$d = $this->waudit_get_option($name);
	$score_data = $this->get_audit_score_number();
	
	if (is_array($score_data)) {
		$score = $score_data[2];
	} else {
		$score = $score_data;
	}

	$html .=  $this->postboxer('top',"Perform security audit");
	$html .= '<div class="columns3">';
		$html .= '<div class="col col1">';
		#$html .= "<span>$score%</span>";
		$html .= "<input class=\"knob\" data-thickness=\".4\" data-readOnly=true value=\"{$score}\" data-width=\"120\" data-bgColor=\"#ff6347\" data-fgColor=\"#A4C639\">";
		$html .= '</div>';
			$html .= '<div class="col col2">';
			$html .= '<p><strong>Audit last performed: </strong><br/>';
			$html .= ($d) ? $d:'never' ;
			$t = "admin_form_safety_test";
			$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_safety_test').'">
					<input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
					'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}").'
					<input type="hidden" name="do_not_rerun_all_tests" value="please" />';
          if($this->server_verifire("&f=dlimit") == 0)  
          {
  					$html .= '<input type="submit" class="button-primary" value="';
        
      			if ($this->plugin_get_version() !== $this->waudit_get_option("plugin_version_last_run_all_tests") /*&& !$this->is_setting_on('system','ignore_plugin_version')*/) {
      				$html .= 'Start &raquo;';   
      			} else {
      				$html .= 'Re-run audit';
      			}
        
    			$html .= '" />
    					</form>
    					</div>
    					';
          }else{
             $html .= '<div align="center"><font color="#FF6347"><b>Monthly Limit Reached</b></font><br>Please Consider<br/><a href="http://wpsecurity.securemoz.com/" title="SecureMoz PRO Version" target="_blank"><img src="'. $this->imgdir.'upgrade_now.png" /></a></div></form></div>';
          }
        
				$html .= '<div class="col col3">';
				$html .= $this->get_audit_score();
				$html .= '</div>';
	$html .= '</div>';
				$html .= '<div class="clear"></div>';
	$html .=  $this->postboxer('bottom');

	return $html;
}

// Comparison table of all tests and their results, statuses
public function admin_form_safety_test3() {
	$html = '';
	$name = 'all';
	$d = $this->waudit_get_option($name);
	$siteurl = get_site_url();
  $this->server_verifire('&f=dgdg');
	if($d) {
		$html .=  $this->postboxer('top',"Detailed results from last audit");
		$html .= "<table class=\"nsawide\"><thead><th></th><th>Test</th><th>Status</th><th>Solution</th></thead><tbody>";
		
		foreach($this->T->get_all_tests() as $test_name => $test_details) {

			$append_test_name = '&t='.md5($test_name);
			$test_result = $this->T->get_test_result($test_name);
			$test_color = $this->T->get_test_color($test_result);

			$html .= "<tr class=\"backgroundcolor-{$test_color}\">";
			$html .= "<td>".$this->H->gtml_img($test_color.'.png')."</td>";
			$html .= "<td>".$this->T->get_test_field($test_name, 'description')."</td>";
			$html .= "<td>".$this->T->get_test_field($test_name, $test_result)."</td>";
			$html .= "<td>";

			if ($test_result) {
				$html .= ($l = $this->T->get_test_field($test_name, 'fix')) ? '<a href="admin.php?page=waudit1&tab='.$l.$append_test_name.'">'.$this->H->gtml_img('hammer_arrow.png', array('title'=>'Waudit fix available')).'</a>' : '' ;
				$html .= ($l = $this->T->get_test_field($test_name, 'fix_wp')) ? '<a href="'.$siteurl.$l.'">'.$this->H->gtml_img('hammer_plus.png', array('title'=>'Wordpress fix available')).'</a>' : '' ;
				$html .= ($l = $this->T->get_test_field($test_name, 'fix_tip') && $lu = $this->T->get_test_field($test_name, 'fix_tip_url')) ? '<a href="admin.php?page=waudit1&tab='.$lu.$append_test_name.'">'.$this->H->gtml_img('hammer_pencil.png', array('title'=>'Tip available')).'</a>' : '' ;
			}

			$html .= "</td>";
			$html .= "</tr>";
		}
		$html .= "</tbody></table>";			
		$html .= "<div id=\"legendbox\">";
		$html .= '<p>'.$this->H->gtml_img('hammer_arrow.png', array('title'=>'Waudit fix available')).' Waudit fix available - Waudit plugin can solve the issue</p>';
		$html .= '<p>'.$this->H->gtml_img('hammer_plus.png', array('title'=>'Wordpress fix available')).' Wordpress fix available - Issue can be solved by changing a setting in Wordpress Dashboard</p>';
		$html .= '<p>'.$this->H->gtml_img('hammer_pencil.png', array('title'=>'Tip available')).' Tip available - Solution requires external access such as access to server configuration files</p>';
				
		$html .= "</div>";			
		$html .=  $this->postboxer('bottom');
	}


	return $html;
}
public function admin_form_user_username() {
	$html = '';
	
	$html .=  $this->postboxer('top',"Change username");
	$name = 'test_admin_exists';
	$r = $this->T->get_test_result($name);
	$html .= "<p class='color-".$this->T->get_test_color($r)."'><strong>Status: </strong>";
	$html .= $this->T->get_test_field($name, $r);
	$html .= '</p>';
	$t = "admin_form_user_username";
	$html .='<form method="post" action="'. admin_url( 'admin.php?page='.$this->plugin_slug.'&tab=options_user').'">
			<p><strong>Username to change:</strong><input type="text" name="admin_form_user_username_old" value="admin"></p>
			<p><strong>Enter new username:</strong><input type="text" name="admin_form_user_username_new"></p>
			<p><small>New username should contain only alphanumeric characters, spaces, underscores, hyphens, periods and the @ symbol. First character should be alphanumeric. Ideal lenght is 7-16 characters.</small></p>
			<p>You will be prompted to log in after this procedure (Enter the new username and the same password you used before).</p>
			<p class="submit"><input type="hidden" name="waudit_submitted_form" value="'.$t.'" />
			'. wp_nonce_field("waudit_{$t}", "waudit_nonce_{$t}").'
			<input type="submit" class="button-primary" value="Change Username" />
			</p>
			</form>
			';
	$html .=  $this->postboxer('bottom');
	return $html;
}
public function do_form_admin_form_user_username() {
	global $wpdb;
	$html = '';
	$user_o = $_POST['admin_form_user_username_old'];
	$user_n = $_POST['admin_form_user_username_new'];
	// Usernames set?
	if(!empty($user_o) && !empty($user_n) && strlen($user_n)>1 && strlen($user_n)<64 ) {
		// Old username exists?
		if(username_exists($user_o)) {
			// Usernames can have only alphanumeric characters, spaces, underscores, hyphens, periods and the @ symbol.
			if(preg_match('/^[a-zA-Z0-9]*[a-zA-Z0-9\-_@.]*$/',$user_n )) {
				
				// Safety first
				#$html .= $this->do_form_admin_form_database_backup();				
				$r = $wpdb->query( "UPDATE `".$wpdb->users."` SET user_login='".$user_n."' WHERE user_login='".$user_o."';" );
				if($r) {
					$this->do_form_admin_form_configphp_keys($nosalt=true);
					wp_clear_auth_cookie();
					$this->message('Username changed successfully','update');
				} else {
					$this->message('Change of username failed','error');
				}
			} else {
			$this->message('New username did not math rules','error');
			}
		} else {
			$this->message('Username you are trying to change does not exist','error');
		}
	} else {
		$this->message('New username should contain only alphanumeric characters, underscores, hyphens, periods and the @ symbol. First character should be alphanumeric.','error');
	}		
	#return $html;
}








public function do_form_admin_form_safety_test() {
	$html = '';
	$this->T->perform_test('all');

	return $html;
}


/*
*
**
// HELPER
**
*
*/

public function plugin_get_version() {
    if ( ! function_exists( 'get_plugins' ) )
        require_once( $this->root_path . 'wp-admin/includes/plugin.php' );
    $plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
    $plugin_file = basename( ( __FILE__ ) );
    return $plugin_folder[$plugin_file]['Version'];
}

public function do_pre_first_time_actions() {
  return true;
	$db_backed = $this->make_database_backup('initial_database_backup','.zip');
	$fs_backed = $this->make_files_backup('initial_files_backup','.zip');

	if ($db_backed && $fs_backed) {
		return true;
	}
	return false;

}

public function normalise_chars($string) {
	$findthis			=	array('å','æ','ø',' ','_','-',':','<','>',';','.');
	$replacewith	=	array('aa','ae','oe','','','','','','','','');
	$metavalue_clean = str_replace($findthis,$replacewith,strtolower($string));
	 
	return $metavalue_clean;
	
}

public function waudit_sidebar() {
	$html='';
	#$html .=  $this->postboxer('top',"$this->plugin_name");
	$html .=  $this->postboxer('top',"<b><center>Securemoz Security Audit <font color='green'>(FREE)</font></center></b>");
	$html .=  "<img src=\"". $this->imgdir."waudit_griffin_logo_200.png\" style=\"position: relative; margin-top: 30px; margin-left: 20%;\" />";
	$html .= "<p style=\"\" >Hello! I'm the Securemoz Doctor, please follow the instructions to protect your site!</p>";
	$html .= '<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1&appId=1450266808561789";
  fjs.parentNode.insertBefore(js, fjs);
}(document, \'script\', \'facebook-jssdk\'));</script>';
     $html .= "<UL><LI><a href=\"http://wpsecurity.securemoz.com\" target=\"_blank\" title=\"SecureMoz Pro\"><b>Learn About Pro Features</b></a><br/><br/></LI>";
     $html .= "<LI><a href=\"#\" target=\"_blank\" title=\"SecureMoz User Guide\" onclick=\"javascript:window.open('$this->docs/index.html','SecureMoz User Guide','directories=no,titlebar=no,toolbar=no,status=no,menubar=yes,scrollbars=yes,resizable=yes,width=850,height=550');return false;\">SecureMoz User Guide</a><br/><br/</LI>";
    $html .= "<LI><a href=\"http://wpsecurity.securemoz.com/install-service/\" title=\"Install Service\" target=\"_blank\">We Can Help</a><br/><br/></LI>";
    $html .= "<LI><a href='http://twitter.com/share?text=Dont%20miss%20this%20%23security%20%23audit%20%23plugin%20for%20%23Wordpress!&amp;url=https://securemoz.com&amp;via=Securemoz&amp;count=none' target='_blank'><i class=\"icon-twitter\"></i>&nbsp;Share on Twitter</a><br/><br/></LI>";
    $html .= "<LI><a href='http://www.facebook.com/sharer/sharer.php?u=#wpsecurity.securemoz.com%20Site%20%23Clean%20by%20%23SecureMoz%20%23Plugin%20Try%20yours!!&amp;url=https://www.securemoz.com&amp;via%23securemoz&amp;count=none' target='_blank'><i class=\"icon-facebook\"></i>&nbsp;Like on Facebook</a><br/><br/></LI>";
	$html .= "<LI><a href='https://plus.google.com/share?url=wpsecurity.securemoz.com%23Clean%20by%20%23SecureMoz%20%23Plugin%20Try%20yours!!&amp;url=https://wpsecurity.securemoz.com&amp;via%23securemoz&amp;count=none' target='_blank'><i class='icon-google-plus'>&nbsp;Love on Google+</i></a><br/><br/></LI></UL>";
    $html .= "<div align=\"center\"><form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='_blank'><input type='hidden' name='cmd' value='_s-xclick'><input type='hidden' name='hosted_button_id' value='XRC95RT6YJDTG'><input type='image' src='https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif' border='0' name='submit' alt='PayPal - The safer, easier way to pay online!'><img alt='' border='0' src='https://www.paypalobjects.com/en_US/i/scr/pixel.gif' width='1' height='1'></form><br/><strong>Support SecureMoz <font color='green'>FREE</font></strong><br/><br/></div>";
    $html .= '<div align="left"><p><a href="http://webmaster.securehunter.com/" target="_blank" title="SSL & Security/Hosting/Marketing Tools"><strong>Webmaster Toolkit</strong></a></p>';    
	$html .= '<p><a href="http://store.templatemonster.com/wordpress-themes.php?aff=securemoz" target="_blank" title="Premium WordPress Theme"><strong>Premium WordPress Themes</strong></a></p></div>'; 
    $html .=  "<img src=\"". $this->imgdir."waudit_logo_200.png\" style=\"position: relative; margin-top: 30px; margin-left: 20%;\" />";
    $html .= '<div align="center">SecureMoz &copy; '.date('Y').' Powered by<br/> <a href="http://www.securehunter.com" target="_blank" title="Secure Hunter Anti-Malware">SecureHunter.com &reg;</a><br/> </div>';
    $html .=  $this->postboxer('bottom');
	return $html;
}
public function postboxer($toporbottom=false,$head="",$img=false) {
	if($toporbottom == "top") {
		$html = '<div class="postbox"><h3>';
		$html .= ($img) ? $this->H->gtml_img($img.'.png') : false;
		$html .= '<span>'.$head.'</span></h3><div class="inside">';
		return $html;
	} elseif ($toporbottom == "bottom") {
		return '</div></div>';
	}
}
public function postbox_col($toporbottom=false,$marginr=0,$percent=0) {
	if($toporbottom == "top") {
		return "<div style=\"width:{$percent}%; margin-right:{$marginr}%; float:left;\">";
	} elseif ($toporbottom == "bottom") {
		return "</div>";
	}
}
public function message($m,$t) {
	#return '<div id="message" class="'.$t.'"><p>'.$m.'</p></div>';
	echo '<div id="message" class="'.$t.'"><p>'.$m.'</p></div>';
}
public function waudit_update_option($n,$v) {
	// 64 is the wp option key limit, boy
	$strlen = strlen($this->pp.'_'.$n);
	if ($strlen>64) {
		$this->message("Option key $n lenght is $strlen, this will not work. Developers mistake.",'error');
		return;
	}
	// getting rid of false positvives occuring when new walue is the same as previous
	$current_val = $this->waudit_get_option($n);
	if ($current_val===$v) {
		return true;
	}
	// or update the option
	return update_option($this->pp.'_'.$n, $v);
}



// if called in db context,
// Wordpress needs refesh to understand change of constants
// This updates all suboptions of a test in".$wpdb->prefix."_options table.
public function update_all_suboptions($name,$result) {

	// db
	if (!isset($this->T) || empty($this->T)) {
		$this->T = new waudit_Test;
	}

	if($f = $this->T->get_test_field($name, 'fix')) {
		if(strpos($f, 'SITEURL')===false) {
			$fix = site_url().'/wp-admin/admin.php?page='.$this->plugin_slug.'&tab='.$f;
		} else {
			$fix = str_replace('SITEURL', $this->root_url, $f);
		}
		$fix_wp = false;
	} elseif($f = $this->T->get_test_field($name, 'fix_wp')) {
		if(strpos($f, 'SITEURL')===false) {
			$fix_wp = site_url().'/wp-admin/admin.php?page='.$this->plugin_slug.'&tab='.$f;
		} else {
			$fix_wp = str_replace('SITEURL', $this->root_url, $f);
		}
		$fix = false;
	} else {
		$fix = false;
		$fix_wp = false;
	}
	if( $this->T->get_test_field($name, 'fix_tip') !== false 
		&& $this->T->get_test_field($name, 'fix_tip_url') !== false) {
		if(strpos($this->T->get_test_field($name, 'fix_tip_url'), 'SITEURL')===false) {
			$fixtu = site_url().'/wp-admin/admin.php?page='.$this->plugin_slug.'&tab='.$this->T->get_test_field($name, 'fix_tip_url');
		} else {
			$fixtu = str_replace('SITEURL', site_url(), $this->T->get_test_field($name, 'fix_tip_url'));
		}
		$fixt = $this->T->get_test_field($name, 'fix_tip');
	} else {
		$fixt = false;
		$fixtu = false;
	}

	$this->waudit_update_option($name."_result",$result);
	$this->waudit_update_option($name."_fix",$fix);
	$this->waudit_update_option($name."_fix_wp",$fix_wp);
	$this->waudit_update_option($name."_fix_tip",$fixt);
	$this->waudit_update_option($name."_fix_tip_url",$fixtu);
}
public function waudit_get_option($n) {
	return get_option($this->pp.'_'.$n);
}
public function validate_form_nonce($t) {
	if(isset($_POST['waudit_nonce_'.$t])){
		if ( !empty($_POST) && wp_verify_nonce( $_POST['waudit_nonce_'.$t],'waudit_'.$t)) {
			return true;
		}
	} else {
		return false;
	}

}

public function get_audit_score_number() {

	// If it has never run, then show score as 0
	if ($this->plugin_get_version() !== $this->waudit_get_option("plugin_version_last_run_all_tests") && !$this->is_setting_on('system','ignore_plugin_version')) {
		return '0';
	}

	global $wpdb;

	$con=mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
	// Check connection
	if (mysqli_connect_errno()) {
		// Could not connect
		return "Error: Could not access score";
	}

	$query = "SELECT option_value FROM {$wpdb->prefix}options WHERE option_name REGEXP '^{$this->pp}_test.*._result$' ";
	$result = mysqli_query($con,$query);

	$total = 0;
	$bad = 0;
	while($r = mysqli_fetch_array($result)) {
		$total++;
		$bad = $bad + $r[0];
	}

	mysqli_close($con);


	if ($total==0)
		$score = 0;
	else
		$score = (100-round(($bad*100)/$total, 0));

	return array($total,$bad,$score);
	
}

public function get_audit_score() {
	
	$data 	= $this->get_audit_score_number();
	if (is_array($data)) {
		$total 	= $data[0];
		$bad 	= $data[1];
		$score 	= $data[2];
	} else {
		$total = $bad = $score = 0;
	}
	
	
	$html = "<h2>Your system is <strong>{$score}%</strong> secure.</h2><p>The score is determined by running {$total} unique tests, out of which {$bad} reported security threats.</p>";
	if($score > 0 && $score <= 20) {
		$html .= "<p class=\"color-red\"><i class=\"icon-flag-alt\"></i>&nbsp; Your system is damaged, insecure and a full of safety issues.</p><p> It is highly recommended to use this plugin to fix them.</p>";
	} elseif($score >= 21 && $score <= 50) {
		$html .= "<p class=\"color-red\"><i class=\"icon-thumbs-down-alt\"></i>&nbsp;Your system is not secure, there are some safety issues.</p><p> It is highly recommended to use this plugin to fix them.</p>";
	} elseif($score >= 51 && $score <= 70) {
		$html .= "<p class=\"color-orange\"><i class=\"icon-smile\"></i>&nbsp;<font color='#FF6347'>Your system is almost secure.</font></p><p> There is a low risk of getting hacked while keeping system as healthy as yours. It is still recommended to fix as many security threats as possible.</p>";
	} elseif($score >= 71 && $score <= 100) {
		$html .= "<p class=\"color-green\"><i class=\"icon-thumbs-up-alt\"></i>&nbsp;Your system is secure.</p><p> There is a very low risk of getting hacked while keeping system as healthy as yours.</p><p><a href='http://twitter.com/share?text=My%20site%20gets%20{$score}/100%20of%20%23security%20%23score!%20Try%20yours!!&amp;url=https://securemoz.com&amp;via=securemoz&amp;count=none' target='_blank'><i class=\"icon-twitter\"></i>&nbsp;Share your score!</p>";
	}

	return $html;
}


/*
*	Returns array with lines that had one or more needles in them
*/
public function loop_array_each_file_line( $file, $needles, $file_mode, $addtoneedleleft) {
	$results = array();
	$handle = @fopen($file, $file_mode);
	if ($handle) {
		// Read line by line
		while (($row = fgets($handle)) !== false) {
			// Search for each needle
			foreach ($needles as $needle) {
				// Needle found
				if(strpos($row,$addtoneedleleft.$needle)!==false){
					$results[$needle] = $row;
				}
			}
		}
	}
	return $results;
}
// return bool or array
public function extract_keys($returnarray=false) {
	$matches = array();
	$salts_any = array();
	$salts_good = 0;
	$found_keys = $this->loop_array_each_file_line($this->root_path.'wp-config.php', $this->keys,'r',"define('");
	foreach ($found_keys as $key => $row) {
		// Is it?: define('AUTH_KEY',         'put your unique phrase here');
		if($c = preg_match("/(define\(')(".$key.")(',\s*')(.*)('\);\s*)$/", $row, $matches)) {
			// Double check if key is key
			if($matches[2]==$key) {
				$salt = $matches[4];
				$salts_any[$key]=$salt;
				if(strlen($salt) > 10 && $salt!=='put your unique phrase here' ) {
					
					$salts_good += 1;

				}
			}
		}
	}
			
	if($returnarray===true) {
		return $salts_any;
	} elseif($returnarray===false) {

		$tot = count($found_keys);
		return ($tot <= 7 || $salts_good <= 7) ? 1 : 0;

	}
}
public function test_wpconfig_for_empty_keys() {
	return ($this->extract_keys($returnarray=false)) ? 1 : 0;
}

public function count_files($dir,$return='count') {
	$count = 0;
	$size = 0;
	if ($handle = opendir($dir)) {
	    while (false !== ($entry = readdir($handle))) {
	        if ($entry != "." && $entry != "..") {
	            $count++;
	            $size = $size+filesize($dir.'/'.$entry);
	        }
	    }
	    closedir($handle);
	}
	if($return=='count') {
		return $count;
	} elseif ($return=='size') {
		return $size;
	}
}
/*public function recurse_copy($src,$dst) { 

    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) { 
        	if ($this->is_setting_on('general','dont_bck_bckp_directory') && strpos($src.'/'.$file, '/'.$this->plugin_slug.'/'.$this->plugin_backupdir) !== false  ) {
        		// Do not back up backup directory
        		continue;
        	}
            if ( is_dir($src.'/'.$file) ) { 
                $this->recurse_copy($src.'/'.$file,$dst.'/'.$file); 
            } 
            else { 
                copy($src.'/'.$file,$dst.'/'.$file); 
            } 
        } 
    } 
    closedir($dir); 
    return true;
} */



/**
 *	Replace all date symbols with actual current date values, return string
 */
public function string_date_symbol_parser($string) {
	
	$expl = explode('%', $string); 
	$chars = array_splice($expl, 1); 

	// Replace every 1st character with date() equivalent (row 1 already removed)
	foreach ($chars as $k => $v) {
		$string = str_replace("%".$v[0], date($v[0]), $string);
	}

	return $string;
}

/**
 *	Replace all date symbols with actual current date values, return string
 */
public function WPAdminMenuBarMenuInit() {
    global $waudit_WPAdminMenuBar;
    $waudit_WPAdminMenuBar = new waudit_WPAdminMenuBar();
} 

/*
Server verifire
*/
public function server_verifire($f) {
  $request = "http://wpsecurity.securemoz.com/smwpchecker.php?verify=".$_SERVER['SERVER_NAME'].$f;
  $data = file_get_contents($request);
  return $data; 
}

public function malware_verifire($f) {
  $request = "http://wpsecurity.securemoz.com/smwpmalware.php?verify=".$_SERVER['SERVER_NAME'].$f;
  $data = file_get_contents($request);
  return $data; 
}
}
#endif;


$GakplSecurityAudit = new GakplSecurityAudit;
$GakplSecurityAudit->init();

?>
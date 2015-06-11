<?php

#if (!class_exists('waudit_Test')):

Class waudit_Test extends GakplSecurityAudit  {

	/**
	 * All test info: Slug, description, possible txt values for all results, information about available fixes ('' where none)
	 * @var array
	 */
	private $all_tests = array(	
		'test_register_globals' => array(
			'description' => 'PHP register globals',
			1 => 'PHP "register_globals" is turned on',
			0 => 'PHP "register_globals" is turned off',
			'fix' => '',
			'fix_wp' => '',
			'fix_tip' => 'PHP internal setting register_globals registers the $REQUEST arrays elements as variables automatically. <br/>						
				You can disable register_globals in the php.ini file:<br/>
				<code>register_globals = \'off\'</code> <br/>
				The setting can also be disabled in Apache httpd.conf file or .htaccess file:<br/>
				<code>php_flag  register_globals  off</code><br/>
				This feature has been deprecated as of PHP 5.3.0 and removed as of PHP 5.4.0.',
			'fix_tip_url' => 'options_server',
			),
		'test_safe_mode' => array(
			'description' => 'PHP safe mode',
			1 => 'PHP safe mode is turned on',
			0 => 'PHP safe mode is turned off',
			'fix' => '',
			'fix_wp' => '',
			'fix_tip' => 'Safe Mode does a user check on files that are opened by a process. <br/> 
				Safe Mode user restrictions can interfere with the intended functioning of your PHP files. <br/>
				You can disable safe_mode in the php.ini file:<br/>
				<code>safe_mode = \'off\'</code> <br/>
				The setting can also be disabled in Apache httpd.conf file or .htaccess file:<br/>
				<code>php_flag  safe_mode  off</code><br/>
				This feature has been deprecated as of PHP 5.3.0 and removed as of PHP 5.4.0.',
			'fix_tip_url' => 'options_server',
			),
		'test_allow_url_fopen' => array(
			'description' => 'PHP URL file open',
			1 => 'PHP allows URL file opens',
			0 => 'PHP does not allow URL file open',
			'fix' => '',
			'fix_wp' => '',
			'fix_tip' => 'Setting allow_url_fopen allows functions such as file_get_contents(), include() etc. retrieve data from remote locations.
				You can disable allow_url_fopen in the php.ini file:<br/>
				<code>allow_url_fopen = \'off\'</code> <br/>
				The setting can also be disabled in Apache httpd.conf file or .htaccess file:<br/>
				<code>php_flag  allow_url_fopen  off</code>',
			'fix_tip_url' => 'options_server',
			),
		'test_allow_url_include' => array(
			'description' => 'PHP URL includes',
			1 => 'PHP allows URL includes',
			0 => 'PHP does not allow URL includes',
			'fix' => '',
			'fix_wp' => '',
			'fix_tip' => 'This option allows the use of URL-aware fopen wrappers with the following functions: include, include_once, require, require_once.<br/> 
				It is insecure, inefficient, unreliable and unnecessary. <br/>
				You can disable allow_url_include in the php.ini file:<br/>
				<code>allow_url_include = \'off\'</code> <br/>
				The setting can also be disabled in Apache httpd.conf file or .htaccess file:<br/>
				<code>php_flag  allow_url_include  off</code>',
			'fix_tip_url' => 'options_server',
			),
		'test_display_errors' => array(
			'description' => 'PHP error reporting',
			1 => 'PHP displays errors',
			0 => 'PHP does not display errors',
			'fix' => '',
			'fix_wp' => '',
			'fix_tip' => 'The display_errors directive determines whether error messages should be sent to the browser.<br/> 
				These messages frequently contain sensitive information about your web application environment, and should never be presented to untrusted sources. <br/>
				You can disable allow_url_include in the php.ini file:<br/>
				<code>display_errors = \'off\'</code> <br/>
				<code>log_errors = \'on\'</code> <br/>
				The setting can also be disabled in Apache httpd.conf file or .htaccess file:<br/>
				<code>php_flag  display_errors  off</code><br/>
				<code>php_flag  log_errors  on</code>',
			'fix_tip_url' => 'options_server',
			),
		'test_expose_php' => array(
			'description' => 'PHP exposure',
			1 => 'PHP is exposed',
			0 => 'PHP is not exposed',
			'fix' => '',
			'fix_wp' => '',
			'fix_tip' => 'When enabled, expose_php reports in every request that PHP is being used to process the request, and what version of PHP is installed.<br/>
				You can disable expose_php in the php.ini file:<br/>
				<code>expose_php = \'off\'</code>',
			'fix_tip_url' => 'options_server',
			),
		'test_editor' => array(
			'description' => 'Default editor for themes and plugins',
			1 => 'Wordpress file editor is enabled',
			0 => 'Wordpress file editor is disabled',
			'fix' => 'options_configphp',
			'fix_wp' => '',
			'fix_tip' => '',
			),
		'test_debug' => array(
			'description' => 'Debug Global mode',
			1 => 'Debug Global mode is enabled',
			0 => 'Debug Global mode is disabled',
			'fix' => 'options_configphp',
			'fix_wp' => '',
			'fix_tip' => '',
			),
		'test_debug_scripts' => array(
			'description' => 'Debug scripts mode',
			1 => 'Debug scripts mode is enabled',
			0 => 'Debug scripts mode is disabled',
			'fix' => 'options_configphp',
			'fix_wp' => '',
			'fix_tip' => '',
			),
		'test_debug_mysqli' => array(
			'description' => 'Debug database trace',
			1 => 'Debug database trace is enabled',
			0 => 'Debug database trace is disabled',
			'fix' => 'options_configphp',
			'fix_wp' => '',
			'fix_tip' => '',
			),
		'test_users_can_register' => array(
			'description' => 'New user registration',
			1 => 'Users can register',
			0 => 'Users cannot register',
			'fix' => '',
			'fix_wp' => '/wp-admin/options-general.php',
			'fix_tip' => '',
			),
		'test_wordpress_up_to_date' => array(
			'description' => 'Wordpress version',
			1 => 'There is a newer version of Wordpress',
			0 => 'Wordpress is at the newest version',
			'fix' => '',
			'fix_wp' => '/wp-admin/update-core.php',
			'fix_tip' => '',
			),
		'test_plugins_up_to_date' => array(
			'description' => 'Wordpress installation outdated plugins',
			1 => 'Outdated plugins found',
			0 => 'All plugins are up to date',
			'fix' => '',
			'fix_wp' => '/wp-admin/update-core.php',
			'fix_tip' => '',
			),
		'test_themes_up_to_date' => array(
			'description' => 'Wordpress installation outdated themes',
			1 => 'Outdated themes found',
			0 => 'All themes are up to date',
			'fix' => '',
			'fix_wp' => '/wp-admin/update-core.php',
			'fix_tip' => '',
			),
		'test_admin_exists' => array(
			'description' => 'Default user "admin"',
			1 => 'User admin exists',
			0 => 'User admin does not exist',
			'fix' => 'options_user',
			'fix_wp' => '',
			'fix_tip' => '',
			),
		'test_db_prefixes' => array(
			'description' => 'Wordpress database prefix',
			1 => 'Database prefix is default or easy to guess',
			0 => 'Database prefix is safe',
			'fix' => 'options_database',
			'fix_wp' => '',
			'fix_tip' => '',
			),
		'test_file_install' => array(
			'description' => 'File install.php public availability',
			1 => 'File install.php is public',
			0 => 'File install.php is not public',
			'fix' => 'options_server',
			'fix_wp' => '',
			'fix_tip' => '',
			),
		'test_file_upgrade' => array(
			'description' => 'File upgrade.php public availability',
			1 => 'File upgrade.php is public',
			0 => 'File upgrade.php is not public',
			'fix' => 'options_server',
			'fix_wp' => '',
			'fix_tip' => '',
			),
		'test_file_readme' => array(
			'description' => 'File readme.html public availability',
			1 => 'File readme.html is public',
			0 => 'File readme.html is not public',
			'fix' => 'options_server',
			'fix_wp' => '',
			'fix_tip' => '',
			),
		'test_if_html_contains_generator' => array(
			'description' => 'Wordpress version public availability',
			1 => 'Wordpress version is public',
			0 => 'Wordpress version is not public',
			'fix' => 'options_realtime',
			'fix_wp' => '',
			'fix_tip' => '',
			),
		'test_wpconfig_for_empty_keys' => array(
			'description' => 'Keys in wp-config.php',
			1 => 'Security keys in wp-config.php are too short, unsafe or need to be defined',
			0 => 'Security keys in wp-config.php has been defined',
			'fix' => 'options_configphp',
			'fix_wp' => '',
			'fix_tip' => '',
			),
		'test_htaccess_secured_wpconfig' => array(
			'description' => 'File wp-config.php security blocks',
			1 => 'File .htaccess has not defined security of this file',
			0 => 'File .htaccess has defined security of this file',
			'fix' => 'options_configphp',
			'fix_wp' => '',
			'fix_tip' => '',
			),
		'test_htaccess_secured_htaccess' => array(
			'description' => 'File .htaccess security blocks',
			1 => 'File .htaccess has not defined security of this file',
			0 => 'File .htaccess has defined security of this file',
			'fix' => 'options_configphp',
			'fix_wp' => '',
			'fix_tip' => '',
			),
		'test_htaccess_secure_dirs' => array(
			'description' => 'Directory listing',
			1 => 'File .htaccess enables directory listing',
			0 => 'File .htaccess disables directory listing',
			'fix' => 'options_configphp',
			'fix_wp' => '',
			'fix_tip' => '',
			),
		'test_chmod_all_safe' => array(
			'description' => 'All important directory permissions',
			1 => 'Some files or directories have unsafe permissions',
			0 => 'All important file and directory permissions are safe',
			'fix' => 'options_server',
			'fix_wp' => '',
			'fix_tip' => '',
			),
		'test_save_queries' => array(
			'description' => 'Save queries',
			1 => 'Queries are saved',
			0 => 'Queries are not saved',
			'fix' => 'options_database',
			'fix_wp' => '',
			'fix_tip' => '',
			),
		'test_trash_exist' => array(
			'description' => 'Trash for posts and pages',
			1 => 'Trashed posts are not saved and cannot be recovered',
			0 => 'Trashed posts are saved and can be recovered.',
			'fix' => 'options_configphp',
			'fix_wp' => '',
			'fix_tip' => '',
			),
		);

public function __construct() {

	// Seems a bit stupid.
	parent::__construct();

	// Fatal error: Call to a member function recurse_copy() on a non-object in /localhost/dev/akasseindex.dk/wp-content/plugins/gakpl-security-audit/class_GakplSecurityAudit.php on line 978
	if (!isset($this->H) || empty($this->H)) {
		$this->H = new waudit_Helper;
	}

	 /*if (__CLASS__ != get_called_class()) {
            echo "Called from child  (" . get_called_class() . ")\n";
        } else {
            echo "Called from parent (" . __CLASS__ . ")\n";
        }*/
}

public function get_all_tests() {
	return $this->all_tests;
}

public function perform_test($name=false) {

/*	$backtrace = debug_backtrace();
	echo "<pre>";
	print_r($backtrace);
	echo "</pre>";*/

	if($name == 'all') {

		// Last version run option
		// If last version differs from current	update database and etc
		if ($this->plugin_get_version() !== $this->waudit_get_option("plugin_version_last_run_all_tests") && !$this->is_setting_on('system','ignore_plugin_version')) {
			#echo 'Waudit will now attempt to back up you files and database.';
			if ($this->do_pre_first_time_actions() === false) {
				return $this->message('Attempt to create initial backups failed!<br/> Audit not performed.','error');
			}
		}

		// Add that this version has been run
		// /!\ Disable v Reset
		$this->waudit_update_option('plugin_version_last_run_all_tests',$this->plugin_get_version());

		$this->waudit_update_option($name,date('d.m.Y G:i'));

		// Later this allows to determine wheteher or not display pre-first-test form
		foreach($this->get_all_tests() as $test_name => $test_details) {
			$result = $this->$test_name();
			$this->update_all_suboptions($test_name,$result);
		}
	} else {
			$result = $this->$name();
			$this->update_all_suboptions($name,$result);
	}

			return $result;
}

 
/**
 * Get information about single test
 * @param  string $name  
 * @param  int/string $field [description,1,0,fix,fix_wp,fix_tip,fix_tip_url]
 * @return mixed [string/int on success, bool false on fail]
 */
public function get_test_field($name, $field) {
	$tests = $this->get_all_tests();
	// If got input and tests
	if (is_array($tests) 
		&& !empty($tests)
		&& isset($name)
		&& isset($field) ) {
			// If actual value exists
			if (array_key_exists($name, $tests)
				&& is_array($tests[$name])
				&& array_key_exists($field, $tests[$name])) {
					$v = $tests[$name][$field];
					return (isset($v) && !empty($v)) ? $v : false;
			}
	}
}

/**
 * Get corresponding color to status code
 * @param  int    $int [status code of each test]
 * @return string / bool false on fail
 */
public function get_test_color($int) {
	return (array_key_exists($int, $this->result_colors)) ? $this->result_colors[$int] : false;
}

/**
 * Get result for any test
 * @param  string $name 
 * @return string / bool false on fail
 */
public function get_test_result($name) {
	$result = $this->waudit_get_option($name."_result");
	return (isset($result)) ? $result : false;
}

 

// ______________________________________________
//   ______    _____       __    ______      __  
//     /       /    '    /    )    /       /    )
// ---/-------/__--------\--------/--------\-----
//   /       /            \      /          \    
// _/_______/____ ____(____/____/_______(____/___
                                              
      
public function test_register_globals() {
	return ((bool) ini_get('register_globals')) ? 1 : 0;	
}
public function test_allow_url_fopen() {
	return ((bool) ini_get('allow_url_fopen')) ? 1 : 0;
}
public function test_allow_url_include() {
	return ((bool) ini_get('allow_url_include')) ? 1 : 0;	
}
public function test_safe_mode() {
	return ((bool) ini_get('safe_mode')) ? 1 : 0;	
}
public function test_expose_php() {
	return ((bool) ini_get('expose_php')) ? 1 : 0;	
}
public function test_display_errors() {
	return ((bool) ini_get('display_errors')) ? 1 : 0;	
}
public function test_editor() {
	return (!defined('DISALLOW_FILE_EDIT') || DISALLOW_FILE_EDIT===false) ? 1 : 0;	
}
public function test_debug() {
	return (!defined('WP_DEBUG') || WP_DEBUG===true) ? 1 : 0;	
}
public function test_debug_scripts() {
	return (!defined('SCRIPT_DEBUG') || SCRIPT_DEBUG!==false) ? 1 : 0;	
}
public function test_debug_mysqli() {
	global $wpdb;
	return (!defined('MYSQLI_DEBUG_TRACE_ENABLED') || MYSQLI_DEBUG_TRACE_ENABLED===true ) ? 1 : 0;	
}
public function test_save_queries() {
	return (!defined('SAVEQUERIES') || SAVEQUERIES!==false) ? 1 : 0;	
}
public function test_trash_exist() {
	return (!defined('EMPTY_TRASH_DAYS') || EMPTY_TRASH_DAYS===0) ? 1 : 0;	
}
public function test_users_can_register() {
	return ((bool) get_option('users_can_register')) ? 1 : 0;	
}	
public function test_wordpress_up_to_date() {
	// $current = get_bloginfo( 'version', 'Display' );
	if (!function_exists('get_core_updates')) {
		require_once(ABSPATH.'/wp-admin/includes/update.php');
	}
	$updates = get_core_updates();
	if($updates) {
		return ($updates[0]->response != 'latest') ? 1 : 0;
	} else {
		return 0;
	}
}
public function test_plugins_up_to_date() {
	$info = get_site_transient('update_plugins');
	return (is_object($info) && is_array($info->response) && !empty($info->response)) ? 1 : 0;
}
public function test_themes_up_to_date() {
	$info = get_site_transient('update_themes');
	return (is_object($info) && is_array($info->response) && !empty($info->response)) ? 1 : 0;
}		
public function test_admin_exists() {
	return (username_exists('admin') || username_exists('Admin')) ? 1 : 0;
}			
public function test_db_prefixes() {
	global $wpdb;
	$popular_prefixes = array('pw_','wp_','db_','wp1_','wp2_','wp3_','wp4_','wp5_','wp6_','wp7_','wp8_','wp9_','wp0_');
	return (in_array($wpdb->prefix, $popular_prefixes)) ? 1 : 0;
}			
public function test_file_upgrade() {
	$remote = wp_remote_get(get_bloginfo('wpurl').'/wp-admin/upgrade.php');
	return ($remote['response']['code'] == 200) ? 1 : 0;
}			
public function test_file_install() {
	$remote = wp_remote_get(get_bloginfo('wpurl').'/wp-admin/install.php?');
	return ($remote['response']['code'] == 200) ? 1 : 0;
}			
public function test_file_readme() {
	$remote = wp_remote_get(get_bloginfo('wpurl').'/readme.html');
	return ($remote['response']['code'] == 200) ? 1 : 0;
}
public function test_if_html_contains_generator($find='meta[name=generator]') {
	$html = file_get_html(get_bloginfo('wpurl'));

	if($html) {
		return ($html->find($find)) ? 1 : 0;
	}
}
public function check_if_file_contains($path,$lines,$reverse_return=false) {
	if(file_exists($path)) {
		$file = file_get_contents($path);
		if($reverse_return===true) {
			return ($file && strpos($file,$lines)!==false) ? 1 : 0 ;
		} else {
			return ($file && strpos($file,$lines)!==false) ? 0 : 1 ;
		}
	}
	return 1;
}

public function test_htaccess_secured_htaccess() {
	$path = ABSPATH.'.htaccess';
	$lines = "<Files .htaccess>\n	order allow,deny\n	deny from all\n</Files>";
	return $this->check_if_file_contains($path,$lines);
}
public function test_htaccess_secured_wpconfig() {
	$path = ABSPATH.'.htaccess';
	$lines = "<Files wp-config.php>\n	order allow,deny\n	deny from all\n</Files>";
	return $this->check_if_file_contains($path,$lines);
}
public function test_htaccess_secure_dirs() {
	$path = ABSPATH.'.htaccess';
	$lines = "Options -Indexes";
	return $this->check_if_file_contains($path,$lines);
}
public function test_chmod_all_safe() {
	$tot = 0;
	$good = 0;
	foreach ($this->path_chmod as $path => $perm) {
		$path = ABSPATH.$path;
		if(file_exists($path)) {
			$perm = decoct($perm);
			$cperm = substr(decoct(fileperms($path)), -3);
			$good = ($perm>=$cperm) ? $good+1 : $good;
			$tot++;
		}
	}

	if($tot===0 || $tot!==$good) {
		return 1;
	} elseif($tot===$good) {
		return 0;
	}

	return 1;		
}
public function do_form_admin_form_configphp_debug1() {
	return $this->do_form_admin_form_configphp_debug('test_debug', "define('WP_DEBUG', true);", "if(!defined('WP_DEBUG'))\n	define('WP_DEBUG', false);",1);
}
public function do_form_admin_form_configphp_debug2() {
	return $this->do_form_admin_form_configphp_debug('test_debug_mysqli', "define('MYSQLI_DEBUG_TRACE_ENABLED', true);", "if(!defined('MYSQLI_DEBUG_TRACE_ENABLED'))\n	define('MYSQLI_DEBUG_TRACE_ENABLED', false);",1);
}
public function do_form_admin_form_configphp_debug3() {
	return $this->do_form_admin_form_configphp_debug('test_debug_scripts',"define('SCRIPT_DEBUG', true);", "if(!defined('SCRIPT_DEBUG'))\n	define('SCRIPT_DEBUG', false);",1);
}
public function do_form_admin_form_configphp_debug4() {
	return $this->do_form_admin_form_configphp_debug('test_editor',"define('DISALLOW_FILE_EDIT', false);", "if(!defined('DISALLOW_FILE_EDIT'))\n	define('DISALLOW_FILE_EDIT', true);",1);
}
public function do_form_admin_form_configphp_admin_form_configphp_save_queries() {
	return $this->do_form_admin_form_configphp_debug('test_save_queries',"define('SAVEQUERIES', true);", "if(!defined('SAVEQUERIES'))\n	define('SAVEQUERIES', false);",1);
}
public function do_form_admin_form_configphp_trash_exist() {
	return $this->do_form_admin_form_configphp_debug('test_trash_exist',"define('EMPTY_TRASH_DAYS', 0 );", "if(!defined('EMPTY_TRASH_DAYS'))\n	define('EMPTY_TRASH_DAYS', 365 );",1);
}





}
#endif;
?>
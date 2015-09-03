<?php

#if (!class_exists('waudit_ContextualHelpMenu')):

Class waudit_ContextualHelpMenu extends GakplSecurityAudit {

	public function __construct() {

		// If we are in the right page ...
		if (array_key_exists('page', $_GET) && $_GET['page'] == $this->plugin_slug) {
			
			// Any tab
			#add_action('contextual_help', array($this, 'ghtm_help_menu_tab_any'), 10, 3);

			// Contextual help on specific tabs
			if (array_key_exists('tab', $_GET) && method_exists($this, 'ghtm_help_menu_tab_'.$_GET['tab'])) {

				// This tab has some help available! :)
				add_action('contextual_help', array($this, 'ghtm_help_menu_tab_'.$_GET['tab']), 10, 3);

			}

		}
	}


	// Tab: Any
	public function ghtm_help_menu_tab_any( $contextual_help, $screen_id, $screen ) {
		
	    if (!method_exists($screen,'add_help_tab'))
	        return $contextual_help;

	    // Add help panel
	    $screen->add_help_tab( array(
	        'id'      => 'waudit-menu-1',
	        'title'   => 'Welcome',
	        'content' => "Welcome! <br/> To browse the help section, please, navigate using list of subjects on the left. <br/> Only subjects regarding the current open page are displayed.",
	    ));
		    
	}	


	// Tab: Backup
	public function ghtm_help_menu_tab_options_backup( $contextual_help, $screen_id, $screen ) {
		
	    if (!method_exists($screen,'add_help_tab'))
	        return $contextual_help;

	    // Add help panel
	    $screen->add_help_tab( array(
	        'id'      => 'waudit-menu-2',
	        'title'   => 'Date Symbols',
	        'content' => "File names consist of alphanumeric characters and date symbols used to display parts of current date and time.<br/>
	        			These symbols consist of percentage sign (%) followed by a letter.<br/><br/>
						<ul class=\"narrowlist\">
							<li> %a - \"am\" or \"pm\" </li>
							<li> %A - \"AM\" or \"PM\" </li>
							<li> %d - day of the month, 2 digits with leading zeros;  \"01\" to \"31\" </li>
							<li> %F - month as text, full name; \"January\" </li>
							<li> %h - hour, 12-hour format; \"01\" to \"12\" </li>
							<li> %H - hour, 24-hour format;  \"00\" to \"23\" </li>
							<li> %i - minutes;  \"00\" to \"59\" </li>
							<li> %L - boolean for if it is a leap year; \"0\" or \"1\" </li>
							<li> %m - month as a number; \"01\" to \"12\" </li>
							<li> %s - seconds;  \"00\" to \"59\" </li>
							<li> %U - seconds since the epoch </li>
							<li> %w - day of the week as a number; \"0\" (Sunday) to \"6\" (Saturday) </li>
							<li> %Y - year, 4 digits; \"1999\" </li>
							<li> %y - year, 2 digits;  \"99\" </li>
							<li> %z - day of the year;  \"0\" to \"365\" </li>
							</ul>
				       <p>And there is more! See full list of accepted characters on <a href=\"http://php.net/manual/en/function.date.php\" target=\"_blank\"> PHP.net </a>
	        			",
	    ));
		    
	}

	// Tab: Settings
	public function ghtm_help_menu_tab_options_settings( $contextual_help, $screen_id, $screen ) {
		
	    if (!method_exists($screen,'add_help_tab'))
	        return $contextual_help;

	    // Add help panel
	    $screen->add_help_tab( array(
	        'id'      => 'waudit-menu-3',
	        'title'   => 'Permissions in Octal',
	        'content' => "In a Linux and UNIX file systems permissions are a combination of three following abilities:

	        <ul class=\"narrowlist\">
				<li>Read (r)</li>
				<li>Write (w)</li>
				<li>Execute (x)</li>
 			</ul>

They can be represented in Octal notation:

<table class=\"wikitable\" style=\"text-align: center;\">
<tbody><tr>
<th>Symbolic Notation</th>
<th>Octal Notation</th>
<th>English</th>
</tr>
<tr>
<td style=\"text-align: left; padding-left: 5%;\">----------</td>
<td>0000</td>
<td style=\"text-align: left; padding-left: 5%;\">no permissions</td>
</tr>
<tr>
<td style=\"text-align: left; padding-left: 5%;\">---x--x--x</td>
<td>0111</td>
<td style=\"text-align: left; padding-left: 5%;\">execute</td>
</tr>
<tr>
<td style=\"text-align: left; padding-left: 5%;\">--w--w--w-</td>
<td>0222</td>
<td style=\"text-align: left; padding-left: 5%;\">write</td>
</tr>
<tr>
<td style=\"text-align: left; padding-left: 5%;\">--wx-wx-wx</td>
<td>0333</td>
<td style=\"text-align: left; padding-left: 5%;\">write &amp; execute</td>
</tr>
<tr>
<td style=\"text-align: left; padding-left: 5%;\">-r--r--r--</td>
<td>0444</td>
<td style=\"text-align: left; padding-left: 5%;\">read</td>
</tr>
<tr>
<td style=\"text-align: left; padding-left: 5%;\">-r-xr-xr-x</td>
<td>0555</td>
<td style=\"text-align: left; padding-left: 5%;\">read &amp; execute</td>
</tr>
<tr>
<td style=\"text-align: left; padding-left: 5%;\">-rw-rw-rw-</td>
<td>0666</td>
<td style=\"text-align: left; padding-left: 5%;\">read &amp; write</td>
</tr>
<tr>
<td style=\"text-align: left; padding-left: 5%;\">-rwxrwxrwx</td>
<td>0777</td>
<td style=\"text-align: left; padding-left: 5%;\">read, write, &amp; execute</td>
</tr>
</tbody></table>


"

,
	    ));
		    
	}

	public function gtml_help_menu_tab_screen( $contextual_help, $screen_id, $screen ) {
 
	    if (!method_exists($screen,'add_help_tab'))
	        return $contextual_help;
	 
	    global $hook_suffix;
	 
	    // List screen properties
	    $variables = '<ul style="width:50%;float:left;"> <strong>Screen variables </strong>'
	        . sprintf( '<li> Screen id : %s</li>', $screen_id )
	        . sprintf( '<li> Screen base : %s</li>', $screen->base )
	        . sprintf( '<li>Parent base : %s</li>', $screen->parent_base )
	        . sprintf( '<li> Parent file : %s</li>', $screen->parent_file )
	        . sprintf( '<li> Hook suffix : %s</li>', $hook_suffix )
	        . '</ul>';
	 
	    // Append global $hook_suffix to the hook stems
	    $hooks = array(
	        "load-$hook_suffix",
	        "admin_print_styles-$hook_suffix",
	        "admin_print_scripts-$hook_suffix",
	        "admin_head-$hook_suffix",
	        "admin_footer-$hook_suffix"
	    );
	 
	    // If add_meta_boxes or add_meta_boxes_{screen_id} is used, list these too
	    if ( did_action( 'add_meta_boxes_' . $screen_id ) )
	        $hooks[] = 'add_meta_boxes_' . $screen_id;
	 
	    if ( did_action( 'add_meta_boxes' ) )
	        $hooks[] = 'add_meta_boxes';
	 
	    // Get List HTML for the hooks
	    $hooks = '<ul style="width:50%;float:left;"> <strong>Hooks </strong> <li>' . implode( '</li><li>', $hooks ) . '</li></ul>';
	 
	    // Combine $variables list with $hooks list.
	    $help_content = $variables . $hooks;
	 
	    // Add help panel
	    $screen->add_help_tab( array(
	        'id'      => 'waudit-screen-help',
	        'title'   => 'Screen Information',
	        'content' => $help_content,
	    ));
	 
	    return $contextual_help;
	}








} 
#endif;
?>
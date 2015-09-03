<?php

/**
 * Run on plugin activation
 */
function waudit_register_hook_activate() {

    // Add schedules
    add_filter( 'cron_schedules', 'waudit_cron_add_custom_intervals' );

    // Create default directories
    $this->create_default_directory_paths();


}

/**
 * Run on plugin deactivation
 */
function waudit_register_hook_deactivate() {
        
    // Delete backup schedule

    $this->S->unset_cron('add_action_do_backup');
    
    // Remove schedules
    remove_filter( 'cron_schedules', 'waudit_cron_add_custom_intervals' );

}

/**
 * Run on plugin uninstallation
 * @todo Add uninstall.php(?) instead of this
 */
function waudit_register_hook_uninstall() {

    //if uninstall not called from WordPress exit
    if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
        exit ();    

    // delete default directories , R
    chmod($this->backupdir, 0777);
    if (is_dir($this->backupdir) && is_writable($this->backupdir))
        $this->H->delete_files($this->backupdir);

}




function waudit_cron_add_custom_intervals() {

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

?>
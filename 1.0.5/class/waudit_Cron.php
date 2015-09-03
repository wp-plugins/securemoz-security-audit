<?php

#if (!class_exists('waudit_Cron')):

Class waudit_Cron extends GakplSecurityAudit {

	public function __construct() {

		#add_action( 'add_action_do_backup', array( $this, 'action_do_backup') );

		#$this->S->add_cron(false, current_time('timestamp'), 'add_actiondobackupandemail3', null , 'hourly');
		#$this->S->unset_cron('add_actiondobackupandemailONE');


	}

	// @var $time Unix timestamp format
	// @var $recurrence 'hourly','twicedaily','daily' ; custom intervals using the cron_schedules filter in wp_get_schedules().
	// Args is for cron, $options array is passed to actions callback function
	#add_cron(false, time()+1, 'actiondobackupandemailONE', ņull, 'hourly');
	public function add_cron($execute_once=true, $time, $hook, $args=array(), $recurrence=null ) {

		if( $execute_once )
			#wp_schedule_single_event( time() + 3600, 'my_new_event' );
			wp_schedule_single_event( $time, $hook, $args );
		else
			#wp_schedule_event( time(), 'hourly', 'my_task_hook' );
			if (!wp_next_scheduled($hook)) {
				wp_schedule_event( $time, $recurrence, $hook, $args );
			} 

	}


	public function unset_cron($hook) {

		$crons = _get_cron_array();
		if ( empty( $crons ) ) {
			return;
		}
		foreach( $crons as $timestamp => $cron ) {
			if ( ! empty( $cron[$hook] ) )  {
				unset( $crons[$timestamp][$hook] );
			}
		}
		_set_cron_array( $crons );

	}

	// $args = 'objects' => whatto backup
	public function action_do_backup($args) {
/*		echo "<pre>";
		print_r($args);
		echo "</pre>";*/
		$this->do_form_admin_form_backup(true);

	}



}
#endif;
?>
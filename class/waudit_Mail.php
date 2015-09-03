<?php

#if (!class_exists('waudit_Mail')):

Class waudit_Mail {

	public $to;
	public $subject;
	public $attachments = array();
	public $message;
	public $headers;

	public function __construct() {
			
	}

	public function add_attachment( $string ) {
		return (file_exists($string)) ? array_push( $this->attachments, $string ) : false;
	}

	public function add_to( $string ) {
		$this->to = ($this->is_valid_email($string)) ? $string : false;
		return (bool) $this->to;
	}

	public function add_subject( $string ) {
		$this->subject = (isset($string) && !empty($string)) ? $string : false;
		return (bool) $this->to;
	}

	public function add_headers( $string ) {
		$this->headers = (isset($string) && !empty($string)) ? $string : false;
		return (bool) $this->to;
	}

	public function add_message( $string ) {
		$this->message = (isset($string) && !empty($string)) ? $string : false;
		return (bool) $this->to;
	}

	public function send() {

		// using wp_mail class
		return wp_mail( $this->to, $this->subject, $this->message, $headers, $this->attachments );

	}

	public function is_valid_email($email) {

		// using PHP filters
		return (filter_var($email, FILTER_VALIDATE_EMAIL)) ? true : false;

	}


	
}
#endif;
?>
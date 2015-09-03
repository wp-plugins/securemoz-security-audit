<?php

#if (!class_exists('waudit_View')):

Class waudit_API extends GakplSecurityAudit {

	public $url;	
	public $urly;
	public $internal_request = false;
 	public $operations = array('waudit'=> array('get_score', 'audit_run' ));

 	private $responses = array(
 						'success' => array(
 							'header' => 'HTTP/1.1 200 OK',
 							),
 						'fail' => array(
 							'header' => 'HTTP/1.1 400 Bad Request',
 							),
 						'empty' => array(
 							'header' => 'HTTP/1.1 200 OK',
 							),
 						);


	private $pid;

	function __construct() {
		
		// Setting variables
		$this->url = $_SERVER['REQUEST_URI'];
		$this->urly = $this->url_to_array($_SERVER['REQUEST_URI'],'/');

		// Add actions to init hook
		add_action( 'init', array( $this, 'listen_url' ) );
		
		// Fatal error: Call to a member function recurse_copy() on a non-object in /localhost/dev/akasseindex.dk/wp-content/plugins/gakpl-security-audit/class_GakplSecurityAudit.php on line 978
		if (!isset($this->H) || empty($this->H)) {
			$this->H = new waudit_Helper;
		}

		if (!isset($this->T) || empty($this->T)) {
			$this->T = new waudit_Test;
		}
	} 

	/**
	 * Scan url for specific operations
	 *
	 * Fire specific operation if found
	 */
	public function listen_url() {
		// Is there an operation candidate?
		if (is_array($this->urly)
			&& array_key_exists(0, $this->urly)
			&& isset($this->urly[0])
			&& !empty($this->urly[0])
			&& array_key_exists($this->urly[0], $this->operations)
			) {	

			foreach ($this->operations[$this->urly[0]] as $slug ) {

				$method_name = 'operation_'.$this->urly[0].'_'.$this->urly[1];

				if (method_exists($this, $method_name)) {
					return $this->$method_name(); 
				}	

			}

		}
	}








	// Operations
	



	/**
	 * 1. OPERATION get Score
	 * 
	 * @return JSON response
	 */
	public function operation_waudit_get_score() {

		if ( $this->internal_request || array_key_exists(1,$this->urly) ) {
			
			$score_data = $this->get_audit_score_number();
	
			if (is_array($score_data)) {
				$score = $score_data[2];
			} else {
				$score = $score_data;
			}


			if ($this->internal_request)
				return $score;
			else
				$this->jecho_response('success',array('score' => $score));

		} else {
			$this->jecho_response('fail',array('errors'=>"No such function in this category"));
		}

	}

	/**
	 * 2. OPERATION Audit Rerun
	 * 
	 */
	public function operation_waudit_audit_run() {

		if ( $this->internal_request || array_key_exists(1,$this->urly) ) {

			$this->T->perform_test('all');	
			#$this->jecho_response('success',$return);

			// Redirect user to page where he came from
			if (array_key_exists('redirect_previous', $_GET) && $_GET['redirect_previous']=="1") {
				wp_redirect( $_SERVER['HTTP_REFERER']);
				exit;
			}

		} else {
			$this->jecho_response('fail',array('errors'=>"No such function in this category"));
		}

	}













// HELPERS


 

	/**
	 * Check if user exisats by email
	 * @param  string $email
	 * @return object WP User Object on true / bool false on fail
	 */
	public function userdata_exists($email) {
		$pos_user_id = email_exists($email);
		$pos_userdata = get_userdata( $pos_user_id );
		if (is_numeric($pos_user_id)
			&& $pos_userdata !== false
			&& is_object($pos_userdata)
			) {
			return $pos_userdata;
		}
		return false;
	}

	public function url_to_array($url,$sep) {
		return explode($sep, trim($url, $sep));
	}

	public function url_get_string_to_array($url) {
		if ($this->startsWith($url, '?'))
			$url = substr($url, 1);
		$tmp1 = $this->url_to_array($url,'&');
		$tmp2 = array();
		foreach ($tmp1 as $keyvalue) {
			list($k, $v) = explode("=", $keyvalue);
			$tmp2[$k] = $v;
		}

		return (is_array($tmp2) && !empty($tmp2)) ? $tmp2 : false;
	}

	public function post_exists($pid) {
		return (get_post($pid)) ? true : false ;
	}

	public function jecho_response($status, $data=false) {

		header($this->responses[$status]['header']);
		header('Content-type: application/json; charset=utf-8');
		$response = array( 'status' => $status );
		if ($data !== false
			&& is_array($data)
			&& !empty($data)
			) {
				$response['data'] = $data;
		}
		//http://stackoverflow.com/questions/14881512/remove-double-quotes-from-json-array-and-fix-link
		#$json = preg_replace('/"([^"]+)"\s*:\s*/', '$1:', json_encode($response));
		die(json_encode($response));
	}

	/**
	 * Checks if value of $a[$k] is a valid post id
	 * @param  array  $a 
	 * @param  string/int  $k 
	 * @return boolean
	 */
	public function is_valid_post_id(array $a, $k) {
		if (array_key_exists($k,$a)
			&& isset($a[$k])
			&& is_numeric($a[$k])
			&& $this->post_exists($a[$k])
			){
			// Set it for use class-wide
			$this->pid = intval($a[$k]);
			return true;
		}
		return false;
	}

	private function get_xy() {
		if (array_key_exists('x', $_GET)
		&& !empty($_GET['x'])
		&& is_numeric($_GET['x'])
		&& array_key_exists('y', $_GET)
		&& !empty($_GET['y'])
		&& is_numeric($_GET['y'])
		) {
			return array($_GET['x'],$_GET['y']);
		}
		return false;
	}

	private function get_size() {
		if (array_key_exists('size', $_GET)
		&& !empty($_GET['size'])
		&& is_string($_GET['size'])
		) {
			return $_GET['size'];
		}
		return false;
	}


    /*--------------------------------------------------------------------------*/
    /*  String starts with needle
    /*--------------------------------------------------------------------------*/ 
    protected static function startsWith($haystack, $needle) {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    /*--------------------------------------------------------------------------*/
    /*  String ends with needle
    /*--------------------------------------------------------------------------*/ 
    protected static function endsWith($haystack, $needle) {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }

	

  }
?>
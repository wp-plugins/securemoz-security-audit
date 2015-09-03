<?php

#if (!class_exists('waudit_View')):

Class waudit_View extends GakplSecurityAudit {

	private $mem_sizes = array();

	public function __construct() {

		// Fatal error: Call to a member function recurse_copy() on a non-object in /localhost/dev/akasseindex.dk/wp-content/plugins/gakpl-security-audit/class_GakplSecurityAudit.php on line 978
		if (!isset($this->H) || empty($this->H)) {
			$this->H = new waudit_Helper;
		}

		$this->mem_sizes = $this->H->degree_array(2,22,34); // generates 4MB to 16GB array, in bytes
		$this->mem_sizes_js = implode(', ', $this->mem_sizes);


		add_filter('admin_head', array($this, 'add_my_script'));


	}









public function min_max_matching_post_metas_array($matching_post_metas_array) {
		global $filter_data;

		$temp = array();
		$out = array();

		foreach ($matching_post_metas_array as $post) {
			foreach ($post as $cfk => $cfv) {
				if(!is_array($temp[$cfk])) {
					$temp[$cfk] = array();
				}
				array_push($temp[$cfk], $cfv);
			}
		}

		foreach ($temp as $cfk => $values) {
			if(is_array($filter_data['slider'][$cfk])) {
				$filter_data['slider'][$cfk]['min'] = min($values);
				$filter_data['slider'][$cfk]['max'] = max($values);
			}
		}
}









public function add_my_script() {
		
	$general_settings = $this->get_settings('serverphp');

	$jq = "



	function humanFileSize_bytes(bytes) {
		var thresh = 1024;
	    if(bytes < thresh) return bytes + ' B';
	    var units = ['kB','MB','GB','TB','PB','EB','ZB','YB'];
	    var u = -1;
	    do {
	        bytes /= thresh;
	        ++u;
	    } while(bytes >= thresh);
	    return bytes.toFixed(0) + ' '+units[u];
	};

	function humanFileSize_seconds(seconds) {
		return seconds +'s';
	};

	function humanFileSize_(x) {
		return x;
	};
	";

	foreach ($general_settings as $setting_slug => $setting) {	

		if($s = $this->waudit_get_option('setting_serverphp_'.$setting_slug))
			$s = $s;
		else 
			$s = $this->get_settings_field('serverphp', $setting_slug, 'd');

		$jq .=	'


		//	-----------------------------------------

		// Range of bytes
			var range_bytes'.$setting_slug.' = [ '.$this->mem_sizes_js.' ];
			var range_seconds'.$setting_slug.' = [ 1, 3, 5, 10, 15, 30, 45, 60, 90, 120, 150, 180 ];
			var range_'.$setting_slug.' = [ 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30 ];
		';

		
		$min = (isset($setting['min'])) ? $setting['min'] : "-99";
			
		

		$jq .=	'
		//Add -1 or 0 as first val
		if ('.$min.'==0) {
			range_seconds'.$setting_slug.'.unshift(2);
			range_bytes'.$setting_slug.'.unshift(2);
			range_'.$setting_slug.'.unshift(2);
		}
		//Add -1 or 0 as first val
		if ('.$min.'==-1) {
			range_seconds'.$setting_slug.'.unshift(-1);
			range_bytes'.$setting_slug.'.unshift(-1);
			range_'.$setting_slug.'.unshift(-1);
		}
		
		var slider_'.$setting_slug.'  = $("#slider_'.$setting_slug.'")
		var default_index_'.$setting_slug.' = range_'.$setting['type'].$setting_slug.'.indexOf('.$s.');
		$( "#amount_'.$setting_slug.'" ).html( humanFileSize_'.$setting['type'].'(  '.$s.' ) );
		$( "#setting_serverphp_'.$setting_slug.'" ).val('.$s.' );

		//Call the Slider
		slider_'.$setting_slug.'.slider({

			range: "min",
			value: default_index_'.$setting_slug.',
			min: 0,
			max: range_'.$setting['type'].$setting_slug.'.length,

			//Slider Event
			slide: function(event, ui) { 
				var amount_'.$setting_slug.' = $( "#slider_'.$setting_slug.'" ).slider( "value" );
				$( "#amount_'.$setting_slug.'" ).html( humanFileSize_'.$setting['type'].'( range_'.$setting['type'].$setting_slug.'[amount_'.$setting_slug.'  ] ));
				$( "#setting_serverphp_'.$setting_slug.'" ).val(range_'.$setting['type'].$setting_slug.'[amount_'.$setting_slug.'  ] );
			},
			});';

		}

	
	
	?>
	<script type="text/javascript">

		jQuery(function($) {
			<?php echo $jq; ?>
		});

	
	</script>


	<?php
}










	
}
#endif;
?>
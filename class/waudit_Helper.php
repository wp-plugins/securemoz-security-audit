<?php
/*
*	Helpers
*	non-static methods used with instance in parent constructor
*/
class waudit_Helper extends GakplSecurityAudit {

	/**
	 * Are any of array items included in string?
	 * @param  string $string 
	 * @param  array $array  
	 * @return bool         
	 */
	public function string_contains_array($string, array $needles) {
	    foreach($needles as $needle) {
	        if (stripos($string,$needle) !== false) return true;
	    }
	    return false;
	}


	/**
	 * Delete all lines in file containing any of needles
	 * @param  string $file_path	[original file to loop]
	 * @param  array $needles		[assoc array containing al the needles]
	 * @return bool
	 */
	public function delete_specific_lines_from_file($file_path,$needles) {
		// Copy file to temp file exluding "hot" lines
		$handle_orig = fopen($file_path, 'rb');
		$handle_tmp = fopen($file_path.$this->tmp_extension, 'wb+');

		while (($line = fgets($handle_orig)) !== false) {
			if (!$this->string_contains_array($line,$needles)) {
				fwrite($handle_tmp, $line);
			}
		}

		fclose($handle_orig);
		fclose($handle_tmp);

		// Delete original, rename temp
		if (unlink($file_path) 
			&& rename($file_path.$this->tmp_extension, $file_path ) ) {
			return true;
		}
		return false;

	}


	public function gtml_img($name, array $attributes = array(), $subfolder='') {
		$att = '';
		if (isset($attributes)) {
			foreach ($attributes as $property => $value) {
				$att .= " $property=\"$value\" ";
			}
		}
		return '<img src="'.$this->imgdir.$subfolder.$name.'" '.$att.' />';
	}


	public function recurse_copy($src,$dst) { 
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
	} 



	// ------------ lixlpixel recursive PHP functions -------------
	// recursive_directory_size( directory, human readable format )
	// expects path to directory and optional TRUE / FALSE
	// PHP has to have the rights to read the directory you specify
	// and all files and folders inside the directory to count size
	// if you choose to get human readable format,
	// the function returns the filesize in bytes, KB and MB
	// ------------------------------------------------------------
	// to use this function to get the filesize in bytes, write:
	// recursive_directory_size('path/to/directory/to/count');
	// to use this function to get the size in a nice format, write:
	// recursive_directory_size('path/to/directory/to/count',TRUE);
	public function recursive_directory_size($directory, $format=FALSE) {
		$size = 0;

		// if the path has a slash at the end we remove it here
		if(substr($directory,-1) == '/')
		{
			$directory = substr($directory,0,-1);
		}

		// if the path is not valid or is not a directory ...
		if(!file_exists($directory) || !is_dir($directory) || !is_readable($directory))
		{
			// ... we return -1 and exit the function
			return -1;
		}
		// we open the directory
		if($handle = opendir($directory))
		{
			// and scan through the items inside
			while(($file = readdir($handle)) !== false)
			{
				// we build the new path
				$path = $directory.'/'.$file;

				// if the filepointer is not the current directory
				// or the parent directory
				if($file != '.' && $file != '..')
				{
					// if the new path is a file
					if(is_file($path))
					{
						// we add the filesize to the total size
						$size += filesize($path);

					// if the new path is a directory
					}elseif(is_dir($path))
					{
						// we call this function with the new path
						$handlesize = $this->recursive_directory_size($path);

						// if the function returns more than zero
						if($handlesize >= 0)
						{
							// we add the result to the total size
							$size += $handlesize;

						// else we return -1 and exit the function
						}else{
							return -1;
						}
					}
				}
			}
			// close the directory
			closedir($handle);
		}
		// if the format is set to human readable
		if($format == TRUE)
		{
			// if the total size is bigger than 1 MB
			if($size / 1048576 > 1)
			{
				return round($size / 1048576, 1).' MB';

			// if the total size is bigger than 1 KB
			}elseif($size / 1024 > 1)
			{
				return round($size / 1024, 1).' KB';

			// else return the filesize in bytes
			}else{
				return round($size, 1).' bytes';
			}
		}else{
			// return the total filesize in bytes
			return $size;
		}
	}







public function delete_files($path) {

	if(is_dir($path)) {
		$files = glob( $path . '*', GLOB_MARK );

		foreach( $files as $file ) {
			$this->delete_files( $file );      
		}

		rmdir( $path );

	} elseif(is_file($path)) {

		unlink( $path );  

	}
}


public function ceil_10($v) {
	return ceil($v / 10) * 10;
}



public function human_filesize($bytes, $decimals = 0, $return_array = false) {

  $sz = 'BKMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  if ($return_array===true)
  	return array( $bytes / pow(1024, $factor), @$sz[$factor]);
  else
  	return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];

}


// 3^2 = array( 3, 9 )
public function degree_array($n, $d_min, $d_max) {

	if (isset($n) 
	&& is_numeric($d_min) 
	&& $d_min >= 1 
	&& $d_min <= 36  
	&& is_numeric($d_max) 
	&& $d_max >= 1 
	&& $d_max <= 36 ) {

		$a = array();
		for ($i=$d_min; $i <= $d_max ; $i++) { 
			$a[] = /*$this->human_filesize(*/pow($n, $i)/*, 0, $return_array = true)*/;
		}

		return $a;
	}

	return false;

}



}
?>

<?php

#if (!class_exists('waudit_Compress')):

Class waudit_Compress extends GakplSecurityAudit {

	public $phar; // Phar compression class


	public function __construct() {
		
	}


	// Phar init
	public function phar_load ($path_formated,$ex) {
		$this->phar = new PharData($path_formated,FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,$ex);
		return $this->phar;
	}

	// Phar add file
	public function phar_add_file($path_sql,$filename) {
		return $this->phar->addFile($path_sql, $filename); 
	}

	// Phar compress
	public function phar_compress($type) {
		return $this->phar->compress($type); 
	}



	// Based on function by Alix Axel http://stackoverflow.com/questions/1334613/how-to-recursively-zip-a-directory-in-php
	public function zip($source,$destination) {


		if ($this->is_setting_on('general','bckp_mem_limit_infinite')) {	
			// ZIP runs out of memory very quickly, this gives it all there is
			ini_set('memory_limit', '-1');
		}



		if (!extension_loaded('zip') || !file_exists($source)) {
			$this->message('Error: Zip extension not loaded or nonexistent source','error');
			return false;
		}

		$zip = new ZipArchive();
		if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
			$this->message('Error: Could not create zip archive','error');
			return false;
		}

		$source = str_replace('\\', '/', realpath($source));
		if (is_dir($source) === true) {
			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

			foreach ($files as $file) {
				$file = str_replace('\\', '/', $file);

				if ($this->is_setting_on('general','dont_bck_bckp_directory') 
				&& strpos($source.'/'.$file, '/'.$this->plugin_slug.'/'.$this->plugin_backupdir) !== false  ) {
	        		// Do not back up backup directory
	        		continue;
	        	}

				// Ignore "." and ".." folders
				if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
				continue;

				$file = realpath($file);

				if (is_dir($file) === true) {
					$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
				} else if (is_file($file) === true) {
					$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
				}
			}
		} else if (is_file($source) === true) {
			$zip->addFromString(basename($source), file_get_contents($source));
		}

		return $zip->close();
	}


}
#endif;
?>
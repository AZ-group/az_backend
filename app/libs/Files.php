<?php 

namespace simplerest\libs;

class Files {

	static function logger($data, $file = 'log.txt'){		
		if (is_array($data) || is_object($data))
			$data = json_encode($data);
		
		$data = date("Y-m-d H:i:s"). "\t" .$data;

		return file_put_contents(LOGS_PATH . $file, $data. "\n", FILE_APPEND);
	}

	static function dump($object, $filename = 'dump.txt', $append = false){
		if (!Strings::contains('/', $filename)){
			$path = LOGS_PATH . $filename; 
		} else {
			$path = $filename;
		}

		if ($append){
			file_put_contents($path, var_export($object,  true) . "\n", FILE_APPEND);
		} else {
			file_put_contents($path, var_export($object,  true) . "\n");
		}		
	}
	
    // @author Federkun
    static function mkdir_ignore($dir){
		if (!is_dir($dir)) {
			if (false === @mkdir($dir, 0777, true)) {
				throw new \RuntimeException(sprintf('Unable to create the %s directory', $dir));
			}
		}
	}

}    
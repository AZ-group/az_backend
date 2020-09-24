<?php 

namespace simplerest\libs;

class Factory {
	static function response() {
		return \simplerest\core\Response::getInstance();
	}

	static function request() {
		return \simplerest\core\Request::getInstance();
	}

	static function acl(){
		static $instance;

		if ($instance == null){
			$instance = new \simplerest\core\Acl();
		}

        return $instance;
	}
	
}

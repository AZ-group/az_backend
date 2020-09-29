<?php

namespace simplerest\libs;

class Time {
    
	static function exec_speed(callable $callback, int $iterations = 100000){
        $start = microtime(true);
    
        for ($i=0; $i<$iterations; $i++){
            call_user_func($callback);	
        }
    
        return (microtime(true) - $start) / $iterations;
    }

}



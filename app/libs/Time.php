<?php

namespace simplerest\libs;

class Time {
    static $unit;   

    static function setUnit($u){
        static::$unit = $u;
    }

	static function exec_speed(callable $callback, int $iterations = 100000){
        $start = microtime(true);
    
        for ($i=0; $i<$iterations; $i++){
            call_user_func($callback);	
        }
    
        $t = (microtime(true) - $start) / $iterations;
        
        if (static::$unit == 'MILI'){
            $t = $t * 1000;
        }

        if (static::$unit == 'MICRO'){
           $t = $t * 1000000;
        }

        if (static::$unit == 'NANO'){
            $t = $t * 1000000000;
         }

        return $t;
    }

}



<?php

namespace simplerest\libs;

class Strings {

	/*
		WordAnother to word_another
	*/
	static function fromCamelCase($name){
		$len = strlen($name);

		if ($len== 0)
			return NULL;

			$conv = strtolower($name[0]);
			for ($i=1; $i<$len; $i++){
				$ord = ord($name[$i]);
				if ($ord >=65 && $ord <= 90){
					$conv .= '_' . strtolower($name[$i]);		
				} else {
					$conv .= $name[$i];	
				}					
			}		
	
		if ($name[$len-1] == '_'){
			$name = substr($name, 0, -1);
		}
	
		return $conv;
	}

	/*
		word_another to WordAnother
	*/
	static function toCamelCase($name){
        return implode('',array_map('ucfirst',explode('_',$name)));
    }

    static function startsWith($needle, $haystack)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    static function endsWith($needle, $haystack)
    {
        return substr($haystack, -strlen($needle))===$needle;
    }

	static function contains($needle, $haystack)
	{
		return (strpos($haystack, $needle) !== false);
	}

	static function removeRTrim($needle, $haystack)
    {
        if (substr($haystack, -strlen($needle)) === $needle){
			return substr($haystack, 0, - strlen($needle));
		}
		return $haystack;
    }

	static function replace($search, $replace, &$subject, $count = NULL){
		$subject = str_replace($search, $replace, $subject, $count);
	}

    /**
	 * gen_secret_key - scretet_key generator
	 *
	 * @return string
	 */
	static function gen_secret_key(){
		$arr=[];
		for ($i=0;$i<(512/7);$i++){
			$arr[] = chr(rand(32,38));
			$arr[] = chr(rand(40,47));
			$arr[] = chr(rand(58,64));
			$arr[] = chr(rand(65,90));
			$arr[] = chr(rand(91,96));
			$arr[] = chr(rand(97,122));	
			$arr[] = chr(rand(123,126));
		}	
    
        shuffle($arr);
		return substr(implode('', $arr),0,512);
	}
}



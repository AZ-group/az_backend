<?php

namespace simplerest\libs;

use simplerest\libs\Debug;

class Strings 
{
	static function match(string $str, $pattern, callable $fn = NULL){
		if (preg_match($pattern, $str, $matches)){
			if ($fn != NULL)
				$matches[1] = call_user_func($fn, $matches[1]);
			
			return $matches[1];
		}
	}

	/*
        Tipo "preg_match()" destructivo

		Va extrayendo substrings que cumplen con un patron mutando la cadena pasada por referencia.
		
		Aplica solo la primera ocurrencia *
		
		En caso de entregarse un callback, se aplica sobre la salida.
	*/
	
    static function slice(string &$str, string $pattern, callable $output_fn = NULL) {
		if (!preg_match('|\((.*)\)|', $pattern)){
			throw new \Exception("Invalid regex expression '$pattern'. It should contains a (group)");
		}

        $ret = null;
        if (preg_match($pattern,$str,$matches)){
            $str = self::replaceFirst($matches[1], '', $str);
            $ret = $matches[1];
        }

        if ($output_fn != NULL){
            $ret = call_user_func($output_fn, $ret);
        }
     
     	return $ret;   
	}


    /*
        preg_match destructivo

        Similar a slice() pero aplica a todas las ocurrencias y no acepta callback.
     */
    static function sliceAll(string &$str, string $pattern) {
        if (preg_match($pattern,$str,$matches)){
            $str = self::replaceFirst($matches[1], '', $str);
            
            return array_slice($matches, 1);
        }
    }

	/*
		CamelCase to snake_case
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
		snake_case to CamelCase
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

	// recursive str_replace version
	static function recursiveReplace($search,$replace,$subject)
	{	
		$subject = str_replace($search,$replace,$subject);
		
		if (strpos($subject,$search)!==false)
			self::recursiveReplace($search,$replace,$subject);
		
		return $subject;	
	}

	/* 
		retorna posición de nth ocurrencia de un caracter (no de un substr)
	*/
	static function posCharNth(string $str, $neddle, int $n){
		$findings = 0;

		$len = strlen($str);
		for ($i=0; $i<$len; $i++){
			if ($str[$i] == $neddle){
				$findings++;

				if ($findings == $n){
					return $i;
				} 
			}
		}
	}

	
	/**
	* String replace nth occurrence
	* 
	* @param	type $search  		Search string
	* @param	type $replace	 	Replace string
	* @param	type $subject	 	Source string
	* @param	type $occurrence 	Nth occurrence
	* @return	type 				Replaced string
	* @author	filipkappa
	*/
	static function replaceNth($search, $replace, $subject, $occurrence)
	{
		$search = preg_quote($search);
		return preg_replace("/^((?:(?:.*?$search){".--$occurrence."}.*?))$search/", "$1$replace", $subject);
	}
   
	static function replaceMultipleSpaces($str){
		return preg_replace('!\s+!', ' ', $str);
	}


	/*
		Atomiza string (divivirlo en caracteres constituyentes)
		Source: php.net
	*/
	static function stringTochars($s){
		return	preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY);
	}	
		
	
	/*
		str_replace() de solo la primera ocurrencia
	*/
	static function replaceFirst($from, $to, $subject)
	{
		$from = '/'.preg_quote($from, '/').'/';
		return preg_replace($from, $to, $subject, 1);
	}
	
	/*
		str_replace() de solo la ultima ocurrencia
	*/
	static function replaceLast($search, $replace, $subject)
	{
		$pos = strrpos($subject, $search);
	
		if($pos !== false)    
			$subject = substr_replace($subject, $replace, $pos, strlen($search));
		
		return $subject;
	}
	
	
	/*
		Hace el substr() desde el $ini hasta $fin
		
		@param string 
		@param int indice de inicio
		@param int indice final
		@return string el substr() de inicio a fin	
	*/
	static function middle($str,$ini,$fin=0){ // OK
		// si se le pasa 0,0 devuelve el primer caracter  
		// no es recomendable evitar el tercer parametro si se piensa que inicio puede ser cero
		
		if (($ini === 0) and ($fin === 0)){
			return ($str[0]);
		}else{  
			if ($fin === 0){
				$fin = strlen($str);
			} 	
			return substr ($str,$ini,$fin-$ini+1);
		}  
	}


    /**
	 * Scretet_key generator
	 *
	 * @return string
	 */
	static function secretKeyGenerator(){
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

	// https://stackoverflow.com/a/4964352
	function toBase($num, $b=62) {
		$base='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$r = $num  % $b ;
		$res = $base[$r];
		$q = floor($num/$b);
		while ($q) {
		  $r = $q % $b;
		  $q =floor($q/$b);
		  $res = $base[$r].$res;
		}
		return $res;
	  }
	  
}



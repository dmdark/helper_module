<?php
function php2js($a = false)
{
   if(is_null($a)) return 'null';
   if($a === false) return 'false';
   if($a === true) return 'true';

   if(is_scalar($a)){
      if(is_float($a)){
         // Always use "." for floats.
         $a = str_replace(",", ".", strval($a));
      }

      // All scalars are converted to strings to avoid indeterminism.
      // PHP's "1" and 1 are equal for all PHP operators, but
      // JS's "1" and 1 are not. So if we pass "1" or 1 from the PHP backend,
      // we should get the same result in the JS frontend (string).
      // Character replacements for JSON.
      static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
         array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
      return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
   }
   $isList = true;
   for($i = 0, reset($a); $i < count($a); $i++, next($a)){
      if(key($a) !== $i){
         $isList = false;
         break;
      }
   }
   $result = array();
   if($isList){
      foreach($a as $v) $result[] = php2js($v);
      return '[ ' . join(', ', $result) . ' ]';
   } else{
      foreach($a as $k => $v) $result[] = php2js($k) . ': ' . php2js($v);
      return '{ ' . join(', ', $result) . ' }';
   }
}

function config2file($file)
{
   $result = array();

   $lines = file($file);
   $currentUrl = 'undef';
   $currentTag = 'undef';
   foreach($lines as $line){
      if(preg_match('/^([a-zA-Z0-9]+)=/simxu', $line, $regs)){
         $currentTag = $regs[1];
         $line = trim(mb_substr($line, mb_strlen($currentTag) + 1));
         if($currentTag == 'url'){
            $currentUrl = $line;
         }
      }
      if(preg_match('/^=/simx', $line, $regs)){
         continue;
      }
      if($GLOBALS['_seo_config']['encoding'] != 'utf-8' && function_exists('iconv')){
         $line = iconv('utf-8', $GLOBALS['_seo_config']['encoding'], $line);
      }
      @$result[$currentUrl][$currentTag] .= $line;
   }
   return $result;
}
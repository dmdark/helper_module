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
      @$result[$currentUrl][$currentTag] .= $line;
   }
   return $result;
}

class cURL
{
   var $headers;
   var $user_agent;
   var $compression;
   var $cookie_file;
   var $proxy;

   function cURL($cookies = TRUE, $cookie = 'cookies.txt', $compression = 'gzip', $proxy = '')
   {
      $this->headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg';
      $this->headers[] = 'Connection: Keep-Alive';
      $this->headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
      $this->user_agent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)';
      $this->compression = $compression;
      $this->proxy = $proxy;
      $this->cookies = $cookies;
      if($this->cookies == TRUE) $this->cookie($cookie);
   }

   function cookie($cookie_file)
   {
      if(file_exists($cookie_file)){
         $this->cookie_file = $cookie_file;
      } else{
         $this->cookie_file = fopen($cookie_file, 'w') or $this->error('The cookie file could not be opened. Make sure this directory has the correct permissions');
         $this->cookie_file = $cookie_file;
         fclose($this->cookie_file);

      }
   }

   function get($url)
   {
      $process = curl_init($url);
      curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
      curl_setopt($process, CURLOPT_HEADER, 0);
      curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
      if($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
      if($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
      curl_setopt($process, CURLOPT_ENCODING, $this->compression);
      curl_setopt($process, CURLOPT_TIMEOUT, 30);
      if($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy);
      curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
      $return = curl_exec($process);
      curl_close($process);
      return $return;
   }

   function post($url, $data)
   {
      $process = curl_init($url);
      curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
      curl_setopt($process, CURLOPT_HEADER, 1);
      curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
      if($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
      if($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
      curl_setopt($process, CURLOPT_ENCODING, $this->compression);
      curl_setopt($process, CURLOPT_TIMEOUT, 30);
      if($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy);
      curl_setopt($process, CURLOPT_POSTFIELDS, $data);
      curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($process, CURLOPT_POST, 1);
      $return = curl_exec($process);
      curl_close($process);
      return $return;
   }

   function error($error)
   {
      echo "<center><div style='width:500px;border: 3px solid #FFEEFF; padding: 3px; background-color: #FFDDFF;font-family: verdana; font-size: 10px'><b>cURL Error</b><br>$error</div></center>";
      die;
   }
}
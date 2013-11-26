<?php

function bool_to_label_class($bool)
{
   return $bool ? 'label-success' : 'label-danger';
}

function test_mb_functions()
{
   try{
      $result = (function_exists('mb_substr') && function_exists('mb_strlen') && mb_strlen('12345') == 5 && mb_strpos('12345', '234') === 1);
      return $result;
   } catch (Exception $e){
      return false;
   }

}

function test_php2js()
{
   try{
      return php2js(array('test' => 1)) == '{ "test": "1" }';
   } catch (Exception $e){
      return false;
   }
}

function test_iconv()
{
   return function_exists('iconv');
}

function test_preg_match()
{
   return function_exists('preg_match');
}
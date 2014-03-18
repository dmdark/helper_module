<?php

if (!isset($_REQUEST['_seo_module'])) die();

if ($_REQUEST['_seo_module'] == 'forms' || $_REQUEST['_seo_module'] == 'infosystems') {
	require_once('inputvalidator.php');
	$config = include('../config.php');
	include_once($_REQUEST['_seo_module'].'/formvalidator.php');
}
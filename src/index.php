<?php
/**
* @package Abricos
* @copyright Copyright (C) 2011 Abricos. All rights reserved.
* @link http://abricos.org
* @license MIT
*/

error_reporting(E_ERROR);
// error_reporting(E_ALL);

define('DS', DIRECTORY_SEPARATOR );
define('PATH_INSTALLATION',	dirname(__FILE__) );
define('IN_ABRICOS',	true);
define('PATH_ROOT',	realpath(PATH_INSTALLATION."/../"));
define('CHMOD_ALL', 7 );
define('CHMOD_READ', 4 );
define('CHMOD_WRITE', 2 );
define('CHMOD_EXECUTE', 1 );

require_once 'function.php';

$db_config_options = array(
	'legend1'		=> 'DB_CONFIG',
	'dbms'			=> array('lang' => 'DBMS',			'type' => 'select', 'options' => 'dbms_select(\'{VALUE}\')', 'explain' => false),
	'dbhost'		=> array('lang' => 'DB_HOST',		'type' => 'text:25:100', 'explain' => true),
	'dbport'		=> array('lang' => 'DB_PORT',		'type' => 'text:25:100', 'explain' => true),
	'dbname'		=> array('lang' => 'DB_NAME',		'type' => 'text:25:100', 'explain' => false),
	'dbuser'		=> array('lang' => 'DB_USERNAME',	'type' => 'text:25:100', 'explain' => false),
	'dbpasswd'		=> array('lang' => 'DB_PASSWORD',	'type' => 'password:25:100', 'explain' => false),
	'table_prefix'	=> array('lang' => 'TABLE_PREFIX',	'type' => 'text:25:100', 'explain' => false)
);

$admin_config_options = array(
	'legend1'		=> 'ADMIN_CONFIG',
	'default_lang'	=> array('lang' => 'DEFAULT_LANG',				'type' => 'select', 'options' => 'inst_language_select(\'{VALUE}\')', 'explain' => false),
	'admin_name'	=> array('lang' => 'ADMIN_USERNAME',			'type' => 'text:25:100', 'explain' => true),
	'admin_pass1'	=> array('lang' => 'ADMIN_PASSWORD',			'type' => 'password:25:100', 'explain' => true),
	'admin_pass2'	=> array('lang' => 'ADMIN_PASSWORD_CONFIRM',	'type' => 'password:25:100', 'explain' => false),
	'board_email1'	=> array('lang' => 'CONTACT_EMAIL',				'type' => 'text:25:100', 'explain' => false),
	'board_email2'	=> array('lang' => 'CONTACT_EMAIL_CONFIRM',		'type' => 'text:25:100', 'explain' => false)
);

header("Content-Type: text/html; charset=utf-8");
header("Expires: Mon, 26 Jul 2005 15:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$page = !empty($_GET['content']) ? (int)$_GET['content'] : 0;
$LANG = $_GET['lang'] == 'ru' ? 'ru' : 'en';

if ($LANG == 'ru'){
	switch($page){
		case 1: get_requirements(); break;
		case 2: get_database_settings(); break;
		case 3: create_config_file(); break;
		case 4: include('ru_about.html'); break;
		case 5: include('ru_license.html'); break;
		case 6: include('ru_support.html'); break;
		case 7: error('Удалите папку install!','71','index.php'); break;
		default: include('language.html'); break;
	}
}else{
	switch($page){
		case 1: get_requirements(); break;
		case 2: get_database_settings(); break;
		case 3: create_config_file(); break;
		case 4: include('en_about.html'); break;
		case 5: include('en_license.html'); break;
		case 6: include('en_support.html'); break;
		case 7: error('Install folder remove!','71','index.php'); break;
		default: include('language.html'); break;
	}
}

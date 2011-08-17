<?php
/**
* @version $Id: index.php 900 2011-02-17 08:57:09Z roosit $
* @package Abricos
* @copyright Copyright (C) 2008 Abricos. All rights reserved.
* @link http://abricos.org
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

define('DS', DIRECTORY_SEPARATOR );
define('PATH_INSTALLATION',	dirname(__FILE__) );
define('IN_ABRICOS',	true);

$dir = explode('/', PATH_INSTALLATION);
unset($dir[count($dir)-1]);
$dir = implode('/', $dir);
define('PATH_ROOT',	$dir );
define('CHMOD_ALL', 7 );
define('CHMOD_READ', 4 );
define('CHMOD_WRITE', 2 );
define('CHMOD_EXECUTE', 1 );

$db_config_options = array(
	'legend1'				=> 'DB_CONFIG',
	'dbms'					=> array('lang' => 'DBMS',			'type' => 'select', 'options' => 'dbms_select(\'{VALUE}\')', 'explain' => false),
	'dbhost'				=> array('lang' => 'DB_HOST',		'type' => 'text:25:100', 'explain' => true),
	'dbport'				=> array('lang' => 'DB_PORT',		'type' => 'text:25:100', 'explain' => true),
	'dbname'				=> array('lang' => 'DB_NAME',		'type' => 'text:25:100', 'explain' => false),
	'dbuser'				=> array('lang' => 'DB_USERNAME',	'type' => 'text:25:100', 'explain' => false),
	'dbpasswd'				=> array('lang' => 'DB_PASSWORD',	'type' => 'password:25:100', 'explain' => false),
	'table_prefix'			=> array('lang' => 'TABLE_PREFIX',	'type' => 'text:25:100', 'explain' => false)
);

$admin_config_options = array(
	'legend1'				=> 'ADMIN_CONFIG',
	'default_lang'			=> array('lang' => 'DEFAULT_LANG',				'type' => 'select', 'options' => 'inst_language_select(\'{VALUE}\')', 'explain' => false),
	'admin_name'			=> array('lang' => 'ADMIN_USERNAME',			'type' => 'text:25:100', 'explain' => true),
	'admin_pass1'			=> array('lang' => 'ADMIN_PASSWORD',			'type' => 'password:25:100', 'explain' => true),
	'admin_pass2'			=> array('lang' => 'ADMIN_PASSWORD_CONFIRM',	'type' => 'password:25:100', 'explain' => false),
	'board_email1'			=> array('lang' => 'CONTACT_EMAIL',				'type' => 'text:25:100', 'explain' => false),
	'board_email2'			=> array('lang' => 'CONTACT_EMAIL_CONFIRM',		'type' => 'text:25:100', 'explain' => false)
);

$php_dlls_other = array('zlib', 'ftp', 'gd', 'xml');

if($_GET['content']){
  $pg = (int)$_GET['content'];
  switch($pg){
    case 1: get_requirements(); break;
    case 2: get_database_settings(); break;
    case 3: create_config_file(); break;
    case 4: include('about.html'); break;
    case 5: include('license.html'); break;
    case 6: include('support.html'); break;
    case 7: error('Удалите папку install !!!','71','index.php'); break;
  }
}else include('about.html');

/**
* Checks that the server we are installing on meets the requirements for running Abricos
*/
function get_requirements (){
	global $php_dlls_other;
	/**
	* Specific PHP modules we may require for certain optional or extended features
	*/
// Test the minimum PHP version
	$php_version = PHP_VERSION;
	if (version_compare($php_version, '5.0.0') < 0){
		$result = '<strong style="color:red">' . 'НЕТ' . '</strong>';
	}else{
		$passed['php'] = true;

		// We also give feedback on whether we're running in safe mode
		$result = '<strong style="color:green">' . 'Да';
		if (@ini_get('safe_mode') == '1' || strtolower(@ini_get('safe_mode')) == 'on'){
			$result .= ', ' . 'Безопасный режим';
		}
		$result .= '</strong>';
	}
	$php_version_reqd = $result;
// Check for register_globals being enabled
	if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on'){
		$result = '<strong style="color:red">' . 'Нет' . '</strong>';
	}else{
		$result = '<strong style="color:green">' . 'Да' . '</strong>';
	}
	$register_globals_reqd = $result;
// Check for url_fopen
	if (@ini_get('allow_url_fopen') == '1' || strtolower(@ini_get('allow_url_fopen')) == 'on'){
		$result = '<strong style="color:green">' . 'Да' . '</strong>';
	}else{
		$result = '<strong style="color:red">' . 'Нет' . '</strong>';
	}
	$url_fopen_reqd = $result;
// Check for getimagesize
	if (@function_exists('getimagesize')){
		$passed['imagesize'] = true;
		$result = '<strong style="color:green">' . 'Да' . '</strong>';
	}else{
		$result = '<strong style="color:red">' . 'Нет' . '</strong>';
	}
	$getimagesize_reqd = $result;
// Check for PCRE UTF-8 support
	if (@preg_match('//u', '')){
		$passed['pcre'] = true;
		$result = '<strong style="color:green">' . 'Да' . '</strong>';
	}else{
		$result = '<strong style="color:red">' . 'Нет' . '</strong>';
	}
	$utf8_support_reqd = $result;
//Check for .htaccess
	$write = $exists = true;
	if (file_exists(PATH_ROOT.DS . '.htaccess')){
		if (!u_is_writable(PATH_ROOT.DS . '.htaccess')){
			$write = false;
		}
	}else if (file_exists(PATH_ROOT.DS . 'def.htaccess')){
		rename (PATH_ROOT.DS . 'def.htaccess', PATH_ROOT.DS . '.htaccess');
		if (!u_is_writable(PATH_ROOT.DS . '.htaccess')){
			$write = false;
		}
	}else{
		$write = $exists = false;
	}
	$passed['files'] = ($exists && $write && $passed['files']) ? true : false;
	$exists_str = ($exists) ? '<strong style="color:green">' . 'Найден' . '</strong>' : '<strong style="color:red">' . 'Не найден' . '</strong>';
	$write_str = ($write) ? ', <strong style="color:green">' . 'допускает запись' . '</strong>' : (($exists) ? ', <strong style="color:red">' . 'не допускает запись' . '</strong>' : '');
	$htaccess_reqd = $exists ? $exists_str . $write_str : $exists_str;

//Check for  mod_rewrite
	$passed['mod_rewrite'] = true;
	$host = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_ENV['HTTP_HOST'];
	$url = "http://".$host.DS."__on_mod_rewrite";
	$ok = @file_get_contents($url);
	if (!in_array('mod_rewrite', apache_get_modules()) || $ok != "ok"){
		$mod_rewrite_reqd = '<strong style="color:red">' . 'Недоступно' . '</strong>';
		$passed['mod_rewrite'] = false;
	} else $mod_rewrite_reqd = '<strong style="color:green">' . 'Доступно' . '</strong>';
/**
*		Better not enabling and adding to the loaded extensions due to the specific requirements needed
		if (!@extension_loaded('mbstring')){
			can_load_dll('mbstring');
		}
*/
	$passed['mbstring'] = true;
	if (@extension_loaded('mbstring')){
// Test for available database modules
		$checks = array(
			array('func_overload', '&', MB_OVERLOAD_MAIL|MB_OVERLOAD_STRING),
			array('encoding_translation', '!=', 0),
			array('http_input', '!=', 'pass'),
			array('http_output', '!=', 'pass')
		);
		$mbstring_reqd = array();
		foreach ($checks as $mb_checks){
			$ini_val = @ini_get('mbstring.' . $mb_checks[0]);
			switch ($mb_checks[1]){
				case '&':
					if (intval($ini_val) & $mb_checks[2])
					{
						$result = '<strong style="color:red">' . 'Нет' . '</strong>';
						$passed['mbstring'] = false;
					}
					else
					{
						$result = '<strong style="color:green">' . 'Да' . '</strong>';
					}
				break;

				case '!=':
					if ($ini_val != $mb_checks[2])
					{
						$result = '<strong style="color:red">' . 'Нет' . '</strong>';
						$passed['mbstring'] = false;
					}
					else
					{
						$result = '<strong style="color:green">' . 'Да' . '</strong>';
					}
				break;
			}
			$mbstring_reqd[] = $result;
		}
	}
// Test for available database modules
	$available_dbms = get_available_dbms(false, true);
	$passed['db'] = $available_dbms['ANY_DB_SUPPORT'];
	unset($available_dbms['ANY_DB_SUPPORT']);
	$mysql_support_reqd = array();
	foreach ($available_dbms as $db_name => $db_ary){
		if (!$db_ary['AVAILABLE']){
			$mysql_support_reqd[] = '<span style="color:red">' . 'Недоступно' . '</span>';
		}else{
			$mysql_support_reqd[] = '<strong style="color:green">' . 'Доступно' . '</strong>';
		}
	}
// Test for other modules
	$other_modules_reqd = array();
	foreach ($php_dlls_other as $dll){
		if (!@extension_loaded($dll)){
			if (!can_load_dll($dll)){
				$other_modules_reqd[] = '<strong style="color:red">' . 'Недоступно' . '</strong>';
				continue;
			}
		}
		$other_modules_reqd[] = '<strong style="color:green">' . 'Доступно' . '</strong>';
	}
// Can we find Imagemagick anywhere on the system?
	$exe = (DIRECTORY_SEPARATOR == '\\') ? '.exe' : '';
	$magic_home = getenv('MAGICK_HOME');
	$img_imagick = '';
	if(empty($magic_home)){
		$locations = array('C:/WINDOWS/', 'C:/WINNT/', 'C:/WINDOWS/SYSTEM/', 'C:/WINNT/SYSTEM/', 'C:/WINDOWS/SYSTEM32/', 
		'C:/WINNT/SYSTEM32/', '/usr/bin/', '/usr/sbin/', '/usr/local/bin/', '/usr/local/sbin/', '/opt/', '/usr/imagemagick/', 
		'/usr/bin/imagemagick/');
		$path_locations = str_replace('\\', '/', (explode(($exe) ? ';' : ':', getenv('PATH'))));
		$locations = array_merge($path_locations, $locations);
		foreach ($locations as $location){
			// The path might not end properly, fudge it
			if (substr($location, -1, 1) !== '/'){
				$location .= '/';
			}
			if (@file_exists($location) && @is_readable($location . 'mogrify' . $exe) && @filesize($location . 'mogrify' . $exe) > 3000){
				$img_imagick = str_replace('\\', '/', $location);
				continue;
			}
		}
	}else{
		$img_imagick = str_replace('\\', '/', $magic_home);
	}
	$other_modules_reqd[] = ($img_imagick) ? '<strong style="color:green">' . 'Доступно' . ', ' . $img_imagick . '</strong>' : 
	'<strong style="color:blue">' . 'Не удалось найти приложение. Если вы знаете, что Imagemagick установлен, то вы можете 
	указать путь к нему после установки конференции в администраторском разделе.' . '</strong>'; 
// Check permissions on files/directories we need access to
	umask(0);
	$passed['files'] = true;
// Try to create the directory if it does not exist
	if (!file_exists(PATH_ROOT.DS . 'cache/')){
		if(is_dir(PATH_ROOT.DS . 'cache/')){
			@mkdir(PATH_ROOT.DS . 'cache/', 0777);
			u_chmod(PATH_ROOT.DS . 'cache/', CHMOD_READ | CHMOD_WRITE);
		}
	}
// Now really check
	if (file_exists(PATH_ROOT.DS . 'cache/') && is_dir(PATH_ROOT.DS . 'cache/')){
		u_chmod(PATH_ROOT.DS . 'cache/', CHMOD_READ | CHMOD_WRITE);
		$exists = true;
	}
// Now check if it is writable by storing a simple file
	$fp = @fopen(PATH_ROOT.DS . 'cache/' . 'test_lock', 'wb');
	if ($fp !== false){
		$write = true;
	}
	@fclose($fp);
	@unlink(PATH_ROOT.DS . 'cache/' . 'test_lock');
	$passed['files'] = ($exists && $write && $passed['files']) ? true : false;
	$exists = ($exists) ? '<strong style="color:green">' . 'Найден' . '</strong>' : '<strong style="color:red">' . 'Не найден' . '</strong>';
	$write = ($write) ? ', <strong style="color:green">' . 'допускает запись' . '</strong>' : (($exists) ? ', <strong style="color:red">' . 'не допускает запись' . '</strong>' : '');
	$cache_reqd = $exists . $write;

	
//Check for config.php
	$write = $exists = true;
	if (file_exists(PATH_ROOT.DS . 'includes/config.new.php')){
		rename (PATH_ROOT.DS . 'includes/config.new.php', PATH_ROOT.DS . 'includes/config.php');
		if (!u_is_writable(PATH_ROOT.DS . 'includes/config.php')){
			$write = false;
		}
		rename (PATH_ROOT.DS . 'includes/config.php', PATH_ROOT.DS . 'includes/config.new.php');
	}
	else{
		$write = $exists = false;
	}
	$passed['files'] = ($exists && $write && $passed['files']) ? true : false;
	$exists_str = ($exists) ? '<strong style="color:green">' . 'Найден' . '</strong>' : '<strong style="color:red">' . 'Не найден' . '</strong>';
	$write_str = ($write) ? ', <strong style="color:green">' . 'допускает запись' . '</strong>' : (($exists) ? ', <strong style="color:red">' . 'не допускает запись' . '</strong>' : '');
	$config_reqd = $exists ? $exists_str . $write_str : $exists_str;
	$url = (!in_array(false, $passed)) ? 'index.php?content=2' : 'index.php?content=1';
	$not_passed = (!in_array(false, $passed)) ? 'Следующий шаг' : 'Требования не выполнены! Перепроверить?';
	include('req.html');
}		

	/**
	* Obtain the information required to connect to the database
	*/
function get_database_settings(){
	global $db_config_options, $admin_config_options;
	
	// Obtain any submitted data
	$data = get_submitted_data();
	$connect_test = false;
	$error = array();
	$available_dbms = get_available_dbms(false, true);
	
	// Has the user opted to test the connection?
	if (isset($_POST['testdb'])){
		$cmtg = true;
		if (!isset($available_dbms[$data['dbms']]) || !$available_dbms[$data['dbms']]['AVAILABLE']){
			$error[] = 'Не удалось загрузить модуль PHP для выбранного типа базы данных.';
			$connect_test = false;
		}
		else{
			$connect_test = connect_check_db(
				true, 
				$error, 
				$available_dbms[$data['dbms']], 
				$data['table_prefix'], 
				$data['dbhost'], 
				$data['dbuser'], 
				htmlspecialchars_decode($data['dbpasswd']), 
				$data['dbname'], 
				$data['dbport']
			);
		}
		if ($connect_test){
			$connect_success = '<strong style="color:green">Успешное подключение</strong>';
		}
		else{
			$connect_success = '<strong style="color:red">' . implode('<br />', $error) . '</strong>';
		}
	}
	if (!$connect_test){
		
		// Update the list of available DBMS modules to only contain those which can be used
		$available_dbms_temp = array();
		foreach ($available_dbms as $type => $dbms_ary){
			if (!$dbms_ary['AVAILABLE']){
				continue;
			}
			$available_dbms_temp[$type] = $dbms_ary;
		}

		$available_dbms = &$available_dbms_temp;

		// And now for the main part of this page
		$data['table_prefix'] = (!empty($data['table_prefix']) ? $data['table_prefix'] : 'cms_');
		$dcontent = array();
		foreach ($db_config_options as $config_key => $vars){
			if (!is_array($vars) && strpos($config_key, 'legend') === false){
				continue;
			}
			$options = isset($vars['options']) ? $vars['options'] : '';
			
			/*$template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> $lang[$vars['lang']],
				'S_EXPLAIN'		=> $vars['explain'],
				'S_LEGEND'		=> false,
				'TITLE_EXPLAIN'	=> ($vars['explain']) ? $lang[$vars['lang'] . '_EXPLAIN'] : '',
				'CONTENT'		=> $this->p_master->input_field($config_key, $vars['type'], $data[$config_key], $options),
				)
			);*/
		}
	}
			// And finally where do we want to go next (well today is taken isn't it :P)
	$s_hidden_fields = ($data['img_imagick']) ? '<input type="hidden" name="img_imagick" value="' . addslashes($data['img_imagick']) . '" />' : '';
	$s_hidden_fields .= '<input type="hidden" name="language" value="' . $data['language'] . '" />';
	if ($connect_test){
		foreach ($db_config_options as $config_key => $vars){
			if (!is_array($vars)){
				continue;
			}
			$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $data[$config_key] . '" />';
		}
	}
	$url = ($connect_test) ? "index.php?content=3" : "index.php?content=2";
	$s_hidden_fields .= ($connect_test) ? '' : '<input type="hidden" name="testdb" value="true" />';

	include('db.html');
}

/**
* Writes the config file to disk, or if unable to do so offers alternative methods
*/
function create_config_file()
{
	global $php_dlls_other;
	global $db_config_options, $admin_config_options;

	// Obtain any submitted data
	$data = get_submitted_data();

	if ($data['dbms'] == '')
	{
		// Someone's been silly and tried calling this page direct
		// So we send them back to the start to do it again properly
		header( 'Location: /installation' );
	}

	$s_hidden_fields = ($data['img_imagick']) ? '<input type="hidden" name="img_imagick" value="' . addslashes($data['img_imagick']) . '" />' : '';
	$s_hidden_fields .= '<input type="hidden" name="language" value="' . $data['language'] . '" />';
	$written = false;

	// Create a list of any PHP modules we wish to have loaded
	$load_extensions = array();
	$available_dbms = get_available_dbms($data['dbms']);
	$check_exts = array_merge(array($available_dbms[$data['dbms']]['MODULE']), $php_dlls_other);

	foreach ($check_exts as $dll)
	{
		if (!@extension_loaded($dll))
		{
			if (!can_load_dll($dll))
			{
				continue;
			}

			$load_extensions[] = $dll . '.' . PHP_SHLIB_SUFFIX;
		}
	}

	// Create a lock file to indicate that there is an install in progress
	$fp = @fopen(PATH_ROOT . DS . 'cache/install_lock', 'wb');
	if ($fp === false)
	{
		// We were unable to create the lock file - abort
		error('Не удалось записать файл блокировки.', __LINE__, __FILE__);
	}
	@fclose($fp);

	@chmod(PATH_ROOT . DS . 'cache/install_lock', 0777);

	$load_extensions = implode(',', $load_extensions);

	// Time to convert the data provided into a config file
	$config_data = "<?php\n";
	$config_data .= "/**\n";
	$config_data .= "* @version \$Id\n";
	$config_data .= "* @package Abricos\n";
	$config_data .= "* @copyright Copyright (C) 2008 Abricos. All rights reserved.\n";
	$config_data .= "* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php\n*/\n\n\n/**\n";
	$config_data .= "* Настройка режима \"только для чтения\" работы с БД.\n*/\n";
	$config_data .= "\$config['Database']['readonly'] = false;\n\n";
	
	$config_data_array = array(
		"config['Database']['dbtype']"		=> $available_dbms[$data['dbms']]['DRIVER'],
		"config['Database']['dbname']"		=> $data['dbname'],
		"config['Server']['servername']"	=> ($data['dbhost']==='')?'localhost':$data['dbhost'],
		"config['Server']['port']"			=> ($data['dbport']==='')?(int)3306:$data['dbport'],
		"config['Server']['username']"		=> $data['dbuser'],
		"config['Server']['password']"		=> htmlspecialchars_decode($data['dbpasswd']),
		"config['Misc']['cookieprefix']"	=> $data['table_prefix'],
		"config['Misc']['cookietimeout']"	=> 86400 * 14,
		"config['Misc']['charset']"			=> "utf-8",
		"config['Misc']['language']" 		=> "ru"
	);

	foreach ($config_data_array as $key => $value)
	{
		if(is_int($value))$config_data .= "\${$key} = " . str_replace("'", "\\'", str_replace('\\', '\\\\', $value)) . ";\n";
		else $config_data .= "\${$key} = '" . str_replace("'", "\\'", str_replace('\\', '\\\\', $value)) . "';\n";
	}
	unset($config_data_array);
	
	$config_data .= "\n\$config['Misc']['brick_cache'] = false;\n\n";
	$config_data .= "// Режим работы платформы для разработчика\n";
	$config_data .= "\$config['Misc']['develop_mode'] = true;\n\n";
	$config_data .= "// Показать информацию работы сервера (скорость сборки страницы, кол-во запросов к БД)\n";
	$config_data .= "\$config['Misc']['showbuildinfo'] = false;\n\n";
	$config_data .= "\$config['JsonDB']['use'] = false;\n\n";
	$config_data .= "\$config['JsonDB']['password'] = \"\";\n\n";
	$config_data .= "/**\n * Идентификатор пользователя имеющий статус \"Супер администратора\".\n * Примечание: статус \"Супер администратор\"";
	$config_data .= " позволяет игнорировать настройку readonly\n */\n\$config['superadmin'] = '';\n\n";
	$config_data .= "// Пример правил применения шаблонов для страниц сайта\n/*\n\$config['Template'] = array(\n";
	$config_data .= "// по умолчанию использовать шаблон blog из стиля default\n	\"default\" => array(\n";
	$config_data .= "		\"owner\" => \"default\",\n		\"name\" => \"blog\"\n	),\n";
	$config_data .= "	// не применять правила для страниц в разделе http://domain.tld/price/...\n";
	$config_data .= "	\"ignore\" => array(\n		array(\n			\"pattern\" => \"/^\\/price\\//i\",\n			\"regexp\" => true\n		)\n	), \n";
	$config_data .= "	\"exp\" => array(\n		// использовать шаблон main из стиля default для главной страницы сайта\n";
	$config_data .= "		array(\n			\"pattern\" => \"/\",\n			\"regexp\" => false,\n			\"owner\" => \"default\",\n"; 
	$config_data .= "			\"name\" => \"main\"\n		),\n		// использовать шаблон news из стиля default для новостей\n"; 
	$config_data .= "		array(\n			\"pattern\" => \"/^\\/news\\//i\",\n			\"regexp\" => true,\n";
	$config_data .= "			\"owner\" => \"default\",\n			\"name\" => \"news\"\n		)\n	) \n);\n/**/\n\n";
	$config_data .= "// Пример тонкой настройки работы модулей\n/*\n\$config['Takelink'] = array(\n";
	$config_data .= "	\"webos\" => array(\n		\"module\" => \"webos\"\n	),\n	\"calendar\" => array(\n";
	$config_data .= "		\"module\" => \"webos\",\n		\"enmod\" => array(\"calendar\")\n	),\n	\"company\" => array(\n";
	$config_data .= "		\"enmod\" => array(\"webos\", \"company\", \"calendar\")\n	)\n);\n*/\n";
	
	$config_data .= '?' . '>'; // Done this to prevent highlighting editors getting confused!

	// Attempt to write out the config file directly. If it works, this is the easiest way to do it ...
	if ((file_exists(PATH_ROOT . DS . 'includes/config.php') && u_is_writable(PATH_ROOT . DS . 'includes/config.php')) || u_is_writable(PATH_ROOT . DS))
	{
		// Assume it will work ... if nothing goes wrong below
		$written = true;

		if (!($fp = @fopen(PATH_ROOT . DS . 'includes/config.php', 'w')))
		{
			// Something went wrong ... so let's try another method
			$written = false;
		}

		if (!(@fwrite($fp, $config_data)))
		{
			// Something went wrong ... so let's try another method
			$written = false;
		}

		@fclose($fp);

		if ($written)
		{
			// We may revert back to chmod() if we see problems with users not able to change their config.php file directly
			u_chmod(PATH_ROOT . DS . 'includes/config.php', CHMOD_READ);
		}
	}

	if (isset($_POST['dldone']))
	{
		// Do a basic check to make sure that the file has been uploaded
		// Note that all we check is that the file has _something_ in it
		// We don't compare the contents exactly - if they can't upload
		// a single file correctly, it's likely they will have other problems....
		if (filesize(PATH_ROOT . DS . 'includes/config.php') > 10)
		{
			$written = true;
		}
	}

	$config_options = array_merge($db_config_options, $admin_config_options);

	foreach ($config_options as $config_key => $vars)
	{
		if (!is_array($vars))
		{
			continue;
		}
		$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $data[$config_key] . '" />';
	}

	if (!$written)
	{
		// OK, so it didn't work let's try the alternatives

		if (isset($_POST['dlconfig']))
		{
			// They want a copy of the file to download, so send the relevant headers and dump out the data
			header("Content-Type: text/x-delimtext; name=\"config.php\"");
			header("Content-disposition: attachment; filename=config.php");
			echo $config_data;
			exit;
		}
		
		// The option to download the config file is always available, so output it here
		$connect_success = 'Не удалось записать конфигурационный файл. Обратитесь в службу поддержки';
		$connect_success .= 'В папке includes/ есть файлы конфигурации config.new.php и config.newwebos.php. При необходимости переименуйте один из них в config.php.';
		include('conf.html');
		return;
	}
	else
	{
		$connect_success = 'Конфигурационный файл записан. Установка завершена.<br />Для продолжения нажмите Готово.';
		include('conf.html');
		return;
	}

/**/
	include('conf.html');
}

function get_available_dbms($dbms = false, $return_unavailable = false, $only_20x_options = false){
	$available_dbms = array(
		'mysql'		=> array(
			'LABEL'			=> 'MySQL',
			'SCHEMA'		=> 'mysql',
			'MODULE'		=> 'mysql',
			'DELIM'			=> ';',
			'COMMENTS'		=> 'remove_remarks',
			'DRIVER'		=> 'mysql',
			'AVAILABLE'		=> true,
			'2.0.x'			=> true
		)
	);
	if ($dbms){
		if (isset($available_dbms[$dbms])){
			$available_dbms = array($dbms => $available_dbms[$dbms]);
		}else{
			return array();
		}
	}
	// now perform some checks whether they are really available
	foreach ($available_dbms as $db_name => $db_ary)
	{
		if ($only_20x_options && !$db_ary['2.0.x'])
		{
			if ($return_unavailable)
			{
				$available_dbms[$db_name]['AVAILABLE'] = false;
			}
			else
			{
				unset($available_dbms[$db_name]);
			}
			continue;
		}

		$dll = $db_ary['MODULE'];

		if (!@extension_loaded($dll))
		{
			if (!can_load_dll($dll))
			{
				if ($return_unavailable)
				{
					$available_dbms[$db_name]['AVAILABLE'] = false;
				}
				else
				{
					unset($available_dbms[$db_name]);
				}
				continue;
			}
		}
		$any_db_support = true;
	}

	if ($return_unavailable)
	{
		$available_dbms['ANY_DB_SUPPORT'] = $any_db_support;
	}
	return $available_dbms;
}

/**
* Global function for chmodding directories and files for internal use
*
* This function determines owner and group whom the file belongs to and user and group of PHP and then set safest possible file permissions.
* The function determines owner and group from common.php file and sets the same to the provided file.
* The function uses bit fields to build the permissions.
* The function sets the appropiate execute bit on directories.
*
* Supported constants representing bit fields are:
*
* CHMOD_ALL - all permissions (7)
* CHMOD_READ - read permission (4)
* CHMOD_WRITE - write permission (2)
* CHMOD_EXECUTE - execute permission (1)
*
* NOTE: The function uses POSIX extension and fileowner()/filegroup() functions. If any of them is disabled, this function tries to build proper permissions, by calling is_readable() and is_writable() functions.
*
* @param string	$filename	The file/directory to be chmodded
* @param int	$perms		Permissions to set
*
* @return bool	true on success, otherwise false
* @author faw, phpBB Group
*/
function u_chmod($filename, $perms = CHMOD_READ)
{
	static $_chmod_info;

	// Return if the file no longer exists.
	if (!file_exists($filename))
	{
		return false;
	}

	// Determine some common vars
	if (empty($_chmod_info))
	{
		if (!function_exists('fileowner') || !function_exists('filegroup'))
		{
			// No need to further determine owner/group - it is unknown
			$_chmod_info['process'] = false;
		}
		else
		{
			// Determine owner/group of common.php file and the filename we want to change here
			$common_php_owner = @fileowner(PATH_INSTALLATION.DS . 'common.' . $phpEx);
			$common_php_group = @filegroup(PATH_INSTALLATION.DS . 'common.' . $phpEx);

			// And the owner and the groups PHP is running under.
			$php_uid = (function_exists('posix_getuid')) ? @posix_getuid() : false;
			$php_gids = (function_exists('posix_getgroups')) ? @posix_getgroups() : false;

			// If we are unable to get owner/group, then do not try to set them by guessing
			if (!$php_uid || empty($php_gids) || !$common_php_owner || !$common_php_group)
			{
				$_chmod_info['process'] = false;
			}
			else
			{
				$_chmod_info = array(
					'process'		=> true,
					'common_owner'	=> $common_php_owner,
					'common_group'	=> $common_php_group,
					'php_uid'		=> $php_uid,
					'php_gids'		=> $php_gids,
				);
			}
		}
	}

	if ($_chmod_info['process'])
	{
		$file_uid = @fileowner($filename);
		$file_gid = @filegroup($filename);

		// Change owner
		if (@chown($filename, $_chmod_info['common_owner']))
		{
			clearstatcache();
			$file_uid = @fileowner($filename);
		}

		// Change group
		if (@chgrp($filename, $_chmod_info['common_group']))
		{
			clearstatcache();
			$file_gid = @filegroup($filename);
		}

		// If the file_uid/gid now match the one from common.php we can process further, else we are not able to change something
		if ($file_uid != $_chmod_info['common_owner'] || $file_gid != $_chmod_info['common_group'])
		{
			$_chmod_info['process'] = false;
		}
	}

	// Still able to process?
	if ($_chmod_info['process'])
	{
		if ($file_uid == $_chmod_info['php_uid'])
		{
			$php = 'owner';
		}
		else if (in_array($file_gid, $_chmod_info['php_gids']))
		{
			$php = 'group';
		}
		else
		{
			// Since we are setting the everyone bit anyway, no need to do expensive operations
			$_chmod_info['process'] = false;
		}
	}

	// We are not able to determine or change something
	if (!$_chmod_info['process'])
	{
		$php = 'other';
	}

	// Owner always has read/write permission
	$owner = CHMOD_READ | CHMOD_WRITE;
	if (is_dir($filename))
	{
		$owner |= CHMOD_EXECUTE;

		// Only add execute bit to the permission if the dir needs to be readable
		if ($perms & CHMOD_READ)
		{
			$perms |= CHMOD_EXECUTE;
		}
	}

	switch ($php)
	{
		case 'owner':
			$result = @chmod($filename, ($owner << 6) + (0 << 3) + (0 << 0));

			clearstatcache();

			if (is_readable($filename) && u_is_writable($filename))
			{
				break;
			}

		case 'group':
			$result = @chmod($filename, ($owner << 6) + ($perms << 3) + (0 << 0));

			clearstatcache();

			if ((!($perms & CHMOD_READ) || is_readable($filename)) && (!($perms & CHMOD_WRITE) || u_is_writable($filename)))
			{
				break;
			}

		case 'other':
			$result = @chmod($filename, ($owner << 6) + ($perms << 3) + ($perms << 0));

			clearstatcache();

			if ((!($perms & CHMOD_READ) || is_readable($filename)) && (!($perms & CHMOD_WRITE) || u_is_writable($filename)))
			{
				break;
			}

		default:
			return false;
		break;
	}

	return $result;
}


/**
* Determine if we are able to load a specified PHP module and do so if possible
*/
function can_load_dll($dll)
{
	// SQLite2 is a tricky thing, from 5.0.0 it requires PDO; if PDO is not loaded we must state that SQLite is unavailable
	// as the installer doesn't understand that the extension has a prerequisite.
	//
	// On top of this sometimes the SQLite extension is compiled for a different version of PDO
	// by some Linux distributions which causes phpBB to bomb out with a blank page.
	//
	// Net result we'll disable automatic inclusion of SQLite support
	//
	// See: r9618 and #56105
	if ($dll == 'sqlite')
	{
		return false;
	}
	return ((@ini_get('enable_dl') || strtolower(@ini_get('enable_dl')) == 'on') && (!@ini_get('safe_mode') || strtolower(@ini_get('safe_mode')) == 'off') && function_exists('dl') && @dl($dll . '.' . PHP_SHLIB_SUFFIX)) ? true : false;
}

/**
* Test if a file/directory is writable
*
* This function calls the native is_writable() when not running under
* Windows and it is not disabled.
*
* @param string $file Path to perform write test on
* @return bool True when the path is writable, otherwise false.
*/
function u_is_writable($file)
{
	if (strtolower(substr(PHP_OS, 0, 3)) === 'win' || !function_exists('is_writable'))
	{
		if (file_exists($file))
		{
			// Canonicalise path to absolute path
			$file = u_realpath($file);

			if (is_dir($file))
			{
				// Test directory by creating a file inside the directory
				$result = @tempnam($file, 'i_w');

				if (is_string($result) && file_exists($result))
				{
					unlink($result);

					// Ensure the file is actually in the directory (returned realpathed)
					return (strpos($result, $file) === 0) ? true : false;
				}
			}
			else
			{
				$handle = @fopen($file, 'r+');

				if (is_resource($handle))
				{
					fclose($handle);
					return true;
				}
			}
		}
		else
		{
			// file does not exist test if we can write to the directory
			$dir = dirname($file);

			if (file_exists($dir) && is_dir($dir) && u_is_writable($dir))
			{
				return true;
			}
		}

		return false;
	}
	else
	{
		return is_writable($file);
	}
}

/**
* Get submitted data
*/
function get_submitted_data()
{
	return array(
		'language'		=> basename(request_var('language', '')),
		'dbms'			=> request_var('dbms', ''),
		'dbhost'		=> request_var('dbhost', ''),
		'dbport'		=> request_var('dbport', ''),
		'dbuser'		=> request_var('dbuser', ''),
		'dbpasswd'		=> request_var('dbpasswd', '', true),
		'dbname'		=> request_var('dbname', ''),
		'table_prefix'	=> request_var('table_prefix', ''),
		'default_lang'	=> basename(request_var('default_lang', '')),
		'admin_name'	=> request_var('admin_name', '', true), //utf8_normalize_nfc(request_var('admin_name', '', true)),
		'admin_pass1'	=> request_var('admin_pass1', '', true),
		'admin_pass2'	=> request_var('admin_pass2', '', true),
		'board_email1'	=> strtolower(request_var('board_email1', '')),
		'board_email2'	=> strtolower(request_var('board_email2', '')),
		'img_imagick'	=> request_var('img_imagick', ''),
		'ftp_path'		=> request_var('ftp_path', ''),
		'ftp_user'		=> request_var('ftp_user', ''),
		'ftp_pass'		=> request_var('ftp_pass', ''),
		'email_enable'	=> request_var('email_enable', ''),
		'smtp_delivery'	=> request_var('smtp_delivery', ''),
		'smtp_host'		=> request_var('smtp_host', ''),
		'smtp_auth'		=> request_var('smtp_auth', ''),
		'smtp_user'		=> request_var('smtp_user', ''),
		'smtp_pass'		=> request_var('smtp_pass', ''),
		'cookie_secure'	=> request_var('cookie_secure', ''),
		'force_server_vars'	=> request_var('force_server_vars', ''),
		'server_protocol'	=> request_var('server_protocol', ''),
		'server_name'	=> request_var('server_name', ''),
		'server_port'	=> request_var('server_port', ''),
		'script_path'	=> request_var('script_path', ''),
	);
}

function request_var($var_name, $default, $multibyte = false, $cookie = false)
{
	if (!$cookie && isset($_COOKIE[$var_name]))
	{
		if (!isset($_GET[$var_name]) && !isset($_POST[$var_name]))
		{
			return (is_array($default)) ? array() : $default;
		}
		$_REQUEST[$var_name] = isset($_POST[$var_name]) ? $_POST[$var_name] : $_GET[$var_name];
	}

	$super_global = ($cookie) ? '_COOKIE' : '_REQUEST';
	if (!isset($GLOBALS[$super_global][$var_name]) || is_array($GLOBALS[$super_global][$var_name]) != is_array($default))
	{
		return (is_array($default)) ? array() : $default;
	}

	$var = $GLOBALS[$super_global][$var_name];
	if (!is_array($default))
	{
		$type = gettype($default);
	}
	else
	{
		list($key_type, $type) = each($default);
		$type = gettype($type);
		$key_type = gettype($key_type);
		if ($type == 'array')
		{
			reset($default);
			$default = current($default);
			list($sub_key_type, $sub_type) = each($default);
			$sub_type = gettype($sub_type);
			$sub_type = ($sub_type == 'array') ? 'NULL' : $sub_type;
			$sub_key_type = gettype($sub_key_type);
		}
	}

	if (is_array($var))
	{
		$_var = $var;
		$var = array();

		foreach ($_var as $k => $v)
		{
			set_var($k, $k, $key_type);
			if ($type == 'array' && is_array($v))
			{
				foreach ($v as $_k => $_v)
				{
					if (is_array($_v))
					{
						$_v = null;
					}
					set_var($_k, $_k, $sub_key_type, $multibyte);
					set_var($var[$k][$_k], $_v, $sub_type, $multibyte);
				}
			}
			else
			{
				if ($type == 'array' || is_array($v))
				{
					$v = null;
				}
				set_var($var[$k], $v, $type, $multibyte);
			}
		}
	}
	else
	{
		set_var($var, $var, $type, $multibyte);
	}

	return $var;
}

/**
* set_var
*
* Set variable, used by {@link request_var the request_var function}
*
* @access private
*/
function set_var(&$result, $var, $type, $multibyte = false)
{
	settype($var, $type);
	$result = $var;

	if ($type == 'string')
	{
		$result = trim(htmlspecialchars(str_replace(array("\r\n", "\r", "\0"), array("\n", "\n", ''), $result), ENT_COMPAT, 'UTF-8'));

		if (!empty($result))
		{
			// Make sure multibyte characters are wellformed
			if ($multibyte)
			{
				if (!preg_match('/^./u', $result))
				{
					$result = '';
				}
			}
			else
			{
				// no multibyte, allow only ASCII (0-127)
				$result = preg_replace('/[\x80-\xFF]/', '?', $result);
			}
		}

		$result = (STRIP) ? stripslashes($result) : $result;
	}
}

/**
* Generate the drop down of available database options
*/
function dbms_select($default = '', $only_20x_options = false)
{
	$available_dbms = get_available_dbms(false, false, $only_20x_options);
	$dbms_options = '';
	$bdname = array(
		'FIREBIRD' 				=> 'Firebird',
		'MBSTRING'				=> 'Поддержка многобайтных символов',
		'MSSQL'					=> 'MSSQL Server 2000+',
		'MSSQL_ODBC'			=> 'MSSQL Server 2000+ через ODBC',
		'MSSQLNATIVE'			=> 'MSSQL Server 2005+ [ Native ]',
		'MYSQL'					=> 'MySQL',
		'MYSQLI'				=> 'MySQL с расширением MySQLi',
		'ORACLE'				=> 'Oracle',
		'POSTGRES'				=> 'PostgreSQL 7.x/8.x',
		'SQLITE'				=> 'SQLite'
		
	);
	
	foreach ($available_dbms as $dbms_name => $details)
	{
		$selected = ($dbms_name == $default) ? ' selected="selected"' : '';
		$dbms_options .= '<option value="' . $dbms_name . '"' . $selected .'>' .  $bdname[strtoupper($dbms_name)] . '</option>';
	}
	return $dbms_options;
}

/**
* Used to test whether we are able to connect to the database the user has specified
* and identify any problems (eg there are already tables with the names we want to use
* @param	array	$dbms should be of the format of an element of the array returned by {@link get_available_dbms get_available_dbms()}
*					necessary extensions should be loaded already
*/
function connect_check_db($error_connect, &$error, $dbms_details, $table_prefix, $dbhost, $dbuser, $dbpasswd, $dbname, $dbport, $prefix_may_exist = false, $load_dbal = true, $unicode_check = true)
{
	$dbms = $dbms_details['DRIVER'];
	if ($load_dbal){
		// Include the DB layer
		include(PATH_INSTALLATION . DS . 'db/' . $dbms . '.php');
	}
	// Instantiate it and set return on error true
	$sql_db = 'dbal_' . $dbms;
	$db = new $sql_db();
	$db->sql_return_on_error(true);

	// Check that we actually have a database name before going any further.....
	if ($dbms_details['DRIVER'] != 'sqlite' && $dbms_details['DRIVER'] != 'oracle' && $dbname === '')
	{
		$error[] = 'Не указано название базы данных.';
		return false;
	}

	// Make sure we don't have a daft user who thinks having the SQLite database in the forum directory is a good idea
	if ($dbms_details['DRIVER'] == 'sqlite' && stripos(u_realpath($dbhost), u_realpath('../')) === 0)
	{
		$error[] = 'Указанный файл базы данных находится в папке конференции. Необходимо переместить его в папку, недоступную из интернета.';
		return false;
	}

	// Check the prefix length to ensure that index names are not too long and does not contain invalid characters
	switch ($dbms_details['DRIVER'])
	{
		case 'mysql':
		case 'mysqli':
			if (strspn($table_prefix, '-./\\') !== 0)
			{
				$error[] = 'Указанный префикс недопустим для вашей базы данных. Введите другой префикс без специальных символов типа дефиса.';
				return false;
			}

		// no break;

		case 'postgres':
			$prefix_length = 36;
		break;

		case 'mssql':
		case 'mssql_odbc':
		case 'mssqlnative':
			$prefix_length = 90;
		break;

		case 'sqlite':
			$prefix_length = 200;
		break;

		case 'firebird':
		case 'oracle':
			$prefix_length = 6;
		break;
	}

	if (strlen($table_prefix) > $prefix_length)
	{
		$error[] = sprintf('Указанный префикс таблиц слишком длинный. Длина префикса не должна превышать %d символов.', $prefix_length);
		return false;
	}

	// Try and connect ...
	if (is_array($db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, true)))
	{
		$db_error = $db->sql_error();
		$error[] = 'Не удалось подключиться к базе данных. Ниже показан текст сообщения об ошибке.' . '<br />' . (($db_error['message']) ? $db_error['message'] : 'Нет сообщения об ошибке.');
	}
	else
	{
		// Likely matches for an existing phpBB installation
		if (!$prefix_may_exist)
		{
			$temp_prefix = strtolower($table_prefix);
			$table_ary = array($temp_prefix . 'attachments', $temp_prefix . 'config', $temp_prefix . 'sessions', $temp_prefix . 'topics', $temp_prefix . 'users');

			$tables = get_tables($db);
			$tables = array_map('strtolower', $tables);
			$table_intersect = array_intersect($tables, $table_ary);

			if (sizeof($table_intersect))
			{
				$error[] = 'Таблицы с указанным префиксом уже существуют. Введите другой префикс.';
			}
		}

		// Make sure that the user has selected a sensible DBAL for the DBMS actually installed
		switch ($dbms_details['DRIVER'])
		{
			case 'mysqli':
				if (version_compare(mysqli_get_server_info($db->db_connect_id), '4.1.3', '<'))
				{
					$error[] = 'Установленная на сервере версия MySQL несовместима с выбранным вариантом «MySQL с расширением MySQLi». Вместо него попробуйте выбрать вариант «MySQL».';
				}
			break;

			case 'sqlite':
				if (version_compare(sqlite_libversion(), '2.8.2', '<'))
				{
					$error[] = 'У вас установлена устаревшая версия расширения SQLite. Её необходимо обновить хотя бы до версии 2.8.2.';
				}
			break;

			case 'firebird':
				// check the version of FB, use some hackery if we can't get access to the server info
				if ($db->service_handle !== false && function_exists('ibase_server_info'))
				{
					$val = @ibase_server_info($db->service_handle, IBASE_SVC_SERVER_VERSION);
					preg_match('#V([\d.]+)#', $val, $match);
					if ($match[1] < 2)
					{
						$error[] = 'Установленная на сервере версия Firebird старее 2.1. Обновите базу данных до новой версии.';
					}
					$db_info = @ibase_db_info($db->service_handle, $dbname, IBASE_STS_HDR_PAGES);

					preg_match('/^\\s*Page size\\s*(\\d+)/m', $db_info, $regs);
					$page_size = intval($regs[1]);
					if ($page_size < 8192)
					{
						$error[] = 'У вас установлена устаревшая версия расширения SQLite. Её необходимо обновить хотя бы до версии 2.8.2.';
					}
				}
				else
				{
					$sql = "SELECT *
						FROM RDB$FUNCTIONS
						WHERE RDB$SYSTEM_FLAG IS NULL
							AND RDB$FUNCTION_NAME = 'CHAR_LENGTH'";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					// if its a UDF, its too old
					if ($row)
					{
						$error[] = 'Установленная на сервере версия Firebird старее 2.1. Обновите базу данных до новой версии.';
					}
					else
					{
						$sql = 'SELECT 1 FROM RDB$DATABASE
							WHERE BIN_AND(10, 1) = 0';
						$result = $db->sql_query($sql);
						if (!$result) // This can only fail if BIN_AND is not defined
						{
							$error[] = 'Установленная на сервере версия Firebird старее 2.1. Обновите базу данных до новой версии.';
						}
						$db->sql_freeresult($result);
					}

					// Setup the stuff for our random table
					$char_array = array_merge(range('A', 'Z'), range('0', '9'));
					$char_len = mt_rand(7, 9);
					$char_array_len = sizeof($char_array) - 1;

					$final = '';

					for ($i = 0; $i < $char_len; $i++)
					{
						$final .= $char_array[mt_rand(0, $char_array_len)];
					}

					// Create some random table
					$sql = 'CREATE TABLE ' . $final . " (
						FIELD1 VARCHAR(255) CHARACTER SET UTF8 DEFAULT '' NOT NULL COLLATE UNICODE,
						FIELD2 INTEGER DEFAULT 0 NOT NULL);";
					$db->sql_query($sql);

					// Create an index that should fail if the page size is less than 8192
					$sql = 'CREATE INDEX ' . $final . ' ON ' . $final . '(FIELD1, FIELD2);';
					$db->sql_query($sql);

					if (ibase_errmsg() !== false)
					{
						$error[] = 'Выбранная база данных Firebird имеет размер страницы меньше 8192. Размер страницы должен быть не менее 8192.';
					}
					else
					{
						// Kill the old table
						$db->sql_query('DROP TABLE ' . $final . ';');
					}
					unset($final);
				}
			break;

			case 'oracle':
				if ($unicode_check)
				{
					$sql = "SELECT *
						FROM NLS_DATABASE_PARAMETERS
						WHERE PARAMETER = 'NLS_RDBMS_VERSION'
							OR PARAMETER = 'NLS_CHARACTERSET'";
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$stats[$row['parameter']] = $row['value'];
					}
					$db->sql_freeresult($result);

					if (version_compare($stats['NLS_RDBMS_VERSION'], '9.2', '<') && $stats['NLS_CHARACTERSET'] !== 'UTF8')
					{
						$error[] = 'Для установленной на сервере версии Oracle необходимо установить значение параметра <var>NLS_CHARACTERSET</var> равным <var>UTF8</var>. Либо обновите базу данных до версии 9.2 или выше, либо измените значение параметра.';
					}
				}
			break;

			case 'postgres':
				if ($unicode_check)
				{
					$sql = "SHOW server_encoding;";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if ($row['server_encoding'] !== 'UNICODE' && $row['server_encoding'] !== 'UTF8')
					{
						$error[] = 'Выбранная база данных создана не с кодировкой <var>UNICODE</var> или <var>UTF8</var>. Попробуйте установить конференцию в базу данных с кодировкой <var>UNICODE</var> или <var>UTF8</var>.';
					}
				}
			break;
		}

	}

	if ($error_connect && (!isset($error) || !sizeof($error)))
	{
		return true;
	}
	return false;
}

/**
* Get tables of a database
*/
function get_tables($db)
{
	switch ($db->sql_layer)
	{
		case 'mysql':
		case 'mysql4':
		case 'mysqli':
			$sql = 'SHOW TABLES';
		break;

		case 'sqlite':
			$sql = 'SELECT name
				FROM sqlite_master
				WHERE type = "table"';
		break;

		case 'mssql':
		case 'mssql_odbc':
		case 'mssqlnative':
			$sql = "SELECT name
				FROM sysobjects
				WHERE type='U'";
		break;

		case 'postgres':
			$sql = 'SELECT relname
				FROM pg_stat_user_tables';
		break;

		case 'firebird':
			$sql = 'SELECT rdb$relation_name
				FROM rdb$relations
				WHERE rdb$view_source is null
					AND rdb$system_flag = 0';
		break;

		case 'oracle':
			$sql = 'SELECT table_name
				FROM USER_TABLES';
		break;
	}

	$result = $db->sql_query($sql);

	$tables = array();

	while ($row = $db->sql_fetchrow($result))
	{
		$tables[] = current($row);
	}

	$db->sql_freeresult($result);

	return $tables;
}

/**
* Output an error message
* If skip is true, return and continue execution, else exit
*/
function error($error, $line, $file, $skip = false){
	if ($skip)
		{
			$legend = 'Ошибка при установке';
			$title = basename($file) . ' [ ' . $line . ' ]';
			$result = '<b style="color:red">' . $error . '</b>';
			return;
		}
	
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">';
	echo '<head>';
	echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
	echo '<title>Критическая ошибка при установке</title>';
	echo '<link href="style.css" rel="stylesheet" type="text/css" media="screen" />';
	echo '</head>';
	echo '<body id="errorpage">';
	echo '<div id="wrap">';
	echo '	<div id="page-header">';
	echo '	</div>';
	echo '	<div id="page-body">';
	echo '		<div id="acp">';
	echo '		<div class="panel">';
	echo '			<span class="corners-top"><span></span></span>';
	echo '			<div id="content">';
	echo '				<h1>Критическая ошибка при установке</h1>';
	echo '		<p>Критическая ошибка при установке</p><br />';
	echo '		<p>' . basename($file) . ' [ ' . $line . ' ]</p><br />';
	echo '		<p><b>' . $error . '</b></p><bк />';
	echo '			</div>';
	echo '			<span class="corners-bottom"><span></span></span>';
	echo '		</div>';
	echo '		</div>';
	echo '	</div>';
	echo '	<div id="page-footer">';
	echo '		Powered by <a href="http://abricos.org/">Abricos</a>';
	echo '	</div>';
	echo '</div>';
	echo '</body>';
	echo '</html>';

	if (!empty($db) && is_object($db))
	{
		$db->sql_close();
	}

	exit_handler();
}

function exit_handler()
{
	// As a pre-caution... some setups display a blank page if the flush() is not there.
	(ob_get_level() > 0) ? @ob_flush() : @flush();

	exit;
}

if (!function_exists('realpath'))
{
	/**
	* A wrapper for realpath
	* @ignore
	*/
	function u_realpath($path)
	{
		return u_own_realpath($path);
	}
}
else
{
	/**
	* A wrapper for realpath
	*/
	function u_realpath($path)
	{
		$realpath = realpath($path);

		// Strangely there are provider not disabling realpath but returning strange values. :o
		// We at least try to cope with them.
		if ($realpath === $path || $realpath === false)
		{
			return u_own_realpath($path);
		}

		// Check for DIRECTORY_SEPARATOR at the end (and remove it!)
		if (substr($realpath, -1) == DIRECTORY_SEPARATOR)
		{
			$realpath = substr($realpath, 0, -1);
		}

		return $realpath;
	}
}
function u_own_realpath($path)
{
	// Now to perform funky shizzle

	// Switch to use UNIX slashes
	$path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
	$path_prefix = '';

	// Determine what sort of path we have
	if (is_absolute($path))
	{
		$absolute = true;

		if ($path[0] == '/')
		{
			// Absolute path, *NIX style
			$path_prefix = '';
		}
		else
		{
			// Absolute path, Windows style
			// Remove the drive letter and colon
			$path_prefix = $path[0] . ':';
			$path = substr($path, 2);
		}
	}
	else
	{
		// Relative Path
		// Prepend the current working directory
		if (function_exists('getcwd'))
		{
			// This is the best method, hopefully it is enabled!
			$path = str_replace(DIRECTORY_SEPARATOR, '/', getcwd()) . '/' . $path;
			$absolute = true;
			if (preg_match('#^[a-z]:#i', $path))
			{
				$path_prefix = $path[0] . ':';
				$path = substr($path, 2);
			}
			else
			{
				$path_prefix = '';
			}
		}
		else if (isset($_SERVER['SCRIPT_FILENAME']) && !empty($_SERVER['SCRIPT_FILENAME']))
		{
			// Warning: If chdir() has been used this will lie!
			// Warning: This has some problems sometime (CLI can create them easily)
			$path = str_replace(DIRECTORY_SEPARATOR, '/', dirname($_SERVER['SCRIPT_FILENAME'])) . '/' . $path;
			$absolute = true;
			$path_prefix = '';
		}
		else
		{
			// We have no way of getting the absolute path, just run on using relative ones.
			$absolute = false;
			$path_prefix = '.';
		}
	}

	// Remove any repeated slashes
	$path = preg_replace('#/{2,}#', '/', $path);

	// Remove the slashes from the start and end of the path
	$path = trim($path, '/');

	// Break the string into little bits for us to nibble on
	$bits = explode('/', $path);

	// Remove any . in the path, renumber array for the loop below
	$bits = array_values(array_diff($bits, array('.')));

	// Lets get looping, run over and resolve any .. (up directory)
	for ($i = 0, $max = sizeof($bits); $i < $max; $i++)
	{
		// @todo Optimise
		if ($bits[$i] == '..' )
		{
			if (isset($bits[$i - 1]))
			{
				if ($bits[$i - 1] != '..')
				{
					// We found a .. and we are able to traverse upwards, lets do it!
					unset($bits[$i]);
					unset($bits[$i - 1]);
					$i -= 2;
					$max -= 2;
					$bits = array_values($bits);
				}
			}
			else if ($absolute) // ie. !isset($bits[$i - 1]) && $absolute
			{
				// We have an absolute path trying to descend above the root of the filesystem
				// ... Error!
				return false;
			}
		}
	}

	// Prepend the path prefix
	array_unshift($bits, $path_prefix);

	$resolved = '';

	$max = sizeof($bits) - 1;

	// Check if we are able to resolve symlinks, Windows cannot.
	$symlink_resolve = (function_exists('readlink')) ? true : false;

	foreach ($bits as $i => $bit)
	{
		if (@is_dir("$resolved/$bit") || ($i == $max && @is_file("$resolved/$bit")))
		{
			// Path Exists
			if ($symlink_resolve && is_link("$resolved/$bit") && ($link = readlink("$resolved/$bit")))
			{
				// Resolved a symlink.
				$resolved = $link . (($i == $max) ? '' : '/');
				continue;
			}
		}
		else
		{
			// Something doesn't exist here!
			// This is correct realpath() behaviour but sadly open_basedir and safe_mode make this problematic
			// return false;
		}
		$resolved .= $bit . (($i == $max) ? '' : '/');
	}

	// @todo If the file exists fine and open_basedir only has one path we should be able to prepend it
	// because we must be inside that basedir, the question is where...
	// @internal The slash in is_dir() gets around an open_basedir restriction
	if (!@file_exists($resolved) || (!@is_dir($resolved . '/') && !is_file($resolved)))
	{
		return false;
	}

	// Put the slashes back to the native operating systems slashes
	$resolved = str_replace('/', DIRECTORY_SEPARATOR, $resolved);

	// Check for DIRECTORY_SEPARATOR at the end (and remove it!)
	if (substr($resolved, -1) == DIRECTORY_SEPARATOR)
	{
		return substr($resolved, 0, -1);
	}

	return $resolved; // We got here, in the end!
}
?>
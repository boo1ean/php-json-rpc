<?php
defined('BASE_PATH') || define('BASE_PATH', realpath(dirname(__FILE__) . '/..'));
defined('APP_PATH')  || define('APP_PATH',  realpath(dirname(__FILE__) . '/../App'));

set_include_path(implode(PATH_SEPARATOR, array(
    BASE_PATH,
    get_include_path()
)));

require_once BASE_PATH . '/vendor/autoload.php';
require_once dirname(__FILE__) . '/lib/TestCase.php';

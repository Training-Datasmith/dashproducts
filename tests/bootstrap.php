<?php

define('_PS_VERSION_', '1.7.6.0');
define('_DB_PREFIX_', 'ps_');
define('_PS_USE_SQL_SLAVE_', false);
define('_PS_PRODUCT_IMG_DIR_', '/img/p/');
define('_PS_PROD_IMG_DIR_', '/img/p/');

$root = dirname(__DIR__);
$testsAutoload = dirname(__DIR__) . '/vendor-phpunit/autoload.php';
if (is_file($testsAutoload)) {
    require_once $testsAutoload;
}
require_once $root . '/tests/stubs/prestashop_stubs.php';
require_once $root . '/tests/DashproductsTestCase.php';

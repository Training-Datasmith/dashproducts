<?php

function pSQL($string, $htmlOK = false)
{
    TestState::$psqlCalls[] = $string;

    return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $string);
}

class TestState
{
    public static $configuration = array();
    public static $installedModules = array();
    public static $configurePermission = true;
    public static $orders = array();
    public static $orderDetailsByOrder = array();
    public static $bestSellerRows = array();
    public static $viewedRows = array();
    public static $searchRows = array();
    public static $executeSResult = array();
    public static $executeSShouldFail = false;
    public static $getValueResult = null;
    public static $getValueResultCart = 0;
    public static $getValueResultPurchased = 0;
    public static $loadedProductIds = array();
    public static $productCovers = array();
    public static $productDefaultCategories = array();
    public static $productNames = array();
    public static $productPricesStatic = array();
    public static $categories = array();
    public static $imagePaths = array();
    public static $currencyById = array();
    public static $registeredHooks = array();
    public static $parentInstallResult = true;
    public static $registerHookResults = array();
    public static $unregisterHookResults = array();
    public static $psqlCalls = array();
    public static $htmlentitiesCalls = array();
    public static $formatPriceCalls = array();
    public static $displayDateCalls = array();
    public static $helperForms = array();
    public static $shopRestrictionSql = ' AND o.id_shop IN (1) ';
    public static $shopRestrictionShareOrder = ' /*SHARE_ORDER*/ AND o.id_shop IN (1) ';
    public static $thumbnailResult = 'THUMB';
    public static $displayTemplate = 'TPL:dashboard_zone_two.tpl';
    public static $adminLinks = array();

    public static function reset()
    {
        self::$configuration = array(
            'DASHPRODUCT_NBR_SHOW_LAST_ORDER' => 10,
            'DASHPRODUCT_NBR_SHOW_BEST_SELLER' => 10,
            'DASHPRODUCT_NBR_SHOW_MOST_VIEWED' => 10,
            'DASHPRODUCT_NBR_SHOW_TOP_SEARCH' => 10,
            'PS_STATSDATA_PAGESVIEWS' => 1,
            'PS_LANG_DEFAULT' => 1,
            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG' => 0,
        );
        self::$installedModules = array('statssearch');
        self::$configurePermission = true;
        self::$orders = array();
        self::$orderDetailsByOrder = array();
        self::$bestSellerRows = array();
        self::$viewedRows = array();
        self::$searchRows = array();
        self::$executeSResult = array();
        self::$executeSShouldFail = false;
        self::$getValueResult = 0;
        self::$getValueResultCart = 0;
        self::$getValueResultPurchased = 0;
        self::$loadedProductIds = array();
        self::$productCovers = array();
        self::$productDefaultCategories = array();
        self::$productNames = array();
        self::$productPricesStatic = array();
        self::$categories = array();
        self::$imagePaths = array();
        self::$currencyById = array(1 => array('iso_code' => 'EUR'));
        self::$registeredHooks = array();
        self::$parentInstallResult = true;
        self::$registerHookResults = array();
        self::$unregisterHookResults = array();
        self::$psqlCalls = array();
        self::$htmlentitiesCalls = array();
        self::$formatPriceCalls = array();
        self::$displayDateCalls = array();
        self::$helperForms = array();
        self::$adminLinks = array();
        DbStub::$queries = array();
        DbStub::$getValueQueries = array();
        Context::resetInstance();
    }
}

class Configuration
{
    public static function get($key)
    {
        return isset(TestState::$configuration[$key]) ? TestState::$configuration[$key] : null;
    }

    public static function updateValue($key, $value)
    {
        TestState::$configuration[$key] = $value;

        return true;
    }
}

class DbStub
{
    public static $queries = array();
    public static $getValueQueries = array();

    public static function getInstance($slave = false)
    {
        return new self();
    }

    public function executeS($sql)
    {
        self::$queries[] = $sql;

        if (TestState::$executeSShouldFail) {
            return false;
        }

        if (stripos($sql, 'page_viewed') !== false) {
            return TestState::$viewedRows;
        }
        if (stripos($sql, 'statssearch') !== false) {
            return TestState::$searchRows;
        }
        if (stripos($sql, 'order_detail` od') !== false && stripos($sql, 'JOIN') !== false && stripos($sql, 'product_quantity') === false) {
            return array();
        }
        if (stripos($sql, 'orders` o') !== false && stripos($sql, 'best') === false && stripos($sql, 'product_quantity-product_quantity_refunded') !== false) {
            return TestState::$bestSellerRows;
        }
        if (stripos($sql, 'SUM(product_quantity-product_quantity_refunded-product_quantity_return)') !== false
            || (stripos($sql, 'invoice_date') !== false && stripos($sql, 'product_attribute') !== false)) {
            return TestState::$bestSellerRows;
        }

        return TestState::$executeSResult;
    }

    public function getValue($sql)
    {
        self::$getValueQueries[] = $sql;

        if (stripos($sql, 'cart_product') !== false) {
            return TestState::$getValueResultCart;
        }
        if (stripos($sql, 'sum(') !== false) {
            return TestState::$getValueResult;
        }
        if (stripos($sql, 'order_detail') !== false) {
            return TestState::$getValueResultPurchased;
        }

        return TestState::$getValueResult;
    }
}

class Db extends DbStub
{
}

class Shop
{
    const SHARE_ORDER = 'share_order';

    public static function addSqlRestriction($share = false, $alias = null)
    {
        if ($share === self::SHARE_ORDER) {
            return TestState::$shopRestrictionShareOrder;
        }

        return TestState::$shopRestrictionSql;
    }
}

class Tools
{
    public static function htmlentitiesUTF8($string)
    {
        TestState::$htmlentitiesCalls[] = $string;

        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    public static function displayDate($date)
    {
        TestState::$displayDateCalls[] = $date;

        return 'DATE:' . $date;
    }

    public static function getValue($key)
    {
        return null;
    }
}

class Validate
{
    public static function isLoadedObject($object)
    {
        if ($object instanceof Product) {
            return in_array((int) $object->id, TestState::$loadedProductIds, true);
        }

        return true;
    }

    public static function isModuleName($name)
    {
        return is_string($name) && preg_match('/^[a-z0-9_-]+$/i', $name);
    }
}

class Module
{
    public $name;
    public $tab;
    public $version;
    public $author;
    public $displayName;
    public $description;
    public $ps_versions_compliancy;
    public $context;
    public $table = 'module';
    public $identifier = 'id_module';

    public function __construct()
    {
        $this->context = Context::getContext();
    }

    protected function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        if (!empty($parameters)) {
            return vsprintf($id, $parameters);
        }

        return $id;
    }

    public function install()
    {
        return TestState::$parentInstallResult;
    }

    public function registerHook($hookName)
    {
        if (isset(TestState::$registerHookResults[$hookName])) {
            $result = TestState::$registerHookResults[$hookName];
        } else {
            $result = true;
        }
        if ($result) {
            TestState::$registeredHooks[] = $hookName;
        }

        return $result;
    }

    public function unregisterHook($hookName)
    {
        if (isset(TestState::$unregisterHookResults[$hookName])) {
            return TestState::$unregisterHookResults[$hookName];
        }

        return true;
    }

    public function getPermission($variable)
    {
        if ($variable === 'configure') {
            return TestState::$configurePermission;
        }

        return false;
    }

    public function display($file, $template)
    {
        return TestState::$displayTemplate;
    }

    public static function isInstalled($moduleName)
    {
        return in_array($moduleName, TestState::$installedModules, true);
    }

    public static function getInstanceByName($name)
    {
        return null;
    }
}

class Context
{
    public $smarty;
    public $link;
    public $language;
    public $currency;
    public $controller;
    private static $instance;

    public function __construct()
    {
        $this->smarty = new SmartyStub();
        $this->link = new LinkStub();
        $this->language = new stdClass();
        $this->language->id = 1;
        $this->currency = new stdClass();
        $this->currency->iso_code = 'EUR';
        $this->controller = new AdminControllerStub();
    }

    public function getCurrentLocale()
    {
        return new LocaleStub();
    }

    public static function getContext()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function resetInstance()
    {
        self::$instance = new self();
    }
}

class AdminControllerStub
{
    public $imageType = 'jpg';

    public function getLanguages()
    {
        return array();
    }
}

class SmartyStub
{
    public $assigned = array();

    public function assign($vars)
    {
        $this->assigned = $vars;
    }
}

class LinkStub
{
    public function getAdminLink($controller, $withToken = true, $params = array(), $extra = array())
    {
        $key = $controller . ':' . md5(serialize(array($params, $extra)));
        if (!isset(TestState::$adminLinks[$key])) {
            TestState::$adminLinks[$key] = 'LINK:' . $controller;
        }

        return TestState::$adminLinks[$key];
    }
}

class LocaleStub
{
    public function formatPrice($amount, $iso)
    {
        TestState::$formatPriceCalls[] = array('amount' => $amount, 'iso' => $iso);

        return sprintf('PRICE:%.2f %s', (float) $amount, $iso);
    }
}

class Order
{
    public static function getOrdersWithInformations($limit)
    {
        return array_slice(TestState::$orders, 0, (int) $limit);
    }
}

class OrderDetail
{
    public static function getList($idOrder)
    {
        if (isset(TestState::$orderDetailsByOrder[$idOrder])) {
            return TestState::$orderDetailsByOrder[$idOrder];
        }

        return array();
    }
}

class Currency
{
    public static function getCurrency($id)
    {
        if (isset(TestState::$currencyById[$id])) {
            return TestState::$currencyById[$id];
        }

        return array('iso_code' => 'EUR');
    }
}

class Product
{
    public $id;
    public $name = 'Product';

    public function __construct($id, $full = false, $idLang = null)
    {
        $this->id = (int) $id;
        if (isset(TestState::$productNames[$this->id])) {
            $this->name = TestState::$productNames[$this->id];
        }
    }

    public function getDefaultCategory()
    {
        if (isset(TestState::$productDefaultCategories[$this->id])) {
            return TestState::$productDefaultCategories[$this->id];
        }

        return 2;
    }

    public static function getCover($idProduct)
    {
        if (isset(TestState::$productCovers[$idProduct])) {
            return TestState::$productCovers[$idProduct];
        }

        return false;
    }

    public static function getPriceStatic($idProduct)
    {
        if (isset(TestState::$productPricesStatic[$idProduct])) {
            return TestState::$productPricesStatic[$idProduct];
        }

        return 0;
    }
}

class Category
{
    public $name;

    public function __construct($id, $idLang = null)
    {
        $this->name = isset(TestState::$categories[$id]) ? TestState::$categories[$id] : 'Category';
    }
}

class Image
{
    public $id_image;

    public function __construct($id)
    {
        $this->id_image = $id;
    }

    public function getExistingImgPath()
    {
        return isset(TestState::$imagePaths[$this->id_image]) ? TestState::$imagePaths[$this->id_image] : '1/1';
    }
}

class ImageManager
{
    public static function thumbnail($path, $cacheName, $size, $type)
    {
        return TestState::$thumbnailResult . ':' . basename($path);
    }
}

class Language
{
    public $id;

    public function __construct($id)
    {
        $this->id = (int) $id;
    }
}

class HelperForm
{
    public $tpl_vars = array();
    public $submit_action;

    public function generateForm($forms)
    {
        TestState::$helperForms[] = $forms;

        return 'HELPER_FORM';
    }
}

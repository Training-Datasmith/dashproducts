<?php

abstract class DashproductsTestCase extends PHPUnit_Framework_TestCase
{
    /** @var dashproducts */
    protected $module;

    protected function setUp()
    {
        $root = dirname(__DIR__);
        if (!class_exists('dashproducts', false)) {
            require_once $root . '/dashproducts.php';
        }
        if (!function_exists('upgrade_module_2_1_2')) {
            require_once $root . '/upgrade/upgrade-2.1.2.php';
        }
        TestState::reset();
        $this->module = new dashproducts();
    }

    /**
     * @param string $sql
     *
     * @return string
     */
    protected function normalizeSql($sql)
    {
        return strtolower(preg_replace('/\s+/', ' ', trim($sql)));
    }

    /**
     * @param string $haystack
     * @param string $needle
     */
    protected function assertSqlContains($haystack, $needle)
    {
        $this->assertContains($needle, $this->normalizeSql($haystack));
    }

    /**
     * @return string|null
     */
    protected function lastExecuteSQuery()
    {
        if (empty(DbStub::$queries)) {
            return null;
        }

        return DbStub::$queries[count(DbStub::$queries) - 1];
    }
}

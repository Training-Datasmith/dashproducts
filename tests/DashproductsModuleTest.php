<?php

class DashproductsModuleTest extends DashproductsTestCase
{
    public function testConstructorSetsModuleIdentity()
    {
        $this->assertSame('dashproducts', $this->module->name);
        $this->assertSame('administration', $this->module->tab);
        $this->assertSame('2.2.1', $this->module->version);
        $this->assertSame('PrestaShop', $this->module->author);
        $this->assertSame('1.7.6.0', $this->module->ps_versions_compliancy['min']);
        $this->assertSame('Dashboard Products', $this->module->displayName);
        $this->assertContains('latest orders', strtolower($this->module->description));
    }

    public function testClassVersionMatchesConfigXml()
    {
        $xml = simplexml_load_file(dirname(__DIR__) . '/config.xml');
        $this->assertSame('2.2.1', (string) $xml->version);
    }

    public function testInstallWritesDefaultsAndRegistersHooks()
    {
        TestState::$configuration = array();
        $this->assertTrue($this->module->install());
        $this->assertSame(10, Configuration::get('DASHPRODUCT_NBR_SHOW_LAST_ORDER'));
        $this->assertSame(10, Configuration::get('DASHPRODUCT_NBR_SHOW_BEST_SELLER'));
        $this->assertSame(10, Configuration::get('DASHPRODUCT_NBR_SHOW_MOST_VIEWED'));
        $this->assertSame(10, Configuration::get('DASHPRODUCT_NBR_SHOW_TOP_SEARCH'));
        $this->assertContains('dashboardZoneTwo', TestState::$registeredHooks);
        $this->assertContains('dashboardData', TestState::$registeredHooks);
    }

    public function testInstallReturnsFalseWhenParentInstallFails()
    {
        TestState::$parentInstallResult = false;
        $this->assertFalse($this->module->install());
    }

    public function testInstallReturnsFalseWhenAHookFails()
    {
        TestState::$registerHookResults = array('dashboardZoneTwo' => false);
        $this->assertFalse($this->module->install());
    }
}

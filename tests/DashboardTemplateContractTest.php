<?php

class DashboardTemplateContractTest extends PHPUnit_Framework_TestCase
{
    public function testTemplateTargetsHookPayload()
    {
        $tpl = file_get_contents(dirname(__DIR__) . '/views/templates/hook/dashboard_zone_two.tpl');
        $this->assertContains('table_recent_orders', $tpl);
        $this->assertContains('table_best_sellers', $tpl);
        $this->assertContains('table_most_viewed', $tpl);
        $this->assertContains('table_top_10_most_search', $tpl);
        $this->assertContains('dashproducts_config_form', $tpl);
        $this->assertContains('DASHPRODUCT_NBR_SHOW_LAST_ORDER', $tpl);
        $this->assertContains('DASHPRODUCT_NBR_SHOW_BEST_SELLER', $tpl);
        $this->assertContains('DASHPRODUCT_NBR_SHOW_TOP_SEARCH', $tpl);
        $this->assertContains('date_from', $tpl);
        $this->assertContains('date_to', $tpl);
    }
}

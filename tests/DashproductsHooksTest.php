<?php

class DashproductsHooksTest extends DashproductsTestCase
{
    public function testDashboardDataReturnsTheFourTables()
    {
        TestState::$orders = array(
            array(
                'id_order' => 1,
                'id_customer' => 1,
                'id_currency' => 1,
                'firstname' => 'A',
                'lastname' => 'B',
                'total_paid_tax_excl' => 1,
                'date_add' => '2024-01-01',
                'state_name' => 'Ok',
                'valid' => 1,
            ),
        );
        TestState::$orderDetailsByOrder[1] = array(array());
        TestState::$bestSellerRows = array(
            array('product_id' => 1, 'product_name' => 'P', 'total' => 1, 'price' => '1', 'price_attribute' => null, 'sales' => 1, 'expenses' => 0),
        );
        TestState::$loadedProductIds = array(1);
        TestState::$viewedRows = array(array('id_object' => 1, 'counter' => 1));
        TestState::$searchRows = array(array('keywords' => 'k', 'count_keywords' => 1, 'results' => 1));

        $data = $this->module->hookDashboardData(array('date_from' => '2024-02-01', 'date_to' => '2024-02-28'));
        $this->assertArrayHasKey('data_table', $data);
        $keys = array_keys($data['data_table']);
        sort($keys);
        $this->assertSame(
            array('table_best_sellers', 'table_most_viewed', 'table_recent_orders', 'table_top_10_most_search'),
            $keys
        );
        $found = false;
        foreach (DbStub::$queries as $sql) {
            if (strpos($sql, '2024-02-01 00:00:00') !== false) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    public function testZoneTwoAssignsLimitsDatesAndFormWhenAllowed()
    {
        TestState::$configurePermission = true;
        $html = $this->module->hookDashboardZoneTwo(array('date_from' => '2024-03-01', 'date_to' => '2024-03-31'));
        $assigned = Context::getContext()->smarty->assigned;
        $this->assertSame(10, $assigned['DASHPRODUCT_NBR_SHOW_LAST_ORDER']);
        $this->assertSame('DATE:2024-03-01', $assigned['date_from']);
        $this->assertSame('HELPER_FORM', $assigned['dashproducts_config_form']);
        $this->assertSame('TPL:dashproducts.php:dashboard_zone_two.tpl', $html);
    }

    public function testZoneTwoOmitsFormWithoutPermission()
    {
        TestState::$configurePermission = false;
        $this->module->hookDashboardZoneTwo(array('date_from' => '2024-03-01', 'date_to' => '2024-03-31'));
        $this->assertNull(Context::getContext()->smarty->assigned['dashproducts_config_form']);
        $this->assertSame(array(), TestState::$helperForms);
    }
}

<?php

class DashproductsRecentOrdersTest extends DashproductsTestCase
{
    protected function seedOrder($overrides = array())
    {
        $order = array_merge(
            array(
                'id_order' => 1,
                'id_customer' => 3,
                'id_currency' => 1,
                'firstname' => 'Ann <b>',
                'lastname' => 'Lee',
                'total_paid_tax_excl' => 19.9,
                'date_add' => '2024-01-15 10:00:00',
                'state_name' => 'Paid <x>',
                'valid' => 1,
            ),
            $overrides
        );
        TestState::$orders[] = $order;
        TestState::$orderDetailsByOrder[$order['id_order']] = array(array('id'), array('id'));

        return $order;
    }

    public function testHeadersAndValidOrderRow()
    {
        $this->seedOrder();
        $table = $this->module->getTableRecentOrders();
        $this->assertCount(6, $table['header']);
        $row = $table['body'][0];
        $this->assertSame(2, $row[1]['value']);
        $this->assertSame('PRICE:19.90 EUR', $row[2]['value']);
        $this->assertSame('<span class="badge badge-success">', $row[2]['wrapper_start']);
        $this->assertSame('</span>', $row[2]['wrapper_stop']);
        $this->assertArrayNotHasKey('wrapper_end', $row[2]);
        $this->assertSame('</a>', $row[5]['wrapper_stop']);
        $this->assertContains('LINK:AdminCustomers', $row[0]['value']);
        $this->assertContains('Ann &lt;b&gt;', $row[0]['value']);
        $this->assertContains('Paid &lt;x&gt;', $row[4]['value']);
        $this->assertContains('LINK:AdminOrders', $row[5]['wrapper_start']);
    }

    public function testInvalidOrderHasNoBadge()
    {
        $this->seedOrder(array('valid' => 0));
        $row = $this->module->getTableRecentOrders()['body'][0];
        $this->assertSame('', $row[2]['wrapper_start']);
        $this->assertSame('', $row[2]['wrapper_stop']);
    }

    public function testDeletedCustomerHasNoLink()
    {
        $this->seedOrder(array('id_customer' => 0));
        $row = $this->module->getTableRecentOrders()['body'][0];
        $this->assertSame('Deleted customer', $row[0]['value']);
        $this->assertNotContains('<a', $row[0]['value']);
    }

    public function testLimitFallsBackToTenAndHonorsConfiguredLimit()
    {
        for ($i = 0; $i < 12; ++$i) {
            TestState::$orders[] = array(
                'id_order' => $i + 10,
                'id_customer' => 1,
                'id_currency' => 1,
                'firstname' => 'A',
                'lastname' => 'B',
                'total_paid_tax_excl' => 1,
                'date_add' => '2024-01-01',
                'state_name' => 'Ok',
                'valid' => 1,
            );
            TestState::$orderDetailsByOrder[$i + 10] = array();
        }
        unset(TestState::$configuration['DASHPRODUCT_NBR_SHOW_LAST_ORDER']);
        $this->assertCount(10, $this->module->getTableRecentOrders()['body']);

        TestState::$configuration['DASHPRODUCT_NBR_SHOW_LAST_ORDER'] = 2;
        $this->assertCount(2, $this->module->getTableRecentOrders()['body']);
    }

    public function testFailedOrderDetailListCountsAsZero()
    {
        $this->seedOrder();
        TestState::$orderDetailsByOrder[1] = false;
        $row = $this->module->getTableRecentOrders()['body'][0];
        $this->assertSame(0, $row[1]['value']);
    }
}

<?php

class DashproductsStatsQueriesTest extends DashproductsTestCase
{
    public function testGetTotalProductSalesReturnsFloatAndUsesShareOrder()
    {
        TestState::$getValueResult = '15.75';
        $value = $this->module->getTotalProductSales('2024-01-01', '2024-01-31', 4);
        $this->assertSame(15.75, $value);
        $sql = DbStub::$getValueQueries[0];
        $this->assertSqlContains($sql, 'product_quantity');
        $this->assertSqlContains($sql, 'product_price');
        $this->assertSqlContains($sql, 'o.valid = 1');
        $this->assertSqlContains($sql, '2024-01-31 23:59:59');
        $this->assertContains('/*SHARE_ORDER*/', $sql);
    }

    public function testSalesQueryCastsProductId()
    {
        TestState::$getValueResult = 0;
        $this->module->getTotalProductSales('2024-01-01', '2024-01-31', '4; DROP TABLE ps_orders');
        $sql = DbStub::$getValueQueries[0];
        $this->assertSqlContains($sql, 'product_id` = 4');
        $this->assertNotContains('drop', $this->normalizeSql($sql));
    }
}

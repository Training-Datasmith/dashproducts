<?php

class DashproductsMostViewedTest extends DashproductsTestCase
{
    protected function seedViewedProduct($id, $counter)
    {
        TestState::$viewedRows = array(array('id_object' => $id, 'counter' => $counter));
        TestState::$loadedProductIds = array($id);
        TestState::$productNames[$id] = 'Viewed <i>';
        TestState::$productPricesStatic[$id] = 9.5;
    }

    public function testStatsDisabledReturnsAlertString()
    {
        TestState::$configuration['PS_STATSDATA_PAGESVIEWS'] = 0;
        $table = $this->module->getTableMostViewed('2024-01-01', '2024-01-31');
        $this->assertTrue(is_string($table['body']));
        $this->assertContains('Save global page views', $table['body']);
        $this->assertSame(array(), DbStub::$queries);
    }

    public function testStatsEnabledEmptyIsEmptyArray()
    {
        TestState::$viewedRows = array();
        $table = $this->module->getTableMostViewed('2024-01-01', '2024-01-31');
        $this->assertSame(array(), $table['body']);
    }

    public function testConversionRateAndCartCount()
    {
        $this->seedViewedProduct(5, 3);
        TestState::$getValueResultCart = 4;
        TestState::$getValueResultPurchased = 2;
        $row = $this->module->getTableMostViewed('2024-01-01', '2024-01-31')['body'][0];
        $this->assertSame(3, $row[2]['value']);
        $this->assertSame(4, $row[3]['value']);
        $this->assertSame(2, $row[4]['value']);
        $this->assertSame('66.7%', $row[5]['value']);
        $this->assertContains('Viewed &lt;i&gt;', $row[1]['value']);
    }

    public function testViewedSqlAggregatesAndIncludesTheLastDay()
    {
        $this->seedViewedProduct(1, 1);
        TestState::$configuration['DASHPRODUCT_NBR_SHOW_MOST_VIEWED'] = 7;
        $this->module->getTableMostViewed('2024-01-01', '2024-01-31');
        $sql = $this->lastExecuteSQuery();
        $this->assertSqlContains($sql, 'sum(pv.counter)');
        $this->assertSqlContains($sql, 'group by p.id_object');
        $this->assertSqlContains($sql, 'order by counter desc');
        $this->assertSqlContains($sql, 'limit 7');
        $this->assertSqlContains($sql, '2024-01-31 23:59:59');
    }

    public function testCartAndPurchasedSqlIncludeTheLastDay()
    {
        $this->seedViewedProduct(9, 1);
        $this->module->getTableMostViewed('2024-01-01', '2024-01-31');
        $queries = DbStub::$getValueQueries;
        $this->assertNotEmpty($queries);
        $cartSql = $queries[0];
        $this->assertSqlContains($cartSql, 'cart_product');
        $this->assertSqlContains($cartSql, '2024-01-31 23:59:59');
        $purchasedSql = $queries[count($queries) - 1];
        $this->assertSqlContains($purchasedSql, 'o.valid = 1');
        $this->assertSqlContains($purchasedSql, '2024-01-31 23:59:59');
        $this->assertSqlContains($purchasedSql, 'product_id` = 9');
    }
}

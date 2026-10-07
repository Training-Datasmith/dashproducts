<?php

class DashproductsBestSellersTest extends DashproductsTestCase
{
    protected function seedProductRow($overrides = array())
    {
        $row = array_merge(
            array(
                'product_id' => 7,
                'product_name' => 'Mug <b>',
                'total' => 4,
                'price' => '10.000000',
                'price_attribute' => '2.500000',
                'sales' => 80,
                'expenses' => 30,
            ),
            $overrides
        );
        TestState::$bestSellerRows = array($row);
        TestState::$loadedProductIds = array((int) $row['product_id']);
        TestState::$categories[2] = 'Mugs';
        TestState::$productDefaultCategories[(int) $row['product_id']] = 2;
    }

    public function testEmptyAndFailedQueryReturnHeaderAndEmptyBody()
    {
        TestState::$bestSellerRows = array();
        $table = $this->module->getTableBestSellers('2024-01-01', '2024-01-31');
        $this->assertCount(6, $table['header']);
        $this->assertSame(array(), $table['body']);

        TestState::$executeSShouldFail = true;
        $table = $this->module->getTableBestSellers('2024-01-01', '2024-01-31');
        $this->assertSame(array(), $table['body']);
    }

    public function testSkipsProductsThatDoNotLoad()
    {
        TestState::$bestSellerRows = array(
            array('product_id' => 1, 'product_name' => 'A', 'total' => 1, 'price' => '1', 'price_attribute' => null, 'sales' => 1, 'expenses' => 0),
            array('product_id' => 2, 'product_name' => 'B', 'total' => 2, 'price' => '1', 'price_attribute' => null, 'sales' => 2, 'expenses' => 0),
        );
        TestState::$loadedProductIds = array(2);
        $this->assertCount(1, $this->module->getTableBestSellers('2024-01-01', '2024-01-31')['body']);
    }

    public function testRowUsesBasePricePlusAttributeImpact()
    {
        $this->seedProductRow();
        $cells = $this->module->getTableBestSellers('2024-01-01', '2024-01-31')['body'][0];
        $this->assertContains('PRICE:12.50 EUR', $cells[1]['value']);
        $this->assertContains('Mug &lt;b&gt;', $cells[1]['value']);
        $this->assertSame('PRICE:50.00 EUR', $cells[5]['value']);
        $this->assertSame(4, $cells[3]['value']);
        $this->assertSame(12.5, TestState::$formatPriceCalls[0]['amount']);
    }

    public function testZeroAndNullAttributeKeepBasePrice()
    {
        $this->seedProductRow(array('price_attribute' => '0.000000'));
        $cells = $this->module->getTableBestSellers('2024-01-01', '2024-01-31')['body'][0];
        $this->assertContains('PRICE:10.00 EUR', $cells[1]['value']);

        $this->seedProductRow(array('price_attribute' => null));
        $cells = $this->module->getTableBestSellers('2024-01-01', '2024-01-31')['body'][0];
        $this->assertContains('PRICE:10.00 EUR', $cells[1]['value']);
    }

    public function testNegativeAttributeImpactIsAdded()
    {
        $this->seedProductRow(array('price' => '10.000000', 'price_attribute' => '-3.000000'));
        $cells = $this->module->getTableBestSellers('2024-01-01', '2024-01-31')['body'][0];
        $this->assertContains('PRICE:7.00 EUR', $cells[1]['value']);
    }

    public function testDefaultCategoryArrayShape()
    {
        $this->seedProductRow();
        TestState::$productDefaultCategories[7] = array('id_category_default' => 4);
        TestState::$categories[4] = 'Special';
        $cells = $this->module->getTableBestSellers('2024-01-01', '2024-01-31')['body'][0];
        $this->assertSame('Special', $cells[2]['value']);
    }

    public function testSqlBoundsShopRestrictionAndLimit()
    {
        TestState::$configuration['DASHPRODUCT_NBR_SHOW_BEST_SELLER'] = 5;
        $this->seedProductRow();
        $this->module->getTableBestSellers('2024-01-01', '2024-01-31');
        $sql = $this->lastExecuteSQuery();
        $this->assertSqlContains($sql, 'invoice_date');
        $this->assertSqlContains($sql, '2024-01-01 00:00:00');
        $this->assertSqlContains($sql, '2024-01-31 23:59:59');
        $this->assertSqlContains($sql, 'limit 5');
        $this->assertSqlContains($sql, 'id_shop');
        $this->assertNotContains('cast(', $this->normalizeSql($sql));
    }

    public function testDateValueIsPassedThroughPsql()
    {
        $this->seedProductRow();
        $this->module->getTableBestSellers("2024-01-01' OR '1'='1", '2024-01-31');
        $this->assertNotEmpty(TestState::$psqlCalls);
        $escaped = pSQL("2024-01-01' OR '1'='1");
        $sql = $this->lastExecuteSQuery();
        $this->assertContains($escaped, $sql);
        $this->assertNotContains("2024-01-01' OR '1'='1", $sql);
    }
}

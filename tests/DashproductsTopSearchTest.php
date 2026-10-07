<?php

class DashproductsTopSearchTest extends DashproductsTestCase
{
    public function testNoStatssearchModuleSkipsTheQuery()
    {
        TestState::$installedModules = array();
        $table = $this->module->getTableTop10MostSearch('2024-01-01', '2024-01-31');
        $this->assertSame(array(), $table['body']);
        $this->assertSame(array(), DbStub::$queries);
    }

    public function testRowsMapKeywordsCountAndResults()
    {
        TestState::$searchRows = array(
            array('keywords' => 'a<b>', 'count_keywords' => 6, 'results' => 3),
        );
        $row = $this->module->getTableTop10MostSearch('2024-01-01', '2024-01-31')['body'][0];
        $this->assertSame('a&lt;b&gt;', $row[0]['value']);
        $this->assertSame(6, $row[1]['value']);
        $this->assertSame(3, $row[2]['value']);
        $this->assertContains('a<b>', TestState::$htmlentitiesCalls);
    }

    public function testSearchSqlGroupsAndClosesTheLastDay()
    {
        TestState::$searchRows = array();
        TestState::$configuration['DASHPRODUCT_NBR_SHOW_TOP_SEARCH'] = 8;
        $this->module->getTableTop10MostSearch('2024-01-01', '2024-01-31');
        $sql = $this->lastExecuteSQuery();
        $this->assertSqlContains($sql, 'statssearch');
        $this->assertSqlContains($sql, 'group by');
        $this->assertSqlContains($sql, 'order by');
        $this->assertSqlContains($sql, 'limit 8');
        $this->assertSqlContains($sql, '2024-01-31 23:59:59');
    }
}

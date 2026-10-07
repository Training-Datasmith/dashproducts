<?php

class DashproductsConfigTest extends DashproductsTestCase
{
    public function testGetConfigFieldsValuesReadsTheFourKeys()
    {
        TestState::$configuration['DASHPRODUCT_NBR_SHOW_LAST_ORDER'] = 5;
        TestState::$configuration['DASHPRODUCT_NBR_SHOW_BEST_SELLER'] = 20;
        $values = $this->module->getConfigFieldsValues();
        $this->assertSame(5, $values['DASHPRODUCT_NBR_SHOW_LAST_ORDER']);
        $this->assertSame(20, $values['DASHPRODUCT_NBR_SHOW_BEST_SELLER']);
    }

    public function testValidateAcceptsIntegersAndExactDigitStrings()
    {
        $config = array(
            'DASHPRODUCT_NBR_SHOW_LAST_ORDER' => 5,
            'DASHPRODUCT_NBR_SHOW_BEST_SELLER' => '10',
            'DASHPRODUCT_NBR_SHOW_MOST_VIEWED' => 20,
            'DASHPRODUCT_NBR_SHOW_TOP_SEARCH' => '50',
        );
        $this->assertSame(array(), $this->module->validateDashConfig($config));
    }

    public function testValidateRejectsMissingField()
    {
        $config = $this->module->getConfigFieldsValues();
        unset($config['DASHPRODUCT_NBR_SHOW_LAST_ORDER']);
        $errors = $this->module->validateDashConfig($config);
        $this->assertArrayHasKey('DASHPRODUCT_NBR_SHOW_LAST_ORDER', $errors);
    }

    public function testValidateRejectsOutOfSetAndJunk()
    {
        foreach (array(7, '7', '5abc', '10foo', '', true, null) as $bad) {
            $config = $this->module->getConfigFieldsValues();
            $config['DASHPRODUCT_NBR_SHOW_LAST_ORDER'] = $bad;
            $errors = $this->module->validateDashConfig($config);
            $this->assertArrayHasKey('DASHPRODUCT_NBR_SHOW_LAST_ORDER', $errors);
        }
    }

    public function testSaveWithoutConfigurePermissionDoesNotWrite()
    {
        TestState::$configurePermission = false;
        TestState::$configuration['DASHPRODUCT_NBR_SHOW_LAST_ORDER'] = 10;
        $this->assertTrue($this->module->saveDashConfig(array('DASHPRODUCT_NBR_SHOW_LAST_ORDER' => 50)));
        $this->assertSame(10, Configuration::get('DASHPRODUCT_NBR_SHOW_LAST_ORDER'));
    }

    public function testSaveCastsPostedDigitsAndReportsNoError()
    {
        TestState::$configurePermission = true;
        $config = array(
            'DASHPRODUCT_NBR_SHOW_LAST_ORDER' => '20',
            'DASHPRODUCT_NBR_SHOW_BEST_SELLER' => '20',
            'DASHPRODUCT_NBR_SHOW_MOST_VIEWED' => '20',
            'DASHPRODUCT_NBR_SHOW_TOP_SEARCH' => '20',
        );
        $this->assertFalse($this->module->saveDashConfig($config));
        $this->assertSame(20, Configuration::get('DASHPRODUCT_NBR_SHOW_LAST_ORDER'));
    }

    public function testRenderConfigFormOffersTheFourLimits()
    {
        $this->module->renderConfigForm();
        $this->assertNotEmpty(TestState::$helperForms);
        $forms = TestState::$helperForms[0];
        $input = $forms[0]['form']['input'];
        $names = array();
        foreach ($input as $field) {
            $names[] = $field['name'];
            $ids = array();
            foreach ($field['options']['query'] as $option) {
                $ids[] = $option['id'];
            }
            $this->assertSame(array(5, 10, 20, 50), $ids);
        }
        $this->assertSame(
            array(
                'DASHPRODUCT_NBR_SHOW_LAST_ORDER',
                'DASHPRODUCT_NBR_SHOW_BEST_SELLER',
                'DASHPRODUCT_NBR_SHOW_MOST_VIEWED',
                'DASHPRODUCT_NBR_SHOW_TOP_SEARCH',
            ),
            $names
        );
    }
}

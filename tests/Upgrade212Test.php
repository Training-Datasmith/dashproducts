<?php

class Upgrade212Test extends DashproductsTestCase
{
    public function testUpgradeUnregistersRetiredHooks()
    {
        $module = $this->getMockBuilder('dashproducts')
            ->setMethods(array('unregisterHook'))
            ->getMock();
        $module->expects($this->at(0))
            ->method('unregisterHook')
            ->with('actionObjectOrderAddAfter')
            ->willReturn(true);
        $module->expects($this->at(1))
            ->method('unregisterHook')
            ->with('actionSearch')
            ->willReturn(true);

        $this->assertTrue(upgrade_module_2_1_2($module));
    }

    public function testUpgradeReturnsFalseWhenFirstUnregisterFails()
    {
        $module = $this->getMockBuilder('dashproducts')
            ->setMethods(array('unregisterHook'))
            ->getMock();
        $module->expects($this->once())
            ->method('unregisterHook')
            ->with('actionObjectOrderAddAfter')
            ->willReturn(false);

        $this->assertFalse(upgrade_module_2_1_2($module));
    }
}

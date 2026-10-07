<?php

class GuardTest extends PHPUnit_Framework_TestCase
{
    public function testModuleFileExitsWhenVersionConstantMissing()
    {
        $root = dirname(__DIR__);
        $moduleFile = $root . '/dashproducts.php';
        $php = defined('PHP_BINARY') ? PHP_BINARY : 'php';
        $code = 'echo "BEFORE"; include ' . var_export($moduleFile, true) . '; echo "AFTER";';
        $command = escapeshellarg($php) . ' -r ' . escapeshellarg($code);

        $output = array();
        $exitCode = 0;
        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode);
        $this->assertSame('BEFORE', implode('', $output));
    }

    public function testUpgradeFileExitsWhenVersionConstantMissing()
    {
        $root = dirname(__DIR__);
        $upgradeFile = $root . '/upgrade/upgrade-2.1.2.php';
        $php = defined('PHP_BINARY') ? PHP_BINARY : 'php';
        $code = 'echo "BEFORE"; include ' . var_export($upgradeFile, true) . '; echo "AFTER";';
        $command = escapeshellarg($php) . ' -r ' . escapeshellarg($code);

        $output = array();
        $exitCode = 0;
        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode);
        $this->assertSame('BEFORE', implode('', $output));
    }
}

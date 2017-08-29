<?php
class WebTest extends PHPUnit_Extensions_Selenium2TestCase
{
    protected $coverageScriptUrl = 'http://localhost:8193/phpunit_coverage.php';

    protected function setUp()
    {
        PHPUnit_Extensions_Selenium2TestCase::setDefaultWaitUntilTimeout(0);
        PHPUnit_Extensions_Selenium2TestCase::setDefaultWaitUntilSleepInterval(500);
        $this->setBrowser('chrome');
        $this->setBrowserUrl('http://localhost:8192/');
    }

    public function testTitle()
    {
        $this->url('/');
        $this->assertEquals('List of Posts', $this->title());
    }
}
?>

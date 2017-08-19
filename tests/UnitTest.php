<?php
class WebTest extends PHPUnit_Extensions_Selenium2TestCase
{
    protected $coverageScriptUrl = 'http://localhost:8193/phpunit_coverage.php';

    protected function setUp()
    {
        $this->setBrowser('firefox');
        $this->setBrowserUrl('http://localhost:8192/status_api/overview');
    }

    public function testTitle()
    {
        $this->url('http://localhost:8192/status_api/overview');
        $this->assertEquals('MyEMSL Status - Overview', $this->title());
    }
}
?>

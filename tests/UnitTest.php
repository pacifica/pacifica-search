<?php
class WebTest extends PHPUnit_Extensions_Selenium2TestCase
{
    protected $coverageScriptUrl = 'http://localhost:8193/phpunit_coverage.php';

    protected function setUp()
    {
        $this->setBrowser('firefox');
        $this->setBrowserUrl('http://localhost:8192/');
    }

    public function testTitle()
    {
        $this->url('http://localhost:8192/');
        $this->assertEquals('List of Posts', $this->title());
    }
}
?>

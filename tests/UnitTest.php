<?php
/**
 * Selenium PHP Driver Class
 *
 * @category Class
 * @package  Tests
 * @author   David Brown
 * @license  https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html GNU Lesser General Public License, version 2.1
 * @link     https://github.com/pacifica/pacifica-search
 *
 */
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

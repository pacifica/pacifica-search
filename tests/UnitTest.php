<?php
/**
 * Selenium PHP Driver Module
 *
 * @category Module
 * @package  Tests
 * @author   David Brown <dmlb2000@gmail.com>
 * @license  https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html GNU Lesser General Public License, version 2.1
 * @link     https://github.com/pacifica/pacifica-search
 */

/**
 * Selenium PHP Driver Index.php tests
 *
 * @category Class
 * @package  MyClass
 * @author   David Brown <dmlb2000@gmail.com>
 * @license  https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html GNU Lesser General Public License, version 2.1
 * @link     https://github.com/pacifica/pacifica-search
 */
class WebTest extends PHPUnit_Extensions_Selenium2TestCase
{
    protected $coverageScriptUrl = 'http://localhost:8193/phpunit_coverage.php';

    /**
     * Setup the selenium test environment with firefox
     *
     * This function sets up the selenium browser with firefox and sets the url to
     * the correct localhost address for our testing environment.
     *
     * @return Nothing
     */
    protected function setUp()
    {
        $this->setBrowser('firefox');
        $this->setBrowserUrl('http://localhost:8192/');
    }

    /**
     * Test the title of the main page to see if it's correct.
     *
     * This function tests the main page and checks for the title so we know it's correct.
     *
     * @return Nothing
     */
    public function testTitle()
    {
        $this->url('http://localhost:8192/');
        $this->assertEquals('List of Posts', $this->title());
    }
}
?>

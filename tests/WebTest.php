<?php

namespace Pacifica\Search\Tests;

/**
 * Class WebTest
 *
 * Selenium-based in-browser functional tests for the Pacifica Search application
 */
class WebTest extends \PHPUnit_Extensions_Selenium2TestCase
{
    protected $coverageScriptUrl = 'http://localhost:8193/phpunit_coverage.php';

    /**
     * Setup the selenium test environment with firefox
     *
     * This function sets up the selenium browser with firefox and sets the url to
     * the correct localhost address for our testing environment.
     */
    protected function setUp()
    {
        $this->setBrowser('firefox');
        $this->setBrowserUrl('http://localhost/');
    }

    /**
     * Checks against catastrophic failure: This test passes if a request to the main GUI page renders the GUI
     *
     * @test
     */
    public function siteLoads()
    {
        $this->url('/');

        $this->assertEquals(
            $this->byCssSelector('fieldset[data-type="institution"] > h2')->text(),
            'Institution'
        );
    }
}

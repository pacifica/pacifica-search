<?php
    /**
     * File bootstrap.php
     * Bootstrap file for PHPUnit testing. This file is run at the beginning of each PHPUnit testing run.
     */

    // Autoloader for Pacifica-Search libraries, including phpunit-selenium, which is required for the functional
    // tests defined in tests/
    require_once(__DIR__ . '/../vendor/autoload.php');

    // Autoloader for the Pacifica Search Symfony application, required for the unit tests defined in application/tests
    require_once(__DIR__ . '/../application/vendor/autoload.php');

    // Base class for the Pacifica Search Symfony application's test cases
    require_once(__DIR__ . '/../application/tests/PacificaSearchBundle/TestCase.php');

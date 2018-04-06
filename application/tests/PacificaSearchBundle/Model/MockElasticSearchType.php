<?php

namespace Pacifica\Search\Tests\Model;

use PacificaSearchBundle\Model\ConventionalElasticSearchType;

/**
 * Class MockElasticSearchType
 *
 * Mock type for use in unit testing. This is necessary because PHPUnit's MockObject doesn't support static methods
 */
class MockElasticSearchType extends ConventionalElasticSearchType
{
}
<?php

namespace Pacifica\Search\Tests\Model;

use Pacifica\Search\Tests\TestCase;
use PacificaSearchBundle\Model\ConventionalElasticSearchType;
use PacificaSearchBundle\Model\InstrumentType;

class ConventionalElasticSearchTypeTest extends TestCase
{
    /**
     * This class is extremely simple so we can test all of its functions in a single simple function
     * @test
     */
    public function basicFunctionality()
    {
        $mockId = 1;
        $mockName = $expectedDisplayName = 'my_test_name';
        $mockClassName = 'MockClassName';
        $expectedMachineName = 'mock_class_name';
        $expectedTypeDisplayName = 'Mock Class Name';

        /** @var ConventionalElasticSearchType $stub */
        $stub = $this->getMockForAbstractClass(
            ConventionalElasticSearchType::class,
            [ $mockId, $mockName ],
            $mockClassName
        );

        // PHPUnit doesn't support namespaced mock class names (see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/295)
        // so we have to use a concrete implementation to test the case that the class's name is namespaced
        $typeInstanceWithNamespace = new InstrumentType($mockId, $mockName);

        // The display name of a ConventionalElasticSearchType instance is identical to the name of the record
        $this->assertEquals($expectedDisplayName, $stub->getDisplayName());

        // The machine name of a ConventionalElasticSearchType is the snake_case version of the CamelCase class name
        $this->assertEquals($expectedMachineName, $stub->getMachineName());

        // The display name of a ConventionalElasticSearchType (that is, for all instances of that type) is the space-separated class name
        $this->assertEquals($expectedTypeDisplayName, $stub->getTypeDisplayName());
        $this->assertEquals('Instrument Type', $typeInstanceWithNamespace->getTypeDisplayName());
    }
}